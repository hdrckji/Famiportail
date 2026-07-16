import { Pool } from "pg";

declare global {
  // eslint-disable-next-line no-var
  var _pgPool: Pool | undefined;
  // eslint-disable-next-line no-var
  var _schemaReady: Promise<void> | undefined;
}

// Schéma PostgreSQL dédié à l'app (utile quand plusieurs apps
// partagent la même base). Défaut : "public".
const SCHEMA = (process.env.DATABASE_SCHEMA || "public").replace(
  /[^a-zA-Z0-9_]/g,
  ""
);

function createPool() {
  return new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl:
      process.env.DATABASE_SSL === "true"
        ? { rejectUnauthorized: false }
        : undefined,
    options: `-csearch_path=${SCHEMA}`,
    max: 5
  });
}

export const pool: Pool = global._pgPool ?? createPool();
if (process.env.NODE_ENV !== "production") global._pgPool = pool;

const SCHEMA_SQL = `
CREATE SCHEMA IF NOT EXISTS ${SCHEMA};

CREATE TABLE IF NOT EXISTS ${SCHEMA}.fiches (
  id SERIAL PRIMARY KEY,
  type TEXT NOT NULL DEFAULT 'info',
  titre TEXT NOT NULL,
  produit TEXT NOT NULL DEFAULT '',
  categorie TEXT NOT NULL DEFAULT '',
  resume TEXT NOT NULL DEFAULT '',
  sections JSONB NOT NULL DEFAULT '[]',
  statut TEXT NOT NULL DEFAULT 'brouillon',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS ${SCHEMA}.settings (
  id INT PRIMARY KEY DEFAULT 1,
  entreprise TEXT NOT NULL DEFAULT '',
  secteur TEXT NOT NULL DEFAULT '',
  ton TEXT NOT NULL DEFAULT '',
  instructions TEXT NOT NULL DEFAULT ''
);

INSERT INTO ${SCHEMA}.settings (id) VALUES (1) ON CONFLICT (id) DO NOTHING;
`;

export function ensureSchema(): Promise<void> {
  if (!global._schemaReady) {
    global._schemaReady = pool.query(SCHEMA_SQL).then(() => undefined);
  }
  return global._schemaReady;
}

export async function query<T = any>(text: string, params?: any[]) {
  await ensureSchema();
  const res = await pool.query(text, params);
  return res.rows as T[];
}
