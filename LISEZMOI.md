# famiPortail 🌿

Le portail interne Famiflora qui regroupe tous les outils de l'entreprise.

## Structure du projet

```
famiportail/
├── index.html              ← Page d'accueil du portail (hub des outils)
├── LISEZMOI.md             ← Ce fichier
├── assets/                 ← Ressources partagées par tous les outils
│   ├── css/style.css       ← Styles communs (couleurs, boutons, badges…)
│   └── img/
│       ├── logo.png        ← Logo Famiflora
│       └── fee/            ← La mascotte (expressions + animation baguette)
└── famicom/
    └── index.html          ← famiCom : les communications officielles
```

## Comment ouvrir le portail

Double-cliquez simplement sur `famiportail/index.html` — tout fonctionne
dans le navigateur, sans serveur ni installation.

## Comment ajouter un nouvel outil

1. Créez un dossier à la racine, ex. `famirh/`
2. Ajoutez-y un `index.html` qui commence par :
   ```html
   <link rel="stylesheet" href="../assets/css/style.css">
   ```
   pour hériter automatiquement de l'identité Famiflora.
3. Dans `index.html` (racine), remplacez la tuile « Bientôt disponible »
   correspondante par un lien `<a class="tuile" href="famirh/index.html">`.

## famiCom en bref

- ✦ Badge « Officiel » sur chaque annonce → la source fiable unique
- 📌 Annonces épinglées en haut du flux
- Filtres par catégorie (Direction, RH, Magasins, IT, Événements) + recherche
- Bouton « + Publier » : les annonces sont conservées dans le navigateur
  (localStorage) — parfait pour la démonstration

## Déploiement sur IONOS 🚀

Le projet est prêt pour votre hébergement IONOS : famiCom inclut
`famicom/api.php` qui stocke les annonces dans une base SQLite
**partagée par tous les utilisateurs**.

### 1. Avant l'envoi : changez le code de publication !

Ouvrez `famicom/api.php` et modifiez la ligne :
```php
const CODE_PUBLICATION = 'famiflora2026';
```
Choisissez un code que seuls les publicateurs autorisés connaîtront.

### 2. Envoyer les fichiers

**Option A — Gestionnaire de fichiers IONOS (le plus simple)**
1. Connectez-vous sur ionos.fr → votre contrat d'hébergement
2. Ouvrez « Espace web » (WebspaceExplorer)
3. Téléversez tout le contenu du dossier `famiportail/` dans le
   répertoire de votre site (ou dans un sous-dossier `/famiportail/`
   si votre site principal doit rester à la racine)

**Option B — SFTP (FileZilla)**
1. Dans l'espace client IONOS : « Accès SFTP » → notez hôte,
   identifiant et mot de passe
2. Dans FileZilla : hôte = `access...webspace-host.com`, port 22
3. Glissez-déposez le dossier `famiportail/`

### 3. Vérifier

- `https://votre-domaine.be/famiportail/` → le portail s'affiche
- Ouvrez famiCom : l'annonce de bienvenue apparaît (créée
  automatiquement au premier accès)
- Publiez un test avec votre code → ouvrez la page depuis un autre
  appareil : l'annonce est visible partout ✅

### Notes techniques

- La base est créée automatiquement dans `famicom/data/famicom.sqlite`
- Le fichier `famicom/data/.htaccess` bloque tout téléchargement
  direct de la base (gardez-le !)
- Aucun MySQL à configurer — mais si vous préférez MySQL plus tard,
  seule la ligne de connexion PDO dans `api.php` change
- Ouvert en local (double-clic), famiCom bascule automatiquement en
  mode démo (localStorage) : pratique pour tester des modifications

### Idées pour la suite

- Restreindre l'accès au portail entier (protection par mot de passe
  IONOS ou `.htaccess` + `.htpasswd`)
- Bouton « supprimer / modifier » une annonce (réservé au code)
- Notifications par e-mail lors d'une nouvelle annonce épinglée
