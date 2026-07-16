# Famibotanic

Outil interne de création de fiches produits, avec génération de contenu par IA (API Anthropic).

Deux types de fiches :

- **Fiche informative** — documentation interne pour les collaborateurs (description, caractéristiques, conseils, infos internes)
- **Fiche de vente** — argumentaire commercial (points forts, cible, réponses aux objections, prix)

Fonctionnalités : génération IA à partir d'informations brutes, édition complète du contenu, recherche et filtres, statut brouillon/publiée, impression ou export PDF (via l'impression du navigateur), paramètres d'entreprise pris en compte par l'IA, protection par mot de passe optionnelle.

> ℹ️ Cette app fait partie de la suite **famiPortail** et utilise **MySQL** (comme le reste de la suite). Voir `CLAUDE.md`.

## Stack

- Next.js 14 (App Router) + TypeScript + Tailwind CSS
- **MySQL** via le paquet `mysql2` (les tables se créent automatiquement au premier démarrage)
- API Anthropic (`@anthropic-ai/sdk`)

## Lancer en local

```bash
npm install
cp .env.example .env      # puis remplir DATABASE_URL (MySQL) et ANTHROPIC_API_KEY
npm run dev
```

L'app tourne sur http://localhost:3000. Il faut une base **MySQL** accessible (locale ou celle de Railway).

## Variables d'environnement

| Variable | Obligatoire | Description |
|---|---|---|
| `DATABASE_URL` | oui | URL **MySQL** au format `mysql://user:pass@host:port/base`. Fournie automatiquement par Railway si la base est liée au service. |
| `ANTHROPIC_API_KEY` | oui | Clé API à créer sur https://console.anthropic.com (menu API Keys). |
| `ANTHROPIC_MODEL` | non | Modèle utilisé (défaut : `claude-sonnet-4-6`). |
| `APP_PASSWORD` | non | Si défini, l'app demande un mot de passe (recommandé pour un outil interne exposé sur internet). Le nom d'utilisateur est libre. |
| `DB_PREFIX` | non | Préfixe des tables de l'app (défaut : `famibotanic_`). Permet de partager une base MySQL avec d'autres apps sans collision. |
| `DATABASE_SSL` | non | Mettre `true` si la connexion MySQL exige SSL (connexion via l'URL publique de Railway). En interne Railway, laisser vide. |

## Déploiement : GitHub + Railway

### 1. Le code est dans le dépôt famiPortail

Famibotanic vit dans le dossier `famibotanic/` du dépôt **hdrckji/Famiportail**. Comme c'est une app **Next.js** (runtime Node), elle se déploie comme **service Railway séparé** du portail PHP.

### 2. Créer le service Railway

1. Sur https://railway.app → dans le projet → **+ New** → **Deploy from GitHub repo** → choisir `Famiportail`.
2. Dans les **Settings** du service → **Root Directory** = `famibotanic` (indispensable : Railway build ce sous-dossier, pas la racine PHP).
3. Railway détecte Next.js automatiquement (build `npm run build`, démarrage `npm start`).

### 3. Ajouter MySQL

1. Dans le projet Railway → **+ New** → **Database** → **MySQL**.
2. Ouvrir le service de l'app → onglet **Variables** → **Add Variable Reference** → sélectionner `MYSQL_URL` du service MySQL, et l'affecter à `DATABASE_URL`.

### 4. Ajouter les autres variables

Dans l'onglet **Variables** du service de l'app :

- `ANTHROPIC_API_KEY` = votre clé (console.anthropic.com)
- `APP_PASSWORD` = un mot de passe de votre choix (recommandé)

### 5. Générer l'URL publique

Onglet **Settings** → **Networking** → **Generate Domain**. L'app est en ligne ; les tables se créent toutes seules à la première requête. Ajoutez ensuite cette URL comme icône sur le portail (dans `assets/js/desktop.js` du portail).

## Base de données partagée entre plusieurs apps

Si Famibotanic partage la **même base MySQL** que d'autres apps de la suite (celle de famiportail/famiformation), l'isolation se fait par **préfixe de table** (MySQL n'a pas de schéma interne comme PostgreSQL) :

1. Sur Railway, liez la même base MySQL à chaque app (variable `DATABASE_URL` en référence).
2. Pour Famibotanic, les tables sont préfixées `famibotanic_` par défaut (variable `DB_PREFIX`) — donc pas de collision avec les tables des autres apps.
3. Faites de même pour chaque autre app avec son propre préfixe.

Avantages : un seul service de base à payer et sauvegarder, isolation propre entre apps, et possibilité de croiser les données plus tard si besoin.

## Coûts à prévoir

- Railway : offre d'essai puis plan payant selon l'usage (app + base de données).
- API Anthropic : facturation à l'usage par génération de fiche (quelques centimes par fiche selon le modèle).

## Pistes d'évolution

- Upload de photos produits (Cloudinary ou stockage Railway)
- Comptes utilisateurs multiples avec rôles
- Export PDF côté serveur avec mise en page personnalisée
- Import en masse depuis un fichier CSV/Excel

> ⚠️ **Refonte prévue** : famibotanic va évoluer vers un générateur d'**affiches plantes pour les clients** (photo de plante + nom → l'IA rédige la fiche → affiche éditable → export). Voir `CLAUDE.md` et le `CLAUDE.md` racine du dépôt. Le code actuel est encore la version « fiches texte internes ».
