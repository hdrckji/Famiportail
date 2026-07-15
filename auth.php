<?php
/* ============================================================
   famiPortail — Authentification & session partagée
   Inclus par le bureau (index.php) et par chaque outil à protéger.
   ============================================================ */

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('famiportail');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' activé automatiquement derrière le HTTPS de Railway
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on')
                      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
    ]);
    session_start();
}

/* ---- État de connexion ---- */
function estConnecte(): bool
{
    return !empty($_SESSION['user_id']);
}

function utilisateurCourant(): ?array
{
    if (!estConnecte()) {
        return null;
    }
    return [
        'id'         => $_SESSION['user_id'],
        'identifiant'=> $_SESSION['identifiant'] ?? '',
        'nom'        => $_SESSION['nom'] ?? '',
        'prenom'     => $_SESSION['prenom'] ?? '',
        'role'       => $_SESSION['role'] ?? 'employe',
        'outils'     => $_SESSION['outils'] ?? '*',
    ];
}

/* ---- Liste des outils autorisés pour l'utilisateur ('*' = tous) ---- */
function outilsAutorises(): string
{
    return $_SESSION['outils'] ?? '';
}
function peutAcceder(string $outilId): bool
{
    $o = outilsAutorises();
    if ($o === '*') {
        return true;
    }
    $liste = array_filter(array_map('trim', explode(',', $o)));
    return in_array($outilId, $liste, true);
}

/* ---- Garde : redirige vers le login si non connecté ---- */
function exigerConnexion(): void
{
    if (!estConnecte()) {
        $cible = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: login.php?suite=' . $cible);
        exit;
    }
}

/* ---- Ouvre la session pour un utilisateur (après vérif mot de passe) ---- */
function connecter(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['identifiant'] = $user['identifiant'];
    $_SESSION['nom']         = $user['nom'] ?? '';
    $_SESSION['prenom']      = $user['prenom'] ?? '';
    $_SESSION['role']        = $user['role'] ?? 'employe';
    $_SESSION['outils']      = $user['outils'] ?? '*';
}

function deconnecter(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---- CSRF ---- */
function jetonCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfValide(?string $jeton): bool
{
    return !empty($_SESSION['csrf']) && is_string($jeton) && hash_equals($_SESSION['csrf'], $jeton);
}

/* ---- Échappement HTML ---- */
function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
