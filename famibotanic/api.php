<?php
/* ============================================================
   famiBotanic — API : analyse d'une photo de plante par l'IA
   L'employé fournit une photo + le nom ; Claude (vision) rédige
   la fiche client. Protégé par la session du portail.
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
$photo = (string) ($in['photo'] ?? '');   // dataURL "data:image/...;base64,...."
$contexte = trim((string) ($in['contexte'] ?? ''));

if ($nom === '' && $photo === '') {
    http_response_code(400);
    echo json_encode(['erreur' => 'Fournissez au moins une photo ou un nom de plante.']);
    exit;
}

// Contenu du message : la photo (si fournie) + le texte
$content = [];
if (preg_match('#^data:(image/[a-zA-Z0-9.+-]+);base64,(.+)$#s', $photo, $m)) {
    $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $m[1], 'data' => $m[2]]];
}
$question = "Nom fourni par l'employé : " . ($nom !== '' ? $nom : "(non fourni — identifie depuis la photo)");
if ($contexte !== '') {
    $question .= "\nContexte : " . $contexte;
}
$content[] = ['type' => 'text', 'text' => $question];

$systeme = <<<TXT
Tu es botaniste pour Famiflora, une grande jardinerie belge. On te fournit la PHOTO d'une plante et le NOM saisi par un employé. Rédige une fiche destinée aux CLIENTS, pour une affiche en magasin.

Réponds UNIQUEMENT avec un objet JSON valide (aucun texte avant/après, aucune balise Markdown), avec EXACTEMENT ces clés :
{
  "nom_commun": "nom courant en français",
  "nom_latin": "nom scientifique",
  "famille": "famille botanique",
  "arrosage": "bref (ex. Modéré, 1x/semaine)",
  "lumiere": "bref (ex. Lumière vive sans soleil direct)",
  "difficulte": "Facile | Moyen | Difficile",
  "taille": "taille adulte (ex. 1 à 2 m)",
  "origine": "région d'origine",
  "toxicite": "ex. Toxique en cas d'ingestion (animaux) / Non toxique",
  "floraison": "période, ou 'Rare en intérieur'",
  "conseil": "une astuce d'entretien concrète"
}

Règles : écris en français, clair et concret pour un client. N'invente pas de chiffres précis : donne des fourchettes. Si le nom fourni et la photo se contredisent, fie-toi à la photo mais reste prudent.
TXT;

$corps = json_encode([
    'model' => 'claude-sonnet-5',
    'max_tokens' => 1200,
    'thinking' => ['type' => 'disabled'],
    'system' => $systeme,
    'messages' => [['role' => 'user', 'content' => $content]],
]);

$entetes = [
    'Content-Type: application/json',
    'x-api-key: ' . $cle,
    'anthropic-version: 2023-06-01',
];

$rep = false; $statut = 0; $errHttp = '';
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $corps, CURLOPT_TIMEOUT => 60, CURLOPT_HTTPHEADER => $entetes,
    ]);
    $rep = curl_exec($ch);
    $statut = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errHttp = curl_error($ch);
    curl_close($ch);
} else {
    $ctx = stream_context_create(['http' => [
        'method' => 'POST', 'header' => implode("\r\n", $entetes),
        'content' => $corps, 'timeout' => 60, 'ignore_errors' => true,
    ]]);
    $rep = @file_get_contents('https://api.anthropic.com/v1/messages', false, $ctx);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $mm)) {
        $statut = (int) $mm[1];
    }
    if ($rep === false) { $errHttp = 'connexion impossible'; }
}

if ($rep === false) {
    http_response_code(502);
    echo json_encode(['erreur' => "Connexion à l'IA impossible : " . $errHttp]);
    exit;
}

$j = json_decode($rep, true);
if ($statut !== 200) {
    http_response_code(502);
    echo json_encode(['erreur' => "L'IA a répondu une erreur ($statut) : " . ($j['error']['message'] ?? 'inconnue')]);
    exit;
}

$texte = '';
foreach (($j['content'] ?? []) as $b) {
    if (($b['type'] ?? '') === 'text') { $texte .= $b['text']; }
}
$texte = trim(preg_replace('/^```(json)?|```$/m', '', trim($texte)));
$data = json_decode($texte, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['erreur' => "Réponse de l'IA illisible, réessayez."]);
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
