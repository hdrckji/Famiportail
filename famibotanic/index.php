<?php
require_once __DIR__ . '/../auth.php';
exigerConnexion();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>famiBotanic — affiches plantes</title>
<style>
  :root{--bg:#0e1712;--panel:#131e18;--panel2:#18241d;--border:#243228;--ink:#e9f1eb;--soft:#94a89b;--faint:#66786d;--brand:#4fb06a;--brand-deep:#2e8b57;--font:-apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,Roboto,Helvetica,Arial,sans-serif;}
  *{box-sizing:border-box;} body{margin:0;font-family:var(--font);background:var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased;}
  button,select,input{font-family:inherit;}
  .topbar{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border);}
  .retour{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;color:var(--soft);text-decoration:none;background:var(--panel2);border:1px solid var(--border);} .retour:hover{color:var(--ink);}
  .mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(150deg,var(--brand),var(--brand-deep));display:grid;place-items:center;color:#06130c;}
  .t{font-weight:700}.t span{color:var(--brand)}.t .s{font-size:12px;font-weight:500;color:var(--faint)}
  .btn{border:none;border-radius:10px;padding:9px 15px;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
  .btn-brand{background:linear-gradient(135deg,#5fd07f,var(--brand-deep));color:#06130c;} .btn-ghost{background:var(--panel2);color:var(--ink);border:1px solid var(--border);}
  .push{margin-left:auto;} .seg{display:inline-flex;background:var(--panel2);border:1px solid var(--border);border-radius:10px;overflow:hidden;} .seg button{border:none;background:transparent;color:var(--soft);padding:8px 13px;font-weight:700;font-size:13px;cursor:pointer;} .seg button.on{background:var(--brand);color:#06130c;}
  .wrap{display:grid;grid-template-columns:340px 1fr;height:calc(100vh - 60px);}
  .panneau{border-right:1px solid var(--border);overflow-y:auto;padding:16px;} .aperc{overflow-y:auto;padding:26px;display:grid;place-items:start center;background:#0b120e;}
  .grp{margin-bottom:16px;} .grp h3{font-size:11.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--soft);margin:0 0 9px;}
  label.champ{display:block;font-size:12px;font-weight:700;color:var(--soft);margin:10px 0 5px;}
  input[type=text],input[type=search]{width:100%;background:var(--panel2);border:1px solid var(--border);color:var(--ink);border-radius:10px;padding:10px 12px;font:inherit;font-size:14px;} input:focus{outline:none;border-color:var(--brand);}
  .drop{border:2px dashed var(--border);border-radius:14px;padding:16px;text-align:center;color:var(--faint);cursor:pointer;background:var(--panel);} .drop:hover{border-color:var(--brand);color:var(--soft);} .drop.plein{padding:0;overflow:hidden;border-style:solid;} .drop img{width:100%;height:130px;object-fit:cover;display:block;}
  .tools{display:flex;flex-wrap:wrap;gap:6px;} .tools button,.tools .sw{width:34px;height:32px;border:1px solid var(--border);background:var(--panel2);color:var(--ink);border-radius:8px;cursor:pointer;font-size:13px;display:grid;place-items:center;}
  .tools .lbl{width:auto;padding:0 10px;font-weight:700;font-size:12px;} .tools .sw{border-radius:50%;} .hint{font-size:11.5px;color:var(--faint);margin-top:6px;}
  .cases{display:grid;grid-template-columns:1fr 1fr;gap:1px;max-height:210px;overflow-y:auto;padding-right:4px;}
  .case{display:flex;align-items:center;gap:7px;padding:5px 7px;border-radius:7px;cursor:pointer;font-size:12.5px;font-weight:600;} .case:hover{background:var(--panel);} .case input{accent-color:var(--brand);width:15px;height:15px;flex:none;}
  .recap{margin-top:8px;display:flex;flex-wrap:wrap;gap:5px;} .chip{background:var(--panel2);border:1px solid var(--border);color:var(--soft);font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;}
  .modeles{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;} .mod{border:2px solid var(--border);border-radius:10px;padding:7px 5px;text-align:center;cursor:pointer;background:var(--panel);font-size:11px;font-weight:700;color:var(--soft);} .mod .vig{height:38px;border-radius:6px;margin-bottom:5px;} .mod.actif{border-color:var(--brand);color:var(--ink);}
  .saved{padding:9px 11px;border-radius:9px;background:var(--panel);border:1px solid var(--border);cursor:pointer;margin-bottom:6px;display:flex;align-items:center;gap:8px;} .saved:hover{border-color:var(--brand);} .saved .in{flex:1;min-width:0;} .saved b{display:block;font-size:13px;} .saved span{font-size:11px;color:var(--faint);} .saved .del{width:30px;height:30px;border:none;background:transparent;color:var(--faint);border-radius:8px;cursor:pointer;} .saved .del:hover{background:rgba(217,88,79,.2);color:#e05a52;}
  .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:rgba(10,20,15,.95);color:#fff;padding:11px 20px;border-radius:999px;font-weight:600;font-size:14px;z-index:2000;box-shadow:0 10px 30px rgba(0,0,0,.4);}

  .affiche{width:440px;background:#fff;color:#1a2a20;border-radius:8px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.5);--ac:#1f7a4d;}
  .affiche .photo{height:250px;background:linear-gradient(150deg,#dfeede,#bcd9be);display:grid;place-items:center;color:#6a8a72;position:relative;} .affiche .photo img{width:100%;height:100%;object-fit:cover;} .affiche .photo .ph{font-size:13px;font-weight:700;}
  .affiche .prixbadge{position:absolute;right:14px;bottom:14px;background:var(--ac);color:#fff;font-weight:900;font-size:22px;padding:6px 14px;border-radius:12px;box-shadow:0 6px 16px rgba(0,0,0,.3);}
  .affiche .corps{padding:16px 20px 18px;} .affiche .titre{font-size:26px;font-weight:800;line-height:1.03;color:var(--ac);} .affiche .latin{font-style:italic;color:#6a7d70;font-size:14px;margin-top:1px;}
  .pictos{display:grid;grid-template-columns:repeat(4,1fr);gap:14px 6px;margin-top:16px;} .picto{display:flex;flex-direction:column;align-items:center;gap:5px;text-align:center;}
  .pic-ic{width:46px;height:46px;border-radius:50%;background:var(--ac);color:#fff;display:grid;place-items:center;} .pic-ic svg{width:25px;height:25px;} .pic-v{font-size:10.5px;font-weight:700;color:#2a3b30;line-height:1.15;min-width:20px;} .pic-k{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.02em;color:#9aa89f;}
  .pied{background:var(--ac);color:#fff;padding:8px 20px;display:flex;align-items:center;gap:8px;font-weight:800;font-size:13px;} .pied .leaf{width:17px;height:17px;}
  .edit{outline:none;border-radius:4px;} .edit:hover{background:rgba(79,176,106,.14);box-shadow:0 0 0 2px rgba(79,176,106,.3);}
  .affiche.tpl-nature{--ac:#5c8a34;} .affiche.tpl-minimal{--ac:#232323;} .affiche.tpl-terra{--ac:#bd5f3d;} .affiche.tpl-blush{--ac:#c56588;} .affiche.tpl-lav{--ac:#7d68b5;} .affiche.tpl-ocean{--ac:#3576ad;} .affiche.tpl-sauge{--ac:#5f9772;} .affiche.tpl-ambre{--ac:#c0902a;} .affiche.tpl-prune{--ac:#7a3f57;}
  @media print{.topbar,.panneau,.fee-back,.modal{display:none!important;}.wrap{display:block;height:auto;}.aperc{padding:0;background:#fff;display:block;overflow:visible;}.affiche{width:100%!important;box-shadow:none;border-radius:0;}.edit:hover{background:none!important;box-shadow:none!important;}}

  .modal{position:fixed;inset:0;background:rgba(4,10,7,.65);backdrop-filter:blur(4px);display:none;place-items:center;z-index:1500;padding:20px;} .modal.on{display:grid;}
  .modal-c{width:min(520px,100%);max-height:80vh;display:flex;flex-direction:column;background:var(--panel);border:1px solid var(--border);border-radius:18px;overflow:hidden;} .modal-c h2{margin:0;padding:16px 18px;font-size:17px;border-bottom:1px solid var(--border);}
  .modal-c .body{padding:12px 14px;overflow-y:auto;} .modal-c .foot{padding:12px 16px;border-top:1px solid var(--border);text-align:right;}

  /* Fée */
  .fee-back{position:fixed;inset:0;z-index:900;background:radial-gradient(circle at 50% 40%,#14261a,#070d09);display:none;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;} .fee-back.on{display:flex;opacity:1;}
  .fee-scene{text-align:center;width:min(520px,92vw);} .fee-duo{display:flex;align-items:flex-end;justify-content:center;gap:8px;position:relative;}
  .fee-perso{width:min(190px,40vw);height:auto;display:block;animation:feeFlotte 3s ease-in-out infinite;transform-origin:50% 70%;} @keyframes feeFlotte{0%,100%{transform:translateY(0) rotate(-1deg)}50%{transform:translateY(-9px) rotate(1deg)}}
  .fee-ailes{animation:feeAiles 1.5s ease-in-out infinite;transform-origin:300px 300px;} @keyframes feeAiles{0%,100%{transform:scaleX(1)}50%{transform:scaleX(.9)}}
  .fee-bras{animation:feeCoup 2s cubic-bezier(.36,.07,.3,1) infinite;transform-origin:340px 342px;} @keyframes feeCoup{0%,40%{transform:rotate(0)}52%{transform:rotate(-26deg)}62%{transform:rotate(22deg)}72%{transform:rotate(-6deg)}82%,100%{transform:rotate(0)}}
  .fee-halo{animation:feeHalo 2s ease-in-out infinite;transform-origin:405px 247px;} @keyframes feeHalo{0%,45%,100%{opacity:.5;transform:scale(.85)}62%{opacity:1;transform:scale(1.35)}}
  .fee-plante{width:min(140px,30vw);height:auto;display:block;} .fee-plant{display:none;} .fee-plant.on{display:block;animation:feePousse .55s cubic-bezier(.2,1.6,.4,1),feeBalance 3.2s ease-in-out .55s infinite;transform-origin:110px 172px;} @keyframes feePousse{from{transform:scale(.15) translateY(20px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}} @keyframes feeBalance{0%,100%{transform:rotate(-1.5deg)}50%{transform:rotate(1.5deg)}}
  .fee-etincelles{position:absolute;left:52%;top:34%;width:1px;height:1px;} .fee-et{position:absolute;width:9px;height:9px;background:#B5D95A;border-radius:2px;box-shadow:0 0 8px rgba(141,198,63,.9);animation:feeVole 1.4s ease-in forwards;} @keyframes feeVole{0%{transform:translate(0,0) scale(.4);opacity:0}20%{opacity:1}100%{transform:translate(var(--dx),var(--dy)) scale(1.1) rotate(220deg);opacity:0}}
  .fee-barre{width:min(300px,80%);height:8px;margin:22px auto 0;background:rgba(255,255,255,.14);border-radius:999px;overflow:hidden;} .fee-barre span{display:block;height:100%;width:35%;border-radius:999px;background:linear-gradient(90deg,#8DC63F,#1E6B33);animation:feeIndef 1.15s ease-in-out infinite;} @keyframes feeIndef{0%{transform:translateX(-115%)}100%{transform:translateX(320%)}}
  .fee-txt{margin-top:14px;color:#DCEBD6;font-weight:700;font-size:.95rem;}
  @media(max-width:860px){.wrap{grid-template-columns:1fr;height:auto;}.panneau{border-right:none;border-bottom:1px solid var(--border);}}
  @media(prefers-reduced-motion:reduce){*{animation:none!important}}
</style>
<style id="printFmt">@page{size:A4 portrait;margin:10mm}</style>
</head>
<body>

<div class="topbar">
  <a class="retour" href="../index.php" target="_top" title="Retour au portail"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
  <div class="mark"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10M12 20V10M12 10C12 6.5 9 4.5 5 4.5c0 3.8 3 6 7 5.5zM12 12c0-3 3-5 7-5 0 3.8-3 6-7 5z"/></svg></div>
  <div class="t">fami<span>Botanic</span><div class="s">Affiche plante — client</div></div>
  <div class="seg push" id="langSeg"><button data-l="fr" class="on">FR</button><button data-l="nl">NL</button></div>
  <button class="btn btn-ghost" onclick="enregistrer(false)">💾 Enregistrer</button>
  <button class="btn btn-brand" onclick="imprimer()">⬇ <span id="lblPrint">Obtenir l'affiche</span></button>
</div>

<div class="wrap">
  <div class="panneau">
    <div class="grp">
      <h3 id="h1">Photo & nom</h3>
      <div class="drop" id="drop"><div id="dropTxt">📷 Photo de la plante</div></div>
      <input type="file" id="file" accept="image/*" hidden>
      <label class="champ" id="lblNom" for="nom">Nom de la plante</label>
      <input type="text" id="nom" placeholder="Ex. : Lavandula angustifolia">
      <button class="btn btn-brand" id="btnGen" style="width:100%;margin-top:11px;justify-content:center;">✨ <span id="lblGen">Générer avec l'IA</span></button>
    </div>

    <div class="grp">
      <h3 id="hEd">Outils d'édition</h3>
      <div class="tools" id="tools">
        <button title="Gras" onmousedown="event.preventDefault()" onclick="fmt('bold')"><b>B</b></button>
        <button title="Surligner" onmousedown="event.preventDefault()" onclick="fmt('hiliteColor','#fff3a0')">✎</button>
        <button title="Aligner à gauche" onmousedown="event.preventDefault()" onclick="fmt('justifyLeft')">⯇</button>
        <button title="Centrer" onmousedown="event.preventDefault()" onclick="fmt('justifyCenter')">≡</button>
        <button title="Aligner à droite" onmousedown="event.preventDefault()" onclick="fmt('justifyRight')">⯈</button>
        <span class="sw" style="background:#1f7a4d" onmousedown="event.preventDefault()" onclick="fmt('foreColor','#1f7a4d')"></span>
        <span class="sw" style="background:#c2453a" onmousedown="event.preventDefault()" onclick="fmt('foreColor','#c2453a')"></span>
        <span class="sw" style="background:#3576ad" onmousedown="event.preventDefault()" onclick="fmt('foreColor','#3576ad')"></span>
        <span class="sw" style="background:#111" onmousedown="event.preventDefault()" onclick="fmt('foreColor','#111')"></span>
      </div>
      <div class="hint">Sélectionnez du texte sur l'affiche puis cliquez.</div>
    </div>

    <div class="grp">
      <h3 id="h2">Éléments à afficher</h3>
      <div class="cases" id="cases"></div>
      <div class="recap" id="recap"></div>
    </div>

    <div class="grp">
      <h3 id="h3">Modèle</h3>
      <div class="modeles" id="modeles"></div>
      <div class="modeles" id="modelesPlus" style="display:none;margin-top:8px;"></div>
      <button class="btn btn-ghost" id="btnPlus" style="width:100%;margin-top:8px;justify-content:center;font-size:12.5px;">Voir plus ▾</button>
    </div>

    <div class="grp">
      <h3>Affiches enregistrées</h3>
      <input type="search" id="rechSaved" list="dlSaved" placeholder="Ouvrir une affiche (tapez le nom)…">
      <datalist id="dlSaved"></datalist>
      <button class="btn btn-ghost" id="btnGerer" style="width:100%;margin-top:8px;justify-content:center;">🗂️ Gérer les affiches</button>
    </div>
  </div>

  <div class="aperc">
    <div class="affiche" id="affiche">
      <div class="photo" id="photo"><span class="ph">Photo de la plante</span><span class="prixbadge edit" contenteditable id="aPrix">0,00 €</span></div>
      <div class="corps">
        <div class="titre edit" contenteditable id="aTitre">Nom de la plante</div>
        <div class="latin edit" contenteditable id="aLatin">Nom scientifique</div>
        <div class="pictos" id="pictos"></div>
      </div>
      <div class="pied"><svg class="leaf" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 9-10 1 6-2 10-7 11"/></svg>Famiflora</div>
    </div>
  </div>
</div>

<!-- Modale gestion -->
<div class="modal" id="modalG"><div class="modal-c">
  <h2>Gérer les affiches enregistrées</h2>
  <div class="body">
    <input type="search" id="rechG" placeholder="Rechercher…" style="margin-bottom:10px;">
    <div id="listeG"></div>
  </div>
  <div class="foot"><button class="btn btn-ghost" onclick="document.getElementById('modalG').classList.remove('on')">Fermer</button></div>
</div></div>

<!-- Fée -->
<div class="fee-back" id="feeBack" aria-hidden="true"><div class="fee-scene"><div class="fee-duo">
  <svg class="fee-perso" viewBox="130 20 360 490" xmlns="http://www.w3.org/2000/svg">
    <defs><linearGradient id="fS" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FBE1C4"/><stop offset="1" stop-color="#F3CBA3"/></linearGradient><linearGradient id="fH" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#94512F"/><stop offset="1" stop-color="#6E3A22"/></linearGradient><linearGradient id="fD" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#A9D454"/><stop offset="1" stop-color="#84BD3C"/></linearGradient><radialGradient id="fG"><stop offset="0" stop-color="#EAF6CE" stop-opacity=".85"/><stop offset=".5" stop-color="#C9E58B" stop-opacity=".4"/><stop offset="1" stop-color="#8DC63F" stop-opacity="0"/></radialGradient></defs>
    <g class="fee-ailes"><path d="M298 318 Q 156 330 140 160 Q 270 152 298 318 Z" fill="#1E6B33"/><path d="M280 296 Q 186 306 176 192 Q 256 186 280 296 Z" fill="#8DC63F"/><path d="M302 318 Q 444 330 460 160 Q 330 152 302 318 Z" fill="#1E6B33"/><path d="M320 296 Q 414 306 424 192 Q 344 186 320 296 Z" fill="#8DC63F"/><path d="M298 330 Q 178 322 170 456 Q 262 466 298 330 Z" fill="#1E6B33"/><path d="M302 330 Q 422 322 430 456 Q 338 466 302 330 Z" fill="#1E6B33"/></g>
    <ellipse cx="274" cy="492" rx="16" ry="9" fill="#1E6B33"/><ellipse cx="326" cy="492" rx="16" ry="9" fill="#1E6B33"/><path d="M258 330 Q 300 318 342 330 L 352 422 Q 300 438 248 422 Z" fill="url(#fD)"/><path d="M288 298 L 312 298 L 311 336 L 289 336 Z" fill="#F3CBA3"/>
    <circle cx="300" cy="220" r="92" fill="url(#fS)"/><circle cx="300" cy="72" r="37" fill="url(#fH)"/><path d="M204 258 Q 184 112 300 88 Q 416 112 396 258 Q 386 262 380 252 Q 386 202 356 178 Q 336 158 300 152 Q 264 158 244 178 Q 214 202 220 252 Q 214 262 204 258 Z" fill="url(#fH)"/>
    <g class="fee-yeux"><circle cx="262" cy="232" r="20" fill="#1E4020"/><circle cx="338" cy="232" r="20" fill="#1E4020"/><circle cx="269" cy="224" r="7" fill="#fff"/><circle cx="345" cy="224" r="7" fill="#fff"/></g><path d="M280 270 Q 300 292 320 270 Q 312 286 300 286 Q 288 286 280 270 Z" fill="#1A1A1A"/>
    <g class="fee-bras"><path d="M340 342 Q 366 330 380 306" stroke="url(#fS)" stroke-width="12" stroke-linecap="round" fill="none"/><line x1="382" y1="300" x2="402" y2="254" stroke="#8A5B36" stroke-width="6" stroke-linecap="round"/><circle class="fee-halo" cx="405" cy="247" r="42" fill="url(#fG)"/><path d="M405 218 l8 21 21 8 -21 8 -8 21 -8 -21 -21 -8 21 -8 Z" fill="#8DC63F"/></g>
  </svg>
  <div class="fee-etincelles" id="feeEt"></div>
  <svg class="fee-plante" viewBox="0 0 220 260" xmlns="http://www.w3.org/2000/svg">
    <defs><linearGradient id="pP" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C97B4A"/><stop offset="1" stop-color="#9C5730"/></linearGradient><linearGradient id="pT" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#2E7D3B"/><stop offset="1" stop-color="#8DC63F"/></linearGradient></defs>
    <path d="M56 168 L164 168 L152 246 Q 110 254 68 246 Z" fill="url(#pP)"/><rect x="48" y="150" width="124" height="24" rx="7" fill="#D98A57"/><ellipse cx="110" cy="172" rx="52" ry="9" fill="#5C3A21"/>
    <g class="fee-plant"><path d="M110 170 Q 106 118 111 74" stroke="url(#pT)" stroke-width="7.5" stroke-linecap="round" fill="none"/><g transform="translate(111,62)"><ellipse cx="-17" cy="0" rx="13" ry="9" fill="#F2A9C4"/><ellipse cx="17" cy="0" rx="13" ry="9" fill="#F2A9C4"/><ellipse cx="0" cy="-16" rx="9" ry="13" fill="#F6BDD2"/><ellipse cx="0" cy="16" rx="9" ry="13" fill="#F6BDD2"/><circle r="9" fill="#E8C46B"/></g></g>
    <g class="fee-plant"><rect x="97" y="90" width="26" height="82" rx="13" fill="#4E9E52"/><ellipse cx="110" cy="84" rx="10" ry="7.5" fill="#F26E7E"/></g>
    <g class="fee-plant"><rect x="102" y="96" width="16" height="74" rx="5" fill="#8A5B36"/><circle cx="110" cy="64" r="40" fill="#2E7D3B"/><circle cx="78" cy="84" r="28" fill="#3C9147"/><circle cx="142" cy="84" r="28" fill="#3C9147"/></g>
    <g class="fee-plant"><path d="M110 170 Q 108 122 111 76" stroke="url(#pT)" stroke-width="7" stroke-linecap="round" fill="none"/><g transform="translate(111,64)" fill="#F4B41A"><ellipse cx="0" cy="-18" rx="6" ry="12"/><ellipse cx="0" cy="18" rx="6" ry="12"/><ellipse cx="-18" cy="0" rx="12" ry="6"/><ellipse cx="18" cy="0" rx="12" ry="6"/></g><circle cx="111" cy="64" r="11" fill="#6E4523"/></g>
  </svg>
</div><div class="fee-barre"><span></span></div><div class="fee-txt" id="feeTxt">La fée identifie votre plante…</div></div></div>

<script>
const S=(p)=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'+p+'</svg>';
const ATTRS=[
  {id:"emplacement",fr:"Emplacement",nl:"Standplaats",type:"val",i:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.4 1.4M17.6 17.6 19 19M5 19l1.4-1.4M17.6 6.4 19 5"/>'},
  {id:"arrosage",fr:"Arrosage",nl:"Water",type:"val",i:'<path d="M12 2s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/>'},
  {id:"hauteur",fr:"Hauteur",nl:"Hoogte",type:"val",i:'<path d="M12 3v18M8 7l4-4 4 4M8 17l4 4 4-4"/>'},
  {id:"largeur",fr:"Largeur",nl:"Breedte",type:"val",i:'<path d="M3 12h18M7 8l-4 4 4 4M17 8l4 4-4 4"/>'},
  {id:"floraison",fr:"Floraison",nl:"Bloeitijd",type:"val",i:'<circle cx="12" cy="12" r="3"/><path d="M12 4a2.4 2.4 0 0 0 0 5M12 20a2.4 2.4 0 0 0 0-5M4 12a2.4 2.4 0 0 0 5 0M20 12a2.4 2.4 0 0 0-5 0"/>'},
  {id:"couleur",fr:"Couleur",nl:"Bloemkleur",type:"val",i:'<circle cx="13.5" cy="7" r="1.3"/><circle cx="17" cy="11" r="1.3"/><circle cx="8" cy="8" r="1.3"/><path d="M12 2a10 10 0 1 0 1 20 1.8 1.8 0 0 0 1.4-3 1.8 1.8 0 0 1 1.4-3H18a4 4 0 0 0 4-4A10 10 0 0 0 12 2z"/>'},
  {id:"type",fr:"Type",nl:"Type",type:"val",i:'<path d="M12 22v-6M8 16h8M17 11a5 5 0 1 0-10 0 4 4 0 0 0 1 8h8a4 4 0 0 0 1-8z"/>'},
  {id:"cueillette",fr:"Cueillette",nl:"Oogsttijd",type:"val",i:'<path d="M4 10h16l-1.4 9a2 2 0 0 1-2 1.7H7.4a2 2 0 0 1-2-1.7L4 10z"/><path d="M8.5 10 10.5 4M15.5 10 13.5 4"/>'},
  {id:"taille",fr:"Taille",nl:"Terugsnoei",type:"val",i:'<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M8.6 7.5 20 18M8.6 16.5 20 6"/>'},
  {id:"melifere",fr:"Mellifère",nl:"Bijvriendelijk",type:"bool",i:'<ellipse cx="12" cy="14" rx="4" ry="5"/><path d="M8 12.5h8M8 15.5h8"/><circle cx="12" cy="6.5" r="2"/><path d="M9 5 6 3M15 5l3-2"/>'},
  {id:"comestible",fr:"Comestible",nl:"Eetbaar",type:"bool",i:'<path d="M6 3v6a2 2 0 0 0 4 0V3M8 11v10M17 3c-1.6 0-2.5 2-2.5 5s1 3.5 2.5 3.5V21"/>'},
  {id:"parfume",fr:"Parfumé",nl:"Geurig",type:"bool",i:'<path d="M4 8c2-2 4-2 6 0s4 2 6 0 4-2 4-2M4 13c2-2 4-2 6 0s4 2 6 0 4-2 4-2M8 18c1.5-1.5 3-1.5 4.5 0"/>'},
  {id:"toxique",fr:"Toxique",nl:"Giftig",type:"bool",i:'<circle cx="9.5" cy="10" r="1"/><circle cx="14.5" cy="10" r="1"/><path d="M12 3a8 8 0 0 0-5 14v3h10v-3a8 8 0 0 0-5-14zM10 20v-2.5M14 20v-2.5"/>'},
  {id:"grimpante",fr:"Grimpante",nl:"Klimplant",type:"bool",i:'<path d="M7 22V4M7 8c3 0 5-2 5-4M7 13c3 0 6-1 8-3M7 18c3 0 8-1 10-4"/>'},
  {id:"purificatrice",fr:"Purificatrice",nl:"Luchtzuivering",type:"bool",i:'<path d="M4 8h11a3 3 0 1 0-3-3M2 12h16a3 3 0 1 1-3 3M4 16h8a2.5 2.5 0 1 1-2.5 2.5"/>'},
  {id:"antilimaces",fr:"Anti-limaces",nl:"Slakkenweerstand",type:"bool",i:'<path d="M3 17h9a5 5 0 1 0-5-5 2.5 2.5 0 1 0 2.5 2.5"/><path d="M12 17l3-3M18 8l2-2M20 10l2-2"/>'},
  {id:"fruit",fr:"Fruit",nl:"Vrucht",type:"bool",i:'<circle cx="12" cy="15" r="6"/><path d="M12 9V5M12 5c0-1.5 1.5-2.5 3-2"/>'},
  {id:"gel",fr:"Résistant au gel",nl:"Winterhard",type:"bool",i:'<path d="M12 2v20M4 7l16 10M20 7 4 17M12 4 9 6M12 4l3 2M12 20l-3-2M12 20l3-2M4 12h4M16 12h4"/>'},
  {id:"persistant",fr:"Persistant",nl:"Wintergroen",type:"bool",i:'<path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 9-10 1 6-2 10-7 11"/><path d="M11 20c0-4 2-7 6-8"/>'}
];
const ELEMENTS=[{id:"latin",fr:"Nom latin",nl:"Latijnse naam"},{id:"prix",fr:"Prix",nl:"Prijs"},...ATTRS];
const L={fr:{h1:"Photo & nom",nom:"Nom de la plante",gen:"Générer avec l'IA",hEd:"Outils d'édition",h2:"Éléments à afficher",h3:"Modèle",print:"Obtenir l'affiche",fee:"La fée identifie votre plante…"},
       nl:{h1:"Foto & naam",nom:"Naam van de plant",gen:"Genereer met AI",hEd:"Bewerken",h2:"Elementen tonen",h3:"Sjabloon",print:"Affiche ophalen",fee:"De fee herkent je plant…"}};

let lang="fr", data={}, valeurs={}, currentId=null;
let actifs={latin:true,prix:true,emplacement:true,arrosage:true,hauteur:true,largeur:false,floraison:true,couleur:false,type:false,cueillette:false,taille:false,melifere:false,comestible:false,parfume:false,toxique:false,grimpante:false,purificatrice:false,antilimaces:false,fruit:false,gel:false,persistant:false};

function esc(s){const d=document.createElement('div');d.textContent=s??'';return d.innerHTML;}
function fmt(cmd,val){document.execCommand(cmd,false,val||null);}

function renderCases(){
  document.getElementById('cases').innerHTML=ELEMENTS.map(e=>'<label class="case"><input type="checkbox" data-e="'+e.id+'"'+(actifs[e.id]?' checked':'')+'>'+e[lang]+'</label>').join('');
  document.querySelectorAll('.case input').forEach(c=>c.addEventListener('change',()=>{actifs[c.dataset.e]=c.checked;renderAffiche();}));
}
function renderRecap(){
  const sel=ELEMENTS.filter(e=>actifs[e.id]).map(e=>e[lang]);
  document.getElementById('recap').innerHTML=sel.length?sel.map(x=>'<span class="chip">'+esc(x)+'</span>').join(''):'<span style="color:var(--faint);font-size:12px">Rien sélectionné</span>';
}
function renderAffiche(){
  document.getElementById('aLatin').style.display=actifs.latin?'':'none';
  const pb=document.getElementById('aPrix'); if(pb) pb.style.display=actifs.prix?'':'none';
  document.getElementById('pictos').innerHTML=ATTRS.filter(a=>actifs[a.id]).map(a=>{
    if(a.type==='val') return '<div class="picto"><div class="pic-ic">'+S(a.i)+'</div><div class="pic-k">'+a[lang]+'</div><div class="pic-v edit" contenteditable data-v="'+a.id+'">'+esc(valeurs[a.id]||'—')+'</div></div>';
    return '<div class="picto"><div class="pic-ic">'+S(a.i)+'</div><div class="pic-v">'+a[lang]+'</div></div>';
  }).join('');
  document.querySelectorAll('#pictos [data-v]').forEach(el=>el.addEventListener('input',()=>{valeurs[el.dataset.v]=el.textContent.trim();}));
  renderRecap();
}
function majLangue(){
  const t=L[lang];
  ['h1','hEd','h2','h3'].forEach(k=>document.getElementById(k).textContent=t[k]);
  document.getElementById('lblNom').textContent=t.nom; document.getElementById('lblGen').textContent=t.gen;
  document.getElementById('lblPrint').textContent=t.print; document.getElementById('feeTxt').textContent=t.fee;
  renderCases(); renderAffiche();
}
document.querySelectorAll('#langSeg button').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('#langSeg button').forEach(x=>x.classList.remove('on'));b.classList.add('on');lang=b.dataset.l;majLangue();}));

// Photo
let photoData="";
const drop=document.getElementById('drop'),file=document.getElementById('file'),photo=document.getElementById('photo');
drop.addEventListener('click',()=>file.click());
file.addEventListener('change',e=>{if(e.target.files[0])charger(e.target.files[0]);});
['dragover'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='#4fb06a';}));
['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='';}));
drop.addEventListener('drop',e=>{if(e.dataTransfer.files[0])charger(e.dataTransfer.files[0]);});
function charger(f){const r=new FileReader();r.onload=()=>{photoData=r.result;photo.innerHTML='<img src="'+photoData+'">'+document.getElementById('aPrix').outerHTML;renderAffiche();drop.classList.add('plein');drop.innerHTML='<img src="'+photoData+'">';};r.readAsDataURL(f);}

document.getElementById('nom').addEventListener('input',e=>{if(e.target.value.trim())document.getElementById('aTitre').textContent=e.target.value;});

// IA
document.getElementById('btnGen').addEventListener('click',generer);
async function generer(){
  const nom=document.getElementById('nom').value.trim();
  if(!photoData && !nom){alert('Renseignez au moins la photo ou le nom.');return;}
  feeShow(L[lang].fee);
  try{
    const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'analyser',nom,photo:photoData,lang})});
    const d=await rep.json(); if(!rep.ok) throw new Error(d.erreur||'Erreur inattendue.');
    data=d;
    if(d.nom_commun) document.getElementById('aTitre').textContent=d.nom_commun;
    if(d.nom_latin) document.getElementById('aLatin').textContent=d.nom_latin;
    ['emplacement','arrosage','hauteur','largeur','floraison','couleur','type','cueillette','taille'].forEach(k=>{if(d[k])valeurs[k]=d[k];});
    ['melifere','comestible','parfume','toxique','grimpante','purificatrice','antilimaces','fruit','gel','persistant'].forEach(k=>{if(d[k]===true)actifs[k]=true;});
    renderCases(); renderAffiche();
  }catch(e){alert('😕 '+(e.message||'Le serveur ne répond pas.'));}
  finally{feeHide();}
}

// Modèles
const MODELES=[{n:"Classique",m:"",c:"#1f7a4d",d:"#124f30"},{n:"Nature",m:"tpl-nature",c:"#6b9e3f",d:"#456a24"},{n:"Minimal",m:"tpl-minimal",c:"#2b2b2b",d:"#111"},{n:"Terracotta",m:"tpl-terra",c:"#c76b47",d:"#984c2e"},{n:"Blush",m:"tpl-blush",c:"#d47a9a",d:"#a04e72"},{n:"Lavande",m:"tpl-lav",c:"#8f7bc4",d:"#5f4a8f"},{n:"Océan",m:"tpl-ocean",c:"#3f86c0",d:"#245e8f"},{n:"Sauge",m:"tpl-sauge",c:"#7fae8f",d:"#4e7d5f"},{n:"Ambre",m:"tpl-ambre",c:"#d3a12f",d:"#9c7318"},{n:"Prune",m:"tpl-prune",c:"#8a4a63",d:"#5f2f43"}];
const swatch=x=>'<div class="mod'+(x.m===""?" actif":"")+'" data-m="'+x.m+'"><div class="vig" style="background:linear-gradient(150deg,'+x.c+','+x.d+')"></div>'+x.n+'</div>';
document.getElementById('modeles').innerHTML=MODELES.slice(0,3).map(swatch).join('');
document.getElementById('modelesPlus').innerHTML=MODELES.slice(3).map(swatch).join('');
document.querySelectorAll('.mod').forEach(m=>m.addEventListener('click',()=>{document.querySelectorAll('.mod').forEach(x=>x.classList.remove('actif'));m.classList.add('actif');document.getElementById('affiche').className='affiche'+(m.dataset.m?' '+m.dataset.m:'');}));
const plusEl=document.getElementById('modelesPlus'),btnPlus=document.getElementById('btnPlus');
btnPlus.addEventListener('click',()=>{const o=plusEl.style.display!=='none';plusEl.style.display=o?'none':'grid';btnPlus.textContent=o?'Voir plus ▾':'Voir moins ▴';});
function imprimer(){enregistrer(true);window.print();}

// Sauvegarde / gestion
let SAVED=[];
function etatCourant(){return{id:currentId,nom_commun:document.getElementById('aTitre').textContent.trim(),nom_latin:document.getElementById('aLatin').textContent.trim(),langue:lang,prix:document.getElementById('aPrix').textContent.trim(),modele:document.getElementById('affiche').className.replace('affiche','').trim(),actifs,valeurs,data,photo:photoData};}
function toast(msg){const t=document.createElement('div');t.className='toast';t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),2000);}
async function enregistrer(silencieux){
  try{const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'enregistrer',id:currentId,etat:etatCourant()})});const d=await rep.json();if(!rep.ok)throw new Error(d.erreur||'Erreur');currentId=d.id;if(!silencieux)toast('Affiche enregistrée ✓');chargerListe();}
  catch(e){if(!silencieux)alert('😕 '+(e.message||'Erreur'));}
}
async function chargerListe(){
  try{const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'lister'})});SAVED=await rep.json();if(!Array.isArray(SAVED))SAVED=[];
    document.getElementById('dlSaved').innerHTML=SAVED.map(a=>'<option value="'+esc((a.nom_commun||'')+' — '+(a.auteur||''))+'">').join('');
    renderGestion('');
  }catch(e){}
}
function renderGestion(f){
  const el=document.getElementById('listeG');
  const list=SAVED.filter(a=>((a.nom_commun||'')+(a.nom_latin||'')+(a.auteur||'')).toLowerCase().includes((f||'').toLowerCase()));
  if(!list.length){el.innerHTML='<div style="color:var(--faint);font-size:13px;padding:8px;">Aucune affiche.</div>';return;}
  el.innerHTML=list.map(a=>'<div class="saved"><div class="in" data-open="'+a.id+'"><b>'+esc(a.nom_commun||'(sans nom)')+'</b><span>'+esc(a.auteur||'')+' · '+a.date+' · '+(a.langue||'fr').toUpperCase()+'</span></div><button class="del" data-del="'+a.id+'" title="Supprimer">🗑</button></div>').join('');
  el.querySelectorAll('[data-open]').forEach(x=>x.addEventListener('click',()=>{document.getElementById('modalG').classList.remove('on');ouvrir(+x.dataset.open);}));
  el.querySelectorAll('[data-del]').forEach(x=>x.addEventListener('click',async()=>{ if(!confirm('Supprimer cette affiche ?'))return; await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'supprimer',id:+x.dataset.del})}); if(currentId==x.dataset.del)currentId=null; chargerListe(); toast('Supprimée'); }));
}
async function ouvrir(id){
  try{const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'obtenir',id})});const et=await rep.json();if(!rep.ok)throw new Error(et.erreur||'Erreur');
    currentId=id;lang=et.langue||'fr';data=et.data||{};valeurs=et.valeurs||{};actifs=Object.assign(actifs,et.actifs||{});
    document.querySelectorAll('#langSeg button').forEach(x=>x.classList.toggle('on',x.dataset.l===lang));
    document.getElementById('affiche').className='affiche'+(et.modele?' '+et.modele:'');
    document.querySelectorAll('.mod').forEach(x=>x.classList.toggle('actif',x.dataset.m===(et.modele||'')));
    document.getElementById('aTitre').textContent=et.nom_commun||'';document.getElementById('aLatin').textContent=et.nom_latin||'';document.getElementById('aPrix').textContent=et.prix||'0,00 €';
    if(et.photo){photoData=et.photo;photo.innerHTML='<img src="'+et.photo+'">'+document.getElementById('aPrix').outerHTML;drop.classList.add('plein');drop.innerHTML='<img src="'+et.photo+'">';}
    else{photoData='';photo.innerHTML='<span class="ph">Photo</span>'+document.getElementById('aPrix').outerHTML;drop.classList.remove('plein');drop.innerHTML='<div id="dropTxt">📷 Photo de la plante</div>';}
    majLangue();toast('Affiche ouverte');
  }catch(e){alert('😕 '+(e.message||'Erreur'));}
}
document.getElementById('rechSaved').addEventListener('change',e=>{const a=SAVED.find(s=>((s.nom_commun||'')+' — '+(s.auteur||''))===e.target.value);if(a)ouvrir(a.id);});
document.getElementById('btnGerer').addEventListener('click',()=>{renderGestion('');document.getElementById('modalG').classList.add('on');});
document.getElementById('rechG').addEventListener('input',e=>renderGestion(e.target.value));
document.getElementById('modalG').addEventListener('click',e=>{if(e.target.id==='modalG')e.currentTarget.classList.remove('on');});

// Fée
const feeBack=document.getElementById('feeBack'),feeTxt=document.getElementById('feeTxt'),feeEt=document.getElementById('feeEt');
let plantes=null,etinc=null,pIdx=-1;
function pousse(){const all=document.querySelectorAll('.fee-plant');all.forEach(g=>g.classList.remove('on'));pIdx=(pIdx+1)%all.length;all[pIdx].classList.add('on');}
function etincelle(){if(!feeBack.classList.contains('on'))return;const s=document.createElement('span');s.className='fee-et';s.style.setProperty('--dx',(60+Math.random()*70)+'px');s.style.setProperty('--dy',(40+Math.random()*60)+'px');s.style.left=(Math.random()*14-7)+'px';feeEt.appendChild(s);setTimeout(()=>s.remove(),1500);}
function feeShow(t){feeTxt.textContent=t||'…';feeBack.classList.add('on');pIdx=-1;pousse();plantes=setInterval(pousse,2200);etinc=setInterval(etincelle,650);}
function feeHide(){feeBack.classList.remove('on');clearInterval(plantes);clearInterval(etinc);}

renderCases(); renderAffiche(); chargerListe();
</script>
</body>
</html>
