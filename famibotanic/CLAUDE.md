# CLAUDE.md — Guide du projet Famibotanic

Ce fichier donne le contexte du projet à Claude Code (ou tout assistant IA) pour travailler correctement sur cette base de code.

## Qu'est-ce que cette app

Famibotanic est un outil interne de création de fiches produits pour l'entreprise. Deux types de fiches :
- `info` : fiche informative destinée aux collaborateurs (description, caractéristiques, conseils)
- `vente` : fiche de vente / argumentaire commercial (points forts, cible, objections, prix)

Le contenu des fiches est généré par l'API Anthropic puis modifiable par l'utilisateur.

## Stack et structure

- Next.js 14 (App Router) + TypeScript + Tailwind CSS 3
- MySQL via le paquet `mysql2` (pas d'ORM, requêtes SQL directes)
- API Anthropic via `@anthropic-ai/sdk` (route `app/api/generate/route.ts`)

```
app/
  page.tsx                  → liste des fiches (filtres ?type= et ?q=)
  fiches/new/page.tsx       → création
  fiches/[id]/page.tsx      → consultation + impression
  fiches/[id]/edit/page.tsx → édition
  parametres/page.tsx       → profil entreprise utilisé dans les prompts IA
  api/fiches/               → CRUD fiches
  api/settings/             → paramètres entreprise
  api/generate/             → génération IA
components/
  FicheEditor.tsx           → formulaire création/édition (client)
  FicheActions.tsx          → imprimer / modifier / supprimer (client)
lib/
  db.ts                     → pool mysql2 + création auto des tables (préfixe)
  types.ts                  → types partagés (Fiche, Section, Settings)
middleware.ts               → auth basique optionnelle via APP_PASSWORD
```

## RÈGLES IMPORTANTES — Base de données

Cette app fait partie d'une suite d'apps internes qui **partagent une seule base MySQL**. MySQL n'a pas de schéma interne comme PostgreSQL : l'isolation se fait par **préfixe de table**.

1. **Toutes les tables de cette app sont préfixées** par `DB_PREFIX` (défaut `famibotanic_`), via les constantes `FICHES` / `SETTINGS` exportées par `lib/db.ts`. Ne jamais créer de table sans ce préfixe.
2. **Toute nouvelle table doit être ajoutée dans `SCHEMA_SQL` dans `lib/db.ts`** (tableau d'instructions), nommée avec le préfixe, en `CREATE TABLE IF NOT EXISTS`. Les tables se créent automatiquement au premier démarrage — pas d'outil de migration.
3. **Toujours passer par `query()` (SELECT) ou `exec()` (INSERT/UPDATE/DELETE) de `lib/db.ts`.** Ne pas créer d'autre pool de connexion.
4. **Requêtes paramétrées obligatoires** (placeholders `?` de mysql2) — jamais d'interpolation de valeurs utilisateur dans le SQL.
5. Les données structurées variables (comme `sections`) sont stockées en **JSON** ; toujours `JSON.stringify()` à l'insertion (mysql2 reparse en objet à la lecture).
6. MySQL ne supporte pas `RETURNING` : après un `INSERT`/`UPDATE`, refaire un `SELECT` (par `insertId` ou par `id`) pour renvoyer la ligne.
7. Si une donnée doit être partagée avec d'autres apps (utilisateurs communs, catalogue…), en discuter d'abord : tables communes préfixées `core_`, pas `famibotanic_`.

## Variables d'environnement

| Variable | Rôle |
|---|---|
| `DATABASE_URL` | Connexion MySQL `mysql://user:pass@host:port/base` (fournie par Railway) |
| `DB_PREFIX` | Préfixe des tables de l'app — défaut `famibotanic_` |
| `DATABASE_SSL` | `true` seulement pour une connexion externe à Railway |
| `ANTHROPIC_API_KEY` | Clé API Anthropic (obligatoire pour la génération) |
| `ANTHROPIC_MODEL` | Modèle IA (défaut `claude-sonnet-4-6`) |
| `APP_PASSWORD` | Si défini, protège toute l'app par mot de passe |

Ne jamais committer de `.env` — utiliser `.env.example` comme référence.

## Conventions de code

- Interface et messages **en français** (labels, erreurs, contenu généré).
- Pages qui lisent la base : composants serveur avec `export const dynamic = "force-dynamic"` (la base n'est pas disponible au build).
- Interactivité (formulaires, boutons) : composants client (`"use client"`) qui appellent les routes `/api/*` via `fetch`.
- Les routes API retournent toujours du JSON ; en cas d'erreur : `{ error: "message en français" }` avec le bon code HTTP.
- Style : classes Tailwind + tokens CSS définis dans `app/globals.css` (`--pine` pour le type info, `--amber` pour le type vente). Réutiliser les classes utilitaires `.btn`, `.input`, `.tag`, `.spine-*`.
- Tout ce qui ne doit pas apparaître à l'impression porte la classe `no-print`.

## Génération IA (`app/api/generate/route.ts`)

- Le prompt intègre les paramètres entreprise (table `settings`) : entreprise, secteur, ton, consignes.
- Le modèle doit répondre en JSON strict `{titre, resume, sections:[{titre, contenu}]}` — la route nettoie les éventuelles balises Markdown avant `JSON.parse`.
- Ne pas inventer de chiffres : le prompt impose `[À compléter]` / `[À vérifier]` pour les données manquantes. Conserver cette règle.

## Commandes

```bash
npm run dev     # développement local (nécessite DATABASE_URL)
npm run build   # build de production
npm start       # démarrage (écoute sur $PORT, requis par Railway)
```

## Déploiement

GitHub → Railway (auto-déploiement à chaque push sur `main`). La base MySQL est un service Railway lié par référence de variable (`DATABASE_URL`). Voir README.md pour la procédure complète.
