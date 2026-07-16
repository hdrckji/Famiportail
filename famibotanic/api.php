<?php
/* ============================================================
   famiBotanic — API : IA (analyse photo) + persistance des affiches
   Actions (JSON "action") : analyser | enregistrer | lister | obtenir
   Base MySQL partagée du portail (table famibotanic_affiches).
   ============================================================ */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
exigerConnexion();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? 'analyser';

/* ---------- Table des affiches enregistrées ---------- */
function tableAffiches(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS famibotanic_affiches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom_commun VARCHAR(255) NOT NULL DEFAULT '',
        nom_latin  VARCHAR(255) NOT NULL DEFAULT '',
        langue     VARCHAR(2) NOT NULL DEFAULT 'fr',
        donnees    JSON NOT NULL,
        auteur     VARCHAR(255) NOT NULL DEFAULT '',
        auteur_id  INT NULL,
        cree_le    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        maj        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/* ============ ENREGISTRER / ACTUALISER ============ */
if ($action === 'enregistrer') {
    try {
        $pdo = portailDb();
        tableAffiches($pdo);
        $u = utilisateurCourant();
        $auteur = trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')); if ($auteur === '') { $auteur = $u['identifiant'] ?? ''; }
        $etat = $in['etat'] ?? [];
        $id = isset($in['id']) && $in['id'] ? (int) $in['id'] : 0;
        $nomC = (string) ($etat['nom_commun'] ?? '');
        $nomL = (string) ($etat['nom_latin'] ?? '');
        $langue = (($etat['langue'] ?? 'fr') === 'nl') ? 'nl' : 'fr';
        $donnees = json_encode($etat, JSON_UNESCAPED_UNICODE);

        if ($id > 0) {
            $st = $pdo->prepare("UPDATE famibotanic_affiches SET nom_commun=?, nom_latin=?, langue=?, donnees=?, maj=CURRENT_TIMESTAMP WHERE id=?");
            $st->execute([$nomC, $nomL, $langue, $donnees, $id]);
        } else {
            $st = $pdo->prepare("INSERT INTO famibotanic_affiches (nom_commun, nom_latin, langue, donnees, auteur, auteur_id) VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute([$nomC, $nomL, $langue, $donnees, $auteur, $u['id'] ?? null]);
            $id = (int) $pdo->lastInsertId();
        }
        echo json_encode(['ok' => true, 'id' => $id, 'auteur' => $auteur]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Enregistrement impossible : ' . $e->getMessage()]);
    }
    exit;
}

/* ============ LISTER (accessible à tous) ============ */
if ($action === 'lister') {
    try {
        $pdo = portailDb();
        tableAffiches($pdo);
        $rows = $pdo->query("SELECT id, nom_commun, nom_latin, auteur, langue,
                              DATE_FORMAT(cree_le,'%d/%m/%Y %H:%i') AS date
                              FROM famibotanic_affiches ORDER BY maj DESC LIMIT 100")->fetchAll();
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['erreur' => $e->getMessage()]);
    }
    exit;
}

/* ============ OUVRIR une affiche ============ */
if ($action === 'obtenir') {
    try {
        $pdo = portailDb();
        tableAffiches($pdo);
        $st = $pdo->prepare("SELECT donnees FROM famibotanic_affiches WHERE id=?");
        $st->execute([(int) ($in['id'] ?? 0)]);
        $row = $st->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['erreur' => 'Affiche introuvable']); exit; }
        $etat = json_decode($row['donnees'], true) ?: [];
        echo json_encode($etat, JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['erreur' => $e->getMessage()]);
    }
    exit;
}

/* ============ SUPPRIMER ============ */
if ($action === 'supprimer') {
    try {
        $pdo = portailDb();
        tableAffiches($pdo);
        $st = $pdo->prepare("DELETE FROM famibotanic_affiches WHERE id=?");
        $st->execute([(int) ($in['id'] ?? 0)]);
        echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['erreur' => $e->getMessage()]);
    }
    exit;
}

/* ============ ANALYSER (IA vision) ============ */
$cle = getenv('ANTHROPIC_API_KEY');
if ($cle === false || $cle === '') {
    http_response_code(500);
    echo json_encode(['erreur' => "Service non configuré : clé API Anthropic manquante."]);
    exit;
}

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
$content[] = ['type' => 'text', 'text' => "Nom fourni par l'employé : " . ($nom !== '' ? $nom : "(non fourni — identifie depuis la photo)")];

$systeme = <<<TXT
Tu es botaniste pour Famiflora, une grande jardinerie belge (guide famiflora.be/plantengids). On te fournit la PHOTO d'une plante et le NOM saisi par un employé. Tu produis les données d'une étiquette/affiche pour les CLIENTS.

Réponds UNIQUEMENT avec un objet JSON valide (aucun texte ni Markdown), avec EXACTEMENT ces clés :
{"nom_commun":"","nom_latin":"","type":"Arbre|Arbuste|Vivace|Conifère|Annuelle/Bisannuelle|Grimpante","emplacement":"Soleil/Mi-ombre/Ombre","arrosage":"Faible/Modéré/Abondant","hauteur":"ex. 1–2 m","largeur":"ex. 0,5–1 m","floraison":"ex. Mai–Août ou vide","couleur":"couleur fleur ou vide","cueillette":"ex. Sept.–Oct. ou vide","taille":"période de taille ou vide","melifere":true,"comestible":false,"parfume":false,"toxique":false,"grimpante":false,"purificatrice":false,"antilimaces":false,"fruit":false,"gel":true,"persistant":false}

Règles : valeurs texte en {$langue}, TRÈS COURTES (1–3 mots), pour un affichage par pictogrammes. Booléens = la caractéristique s'applique ou non. N'invente pas de chiffres précis (fourchettes). Laisse "" si non pertinent.
TXT;

$corps = json_encode([
    'model' => 'claude-sonnet-5', 'max_tokens' => 1000, 'thinking' => ['type' => 'disabled'],
    'system' => $systeme, 'messages' => [['role' => 'user', 'content' => $content]],
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
