<?php
require_once __DIR__ . '/auth.php';

// Déjà connecté ? → au bureau.
if (estConnecte()) {
    header('Location: index.php');
    exit;
}

$suite = $_GET['suite'] ?? $_POST['suite'] ?? 'index.php';
if (!preg_match('#^[A-Za-z0-9_./?=&%-]*$#', (string) $suite) || strpos((string) $suite, '//') !== false) {
    $suite = 'index.php';
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValide($_POST['csrf'] ?? null)) {
        $erreur = 'Session expirée, réessayez.';
    } else {
        $identifiant = trim($_POST['identifiant'] ?? '');
        $mdp = (string) ($_POST['mot_de_passe'] ?? '');
        $stmt = portailDb()->prepare("SELECT * FROM utilisateurs WHERE identifiant = ? AND actif = 1");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();
        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            connecter($user);
            header('Location: ' . $suite);
            exit;
        }
        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}
$jeton = jetonCsrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — famiPortail</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/nature.css">
<style>
  body { min-height: 100vh; display: grid; place-items: center; overflow: hidden; }
  .carte-login {
    position: relative; z-index: 2;
    width: min(400px, 92vw);
    background: rgba(255,255,255,0.82);
    backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: 26px;
    box-shadow: 0 24px 60px rgba(20,50,15,0.35);
    padding: 30px 28px 26px;
    text-align: center;
    animation: entree 0.4s ease-out;
  }
  @keyframes entree { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .carte-login .avatar-fee {
    width: 84px; height: 84px; margin: -66px auto 6px;
    box-shadow: 0 10px 26px rgba(20,50,15,0.3);
  }
  .carte-login h1 {
    font-family: var(--font-display); font-weight: 600;
    color: var(--vert-fonce); font-size: 1.5rem; margin-top: 6px;
  }
  .carte-login h1 span { color: var(--vert-feuille); }
  .carte-login .sous { color: var(--encre-douce); font-size: 0.9rem; margin: 4px 0 20px; }
  .champ { text-align: left; margin-bottom: 14px; }
  .champ label { display: block; font-weight: 800; font-size: 0.82rem; color: var(--vert-fonce); margin-bottom: 5px; }
  .champ input {
    width: 100%; font-family: var(--font-body); font-size: 1rem;
    padding: 0.7rem 1rem; border: 2px solid var(--vert-pale); border-radius: 12px;
    background: rgba(255,255,255,0.9);
  }
  .champ input:focus { border-color: var(--vert-feuille); outline: none; }
  .btn-login {
    width: 100%; margin-top: 6px;
    font-family: var(--font-body); font-weight: 800; font-size: 1.05rem;
    color: #fff; border: none; border-radius: 999px; padding: 0.8rem;
    background: linear-gradient(135deg, var(--vert-feuille), var(--vert-fonce));
    cursor: pointer; transition: transform 0.12s, box-shadow 0.12s;
  }
  .btn-login:hover { transform: translateY(-1px); box-shadow: var(--ombre); }
  .err { background: #FBE9E7; color: #7d2d26; border-radius: 12px; padding: 0.6rem 0.9rem; font-weight: 700; font-size: 0.85rem; margin-bottom: 16px; }
  .pied-login { position: fixed; bottom: 14px; left: 0; right: 0; text-align: center; color: #22461a; font-size: 0.78rem; z-index: 2; font-weight: 700; text-shadow: 0 1px 3px rgba(255,255,255,0.4); }
</style>
</head>
<body>

<!-- Décor nature -->
<div class="scene-nature" aria-hidden="true">
  <div class="soleil"></div>
  <div class="colline c3"></div>
  <div class="colline c2"></div>
  <div class="colline c1"></div>
  <div class="feuillage g"></div><div class="feuillage d"></div>
  <div class="pollen">
    <span style="left:12%; animation-duration:16s; animation-delay:0s;"></span>
    <span style="left:28%; animation-duration:20s; animation-delay:3s;"></span>
    <span style="left:47%; animation-duration:14s; animation-delay:6s;"></span>
    <span style="left:66%; animation-duration:22s; animation-delay:1s;"></span>
    <span style="left:82%; animation-duration:18s; animation-delay:8s;"></span>
    <span style="left:92%; animation-duration:15s; animation-delay:4s;"></span>
  </div>
</div>

<main class="carte-login">
  <div class="fee-tete avatar-fee">
    <svg class="fee-tete-svg" viewBox="190 30 220 300" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <defs>
        <linearGradient id="tSkin" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FBE1C4"/><stop offset="1" stop-color="#F3CBA3"/></linearGradient>
        <linearGradient id="tHair" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#94512F"/><stop offset="1" stop-color="#6E3A22"/></linearGradient>
      </defs>
      <circle cx="300" cy="220" r="92" fill="url(#tSkin)"/>
      <circle cx="300" cy="72" r="37" fill="url(#tHair)"/>
      <path d="M326 52 Q 342 34 364 36 Q 356 58 334 64 Z" fill="#8DC63F"/>
      <path d="M204 258 Q 184 112 300 88 Q 416 112 396 258 Q 386 262 380 252 Q 386 202 356 178 Q 336 158 300 152 Q 264 158 244 178 Q 214 202 220 252 Q 214 262 204 258 Z" fill="url(#tHair)"/>
      <path d="M222 236 Q 216 262 226 284 Q 236 274 234 248 Q 228 238 222 236 Z" fill="url(#tHair)"/>
      <path d="M378 236 Q 384 262 374 284 Q 364 274 366 248 Q 372 238 378 236 Z" fill="url(#tHair)"/>
      <path d="M241 214 Q 262 199 283 212" stroke="#8DC63F" stroke-width="7" stroke-linecap="round" fill="none" opacity="0.8"/>
      <path d="M317 212 Q 338 199 359 214" stroke="#8DC63F" stroke-width="7" stroke-linecap="round" fill="none" opacity="0.8"/>
      <g class="fee-yeux">
        <circle cx="262" cy="232" r="20" fill="#1E4020"/><circle cx="338" cy="232" r="20" fill="#1E4020"/>
        <circle cx="269" cy="224" r="7" fill="#fff"/><circle cx="345" cy="224" r="7" fill="#fff"/>
      </g>
      <path d="M242 218 Q 248 210 256 207 M344 207 Q 352 210 358 218" stroke="#1E4020" stroke-width="3.2" stroke-linecap="round" fill="none"/>
      <ellipse cx="230" cy="268" rx="17" ry="10.5" fill="#F0A96B" opacity="0.6"/><ellipse cx="370" cy="268" rx="17" ry="10.5" fill="#F0A96B" opacity="0.6"/>
      <path d="M280 270 Q 300 292 320 270 Q 312 286 300 286 Q 288 286 280 270 Z" fill="#1A1A1A"/>
    </svg>
  </div>

  <h1>fami<span>Portail</span></h1>
  <p class="sous">Connectez-vous pour entrer dans votre bureau 🌿</p>

  <?php if ($erreur): ?><div class="err">⚠️ <?= h($erreur) ?></div><?php endif; ?>

  <form method="POST" autocomplete="on">
    <input type="hidden" name="csrf" value="<?= h($jeton) ?>">
    <input type="hidden" name="suite" value="<?= h($suite) ?>">
    <div class="champ">
      <label for="id">Identifiant</label>
      <input id="id" name="identifiant" type="text" required autofocus placeholder="ex. : prenom.nom">
    </div>
    <div class="champ">
      <label for="mdp">Mot de passe</label>
      <input id="mdp" name="mot_de_passe" type="password" required placeholder="••••••••">
    </div>
    <button class="btn-login" type="submit">Entrer dans le bureau →</button>
  </form>
</main>

<div class="pied-login">famiPortail · Outil interne Famiflora</div>

</body>
</html>
