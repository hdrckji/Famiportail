# CLAUDE.md — famiPortail

Contexte du projet pour Claude Code (ou tout assistant IA). **Lis ce fichier en premier**, puis regarde `## À FAIRE` en bas : c'est la feuille de route.

## Qu'est-ce que c'est

**famiPortail** = le portail interne de **Famiflora** (jardinerie belge). Un **écran d'accueil type iPhone** (springboard) qui regroupe toutes les apps internes ; on tape une icône, l'app s'ouvre. Déployé sur **Railway** depuis le repo GitHub **hdrckji/Famiportail** (auto-déploiement à chaque push sur `main`).

## Stack & déploiement

- **PHP 8.2 + Apache** dans une image **Docker** (`Dockerfile`), déployée sur **Railway**.
- **MySQL** = base partagée (celle de **famiformation**, importée sur Railway). Connexion via `DATABASE_URL`. Extension `pdo_mysql` (+ `pdo_sqlite` encore utilisé par le vieux famicom/api.php).
- Port dynamique Railway géré par `docker-entrypoint.sh` (adapte Apache à `$PORT`, corrige aussi le bug MPM « More than one MPM loaded »).
- **Tout est en PHP dans le monorepo**, y compris **famibotanic** (reconstruit en PHP — plus de Next.js, plus de service séparé). Un seul service, une seule base MySQL.
- Fins de ligne **LF** forcées sur les `.sh` (`.gitattributes`). Secrets en **variables d'env**, jamais dans git. Données (`.sqlite`) hors git, sur volume.

### Variables d'environnement Railway
- `DATABASE_URL` — MySQL (`mysql://…`). Sur le service portail : `${{MySQL.MYSQL_URL}}`.
- `ANTHROPIC_API_KEY` — requis par famiRayon (et le futur famibotanic).
- `CODE_ACCES_RAYON` (défaut `famiflora`), `CODE_PUBLICATION` (famiCom legacy).
- Volume monté sur `/var/www/html/data` (bases SQLite legacy).

## Authentification

- Le bureau `index.php` **exige la connexion** (`login.php` / `logout.php` / `auth.php` / `db.php`). Session PHP `famiportail`, CSRF, `password_verify`.
- Comptes = table **`utilisateurs`** de la base famiformation (identifiant + mot_de_passe hachés — compatibles). Respecte `account_activation_pending`.
- Accès aux outils par profil : table **`portail_acces`** (user_id → outils, `*` ou CSV) ; à défaut, défaut par rôle (admin/teamcoach → tous). Le bureau filtre les icônes selon `outils`.
- ⚠️ **Seul le bureau est protégé** : les pages d'outils (`famicom/*`, `famirayon/*`, `famidata/*`, `cloud/*`) sont accessibles par URL directe sans session. → voir À FAIRE.

## Structure & apps

```
index.php            → bureau/springboard (protégé). Injecte window.PORTAIL (user, outils).
login.php logout.php auth.php db.php   → connexion + session + MySQL
assets/js/desktop.js → catalogue des apps (const APPS), génère les icônes ; tap = navigation
assets/css/desktop.css → look springboard iPhone (fond vert profond)
famicom/index.html   → messagerie + actus (voir ci-dessous)
famirayon/index.html → conseil mise en rayon par IA (voir ci-dessous)
famidata/index.html  → data hub (STARTER seulement, voir À FAIRE)
cloud/index.html     → gestionnaire de fichiers (démo localStorage)
famibotanic/         → générateur d'affiches plantes (PHP) : index.php (éditeur) + api.php (IA vision)
```

Ajouter une app au bureau = **une ligne dans `APPS`** (`assets/js/desktop.js`) : `{id, nom, url, glyphe, grad, [pastille], [bientot]}`. `url` peut être locale ou une adresse externe.

## Direction visuelle (IMPORTANT — validé par l'utilisateur)

