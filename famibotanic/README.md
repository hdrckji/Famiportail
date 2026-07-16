# Famibotanic

Outil interne de création de fiches produits, avec génération de contenu par IA (API Anthropic).

Deux types de fiches :

- **Fiche informative** — documentation interne pour les collaborateurs (description, caractéristiques, conseils, infos internes)
- **Fiche de vente** — argumentaire commercial (points forts, cible, réponses aux objections, prix)

Fonctionnalités : génération IA à partir d'informations brutes, édition complète du contenu, recherche et filtres, statut brouillon/publiée, impression ou export PDF (via l'impression du navigateur), paramètres d'entreprise pris en compte par l'IA, protection par mot de passe optionnelle.

## Stack

- Next.js 14 (App Router) + TypeScript + Tailwind CSS
- PostgreSQL (le schéma se crée automatiquement au premier démarrage)
- API Anthropic (`@anthropic-ai/sdk`)

## Lancer en local

```bash
npm install
cp .env.example .env      # puis remplir DATABASE_URL et ANTHROPIC_API_KEY
npm run dev
```

L'app tourne sur http://localhost:3000. Il faut une base PostgreSQL accessible (locale ou celle de Railway).

## Variables d'environnement

| Variable | Obligatoire | Description |
|---|---|---|
| `DATABASE_URL` | oui | URL PostgreSQL. Fournie automatiquement par Railway si la base est liée au service. |
| `ANTHROPIC_API_KEY` | oui | Clé API à créer sur https://console.anthropic.com (menu API Keys). |
| `ANTHROPIC_MODEL` | non | Modèle utilisé (défaut : `claude-sonnet-4-6`). |
| `APP_PASSWORD` | non | Si défini, l'app demande un mot de passe (recommandé pour un outil interne exposé sur internet). Le nom d'utilisateur est libre. |
| `DATABASE_SCHEMA` | non | Schéma PostgreSQL dédié à l'app (défaut : `public`). Recommandé : `famibotanic` si la base est partagée avec d'autres apps. |
| `DATABASE_SSL` | non | Mettre `true` si la connexion PostgreSQL exige SSL (connexion via l'URL publique de Railway). En interne Railway, laisser vide. |

## Déploiement : GitHub + Railway

### 1. Pousser le code sur GitHub

```bash
cd famibotanic
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/VOTRE_COMPTE/famibotanic.git
git push -u origin main
```

(Créez d'abord un dépôt vide nommé `famibotanic` sur github.com, sans README ni .gitignore.)

### 2. Créer le projet Railway

1. Sur https://railway.app → **New Project** → **Deploy from GitHub repo** → choisir `famibotanic`.
2. Railway détecte Next.js automatiquement (build `npm run build`, démarrage `npm start`).

### 3. Ajouter PostgreSQL

1. Dans le projet Railway → **+ New** → **Database** → **PostgreSQL**.
2. Ouvrir le service de l'app → onglet **Variables** → **Add Variable Reference** → sélectionner `DATABASE_URL` du service Postgres.

### 4. Ajouter les autres variables

Dans l'onglet **Variables** du service de l'app :

- `ANTHROPIC_API_KEY` = votre clé (console.anthropic.com)
- `APP_PASSWORD` = un mot de passe de votre choix (recommandé)

### 5. Générer l'URL publique

Onglet **Settings** → **Networking** → **Generate Domain**. L'app est en ligne ; les tables se créent toutes seules à la première requête.

## Base de données partagée entre plusieurs apps

Si Famibotanic rejoint une suite d'apps internes, il est conseillé d'utiliser **une seule base PostgreSQL** pour toutes, avec **un schéma par app** :

1. Sur Railway, liez la même base PostgreSQL à chaque app (variable `DATABASE_URL` en référence).
2. Pour Famibotanic, ajoutez `DATABASE_SCHEMA=famibotanic` : ses tables seront créées dans le schéma `famibotanic`, isolées des autres apps.
3. Faites de même pour chaque autre app avec son propre nom de schéma.

Avantages : un seul service de base à payer et sauvegarder, isolation propre entre apps, et possibilité de croiser les données plus tard si besoin.

## Coûts à prévoir

- Railway : offre d'essai puis plan payant selon l'usage (app + base de données).
- API Anthropic : facturation à l'usage par génération de fiche (quelques centimes par fiche selon le modèle).

## Pistes d'évolution

- Upload de photos produits (Cloudinary ou stockage Railway)
- Comptes utilisateurs multiples avec rôles
- Export PDF côté serveur avec mise en page personnalisée
- Import en masse depuis un fichier CSV/Excel
