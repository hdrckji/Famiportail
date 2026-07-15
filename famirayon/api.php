<?php
/* ============================================================
   famiRayon — Conseiller d'agencement de rayon (IA)
   Relais sécurisé vers l'API Claude : la clé reste sur le serveur.
   ============================================================ */

// 1. Clé API Anthropic — définie via la variable d'environnement
//    ANTHROPIC_API_KEY (ex. sur Railway). Reste toujours côté serveur.
$cleApi = getenv('ANTHROPIC_API_KEY');
define('CLE_API_ANTHROPIC', ($cleApi !== false && $cleApi !== '') ? $cleApi : '');

// 2. Code d'accès demandé aux employés (une seule fois par appareil).
//    Évite que des inconnus consomment votre crédit API.
//    Défini via CODE_ACCES_RAYON ; repli sur 'famiflora'. Mettez '' pour désactiver.
$codeAcces = getenv('CODE_ACCES_RAYON');
define('CODE_ACCES', ($codeAcces !== false) ? $codeAcces : 'famiflora');

const MODELE = 'claude-sonnet-5';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

if (CLE_API_ANTHROPIC === '') {
    http_response_code(500);
    echo json_encode(['erreur' => "Service non configuré : clé API Anthropic manquante."]);
    exit;
}

$donnees = json_decode(file_get_contents('php://input'), true);

if (CODE_ACCES !== '' && !hash_equals(CODE_ACCES, (string)($donnees['code'] ?? ''))) {
    http_response_code(403);
    echo json_encode(['erreur' => 'code_invalide']);
    exit;
}

$produit = trim($donnees['produit'] ?? '');
$contexte = trim($donnees['contexte'] ?? '');

if ($produit === '' || strlen($produit) > 200 || strlen($contexte) > 500) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Décrivez le produit ou le rayon (200 caractères max).']);
    exit;
}

// --- Construction du prompt ---
$systeme = <<<TXT
Tu es un expert en merchandising pour Famiflora, une grande jardinerie belge
(jardin, plantes, animalerie, piscines, décoration, mobilier de jardin, bricolage).

Ta mission : aider un employé de magasin à agencer son rayon pour encourager
les achats complémentaires (cross-merchandising). Le principe : à côté d'un
produit principal, placer des articles complémentaires que le client réalisera
avoir besoin en les voyant (ex. : à côté des piscines → produits d'entretien
de l'eau, épuisettes, bâches, jeux d'eau, transats).

Réponds UNIQUEMENT avec un objet JSON valide, sans texte avant ni après,
sans balises Markdown, avec exactement cette structure :
{
  "analyse": "1 à 2 phrases : le besoin du client qui achète ce produit et la logique d'ensemble",
  "suggestions": [
    {
      "produit": "nom de l'article complémentaire",
      "pourquoi": "en 1 phrase : pourquoi le client le prendra en le voyant",
      "placement": "conseil concret de placement (hauteur, distance, tête de gondole…)"
    }
  ],
  "conseil_general": "1 astuce de merchandising applicable à ce rayon"
}

Donne entre 5 et 8 suggestions, classées de la plus évidente à la plus astucieuse.
Reste réaliste : uniquement des articles qu'une jardinerie comme Famiflora vend.
Écris en français, avec un ton simple et concret pour un employé de terrain.
TXT;

$question = "Produit ou rayon principal : " . $produit;
if ($contexte !== '') {
    $question .= "\nContexte donné par l'employé (saison, place disponible, magasin…) : " . $contexte;
}

// --- Appel de l'API Claude (via cURL si dispo, sinon flux natifs PHP) ---
$corps = json_encode([
    'model' => MODELE,
    'max_tokens' => 1500,
    // Réflexion désactivée : réponse JSON rapide et prévisible dans le budget de tokens.
    // (Sur claude-sonnet-5, la réflexion adaptative est active par défaut si on l'omet.)
    'thinking' => ['type' => 'disabled'],
    'system' => $systeme,
    'messages' => [
        ['role' => 'user', 'content' => $question]
    ]
]);

$entetes = [
    'Content-Type: application/json',
    'x-api-key: ' . CLE_API_ANTHROPIC,
    'anthropic-version: 2023-06-01'
];

$reponse = false;
$statut = 0;
$erreurHttp = '';

if (function_exists('curl_init')) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $corps,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $entetes
    ]);
    $reponse = curl_exec($ch);
    $statut = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erreurHttp = curl_error($ch);
    curl_close($ch);
} else {
    $contexteHttp = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $entetes),
            'content' => $corps,
            'timeout' => 60,
            'ignore_errors' => true // récupère le corps même en cas d'erreur HTTP
        ]
    ]);
    $reponse = @file_get_contents('https://api.anthropic.com/v1/messages', false, $contexteHttp);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $statut = (int)$m[1];
    }
    if ($reponse === false) {
        $erreurHttp = 'connexion impossible';
    }
}

if ($reponse === false) {
    http_response_code(502);
    echo json_encode(['erreur' => 'Connexion à l\'IA impossible : ' . $erreurHttp]);
    exit;
}

$json = json_decode($reponse, true);

if ($statut !== 200) {
    $detail = $json['error']['message'] ?? 'Erreur inconnue';
    http_response_code(502);
    echo json_encode(['erreur' => "L'IA a répondu une erreur ($statut) : $detail"]);
    exit;
}

// --- Extraction du texte puis du JSON produit par le modèle ---
$texte = '';
foreach (($json['content'] ?? []) as $bloc) {
    if (($bloc['type'] ?? '') === 'text') {
        $texte .= $bloc['text'];
    }
}
// Nettoyage au cas où le modèle entoure de ```json ... ```
$texte = trim(preg_replace('/^```(json)?|```$/m', '', trim($texte)));

$resultat = json_decode($texte, true);
if (!is_array($resultat) || !isset($resultat['suggestions'])) {
    http_response_code(502);
    echo json_encode(['erreur' => "Réponse de l'IA illisible, réessayez."]);
    exit;
}

echo json_encode($resultat);