- **Moderne et sobre**, PAS enfantin. Référence : écran d'accueil iOS / Linear / Notion.
- **Accueil = springboard** d'icônes d'apps sur fond vert Famiflora profond ; tap → l'app s'ouvre (navigation, PAS de fenêtres d'ordinateur).
- Apps internes = **thème sombre pro**, icônes = **SVG trait fin** (jamais d'emoji comme icône d'app), typo **système** (pas de police ronde).
- **Interdits** (rejetés par l'utilisateur) : fenêtres d'ordi (window manager), fée cartoon en décor, scène nature dessinée, emojis-icônes, vert « bonbon », police Fredoka.
- **Exception fée** : la fée qui **fait pousser des plantes sur fond sombre** (reprise de famiformation) est appréciée — utilisée UNIQUEMENT comme **écran de chargement** dans famicom/famirayon (pas en décor).
- **Couleur par app** (icône + accent interne) : famiCom = **rose** (pétale de rose, `#e79ac2→#c25f96`, accent interne `--brand:#e888bb`), Famidata = **rouge** (`#e0655e→#b1362f`), famiRayon = ambre, Cloud = teal, portail = vert.
- UI **en français**.

## État des apps

- **famiCom** (`famicom/index.html`) : UI moderne rose = **Actus** (feed type Instagram, publication réservée équipe com) + **Messages** (groupes + chats individuels, type WhatsApp). ⚠️ **Données d'EXEMPLE (front-end only)** — pas de backend. Écran de chargement = fée qui pousse les plantes.
- **famiRayon** (`famirayon/index.html`) : conseil de mise en rayon par IA. **Fonctionnel** : `famirayon/api.php` relaie vers l'API Anthropic (modèle `claude-sonnet-5`, thinking désactivé). Clé via `ANTHROPIC_API_KEY`, code d'accès via `CODE_ACCES_RAYON`. Fée en chargement.
- **famidata** (`famidata/index.html`) : **STARTER seulement** (KPIs + tableau produits en données d'exemple). Rouge. À construire (voir À FAIRE).
- **cloud** (`cloud/index.html`) : gestionnaire de fichiers **démo** (localStorage). À remplacer par un vrai stockage serveur.
- **famibotanic** (`famibotanic/index.php`) : générateur d'**affiches plantes clients** (PHP). L'employé dépose une **photo + le nom** → `api.php` appelle **Claude (vision)** → remplit l'affiche → **modèles + zones éditables + cases à cocher** des infos → impression/PDF. Réutilise `../auth.php` (session) et `../db.php` (MySQL). Fée en chargement. Accent vert. **TODO** : sauvegarde des affiches (table `famibotanic_affiches`).

---

## À FAIRE (feuille de route — par priorité)

### 1. Famidata — construire le vrai (PHP + MySQL)
**But** : chercher des données + stats/dashboards (principal), avec aussi édition et exports (bonus).
**Règles décidées avec l'utilisateur :**
- **Toutes les données dans NOTRE propre base MySQL** (celle du monorepo), tables préfixées **`famidata_`**. **PAS de connexion live à Becosoft** (leur ERP actuel).
- Les données **entrent par import** d'exports Becosoft (**CSV/Excel**) — puis Famidata devient la base de référence.
- Fonctions : **recherche + filtres + tri**, **statistiques / tableaux de bord** (KPIs, graphes), **édition** des lignes, **export** (Excel/CSV/PDF).
- App **PHP + MySQL** dans le monorepo (côté Apache), PAS Next.js. Thème sombre, accent **rouge** (voir le starter).
- **Décision en attente** : soit l'utilisateur fournit un **échantillon d'export Becosoft** (pour caler le schéma exact), soit faire un **importeur CSV générique** (détection auto des colonnes). Par défaut si rien : importeur générique.

### 2. famiCom — backend réel
Remplacer les données d'exemple par du **MySQL** (tables `famicom_*`) : **posts** (Actus) + **messages** (groupes + directs). Brancher l'**auth** (qui est connecté via la session portail). **Permission** : seules les personnes de l'**équipe com** peuvent publier une Actu. Garder le thème rose + la fée en chargement.

### 3. famibotanic — finir (générateur d'affiches plantes, PHP)
**Déjà construit** : `index.php` (éditeur : photo+nom → bouton « Générer avec l'IA », modèles, zones éditables, cases à cocher des infos, impression/PDF, fée en chargement) + `api.php` (envoie la photo à **Claude vision** → renvoie la fiche JSON). PHP dans le monorepo, session + MySQL partagés. **Reste à faire** : **sauvegarde/rechargement** des affiches (table `famibotanic_affiches` via `../db.php`), récupérer **prix/code** depuis Famidata si dispo, ajouter des modèles.

### 4. Sécurité — protéger chaque outil
Aujourd'hui seul `index.php` vérifie la session. Mettre chaque outil (et ses `api.php`) derrière `exigerConnexion()` de `auth.php`. Migrer aussi le vieux famicom/api.php (SQLite) vers MySQL.

### 5. Cloud — vrai stockage serveur
Remplacer la démo localStorage par un stockage serveur (fichiers sur volume + table MySQL des métadonnées).

### 6. Logos
Intégrer le jeu de logos validé (famiCom = **pétale de rose**) : icônes du bureau + en-tête de chaque app. Planche proposée : `C:\Users\enyls\Desktop\logos-famiflora.html`.

### 7. Mettre en ligne
S'assurer que Railway tourne : brancher `DATABASE_URL` (MySQL importé), vérifier le fix MPM, monter le volume, définir les variables d'env. Déployer famibotanic comme service séparé.

---

## Conventions
- UI et messages **en français**.
- Pas d'emoji comme icône d'app (SVG trait fin). Thème sombre pour les apps internes.
- Secrets en variables d'env. Jamais de `.sqlite` / données dans git.
- Pour montrer un rendu à l'utilisateur (qui lance difficilement les projets en local) : écrire un HTML autonome dans `C:\Users\enyls\Desktop\apercu-*.html` et l'ouvrir avec PowerShell `Start-Process`.
