<?php
require_once __DIR__ . '/auth.php';
exigerConnexion();
$u = utilisateurCourant();
$nomComplet = trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''));
if ($nomComplet === '') { $nomComplet = $u['identifiant']; }
$prenom = trim((string) ($u['prenom'] ?? ''));
if ($prenom === '') { $prenom = explode(' ', $nomComplet)[0]; }

// Initiales pour l'avatar
$mots = preg_split('/\s+/', trim($nomComplet));
$initiales = strtoupper(mb_substr($mots[0] ?? '', 0, 1) . (isset($mots[1]) ? mb_substr($mots[1], 0, 1) : ''));
if ($initiales === '') { $initiales = strtoupper(mb_substr($u['identifiant'], 0, 2)); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>famiPortail</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/desktop.css">
<script>
  window.PORTAIL = {
    user:  <?= json_encode(['nom' => $nomComplet, 'role' => $u['role']], JSON_UNESCAPED_UNICODE) ?>,
    outils: <?= json_encode(outilsAutorises()) ?>
  };
</script>
</head>
<body class="home">

  <div class="statusbar">
    <div class="horloge">
      <div class="h" id="heure">--:--</div>
      <div class="d" id="date-jour"></div>
    </div>
    <div class="spacer"></div>
    <div class="user">
      <div class="avatar"><?= h($initiales) ?></div>
      <span class="nom"><?= h($nomComplet) ?></span>
    </div>
    <a class="deco" href="parametres.php" title="Paramètres" aria-label="Paramètres">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    </a>
    <a class="deco" href="logout.php" title="Se déconnecter" aria-label="Se déconnecter">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
    </a>
  </div>

  <div class="accueil">
    <h1>Bonjour, <?= h($prenom) ?></h1>
    <p>Vos outils Famiflora, réunis.</p>
  </div>

  <div class="springboard" id="springboard" role="list"></div>

<script src="assets/js/desktop.js" defer></script>
</body>
</html>
