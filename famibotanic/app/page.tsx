import Link from "next/link";
import { query } from "@/lib/db";
import type { Fiche } from "@/lib/types";

export const dynamic = "force-dynamic";

export default async function Home({
  searchParams
}: {
  searchParams: { type?: string; q?: string };
}) {
  const type = searchParams.type === "info" || searchParams.type === "vente"
    ? searchParams.type
    : null;
  const q = (searchParams.q ?? "").trim();

  const conditions: string[] = [];
  const params: any[] = [];
  if (type) {
    params.push(type);
    conditions.push(`type = $${params.length}`);
  }
  if (q) {
    params.push(`%${q}%`);
    conditions.push(
      `(titre ILIKE $${params.length} OR produit ILIKE $${params.length} OR categorie ILIKE $${params.length})`
    );
  }
  const where = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";

  const fiches = await query<Fiche>(
    `SELECT * FROM fiches ${where} ORDER BY updated_at DESC`,
    params
  );

  const titre =
    type === "info"
      ? "Fiches informatives"
      : type === "vente"
        ? "Fiches de vente"
        : "Toutes les fiches";

  return (
    <div>
      <div className="flex items-end justify-between gap-4 mb-6">
        <div>
          <h1 className="font-display text-3xl font-bold">{titre}</h1>
          <p className="text-muted text-sm mt-1">
            {fiches.length} fiche{fiches.length > 1 ? "s" : ""}
          </p>
        </div>
        <Link href="/fiches/new" className="btn btn-primary">
          + Nouvelle fiche
        </Link>
      </div>

      <form className="mb-6 flex gap-2" action="/">
        {type && <input type="hidden" name="type" value={type} />}
        <input
          className="input max-w-sm"
          type="search"
          name="q"
          placeholder="Rechercher un produit, une catégorie…"
          defaultValue={q}
        />
        <button className="btn btn-ghost" type="submit">
          Rechercher
        </button>
      </form>

      {fiches.length === 0 ? (
        <div className="rounded-lg border border-dashed border-line bg-white p-12 text-center">
          <p className="font-display text-lg font-semibold">
            Aucune fiche pour l'instant
          </p>
          <p className="text-muted text-sm mt-1 mb-4">
            Créez votre première fiche : renseignez quelques informations et
            laissez l'IA rédiger le contenu.
          </p>
          <Link href="/fiches/new" className="btn btn-primary">
            Créer une fiche
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {fiches.map((f) => (
            <Link
              key={f.id}
              href={`/fiches/${f.id}`}
              className={`block rounded-lg border border-line bg-white p-5 hover:shadow-md transition ${
                f.type === "info" ? "spine-info" : "spine-vente"
              }`}
            >
              <div className="flex items-center justify-between gap-2 mb-2">
                <span className={`tag ${f.type === "info" ? "tag-info" : "tag-vente"}`}>
                  {f.type === "info" ? "Interne" : "Vente"}
                </span>
                {f.statut === "brouillon" && (
                  <span className="font-mono text-[11px] text-muted">
                    brouillon
                  </span>
                )}
              </div>
              <h2 className="font-display text-lg font-semibold leading-snug">
                {f.titre}
              </h2>
              <p className="text-sm text-muted mt-1 line-clamp-2">{f.resume}</p>
              <p className="font-mono text-[11px] text-muted mt-3">
                {f.categorie || "Sans catégorie"} ·{" "}
                {new Date(f.updated_at).toLocaleDateString("fr-FR")}
              </p>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
