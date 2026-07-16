import mysql, { type Pool, type ResultSetHeader } from "mysql2/promise";

declare global {
  // eslint-disable-next-line no-var
  var _mysqlPool: Pool | undefined;
  // eslint-disable-next-line no-var
  var _schemaReady: Promise<void> | undefined;
}

// MySQL n'a pas de « schéma » interne comme PostgreSQL : quand plusieurs apps
// partagent UNE base MySQL, on isole par PRÉFIXE de table. Défaut : "famibotanic_".
const PREFIX = (process.env.DB_PREFIX ?? "famibotanic_").replace(/[^a-zA-Z0-9_]/g, "");
export const FICHES = `${PREFIX}fiches`;
export const SETTINGS = `${PREFIX}settings`;

function createPool(): Pool {
  const url = new URL(process.env.DATABASE_URL || "mysql://root@localhost:3306/famibotanic");
  return mysql.createPool({
    host: url.hostname,
    port: Number(url.port || 3306),
    user: decodeURIComponent(url.username),
    password: decodeURIComponent(url.password),
    database: url.pathname.replace(/^\//, ""),
    // SSL uniquement pour une connexion externe (URL publique Railway). En interne : rien.
    ssl: process.env.DATABASE_SSL === "true" ? { rejectUnauthorized: false } : undefined,
    waitForConnections: true,
    connectionLimit: 5,
    charset: "utf8mb4"
  });
}

export const pool: Pool = global._mysqlPool ?? createPool();
if (process.env.NODE_ENV !== "production") global._mysqlPool = pool;

// Schéma créé automatiquement au premier démarrage (une instruction par entrée).
const SCHEMA_SQL = [
  `CREATE TABLE IF NOT EXISTS ${FICHES} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL DEFAULT 'info',
    titre VARCHAR(255) NOT NULL,
    produit VARCHAR(255) NOT NULL DEFAULT '',
    categorie VARCHAR(255) NOT NULL DEFAULT '',
    resume TEXT NULL,
    sections JSON NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'brouillon',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`,
  `CREATE TABLE IF NOT EXISTS ${SETTINGS} (
    id INT PRIMARY KEY,
    entreprise TEXT NULL,
    secteur TEXT NULL,
    ton TEXT NULL,
    instructions TEXT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`,
  `INSERT IGNORE INTO ${SETTINGS} (id, entreprise, secteur, ton, instructions) VALUES (1, '', '', '', '')`
];

export function ensureSchema(): Promise<void> {
  if (!global._schemaReady) {
    global._schemaReady = (async () => {
      for (const sql of SCHEMA_SQL) await pool.query(sql);
    })();
  }
  return global._schemaReady;
}

// SELECT : renvoie les lignes.
export async function query<T = any>(sql: string, params?: any[]): Promise<T[]> {
  await ensureSchema();
  const [rows] = await pool.query(sql, params);
  return rows as T[];
}

// INSERT / UPDATE / DELETE : renvoie l'en-tête (insertId, affectedRows…).
export async function exec(sql: string, params?: any[]): Promise<ResultSetHeader> {
  await ensureSchema();
  const [res] = await pool.query(sql, params);
  return res as ResultSetHeader;
}
