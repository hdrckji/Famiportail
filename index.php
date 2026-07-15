<?php
require_once __DIR__ . '/auth.php';
exigerConnexion();
$u = utilisateurCourant();
$nomAffiche = trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''));
if ($nomAffiche === '') { $nomAffiche = $u['identifiant']; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>famiPortail — Bureau Famiflora</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/nature.css">
<link rel="stylesheet" href="assets/css/desktop.css">
<script>
  window.PORTAIL = {
    user:  <?= json_encode(['nom' => $nomAffiche, 'role' => $u['role']], JSON_UNESCAPED_UNICODE) ?>,
    outils: <?= json_encode(outilsAutorises()) ?>
  };
</script>
</head>
<body class="bureau">

<!-- Décor nature immersif -->
<div class="scene-nature" aria-hidden="true">
  <div class="soleil"></div>
  <div class="colline c3"></div>
  <div class="colline c2"></div>
  <div class="colline c1"></div>
  <div class="feuillage g"></div><div class="feuillage d"></div>
  <div class="pollen">
    <span style="left:10%; animation-duration:17s; animation-delay:0s;"></span>
    <span style="left:24%; animation-duration:21s; animation-delay:4s;"></span>
    <span style="left:40%; animation-duration:15s; animation-delay:7s;"></span>
    <span style="left:58%; animation-duration:23s; animation-delay:2s;"></span>
    <span style="left:73%; animation-duration:18s; animation-delay:9s;"></span>
    <span style="left:88%; animation-duration:16s; animation-delay:5s;"></span>
  </div>
</div>

<!-- Le bureau -->
<main id="bureau" aria-label="Bureau famiPortail">
  <!-- La fée, en petite tête, en haut à droite -->
  <button class="fee-tete fee-coin" id="feeCoin" title="Coucou 🌿" aria-label="La fée Famiflora">
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
  </button>

  <div id="icones-bureau" class="icones-bureau" role="list"></div>
</main>

<!-- Lanceur d'applications -->
<div id="menu-demarrer" class="menu-demarrer" hidden>
  <div class="menu-entete">
    <img src="assets/img/logo.png" alt="Famiflora">
    <div>
      <strong><?= h($nomAffiche) ?></strong>
      <span><?= $u['role'] === 'admin' ? 'Administrateur' : 'Employé' ?> · Famiflora</span>
    </div>
    <a href="logout.php" class="menu-deconnexion" title="Se déconnecter" aria-label="Se déconnecter">⏻</a>
  </div>
  <div id="menu-liste" class="menu-liste" role="menu"></div>
  <div class="menu-pied">🌿 Bon travail sur famiPortail</div>
</div>

<!-- Dock (barre d'applications) -->
<footer id="barre-taches" class="barre-taches">
  <button id="btn-demarrer" class="btn-demarrer" aria-haspopup="true" aria-expanded="false" title="Applications">
    <img src="assets/img/logo.png" alt="">
    <span>Applications</span>
  </button>
  <div id="taches-ouvertes" class="taches-ouvertes" role="list"></div>
  <button id="horloge" class="horloge" title="Date et heure">
    <span id="heure">--:--</span>
    <span id="date-jour"></span>
  </button>
</footer>

<script src="assets/js/desktop.js" defer></script>
</body>
</html>
