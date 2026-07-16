<?php
/* ============================================================
   famiBotanic — API : analyse d'une photo de plante par l'IA
   Photo + nom (+ langue fr/nl) -> Claude vision -> attributs de la
   fiche (pictogrammes) dans la langue demandée. Protégé par la session.
   ============================================================ */
require_once __DIR__ . '/../auth.php';
exigerConnexion();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

$cle = getenv('ANTHROPIC_API_KEY');
if ($cle === false || $cle === '') {
    http_response_code(500);
    echo json_encode(['erreur' => "Service non configuré : clé API Anthropic manquante."]);
    exit;
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$nom = trim((string) ($in['nom'] ?? ''));
$photo = (string) ($in['photo'] ?? '');
$lang = (($in['lang'] ?? 'fr') === 'nl') ? 'nl' : 'fr';

if ($nom === '' && $photo === '') {
    http_response_code(400);
    echo json_encode(['erreur' => 'Fournissez au moins une photo ou un nom de plante.']);
    exit;
}

$content = [];
if (preg_match('#^data:(image/[a-zA-Z0-9.+-]+);base64,(.+)$#s', $photo, $m)) {
    $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $m[1], 'data' => $m[2]]];
}
$langue = $lang === 'nl' ? 'néerlandais (Nederlands)' : 'français';
$question = "Nom fourni par l'employé : " . ($nom !== '' ? $nom : "(non fourni — identifie depuis la photo)");
$content[] = ['type' => 'text', 'text' => $question];

$systeme = <<<TXT
Tu es botaniste pour Famiflora, une grande jardinerie belge (voir son guide des plantes famiflora.be/plantengids). On te fournit la PHOTO d'une plante et le NOM saisi par un employé. Tu produis les données d'une étiquette / affiche destinée aux CLIENTS.

Réponds UNIQUEMENT avec un objet JSON valide (aucun texte ni balise Markdown autour), avec EXACTEMENT ces clés :
{
  "nom_commun": "nom courant",
  "nom_latin": "nom scientifique",
  "type": "Arbre | Arbuste | Vivace | Conifère | Annuelle/Bisannuelle | Grimpante",
  "emplacement": "exposition courte (Soleil / Mi-ombre / Ombre / Soleil-mi-ombre)",
  "arrosage": "besoin en eau court (Faible / Modéré / Abondant)",
  "hauteur": "hauteur adulte (ex. 1–2 m)",
  "largeur": "largeur adulte (ex. 0,5–1 m)",
  "floraison": "période de floraison courte (ex. Mai–Août) ou vide",
  "couleur": "couleur de fleur principale ou vide",
  "cueillette": "période de récolte (ex. Sept.–Oct.) ou vide",
  "taille": "période de taille courte ou vide",
  "melifere": true/false,
  "comestible": true/false,
  "parfume": true/false,
  "toxique": true/false,
  "grimpante": true/false,
  "purificatrice": true/false,
  "antilimaces": true/false,
  "fruit": true/false,
  "gel": true/false,
  "persistant": true/false
}

Règles :
- Toutes les valeurs texte doivent être en {$langue}, TRÈS COURTES (1 à 3 mots), pour un affichage par pictogrammes (peu de texte).
- Les clés booléennes indiquent si la caractéristique s'applique (mellifère, comestible, parfumé, toxique, grimpante, purificatrice d'air, résistant aux limaces, porte des fruits, résistant au gel, feuillage persistant).
- N'invente pas de chiffres précis : donne des fourchettes. Laisse une valeur vide ("") si non pertinente.
TXT;

$corps = json_encode([
    'model' => 'claude-sonnet-5',
    'max_tokens' => 1000,
    'thinking' => ['type' => 'disabled'],
    'system' => $systeme,
    'messages' => [['role' => 'user', 'content' => $content]],
]);

$entetes = ['Content-Type: application/json', 'x-api-key: ' . $cle, 'anthropic-version: 2023-06-01'];
$rep = false; $statut = 0; $errHttp = '';
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $corps, CURLOPT_TIMEOUT => 60, CURLOPT_HTTPHEADER => $entetes]);
    $rep = curl_exec($ch); $statut = curl_getinfo($ch, CURLINFO_HTTP_CODE); $errHttp = curl_error($ch); curl_close($ch);
} else {
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $entetes), 'content' => $corps, 'timeout' => 60, 'ignore_errors' => true]]);
    $rep = @file_get_contents('https://api.anthropic.com/v1/messages', false, $ctx);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $mm)) { $statut = (int) $mm[1]; }
    if ($rep === false) { $errHttp = 'connexion impossible'; }
}

if ($rep === false) { http_response_code(502); echo json_encode(['erreur' => "Connexion à l'IA impossible : " . $errHttp]); exit; }
$j = json_decode($rep, true);
if ($statut !== 200) { http_response_code(502); echo json_encode(['erreur' => "L'IA a répondu une erreur ($statut) : " . ($j['error']['message'] ?? 'inconnue')]); exit; }
$texte = '';
foreach (($j['content'] ?? []) as $b) { if (($b['type'] ?? '') === 'text') { $texte .= $b['text']; } }
$texte = trim(preg_replace('/^```(json)?|```$/m', '', trim($texte)));
$data = json_decode($texte, true);
if (!is_array($data)) { http_response_code(502); echo json_encode(['erreur' => "Réponse de l'IA illisible, réessayez."]); exit; }
echo json_encode($data, JSON_UNESCAPED_UNICODE);
