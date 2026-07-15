<?php
/* ============================================================
   famiPortail — Base de données partagée (SQLite)
   Une seule base pour tout le bureau : utilisateurs + (à venir)
   données des outils. Stockée dans data/ (à monter sur le volume Railway).
   ============================================================ */

const PORTAIL_DB = __DIR__ . '/data/portail.sqlite';

function portailDb(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dossier = dirname(PORTAIL_DB);
    if (!is_dir($dossier)) {
        @mkdir($dossier, 0775, true);
    }

    $pdo = new PDO('sqlite:' . PORTAIL_DB);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL;');

    // Table des utilisateurs (comptes du bureau)
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        identifiant  TEXT UNIQUE NOT NULL,
        mot_de_passe TEXT NOT NULL,
        nom          TEXT NOT NULL DEFAULT '',
        prenom       TEXT NOT NULL DEFAULT '',
        role         TEXT NOT NULL DEFAULT 'employe',   -- 'admin' | 'employe'
        outils       TEXT NOT NULL DEFAULT '*',          -- '*' = tous, sinon CSV: 'famicom,cloud'
        actif        INTEGER NOT NULL DEFAULT 1,
        cree_le      TEXT NOT NULL
    )");

    // Compte admin initial (au premier lancement).
    // Mot de passe via variable d'env ADMIN_MDP, repli sur 'famiflora2026'.
    $nb = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    if ($nb === 0) {
        $mdp = getenv('ADMIN_MDP');
        if ($mdp === false || $mdp === '') {
            $mdp = 'famiflora2026';
        }
        $stmt = $pdo->prepare("INSERT INTO utilisateurs
            (identifiant, mot_de_passe, nom, prenom, role, outils, actif, cree_le)
            VALUES (?, ?, ?, ?, 'admin', '*', 1, ?)");
        $stmt->execute([
            'admin',
            password_hash($mdp, PASSWORD_DEFAULT),
            'Administrateur',
            '',
            date('c'),
        ]);
    }

    return $pdo;
}
