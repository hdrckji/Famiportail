/* ============================================================
   famiPortail — Bureau (window manager)
   Vanilla JS, sans dépendance.
   ============================================================ */
(function () {
  "use strict";

  // --- Catalogue des applications ---
  const APPS = [
    { id: "famicom",     nom: "famiCom",      icone: "📣", url: "famicom/index.html",   vedette: true },
    { id: "famirayon",   nom: "famiRayon",    icone: "🛒", url: "famirayon/index.html" },
    { id: "cloud",       nom: "Espace Cloud", icone: "☁️", url: "cloud/index.html" },
    { id: "famirh",      nom: "famiRH",       icone: "👥", bientot: true },
    { id: "famiplanning",nom: "famiPlanning", icone: "🗓️", bientot: true },
    { id: "famistock",   nom: "famiStock",    icone: "📦", bientot: true },
    { id: "famidoc",     nom: "famiDoc",      icone: "📚", bientot: true },
    { id: "famiidees",   nom: "famiIdées",    icone: "💡", bientot: true },
  ];

  const bureau       = document.getElementById("bureau");
  const zoneIcones   = document.getElementById("icones-bureau");
  const zoneTaches   = document.getElementById("taches-ouvertes");
  const menu         = document.getElementById("menu-demarrer");
  const menuListe    = document.getElementById("menu-liste");
  const btnDemarrer  = document.getElementById("btn-demarrer");

  const tactile = window.matchMedia("(hover: none)").matches;
  const fenetres = new Map();   // appId -> objet fenêtre
  let zTop = 10;
  let cascade = 0;

  /* ---------------- Icônes du bureau ---------------- */
  APPS.forEach((app) => {
    const el = document.createElement("div");
    el.className = "icone-bureau" + (app.vedette ? " vedette" : "");
    el.setAttribute("role", "listitem");
    el.tabIndex = 0;
    el.innerHTML =
      '<div class="glyphe">' + app.icone + "</div>" +
      '<div class="etiquette">' + app.nom +
      (app.bientot ? '<br><span class="badge-soon">Bientôt</span>' : "") +
      "</div>";

    const ouvrir = () => lancer(app);
    if (tactile) {
      el.addEventListener("click", ouvrir);
    } else {
      el.addEventListener("click", () => selectionner(el));
      el.addEventListener("dblclick", ouvrir);
    }
    el.addEventListener("keydown", (e) => { if (e.key === "Enter") ouvrir(); });
    zoneIcones.appendChild(el);
  });

  function selectionner(el) {
    document.querySelectorAll(".icone-bureau.sel").forEach((i) => i.classList.remove("sel"));
    el.classList.add("sel");
  }
  bureau.addEventListener("mousedown", (e) => {
    if (e.target === bureau || e.target.classList.contains("fee-deco")) {
      document.querySelectorAll(".icone-bureau.sel").forEach((i) => i.classList.remove("sel"));
    }
  });

  /* ---------------- Menu Démarrer ---------------- */
  APPS.forEach((app) => {
    const b = document.createElement("button");
    b.className = "menu-item";
    b.setAttribute("role", "menuitem");
    b.innerHTML = '<span class="m-glyphe">' + app.icone + "</span>" + app.nom;
    if (app.bientot) b.disabled = true;
    b.addEventListener("click", () => { lancer(app); fermerMenu(); });
    menuListe.appendChild(b);
  });

  function ouvrirMenu() { menu.hidden = false; btnDemarrer.setAttribute("aria-expanded", "true"); }
  function fermerMenu() { menu.hidden = true;  btnDemarrer.setAttribute("aria-expanded", "false"); }
  btnDemarrer.addEventListener("click", (e) => {
    e.stopPropagation();
    menu.hidden ? ouvrirMenu() : fermerMenu();
  });
  document.addEventListener("click", (e) => {
    if (!menu.hidden && !menu.contains(e.target) && e.target !== btnDemarrer) fermerMenu();
  });
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") fermerMenu(); });

  /* ---------------- Lancement d'une app ---------------- */
  function lancer(app) {
    if (app.bientot) { toast(app.nom + " arrive bientôt 🌱"); return; }
    if (fenetres.has(app.id)) {
      const f = fenetres.get(app.id);
      if (f.el.classList.contains("min")) restaurer(f);
      focus(f);
      return;
    }
    creerFenetre(app);
  }

  /* ---------------- Création d'une fenêtre ---------------- */
  function creerFenetre(app) {
    const el = document.createElement("section");
    el.className = "fenetre";
    el.setAttribute("role", "dialog");
    el.setAttribute("aria-label", app.nom);

    el.innerHTML =
      '<header class="fenetre-titre">' +
        '<span class="t-glyphe">' + app.icone + "</span>" +
        '<span class="t-nom">' + app.nom + "</span>" +
        '<div class="fenetre-controls">' +
          '<button class="reduire"  title="Réduire" aria-label="Réduire">—</button>' +
          '<button class="agrandir" title="Agrandir" aria-label="Agrandir">▢</button>' +
          '<button class="fermer"   title="Fermer" aria-label="Fermer">✕</button>' +
        "</div>" +
      "</header>" +
      '<div class="fenetre-corps">' +
        '<div class="voile-iframe"></div>' +
        '<iframe src="' + app.url + '" title="' + app.nom + '" loading="lazy"></iframe>' +
      "</div>" +
      '<div class="poignee-resize" title="Redimensionner"></div>';

    // Taille + position en cascade
    const largeur = Math.min(940, bureau.clientWidth - 40);
    const hauteur = Math.min(620, bureau.clientHeight - 40);
    const decal = (cascade % 6) * 28;
    cascade++;
    el.style.width  = largeur + "px";
    el.style.height = hauteur + "px";
    el.style.left = Math.max(10, (bureau.clientWidth - largeur) / 2 + decal - 70) + "px";
    el.style.top  = Math.max(10, (bureau.clientHeight - hauteur) / 2 + decal - 40) + "px";

    bureau.appendChild(el);

    const f = { id: app.id, app: app, el: el, tache: null, prevRect: null };
    fenetres.set(app.id, f);

    // Contrôles
    el.querySelector(".fermer").addEventListener("click", () => fermer(f));
    el.querySelector(".reduire").addEventListener("click", () => reduire(f));
    el.querySelector(".agrandir").addEventListener("click", () => basculerMax(f));
    el.querySelector(".fenetre-titre").addEventListener("dblclick", () => basculerMax(f));
    el.addEventListener("mousedown", () => focus(f));
    el.addEventListener("touchstart", () => focus(f), { passive: true });

    rendreDeplacable(f);
    rendreRedimensionnable(f);
    ajouterTache(f);
    focus(f);
  }

  /* ---------------- Focus / z-index ---------------- */
  function focus(f) {
    zTop += 1;
    f.el.style.zIndex = zTop;
    fenetres.forEach((g) => {
      g.el.classList.toggle("active", g === f);
      if (g.tache) g.tache.classList.toggle("active", g === f && !g.el.classList.contains("min"));
    });
  }

  /* ---------------- Réduire / restaurer / agrandir ---------------- */
  function reduire(f) {
    f.el.classList.add("min");
    if (f.tache) f.tache.classList.remove("active");
  }
  function restaurer(f) { f.el.classList.remove("min"); }
  function basculerMax(f) {
    f.el.classList.toggle("max");
    focus(f);
  }

  /* ---------------- Fermeture ---------------- */
  function fermer(f) {
    f.el.remove();
    if (f.tache) f.tache.remove();
    fenetres.delete(f.id);
  }

  /* ---------------- Barre des tâches ---------------- */
  function ajouterTache(f) {
    const b = document.createElement("button");
    b.className = "tache";
    b.setAttribute("role", "listitem");
    b.innerHTML = '<span>' + f.app.icone + '</span><span class="t-nom">' + f.app.nom + "</span>";
    b.addEventListener("click", () => {
      const estMin = f.el.classList.contains("min");
      const estActive = f.el.classList.contains("active") && !estMin;
      if (estMin) { restaurer(f); focus(f); }
      else if (estActive) { reduire(f); }
      else { focus(f); }
    });
    zoneTaches.appendChild(b);
    f.tache = b;
  }

  /* ---------------- Déplacement ---------------- */
  function rendreDeplacable(f) {
    const titre = f.el.querySelector(".fenetre-titre");
    let ox = 0, oy = 0, actif = false;

    titre.addEventListener("pointerdown", (e) => {
      if (e.target.closest(".fenetre-controls")) return;
      if (f.el.classList.contains("max")) return;
      actif = true;
      focus(f);
      const r = f.el.getBoundingClientRect();
      ox = e.clientX - r.left;
      oy = e.clientY - r.top;
      document.body.classList.add("en-manip");
      titre.setPointerCapture(e.pointerId);
    });
    titre.addEventListener("pointermove", (e) => {
      if (!actif) return;
      const maxX = bureau.clientWidth - 60;
      const maxY = bureau.clientHeight - 40;
      let x = e.clientX - ox;
      let y = e.clientY - oy;
      x = Math.min(Math.max(x, -f.el.offsetWidth + 120), maxX);
      y = Math.min(Math.max(y, 0), maxY);
      f.el.style.left = x + "px";
      f.el.style.top  = y + "px";
    });
    const fin = () => { actif = false; document.body.classList.remove("en-manip"); };
    titre.addEventListener("pointerup", fin);
    titre.addEventListener("pointercancel", fin);
  }

  /* ---------------- Redimensionnement ---------------- */
  function rendreRedimensionnable(f) {
    const poignee = f.el.querySelector(".poignee-resize");
    let actif = false, sx = 0, sy = 0, sw = 0, sh = 0;

    poignee.addEventListener("pointerdown", (e) => {
      if (f.el.classList.contains("max")) return;
      actif = true;
      focus(f);
      sx = e.clientX; sy = e.clientY;
      sw = f.el.offsetWidth; sh = f.el.offsetHeight;
      document.body.classList.add("en-manip");
      poignee.setPointerCapture(e.pointerId);
      e.preventDefault();
    });
    poignee.addEventListener("pointermove", (e) => {
      if (!actif) return;
      const w = Math.max(300, sw + (e.clientX - sx));
      const h = Math.max(200, sh + (e.clientY - sy));
      f.el.style.width  = w + "px";
      f.el.style.height = h + "px";
    });
    const fin = () => { actif = false; document.body.classList.remove("en-manip"); };
    poignee.addEventListener("pointerup", fin);
    poignee.addEventListener("pointercancel", fin);
  }

  /* ---------------- Toast ---------------- */
  let toastTimer = null;
  function toast(msg) {
    const t = document.createElement("div");
    t.className = "toast";
    t.textContent = msg;
    document.body.appendChild(t);
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.remove(), 2200);
  }

  /* ---------------- Horloge ---------------- */
  const elHeure = document.getElementById("heure");
  const elDate  = document.getElementById("date-jour");
  const jours = ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."];
  const mois  = ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."];
  function majHorloge() {
    const d = new Date();
    const hh = String(d.getHours()).padStart(2, "0");
    const mm = String(d.getMinutes()).padStart(2, "0");
    elHeure.textContent = hh + ":" + mm;
    elDate.textContent = jours[d.getDay()] + " " + d.getDate() + " " + mois[d.getMonth()];
  }
  majHorloge();
  setInterval(majHorloge, 15000);

  /* ---------------- Ouverture inter-app (ex. depuis le Cloud) ---------------- */
  window.addEventListener("message", (e) => {
    if (e.data && e.data.type === "famiportail:ouvrir") {
      const app = APPS.find((a) => a.id === e.data.app);
      if (app) lancer(app);
    }
  });

  // Ouvre famiCom par défaut au premier chargement (accueil chaleureux)
  // — commenté : on laisse le bureau vide au démarrage.
  // lancer(APPS[0]);
})();
