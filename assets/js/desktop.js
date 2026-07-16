/* ============================================================
   famiPortail — Écran d'accueil (springboard type iPhone)
   Chaque app = une icône. Tap → l'app s'ouvre (navigation).
   ============================================================ */
(function () {
  "use strict";

  // Glyphes (SVG trait fin, style moderne)
  const G = {
    com:      '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    rayon:    '<path d="M2 7l2-4h16l2 4"/><path d="M4 7v13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7"/><path d="M9 12h6"/>',
    cloud:    '<path d="M17.5 19a4.5 4.5 0 0 0 .5-8.98A6 6 0 0 0 6.3 9.5 4 4 0 0 0 7 19h10.5z"/>',
    data:     '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    botanic:  '<path d="M7 20h10"/><path d="M12 20V10"/><path d="M12 10C12 6.5 9 4.5 5 4.5c0 3.8 3 6 7 5.5z"/><path d="M12 12c0-3 3-5 7-5 0 3.8-3 6-7 5z"/>',
    rh:       '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    planning: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    stock:    '<path d="M21 8V21H3V8"/><path d="M1 3h22v5H1zM10 12h4"/>',
    doc:      '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
    idees:    '<path d="M9 18h6M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1.5.5 2.5 1.5 3.5.76.76 1.23 1.52 1.41 2.5"/>',
  };

  // Catalogue : url locale OU adresse d'une autre app web (https://...)
  const APPS = [
    { id: "famicom",     nom: "famiCom",      url: "famicom/index.html",   glyphe: G.com,      grad: "linear-gradient(150deg,#e79ac2,#c25f96)", pastille: "✦" },
    { id: "famirayon",   nom: "famiRayon",    url: "famirayon/index.html", glyphe: G.rayon,    grad: "linear-gradient(150deg,#e0aa46,#b67d24)", pastille: "✨" },
    { id: "cloud",       nom: "Espace Cloud", url: "cloud/index.html",     glyphe: G.cloud,    grad: "linear-gradient(150deg,#54abc2,#2f7d92)" },
    { id: "famidata",    nom: "Famidata",     url: "famidata/index.html",  glyphe: G.data,     grad: "linear-gradient(150deg,#e0655e,#b1362f)" },
    { id: "famibotanic", nom: "famiBotanic",  url: "famibotanic/index.php", glyphe: G.botanic, grad: "linear-gradient(150deg,#5cc07a,#2e8b57)" },
    { id: "famirh",      nom: "famiRH",       glyphe: G.rh,       grad: "linear-gradient(150deg,#8a78b8,#5a4b86)", bientot: true },
    { id: "famiplanning",nom: "famiPlanning", glyphe: G.planning,grad: "linear-gradient(150deg,#d1727f,#9c4b5a)", bientot: true },
    { id: "famistock",   nom: "famiStock",    glyphe: G.stock,   grad: "linear-gradient(150deg,#5487c2,#2f5a92)", bientot: true },
    { id: "famidoc",     nom: "famiDoc",      glyphe: G.doc,     grad: "linear-gradient(150deg,#3fae99,#1f7d6b)", bientot: true },
    { id: "famiidees",   nom: "famiIdées",    glyphe: G.idees,   grad: "linear-gradient(150deg,#e0aa46,#c07a24)", bientot: true },
  ];

  const board = document.getElementById("springboard");

  // Filtrage par profil
  const PORTAIL = window.PORTAIL || { outils: "*", user: { nom: "" } };
  function autorise(app) {
    if (app.bientot) return true;
    if (PORTAIL.outils === "*") return true;
    const permis = String(PORTAIL.outils).split(",").map((s) => s.trim()).filter(Boolean);
    return permis.includes(app.id);
  }

  const NS = "http://www.w3.org/2000/svg";
  function svg(paths) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" ' +
           'stroke-linecap="round" stroke-linejoin="round">' + paths + '</svg>';
  }

  APPS.filter(autorise).forEach((app) => {
    const el = document.createElement(app.bientot ? "div" : "a");
    el.className = "app-icone" + (app.bientot ? " bientot" : "");
    el.setAttribute("role", "listitem");
    if (!app.bientot) { el.href = app.url; el.target = app.cible || "_self"; }
    else { el.tabIndex = 0; }

    el.innerHTML =
      '<div class="tuile-wrap">' +
        '<div class="tuile" style="background:' + app.grad + '">' + svg(app.glyphe) + "</div>" +
        (app.pastille ? '<span class="pastille">' + app.pastille + "</span>" : "") +
      "</div>" +
      '<div class="nom">' + app.nom + "</div>";

    if (app.bientot) {
      const dire = () => toast(app.nom + " arrive bientôt");
      el.addEventListener("click", dire);
      el.addEventListener("keydown", (e) => { if (e.key === "Enter") dire(); });
    }
    board.appendChild(el);
  });

  /* ---------- Toast ---------- */
  let toastTimer = null;
  function toast(msg) {
    const t = document.createElement("div");
    t.className = "toast";
    t.textContent = msg;
    document.body.appendChild(t);
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.remove(), 2000);
  }

  /* ---------- Horloge ---------- */
  const elH = document.getElementById("heure");
  const elD = document.getElementById("date-jour");
  const jours = ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"];
  const mois = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
  function majHorloge() {
    if (!elH) return;
    const d = new Date();
    elH.textContent = String(d.getHours()).padStart(2, "0") + ":" + String(d.getMinutes()).padStart(2, "0");
    if (elD) elD.textContent = jours[d.getDay()] + " " + d.getDate() + " " + mois[d.getMonth()];
  }
  majHorloge();
  setInterval(majHorloge, 15000);
})();
