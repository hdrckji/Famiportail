<?php
/* ============================================================
   famiPortail — Connexion à la base MySQL PARTAGÉE
   Le portail se connecte à la base de famiformation (utilisateurs
   déjà présents). Il n'ALTÈRE PAS la table `utilisateurs` : les droits
   d'accès aux outils sont gérés dans une table à part `portail_acces`.
   ============================================================ */

/**
 * Lit la configuration MySQL depuis l'environnement (Railway).
 * Ordre : DATABASE_URL / MYSQL_URL (mysql://user:pass@host:port/db),
 * puis variables individuelles Railway (MYSQLHOST…) ou génériques (DB_HOST…).
 * @return array{0:string,1:int,2:string,3:string,4:string} [host,port,db,user,pass]
 */
function configMysql(): array
{
    $url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: '';
    if ($url !== '') {
        $p = parse_url($url);
        if ($p && !empty($p['host'])) {
            return [
                $p['host'],
                (int) ($p['port'] ?? 3306),
                ltrim($p['path'] ?? '', '/'),
                urldecode($p['user'] ?? ''),
                urldecode($p['pass'] ?? ''),
            ];
        }
    }
    $env = function (array $cles, string $defaut = '') {
        foreach ($cles as $c) {
            $v = getenv($c);
            if ($v !== false && $v !== '') {
                return $v;
            }
        }
        return $defaut;
    };
    return [
        $env(['MYSQLHOST', 'DB_HOST', 'MYSQL_HOST'], 'localhost'),
        (int) $env(['MYSQLPORT', 'DB_PORT', 'MYSQL_PORT'], '3306'),
        $env(['MYSQLDATABASE', 'DB_NAME', 'MYSQL_DATABASE'], ''),
        $env(['MYSQLUSER', 'DB_USER', 'MYSQL_USER'], ''),
        $env(['MYSQLPASSWORD', 'DB_PASSWORD', 'DB_PASS', 'MYSQL_PASSWORD'], ''),
    ];
}

function portailDb(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    [$host, $port, $db, $user, $pass] = configMysql();
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Table PROPRE au portail : droits d'accès aux outils (n'altère pas `utilisateurs`).
    // outils : '*' = tous, sinon CSV d'ids ('famicom,cloud'). Absence de ligne = défaut par rôle.
    $pdo->exec("CREATE TABLE IF NOT EXISTS portail_acces (
        user_id INT UNSIGNED PRIMARY KEY,
        outils  VARCHAR(255) NOT NULL DEFAULT '*',
        maj     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return $pdo;
}

/**
 * Outils autorisés pour un utilisateur : sa ligne portail_acces si elle existe,
 * sinon un défaut selon son rôle famiformation.
 */
function outilsPourUtilisateur(PDO $pdo, $userId, string $role): string
{
    try {
        $st = $pdo->prepare("SELECT outils FROM portail_acces WHERE user_id = ?");
        $st->execute([$userId]);
        $row = $st->fetch();
        if ($row && trim((string) $row['outils']) !== '') {
            return trim((string) $row['outils']);
        }
    } catch (Throwable $e) {
        // table pas encore prête : on retombe sur le défaut par rôle
    }
    $r = strtolower(trim($role));
    if (in_array($r, ['admin', 'superadmin', 'teamcoach'], true)) {
        return '*';
    }
    return 'famicom,famirayon,cloud';
}
