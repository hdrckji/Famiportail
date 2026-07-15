<?php
require_once __DIR__ . '/auth.php';

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
        try {
            $pdo = portailDb();
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE identifiant = ?");
            $stmt->execute([$identifiant]);
            $user = $stmt->fetch();

            $enAttente = $user && !empty($user['account_activation_pending']);
            if ($user && !$enAttente && !empty($user['mot_de_passe']) && password_verify($mdp, $user['mot_de_passe'])) {
                $outils = outilsPourUtilisateur($pdo, $user['id'], (string) ($user['role'] ?? ''));
                connecter($user, $outils);
                header('Location: ' . $suite);
                exit;
            }
            $erreur = 'Identifiant ou mot de passe incorrect.';
        } catch (Throwable $e) {
            $erreur = "Base de données indisponible. Vérifiez la connexion MySQL.";
        }
    }
}
$jeton = jetonCsrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Connexion — famiPortail</title>
<link rel="icon" href="assets/img/logo.png">
<style>
  :root { --font: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, Roboto, Helvetica, Arial, sans-serif; --brand: #1c6b41; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px;
    font-family: var(--font); -webkit-font-smoothing: antialiased; color: #fff;
    background:
      radial-gradient(1000px 640px at 12% -5%, rgba(96,176,120,0.28), transparent 55%),
      radial-gradient(1000px 800px at 100% 105%, rgba(18,88,58,0.55), transparent 55%),
      linear-gradient(158deg, #1d4331 0%, #143526 52%, #0c1f17 100%);
    background-attachment: fixed;
  }
  .carte {
    width: min(384px, 100%);
    background: rgba(255,255,255,0.09);
    border: 1px solid rgba(255,255,255,0.16);
    backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
    border-radius: 24px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.4);
    padding: 36px 30px 30px;
    text-align: center;
    animation: entree .35s ease-out;
  }
  @keyframes entree { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
  .logo {
    width: 66px; height: 66px; margin: 0 auto 16px; border-radius: 18px;
    background: rgba(255,255,255,0.95); display: grid; place-items: center;
    box-shadow: 0 12px 26px rgba(0,0,0,0.3);
  }
  .logo img { width: 46px; height: 46px; object-fit: contain; }
  h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -.01em; }
  h1 b { font-weight: 800; } h1 span { color: #8fe0ad; }
  .sous { margin: 6px 0 24px; font-size: 13.5px; color: rgba(255,255,255,0.6); }

  .champ { text-align: left; margin-bottom: 13px; }
  .champ label { display: block; font-size: 11.5px; font-weight: 650; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,0.66); margin-bottom: 6px; }
  .champ input {
    width: 100%; font-family: var(--font); font-size: 15px; color: #fff;
    padding: 12px 14px; border-radius: 12px;
    background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2);
  }
  .champ input::placeholder { color: rgba(255,255,255,0.4); }
  .champ input:focus { outline: none; border-color: #6cc492; background: rgba(255,255,255,0.12); }

  .btn {
    width: 100%; margin-top: 8px; font-family: var(--font); font-size: 15px; font-weight: 700; color: #08130d;
    padding: 13px; border: none; border-radius: 12px; cursor: pointer;
    background: linear-gradient(135deg, #7bd6a1, #34a866);
    transition: transform .12s, box-shadow .12s, filter .12s;
  }
  .btn:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(0,0,0,0.35); filter: brightness(1.03); }
  .btn:active { transform: translateY(0); }

  .err {
    background: rgba(220,90,80,0.16); border: 1px solid rgba(220,90,80,0.4); color: #ffd7d2;
    border-radius: 12px; padding: 10px 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; text-align: left;
  }
  .pied { margin-top: 22px; text-align: center; font-size: 12px; color: rgba(255,255,255,0.45); }
  @media (prefers-reduced-motion: reduce) { .carte { animation: none; } }
</style>
</head>
<body>

<main class="carte">
  <div class="logo"><img src="assets/img/logo.png" alt="Famiflora"></div>
  <h1><b>fami</b><span>Portail</span></h1>
  <p class="sous">Connectez-vous pour accéder à vos outils</p>

  <?php if ($erreur): ?><div class="err"><?= h($erreur) ?></div><?php endif; ?>

  <form method="POST" autocomplete="on">
    <input type="hidden" name="csrf" value="<?= h($jeton) ?>">
    <input type="hidden" name="suite" value="<?= h($suite) ?>">
    <div class="champ">
      <label for="id">Identifiant</label>
      <input id="id" name="identifiant" type="text" required autofocus placeholder="prenom.nom">
    </div>
    <div class="champ">
      <label for="mdp">Mot de passe</label>
      <input id="mdp" name="mot_de_passe" type="password" required placeholder="••••••••">
    </div>
    <button class="btn" type="submit">Se connecter</button>
  </form>

  <div class="pied">Outil interne Famiflora</div>
</main>

</body>
</html>
