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
  :root{
    --bg:#0e1712; --panel:#131e18; --panel2:#18241d; --border:#243228; --ink:#e9f1eb; --soft:#94a89b; --faint:#66786d;
    --brand:#4fb06a; --brand-deep:#2e8b57;
    --font:-apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,Roboto,Helvetica,Arial,sans-serif;
  }
  *{box-sizing:border-box;}
  body{margin:0;font-family:var(--font);background:var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased;}
  button,select{font-family:inherit;}
  .topbar{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border);}
  .retour{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;color:var(--soft);text-decoration:none;background:var(--panel2);border:1px solid var(--border);}
  .retour:hover{color:var(--ink);}
  .mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(150deg,var(--brand),var(--brand-deep));display:grid;place-items:center;color:#06130c;}
  .t{font-weight:700}.t span{color:var(--brand)}.t .s{font-size:12px;font-weight:500;color:var(--faint)}
  .btn{border:none;border-radius:10px;padding:9px 15px;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
  .btn-brand{background:linear-gradient(135deg,#5fd07f,var(--brand-deep));color:#06130c;}
  .btn-ghost{background:var(--panel2);color:var(--ink);border:1px solid var(--border);}
  .push{margin-left:auto;}
  .seg{display:inline-flex;background:var(--panel2);border:1px solid var(--border);border-radius:10px;overflow:hidden;}
  .seg button{border:none;background:transparent;color:var(--soft);padding:8px 13px;font-weight:700;font-size:13px;cursor:pointer;}
  .seg button.on{background:var(--brand);color:#06130c;}

  .wrap{display:grid;grid-template-columns:340px 1fr;height:calc(100vh - 60px);}
  .panneau{border-right:1px solid var(--border);overflow-y:auto;padding:16px;}
  .aperc{overflow-y:auto;padding:26px;display:grid;place-items:start center;background:#0b120e;}
  .grp{margin-bottom:16px;} .grp h3{font-size:11.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--soft);margin:0 0 9px;}
  label.champ{display:block;font-size:12px;font-weight:700;color:var(--soft);margin:10px 0 5px;}
  input[type=text]{width:100%;background:var(--panel2);border:1px solid var(--border);color:var(--ink);border-radius:10px;padding:10px 12px;font:inherit;font-size:14px;}
  input:focus{outline:none;border-color:var(--brand);}
  .drop{border:2px dashed var(--border);border-radius:14px;padding:18px;text-align:center;color:var(--faint);cursor:pointer;background:var(--panel);}
  .drop:hover{border-color:var(--brand);color:var(--soft);}
  .drop.plein{padding:0;overflow:hidden;border-style:solid;} .drop img{width:100%;height:140px;object-fit:cover;display:block;}

  .cases{display:grid;grid-template-columns:1fr 1fr;gap:2px;}
  .case{display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:8px;cursor:pointer;font-size:12.5px;font-weight:600;}
  .case:hover{background:var(--panel);}
  .case input{accent-color:var(--brand);width:15px;height:15px;flex:none;}

  .modeles{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
  .mod{border:2px solid var(--border);border-radius:10px;padding:7px 5px;text-align:center;cursor:pointer;background:var(--panel);font-size:11px;font-weight:700;color:var(--soft);}
  .mod .vig{height:40px;border-radius:6px;margin-bottom:5px;} .mod.actif{border-color:var(--brand);color:var(--ink);}

  /* ---------- L'AFFICHE (peu de texte, pictogrammes) ---------- */
  .affiche{width:440px;background:#fff;color:#1a2a20;border-radius:8px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.5);--ac:#1c6b41;}
  .affiche .photo{height:250px;background:linear-gradient(150deg,#dfeede,#bcd9be);display:grid;place-items:center;color:#6a8a72;position:relative;}
  .affiche .photo img{width:100%;height:100%;object-fit:cover;} .affiche .photo .ph{font-size:13px;font-weight:700;}
  .affiche .prixbadge{position:absolute;right:14px;bottom:14px;background:var(--ac);color:#fff;font-weight:900;font-size:22px;padding:6px 14px;border-radius:12px;box-shadow:0 6px 16px rgba(0,0,0,.3);}
  .affiche .corps{padding:16px 20px 18px;}
  .affiche .titre{font-size:26px;font-weight:800;line-height:1.03;color:var(--ac);}
  .affiche .latin{font-style:italic;color:#6a7d70;font-size:14px;margin-top:1px;}
  .pictos{display:grid;grid-template-columns:repeat(4,1fr);gap:14px 6px;margin-top:16px;}
  .picto{display:flex;flex-direction:column;align-items:center;gap:5px;text-align:center;}
  .pic-ic{width:46px;height:46px;border-radius:50%;background:var(--ac);color:#fff;display:grid;place-items:center;} .pic-ic svg{width:25px;height:25px;}
  .pic-v{font-size:10.5px;font-weight:700;color:#2a3b30;line-height:1.15;}
  .pic-k{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.02em;color:#9aa89f;}
  .pied{background:var(--ac);color:#fff;padding:8px 20px;display:flex;align-items:center;gap:8px;font-weight:800;font-size:13px;} .pied .leaf{width:17px;height:17px;}
  .edit{outline:none;border-radius:4px;} .edit:hover{background:rgba(79,176,106,.14);box-shadow:0 0 0 2px rgba(79,176,106,.3);}
  .affiche.tpl-nature{--ac:#5a8f2e;} .affiche.tpl-orange{--ac:#d97a2b;} .affiche.tpl-rose{--ac:#c25f96;} .affiche.tpl-violet{--ac:#7d5aa8;} .affiche.tpl-bleu{--ac:#3a72b5;} .affiche.tpl-turquoise{--ac:#2a9d8f;} .affiche.tpl-rouge{--ac:#c2453a;} .affiche.tpl-or{--ac:#c0922c;} .affiche.tpl-minimal{--ac:#1a1a1a;}
  @media print{ .topbar,.panneau,.fee-back{display:none!important;} .wrap{display:block;height:auto;} .aperc{padding:0;background:#fff;display:block;overflow:visible;} .affiche{width:100%!important;box-shadow:none;border-radius:0;} .edit:hover{background:none!important;box-shadow:none!important;} }

  /* Fée */
  .fee-back{position:fixed;inset:0;z-index:900;background:radial-gradient(circle at 50% 40%,#14261a,#070d09);display:none;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;}
  .fee-back.on{display:flex;opacity:1;} .fee-scene{text-align:center;width:min(520px,92vw);} .fee-duo{display:flex;align-items:flex-end;justify-content:center;gap:8px;position:relative;}
  .fee-perso{width:min(190px,40vw);height:auto;display:block;animation:feeFlotte 3s ease-in-out infinite;transform-origin:50% 70%;}
  @keyframes feeFlotte{0%,100%{transform:translateY(0) rotate(-1deg)}50%{transform:translateY(-9px) rotate(1deg)}}
  .fee-ailes{animation:feeAiles 1.5s ease-in-out infinite;transform-origin:300px 300px;} @keyframes feeAiles{0%,100%{transform:scaleX(1)}50%{transform:scaleX(.9)}}
  .fee-bras{animation:feeCoup 2s cubic-bezier(.36,.07,.3,1) infinite;transform-origin:340px 342px;} @keyframes feeCoup{0%,40%{transform:rotate(0)}52%{transform:rotate(-26deg)}62%{transform:rotate(22deg)}72%{transform:rotate(-6deg)}82%,100%{transform:rotate(0)}}
  .fee-halo{animation:feeHalo 2s ease-in-out infinite;transform-origin:405px 247px;} @keyframes feeHalo{0%,45%,100%{opacity:.5;transform:scale(.85)}62%{opacity:1;transform:scale(1.35)}}
  .fee-yeux{animation:feeCligne 4.5s infinite;transform-origin:300px 232px;} @keyframes feeCligne{0%,94%,100%{transform:scaleY(1)}97%{transform:scaleY(.1)}}
  .fee-plante{width:min(140px,30vw);height:auto;display:block;} .fee-plant{display:none;}
  .fee-plant.on{display:block;animation:feePousse .55s cubic-bezier(.2,1.6,.4,1),feeBalance 3.2s ease-in-out .55s infinite;transform-origin:110px 172px;}
  @keyframes feePousse{from{transform:scale(.15) translateY(20px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}} @keyframes feeBalance{0%,100%{transform:rotate(-1.5deg)}50%{transform:rotate(1.5deg)}}
  .fee-etincelles{position:absolute;left:52%;top:34%;width:1px;height:1px;} .fee-et{position:absolute;width:9px;height:9px;background:#B5D95A;border-radius:2px;box-shadow:0 0 8px rgba(141,198,63,.9);animation:feeVole 1.4s ease-in forwards;}
  @keyframes feeVole{0%{transform:translate(0,0) scale(.4);opacity:0}20%{opacity:1}100%{transform:translate(var(--dx),var(--dy)) scale(1.1) rotate(220deg);opacity:0}}
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
  <select id="format" class="btn btn-ghost" title="Format" style="padding:9px 10px;width:auto;"><option>A4</option><option>A3</option><option>A5</option></select>
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
      <h3 id="h2">Pictogrammes à afficher</h3>
      <div class="cases" id="cases"></div>
    </div>
    <div class="grp">
      <h3 id="h3">Modèle</h3>
      <div class="modeles" id="modeles"></div>
      <div class="modeles" id="modelesPlus" style="display:none;margin-top:8px;"></div>
      <button class="btn btn-ghost" id="btnPlus" style="width:100%;margin-top:8px;justify-content:center;font-size:12.5px;">Voir plus ▾</button>
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

<!-- Fée -->
<div class="fee-back" id="feeBack" aria-hidden="true"><div class="fee-scene"><div class="fee-duo">
  <svg class="fee-perso" viewBox="130 20 360 490" xmlns="http://www.w3.org/2000/svg">
    <defs><linearGradient id="fS" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FBE1C4"/><stop offset="1" stop-color="#F3CBA3"/></linearGradient><linearGradient id="fH" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#94512F"/><stop offset="1" stop-color="#6E3A22"/></linearGradient><linearGradient id="fD" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#A9D454"/><stop offset="1" stop-color="#84BD3C"/></linearGradient><radialGradient id="fG"><stop offset="0" stop-color="#EAF6CE" stop-opacity=".85"/><stop offset=".5" stop-color="#C9E58B" stop-opacity=".4"/><stop offset="1" stop-color="#8DC63F" stop-opacity="0"/></radialGradient></defs>
    <g class="fee-ailes"><path d="M298 318 Q 156 330 140 160 Q 270 152 298 318 Z" fill="#1E6B33"/><path d="M280 296 Q 186 306 176 192 Q 256 186 280 296 Z" fill="#8DC63F"/><path d="M302 318 Q 444 330 460 160 Q 330 152 302 318 Z" fill="#1E6B33"/><path d="M320 296 Q 414 306 424 192 Q 344 186 320 296 Z" fill="#8DC63F"/><path d="M298 330 Q 178 322 170 456 Q 262 466 298 330 Z" fill="#1E6B33"/><path d="M302 330 Q 422 322 430 456 Q 338 466 302 330 Z" fill="#1E6B33"/></g>
    <ellipse cx="274" cy="492" rx="16" ry="9" fill="#1E6B33"/><ellipse cx="326" cy="492" rx="16" ry="9" fill="#1E6B33"/>
    <path d="M258 330 Q 300 318 342 330 L 352 422 Q 300 438 248 422 Z" fill="url(#fD)"/><path d="M288 298 L 312 298 L 311 336 L 289 336 Z" fill="#F3CBA3"/>
    <circle cx="300" cy="220" r="92" fill="url(#fS)"/><circle cx="300" cy="72" r="37" fill="url(#fH)"/>
    <path d="M204 258 Q 184 112 300 88 Q 416 112 396 258 Q 386 262 380 252 Q 386 202 356 178 Q 336 158 300 152 Q 264 158 244 178 Q 214 202 220 252 Q 214 262 204 258 Z" fill="url(#fH)"/>
    <g class="fee-yeux"><circle cx="262" cy="232" r="20" fill="#1E4020"/><circle cx="338" cy="232" r="20" fill="#1E4020"/><circle cx="269" cy="224" r="7" fill="#fff"/><circle cx="345" cy="224" r="7" fill="#fff"/></g>
    <path d="M280 270 Q 300 292 320 270 Q 312 286 300 286 Q 288 286 280 270 Z" fill="#1A1A1A"/>
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
// Attributs : id, libellé FR, libellé NL, type (val=valeur / bool=badge), icône
const ATTRS=[
  {id:"emplacement",fr:"Emplacement",nl:"Standplaats",type:"val",i:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.4 1.4M17.6 17.6 19 19M5 19l1.4-1.4M17.6 6.4 19 5"/>'},
  {id:"arrosage",fr:"Arrosage",nl:"Water",type:"val",i:'<path d="M12 2s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/>'},
  {id:"hauteur",fr:"Hauteur",nl:"Hoogte",type:"val",i:'<path d="M12 3v18M8 7l4-4 4 4M8 17l4 4 4-4"/>'},
  {id:"largeur",fr:"Largeur",nl:"Breedte",type:"val",i:'<path d="M3 12h18M7 8l-4 4 4 4M17 8l4 4-4 4"/>'},
  {id:"floraison",fr:"Floraison",nl:"Bloeitijd",type:"val",i:'<circle cx="12" cy="12" r="3"/><path d="M12 4a2.4 2.4 0 0 0 0 5M12 20a2.4 2.4 0 0 0 0-5M4 12a2.4 2.4 0 0 0 5 0M20 12a2.4 2.4 0 0 0-5 0"/>'},
  {id:"couleur",fr:"Couleur",nl:"Bloemkleur",type:"val",i:'<circle cx="13.5" cy="7" r="1.3"/><circle cx="17" cy="11" r="1.3"/><circle cx="8" cy="8" r="1.3"/><circle cx="7" cy="13" r="1.3"/><path d="M12 2a10 10 0 1 0 1 20 1.8 1.8 0 0 0 1.4-3 1.8 1.8 0 0 1 1.4-3H18a4 4 0 0 0 4-4A10 10 0 0 0 12 2z"/>'},
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
const L={fr:{h1:"Photo & nom",nom:"Nom de la plante",gen:"Générer avec l'IA",h2:"Pictogrammes à afficher",h3:"Modèle",print:"Obtenir l'affiche",titre:"Nom de la plante",latin:"Nom scientifique",photo:"Photo de la plante",fee:"La fée identifie votre plante…"},
       nl:{h1:"Foto & naam",nom:"Naam van de plant",gen:"Genereer met AI",h2:"Pictogrammen tonen",h3:"Sjabloon",print:"Affiche ophalen",titre:"Naam van de plant",latin:"Wetenschappelijke naam",photo:"Foto van de plant",fee:"De fee herkent je plant…"}};

let lang="fr", data={}, actifs={};
ATTRS.forEach(a=>actifs[a.id]=true); // tous cochés par défaut

// --- Cases à cocher ---
function rendreCases(){
  document.getElementById('cases').innerHTML=ATTRS.map(a=>
    '<label class="case"><input type="checkbox" data-a="'+a.id+'"'+(actifs[a.id]?' checked':'')+'>'+a[lang]+'</label>').join('');
  document.querySelectorAll('.case input').forEach(c=>c.addEventListener('change',()=>{ actifs[c.dataset.a]=c.checked; rendrePictos(); }));
}
// --- Pictogrammes sur l'affiche ---
function rendrePictos(){
  const html=ATTRS.filter(a=>actifs[a.id]).map(a=>{
    if(a.type==='val'){ const v=data[a.id]; if(!v) return ''; return '<div class="picto"><div class="pic-ic">'+S(a.i)+'</div><div class="pic-k">'+a[lang]+'</div><div class="pic-v">'+esc(v)+'</div></div>'; }
    if(data[a.id]===true){ return '<div class="picto"><div class="pic-ic">'+S(a.i)+'</div><div class="pic-v">'+a[lang]+'</div></div>'; }
    return '';
  }).join('');
  document.getElementById('pictos').innerHTML=html;
}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

// --- Langue ---
function majLangue(){
  const t=L[lang];
  document.getElementById('h1').textContent=t.h1; document.getElementById('lblNom').textContent=t.nom;
  document.getElementById('lblGen').textContent=t.gen; document.getElementById('h2').textContent=t.h2;
  document.getElementById('h3').textContent=t.h3; document.getElementById('lblPrint').textContent=t.print;
  document.getElementById('feeTxt').textContent=t.fee;
  const ti=document.getElementById('aTitre'); if(!data.nom_commun) ti.textContent=t.titre;
  const la=document.getElementById('aLatin'); if(!data.nom_latin) la.textContent=t.latin;
  rendreCases(); rendrePictos();
}
document.querySelectorAll('#langSeg button').forEach(b=>b.addEventListener('click',()=>{
  document.querySelectorAll('#langSeg button').forEach(x=>x.classList.remove('on')); b.classList.add('on');
  lang=b.dataset.l; majLangue();
}));

// --- Photo ---
let photoData="";
const drop=document.getElementById('drop'),file=document.getElementById('file'),photo=document.getElementById('photo');
drop.addEventListener('click',()=>file.click());
file.addEventListener('change',e=>{ if(e.target.files[0]) charger(e.target.files[0]); });
['dragover'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='#4fb06a';}));
['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='';}));
drop.addEventListener('drop',e=>{ if(e.dataTransfer.files[0]) charger(e.dataTransfer.files[0]); });
function charger(f){const r=new FileReader();r.onload=()=>{photoData=r.result;const pb=document.getElementById('aPrix').outerHTML;photo.innerHTML='<img src="'+photoData+'">'+pb;drop.classList.add('plein');drop.innerHTML='<img src="'+photoData+'">';};r.readAsDataURL(f);}

document.getElementById('nom').addEventListener('input',e=>{ if(e.target.value.trim()) document.getElementById('aTitre').textContent=e.target.value; });

// --- Génération IA ---
document.getElementById('btnGen').addEventListener('click',generer);
async function generer(){
  const nom=document.getElementById('nom').value.trim();
  if(!photoData && !nom){ alert('Ajoutez une photo ou saisissez un nom.'); return; }
  feeShow(L[lang].fee);
  try{
    const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({nom,photo:photoData,lang})});
    const d=await rep.json();
    if(!rep.ok) throw new Error(d.erreur||'Erreur inattendue.');
    data=d;
    if(d.nom_commun) document.getElementById('aTitre').textContent=d.nom_commun;
    if(d.nom_latin) document.getElementById('aLatin').textContent=d.nom_latin;
    rendrePictos();
  }catch(e){ alert('😕 '+(e.message||'Le serveur ne répond pas.')); }
  finally{ feeHide(); }
}

// --- Modèles ---
const MODELES=[{n:"Classique",m:"",c:"#1c6b41",d:"#0e3a22"},{n:"Nature",m:"tpl-nature",c:"#5a8f2e",d:"#33591a"},{n:"Orange",m:"tpl-orange",c:"#d97a2b",d:"#a1531a"},{n:"Rose",m:"tpl-rose",c:"#c25f96",d:"#8c3d69"},{n:"Violet",m:"tpl-violet",c:"#7d5aa8",d:"#523870"},{n:"Bleu",m:"tpl-bleu",c:"#3a72b5",d:"#254e80"},{n:"Turquoise",m:"tpl-turquoise",c:"#2a9d8f",d:"#186b61"},{n:"Rouge",m:"tpl-rouge",c:"#c2453a",d:"#8a2c24"},{n:"Or",m:"tpl-or",c:"#c0922c",d:"#8a6718"},{n:"Minimal",m:"tpl-minimal",c:"#3a3a3a",d:"#111"}];
const swatch=x=>'<div class="mod'+(x.m===""?" actif":"")+'" data-m="'+x.m+'"><div class="vig" style="background:linear-gradient(150deg,'+x.c+','+x.d+')"></div>'+x.n+'</div>';
document.getElementById('modeles').innerHTML=MODELES.slice(0,3).map(swatch).join('');
document.getElementById('modelesPlus').innerHTML=MODELES.slice(3).map(swatch).join('');
document.querySelectorAll('.mod').forEach(m=>m.addEventListener('click',()=>{document.querySelectorAll('.mod').forEach(x=>x.classList.remove('actif'));m.classList.add('actif');document.getElementById('affiche').className='affiche'+(m.dataset.m?' '+m.dataset.m:'');}));
const plus=document.getElementById('modelesPlus'),btnPlus=document.getElementById('btnPlus');
btnPlus.addEventListener('click',()=>{const o=plus.style.display!=='none';plus.style.display=o?'none':'grid';btnPlus.textContent=o?'Voir plus ▾':'Voir moins ▴';});
function imprimer(){document.getElementById('printFmt').textContent='@page{size:'+document.getElementById('format').value+' portrait;margin:10mm}';window.print();}

// --- Fée ---
const feeBack=document.getElementById('feeBack'),feeTxt=document.getElementById('feeTxt'),feeEt=document.getElementById('feeEt');
let plantes=null,etinc=null,pIdx=-1;
function pousse(){const all=document.querySelectorAll('.fee-plant');all.forEach(g=>g.classList.remove('on'));pIdx=(pIdx+1)%all.length;all[pIdx].classList.add('on');}
function etincelle(){if(!feeBack.classList.contains('on'))return;const s=document.createElement('span');s.className='fee-et';s.style.setProperty('--dx',(60+Math.random()*70)+'px');s.style.setProperty('--dy',(40+Math.random()*60)+'px');s.style.left=(Math.random()*14-7)+'px';feeEt.appendChild(s);setTimeout(()=>s.remove(),1500);}
function feeShow(t){feeTxt.textContent=t||'…';feeBack.classList.add('on');pIdx=-1;pousse();plantes=setInterval(pousse,2200);etinc=setInterval(etincelle,650);}
function feeHide(){feeBack.classList.remove('on');clearInterval(plantes);clearInterval(etinc);}

rendreCases();
</script>
</body>
</html>
