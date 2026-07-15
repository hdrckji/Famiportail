<?php
/* ============================================================
   famiCom — API de gestion des annonces (PHP + SQLite)
   Compatible hébergement mutualisé IONOS (aucune config MySQL requise)
   ============================================================ */

// ⚠️ CHANGEZ CE CODE avant la mise en ligne !
// C'est le code que les publicateurs devront saisir pour publier.
const CODE_PUBLICATION = 'famiflora2026';

header('Content-Type: application/json; charset=utf-8');

// --- Connexion / création de la base SQLite ---
$dossierData = __DIR__ . '/data';
if (!is_dir($dossierData)) {
    mkdir($dossierData, 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $dossierData . '/famicom.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS annonces (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre TEXT NOT NULL,
        categorie TEXT NOT NULL,
        auteur TEXT NOT NULL,
        texte TEXT NOT NULL,
        epinglee INTEGER DEFAULT 0,
        date TEXT NOT NULL
    )");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Base de données indisponible']);
    exit;
}

// --- Première utilisation : annonce de bienvenue ---
$nb = (int)$pdo->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
if ($nb === 0) {
    $stmt = $pdo->prepare("INSERT INTO annonces (titre, categorie, auteur, texte, epinglee, date)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'Bienvenue sur famiCom, votre nouvelle source officielle !',
        'Direction',
        'Direction générale',
        "famiCom devient le canal unique des communications internes Famiflora. Toute information publiée ici est vérifiée et validée. En cas de doute, c'est famiCom qui fait foi !",
        1,
        date('Y-m-d')
    ]);
}

$methode = $_SERVER['REQUEST_METHOD'];

// --- GET : liste des annonces ---
if ($methode === 'GET') {
    $lignes = $pdo->query("SELECT * FROM annonces ORDER BY epinglee DESC, date DESC, id DESC")
                  ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($lignes as &$l) { $l['epinglee'] = (bool)$l['epinglee']; }
    echo json_encode($lignes);
    exit;
}

// --- POST : publier une annonce ---
if ($methode === 'POST') {
    $donnees = json_decode(file_get_contents('php://input'), true);

    if (!is_array($donnees) || !hash_equals(CODE_PUBLICATION, (string)($donnees['code'] ?? ''))) {
        http_response_code(403);
        echo json_encode(['erreur' => 'Code de publication incorrect']);
        exit;
    }

    $titre  = trim($donnees['titre']  ?? '');
    $auteur = trim($donnees['auteur'] ?? '');
    $texte  = trim($donnees['texte']  ?? '');
    $cat    = $donnees['categorie']   ?? '';
    $categoriesValides = ['Direction', 'RH', 'Magasins', 'IT', 'Événements'];

    // Longueur en caractères, sans dépendre de l'extension mbstring
    $longueur = fn($s) => function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);

    if ($titre === '' || $auteur === '' || $texte === '' || !in_array($cat, $categoriesValides, true)
        || $longueur($titre) > 120 || $longueur($auteur) > 80 || $longueur($texte) > 5000) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Formulaire incomplet ou invalide']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO annonces (titre, categorie, auteur, texte, epinglee, date)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $cat, $auteur, $texte, !empty($donnees['epinglee']) ? 1 : 0, date('Y-m-d')]);

    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['erreur' => 'Méthode non autorisée']);
