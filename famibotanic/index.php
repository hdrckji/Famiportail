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
  button{font-family:inherit;}
  .topbar{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border);}
  .retour{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;color:var(--soft);text-decoration:none;background:var(--panel2);border:1px solid var(--border);}
  .retour:hover{color:var(--ink);}
  .mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(150deg,var(--brand),var(--brand-deep));display:grid;place-items:center;color:#06130c;}
  .t{font-weight:700}.t span{color:var(--brand)}.t .s{font-size:12px;font-weight:500;color:var(--faint)}
  .btn{border:none;border-radius:10px;padding:9px 15px;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
  .btn-brand{background:linear-gradient(135deg,#5fd07f,var(--brand-deep));color:#06130c;}
  .btn-ghost{background:var(--panel2);color:var(--ink);border:1px solid var(--border);}
  .push{margin-left:auto;}

  .wrap{display:grid;grid-template-columns:360px 1fr;height:calc(100vh - 59px);}
  .panneau{border-right:1px solid var(--border);overflow-y:auto;padding:18px;}
  .aperc{overflow-y:auto;padding:28px;display:grid;place-items:start center;background:#0b120e;}
  .grp{margin-bottom:18px;} .grp h3{font-size:11.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--soft);margin:0 0 10px;}
  label.champ{display:block;font-size:12px;font-weight:700;color:var(--soft);margin:10px 0 5px;}
  input[type=text]{width:100%;background:var(--panel2);border:1px solid var(--border);color:var(--ink);border-radius:10px;padding:10px 12px;font:inherit;font-size:14px;}
  input:focus{outline:none;border-color:var(--brand);}
  .drop{border:2px dashed var(--border);border-radius:14px;padding:18px;text-align:center;color:var(--faint);cursor:pointer;background:var(--panel);}
  .drop:hover{border-color:var(--brand);color:var(--soft);}
  .drop.plein{padding:0;overflow:hidden;border-style:solid;} .drop img{width:100%;height:150px;object-fit:cover;display:block;}
  .toggles{display:flex;flex-direction:column;gap:2px;}
  .tog{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;cursor:pointer;} .tog:hover{background:var(--panel);}
  .tog input{display:none;}
  .tog .sw{width:38px;height:22px;border-radius:999px;background:var(--panel2);border:1px solid var(--border);position:relative;flex:none;transition:background .15s;}
  .tog .sw::after{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:var(--faint);transition:transform .15s,background .15s;}
  .tog input:checked + .sw{background:rgba(79,176,106,.35);border-color:var(--brand);}
  .tog input:checked + .sw::after{transform:translateX(16px);background:var(--brand);}
  .tog .lbl{font-size:13.5px;font-weight:600;} .tog .lbl small{display:block;color:var(--faint);font-weight:500;font-size:11.5px;}
  .modeles{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
  .mod{border:2px solid var(--border);border-radius:10px;padding:8px 6px;text-align:center;cursor:pointer;background:var(--panel);font-size:11.5px;font-weight:700;color:var(--soft);}
  .mod .vig{height:44px;border-radius:6px;margin-bottom:5px;} .mod.actif{border-color:var(--brand);color:var(--ink);}

  .affiche{width:420px;background:#fff;color:#1a2a20;border-radius:8px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.5);--ac:#1c6b41;}
  .affiche .photo{height:230px;background:linear-gradient(150deg,#dfeede,#bcd9be);display:grid;place-items:center;color:#6a8a72;}
  .affiche .photo img{width:100%;height:100%;object-fit:cover;} .affiche .photo .ph{font-size:13px;font-weight:700;}
  .affiche .corps{padding:20px 22px 8px;}
  .affiche .titre{font-size:27px;font-weight:800;line-height:1.05;color:var(--ac);}
  .affiche .latin{font-style:italic;color:#6a7d70;font-size:15px;margin-top:2px;}
  .affiche .famille{display:inline-block;margin-top:8px;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--ac);background:rgba(28,107,65,.1);padding:3px 9px;border-radius:999px;}
  .soins{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:16px 0 4px;}
  .soin{display:flex;align-items:center;gap:10px;background:#f4f8f2;border-radius:11px;padding:10px 12px;}
  .soin .ic{width:34px;height:34px;border-radius:9px;background:var(--ac);color:#fff;display:grid;place-items:center;flex:none;} .soin .ic svg{width:19px;height:19px;}
  .soin .k{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:#8a9a90;} .soin .v{font-size:13.5px;font-weight:700;color:#26372c;}
  .prix{display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px dashed #d7e2d6;}
  .prix .p{font-size:30px;font-weight:900;color:var(--ac);} .prix .code{font-family:ui-monospace,monospace;font-size:11px;color:#8a9a90;}
  .extras{margin-top:12px;display:flex;flex-direction:column;gap:6px;} .extra{font-size:12.5px;color:#3a4c40;} .extra b{color:var(--ac);}
  .pied{margin-top:14px;background:var(--ac);color:#fff;padding:9px 22px;display:flex;align-items:center;gap:8px;font-weight:800;font-size:13px;} .pied .leaf{width:18px;height:18px;}
  .edit{outline:none;border-radius:4px;} .edit:hover{background:rgba(79,176,106,.14);box-shadow:0 0 0 2px rgba(79,176,106,.3);}
  .off{display:none !important;}
  .affiche.nature{--ac:#2e6b3a;} .affiche.minimal{--ac:#1a1a1a;} .affiche.minimal .famille,.affiche.minimal .soin .ic,.affiche.minimal .pied{background:#1a1a1a;} .affiche.minimal .famille{color:#fff;}

  /* Fée (chargement) */
  .fee-back{position:fixed;inset:0;z-index:900;background:radial-gradient(circle at 50% 40%,#14261a,#070d09);display:none;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;}
  .fee-back.on{display:flex;opacity:1;}
  .fee-scene{text-align:center;width:min(520px,92vw);} .fee-duo{display:flex;align-items:flex-end;justify-content:center;gap:8px;position:relative;}
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
  @media(max-width:820px){.wrap{grid-template-columns:1fr;height:auto;}.panneau{border-right:none;border-bottom:1px solid var(--border);}}
  @media(prefers-reduced-motion:reduce){*{animation:none!important}}
</style>
</head>
<body>

<div class="topbar">
  <a class="retour" href="../index.php" target="_top" title="Retour au portail"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
  <div class="mark"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10M12 20V10M12 10C12 6.5 9 4.5 5 4.5c0 3.8 3 6 7 5.5zM12 12c0-3 3-5 7-5 0 3.8-3 6-7 5z"/></svg></div>
  <div class="t">fami<span>Botanic</span><div class="s">Affiche plante pour le client</div></div>
  <button class="btn btn-brand push" onclick="window.print()">⬇ Obtenir l'affiche</button>
</div>

<div class="wrap">
  <div class="panneau">
    <div class="grp">
      <h3>1 · Photo & nom</h3>
      <div class="drop" id="drop"><div id="dropTxt">📷 Cliquez ou déposez la photo de la plante</div></div>
      <input type="file" id="file" accept="image/*" hidden>
      <label class="champ" for="nom">Nom de la plante</label>
      <input type="text" id="nom" placeholder="Ex. : Monstera deliciosa">
      <button class="btn btn-brand" id="btnGen" style="width:100%;margin-top:12px;justify-content:center;">✨ Générer la fiche avec l'IA</button>
    </div>

    <div class="grp">
      <h3>2 · Infos à afficher</h3>
      <div class="toggles">
        <label class="tog"><input type="checkbox" data-b="bIdent" checked><span class="sw"></span><span class="lbl">Identité<small>Nom latin, famille</small></span></label>
        <label class="tog"><input type="checkbox" data-b="bSoins" checked><span class="sw"></span><span class="lbl">Entretien<small>Eau, lumière, difficulté, taille</small></span></label>
        <label class="tog"><input type="checkbox" data-b="bPrix" checked><span class="sw"></span><span class="lbl">Commercial<small>Prix, code produit</small></span></label>
        <label class="tog"><input type="checkbox" data-b="bExtras" checked><span class="sw"></span><span class="lbl">Conseils & plus<small>Origine, toxicité, floraison, astuce</small></span></label>
      </div>
    </div>

    <div class="grp">
      <h3>3 · Modèle d'affiche</h3>
      <div class="modeles">
        <div class="mod actif" data-m=""><div class="vig" style="background:linear-gradient(150deg,#1c6b41,#4fb06a)"></div>Classique</div>
        <div class="mod" data-m="nature"><div class="vig" style="background:linear-gradient(150deg,#2e6b3a,#7fb539)"></div>Nature</div>
        <div class="mod" data-m="minimal"><div class="vig" style="background:linear-gradient(150deg,#3a3a3a,#111)"></div>Minimal</div>
      </div>
    </div>
  </div>

  <div class="aperc">
    <div class="affiche" id="affiche">
      <div class="photo" id="photo"><span class="ph">La photo de la plante ici</span></div>
      <div class="corps">
        <div class="titre edit" contenteditable id="aTitre">Nom de la plante</div>
        <div class="latin edit" contenteditable id="aLatin" data-b="bIdent">Nom scientifique</div>
        <div><span class="famille edit" contenteditable id="aFamille" data-b="bIdent">Famille</span></div>
        <div class="soins" data-b="bSoins">
          <div class="soin"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/></svg></div><div><div class="k">Arrosage</div><div class="v edit" contenteditable id="aArrosage">—</div></div></div>
          <div class="soin"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5 19 19M5 19l1.5-1.5M17.5 6.5 19 5"/></svg></div><div><div class="k">Lumière</div><div class="v edit" contenteditable id="aLumiere">—</div></div></div>
          <div class="soin"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="k">Difficulté</div><div class="v edit" contenteditable id="aDiff">—</div></div></div>
          <div class="soin"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20V4M6 10l6-6 6 6"/></svg></div><div><div class="k">Taille adulte</div><div class="v edit" contenteditable id="aTaille">—</div></div></div>
        </div>
        <div class="prix" data-b="bPrix">
          <div class="p edit" contenteditable>0,00 €</div>
          <div class="code edit" contenteditable>Réf. —</div>
        </div>
        <div class="extras" data-b="bExtras">
          <div class="extra"><b>Origine :</b> <span class="edit" contenteditable id="aOrigine">—</span></div>
          <div class="extra"><b>⚠ Toxicité :</b> <span class="edit" contenteditable id="aToxic">—</span></div>
          <div class="extra"><b>Floraison :</b> <span class="edit" contenteditable id="aFloraison">—</span></div>
          <div class="extra"><b>Astuce :</b> <span class="edit" contenteditable id="aConseil">—</span></div>
        </div>
      </div>
      <div class="pied"><svg class="leaf" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 9-10 1 6-2 10-7 11"/></svg>Famiflora</div>
    </div>
  </div>
</div>

<!-- Fée (chargement) -->
<div class="fee-back" id="feeBack" aria-hidden="true">
  <div class="fee-scene">
    <div class="fee-duo">
      <svg class="fee-perso" viewBox="130 20 360 490" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="fS" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FBE1C4"/><stop offset="1" stop-color="#F3CBA3"/></linearGradient>
          <linearGradient id="fH" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#94512F"/><stop offset="1" stop-color="#6E3A22"/></linearGradient>
          <linearGradient id="fD" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#A9D454"/><stop offset="1" stop-color="#84BD3C"/></linearGradient>
          <radialGradient id="fG"><stop offset="0" stop-color="#EAF6CE" stop-opacity=".85"/><stop offset=".5" stop-color="#C9E58B" stop-opacity=".4"/><stop offset="1" stop-color="#8DC63F" stop-opacity="0"/></radialGradient>
        </defs>
        <g class="fee-ailes"><path d="M298 318 Q 156 330 140 160 Q 270 152 298 318 Z" fill="#1E6B33"/><path d="M280 296 Q 186 306 176 192 Q 256 186 280 296 Z" fill="#8DC63F"/><path d="M302 318 Q 444 330 460 160 Q 330 152 302 318 Z" fill="#1E6B33"/><path d="M320 296 Q 414 306 424 192 Q 344 186 320 296 Z" fill="#8DC63F"/><path d="M298 330 Q 178 322 170 456 Q 262 466 298 330 Z" fill="#1E6B33"/><path d="M302 330 Q 422 322 430 456 Q 338 466 302 330 Z" fill="#1E6B33"/></g>
        <ellipse cx="274" cy="492" rx="16" ry="9" fill="#1E6B33"/><ellipse cx="326" cy="492" rx="16" ry="9" fill="#1E6B33"/>
        <path d="M258 330 Q 300 318 342 330 L 352 422 Q 300 438 248 422 Z" fill="url(#fD)"/>
        <path d="M288 298 L 312 298 L 311 336 L 289 336 Z" fill="#F3CBA3"/>
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
    </div>
    <div class="fee-barre"><span></span></div>
    <div class="fee-txt" id="feeTxt">La fée identifie votre plante…</div>
  </div>
</div>

<script>
  let photoData="";
  const drop=document.getElementById('drop'), file=document.getElementById('file'), photo=document.getElementById('photo');
  drop.addEventListener('click',()=>file.click());
  file.addEventListener('change',e=>{ if(e.target.files[0]) charger(e.target.files[0]); });
  ['dragover'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='#4fb06a';}));
  ['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();drop.style.borderColor='';}));
  drop.addEventListener('drop',e=>{ if(e.dataTransfer.files[0]) charger(e.dataTransfer.files[0]); });
  function charger(f){ const r=new FileReader(); r.onload=()=>{ photoData=r.result; photo.innerHTML='<img src="'+photoData+'">'; drop.classList.add('plein'); drop.innerHTML='<img src="'+photoData+'">'; }; r.readAsDataURL(f); }

  document.getElementById('nom').addEventListener('input',e=>{ if(e.target.value.trim()) document.getElementById('aTitre').textContent=e.target.value; });

  document.querySelectorAll('.tog input').forEach(t=>t.addEventListener('change',()=>{
    document.querySelectorAll('[data-b="'+t.dataset.b+'"]').forEach(el=>el.classList.toggle('off',!t.checked));
  }));
  document.querySelectorAll('.mod').forEach(m=>m.addEventListener('click',()=>{
    document.querySelectorAll('.mod').forEach(x=>x.classList.remove('actif')); m.classList.add('actif');
    document.getElementById('affiche').className='affiche'+(m.dataset.m?' '+m.dataset.m:'');
  }));

  // Génération IA
  document.getElementById('btnGen').addEventListener('click',generer);
  async function generer(){
    const nom=document.getElementById('nom').value.trim();
    if(!photoData && !nom){ alert('Ajoutez une photo ou saisissez un nom.'); return; }
    feeShow('La fée identifie votre plante…');
    try{
      const rep=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({nom,photo:photoData})});
      const d=await rep.json();
      if(!rep.ok) throw new Error(d.erreur||'Erreur inattendue.');
      remplir(d);
    }catch(e){ alert('😕 '+(e.message||'Le serveur ne répond pas.')); }
    finally{ feeHide(); }
  }
  function remplir(d){
    if(d.nom_commun) document.getElementById('aTitre').textContent=d.nom_commun;
    const set=(id,v)=>{ if(v) document.getElementById(id).textContent=v; };
    set('aLatin',d.nom_latin); set('aFamille',d.famille); set('aArrosage',d.arrosage); set('aLumiere',d.lumiere);
    set('aDiff',d.difficulte); set('aTaille',d.taille); set('aOrigine',d.origine); set('aToxic',d.toxicite);
    set('aFloraison',d.floraison); set('aConseil',d.conseil);
  }

  // Fée
  const feeBack=document.getElementById('feeBack'),feeTxt=document.getElementById('feeTxt'),feeEt=document.getElementById('feeEt');
  let plantes=null,etinc=null,pIdx=-1;
  function pousse(){const all=document.querySelectorAll('.fee-plant');all.forEach(g=>g.classList.remove('on'));pIdx=(pIdx+1)%all.length;all[pIdx].classList.add('on');}
  function etincelle(){if(!feeBack.classList.contains('on'))return;const s=document.createElement('span');s.className='fee-et';s.style.setProperty('--dx',(60+Math.random()*70)+'px');s.style.setProperty('--dy',(40+Math.random()*60)+'px');s.style.left=(Math.random()*14-7)+'px';feeEt.appendChild(s);setTimeout(()=>s.remove(),1500);}
  function feeShow(t){feeTxt.textContent=t||'Un instant…';feeBack.classList.add('on');pIdx=-1;pousse();plantes=setInterval(pousse,2200);etinc=setInterval(etincelle,650);}
  function feeHide(){feeBack.classList.remove('on');clearInterval(plantes);clearInterval(etinc);}
</script>
</body>
</html>
