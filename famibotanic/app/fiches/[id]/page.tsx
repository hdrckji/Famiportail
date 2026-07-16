import Link from "next/link";
import { notFound } from "next/navigation";
import { query, FICHES } from "@/lib/db";
import type { Fiche } from "@/lib/types";
import FicheActions from "@/components/FicheActions";

export const dynamic = "force-dynamic";

export default async function FichePage({
  params
}: {
  params: { id: string };
}) {
  const id = Number(params.id);
  if (!Number.isInteger(id)) notFound();

  const rows = await query<Fiche>(`SELECT * FROM ${FICHES} WHERE id = ?`, [id]);
  const fiche = rows[0];
  if (!fiche) notFound();

  return (
    <div>
      <div className="no-print flex items-center justify-between gap-4 mb-6">
        <Link href="/" className="text-sm text-muted hover:text-ink">
          ← Retour aux fiches
        </Link>
        <FicheActions id={fiche.id} />
      </div>

      <article
        className={`print-sheet rounded-lg border border-line bg-white p-8 max-w-3xl ${
          fiche.type === "info" ? "spine-info" : "spine-vente"
        }`}
      >
        <div className="flex items-center gap-3 mb-4">
          <span className={`tag ${fiche.type === "info" ? "tag-info" : "tag-vente"}`}>
            {fiche.type === "info" ? "Fiche interne" : "Fiche de vente"}
          </span>
          {fiche.categorie && (
            <span className="font-mono text-[11px] uppercase tracking-widest text-muted">
              {fiche.categorie}
            </span>
          )}
        </div>

        <h1 className="font-display text-3xl font-bold leading-tight">
          {fiche.titre}
        </h1>
        {fiche.resume && (
          <p className="mt-3 text-lg text-muted leading-relaxed">{fiche.resume}</p>
        )}

        <div className="mt-8 space-y-6">
          {fiche.sections.map((s, i) => (
            <section key={i}>
              <h2 className="font-display text-lg font-semibold border-b border-line pb-1 mb-2">
                {s.titre}
              </h2>
              <p className="whitespace-pre-wrap leading-relaxed text-[15px]">
                {s.contenu}
              </p>
            </section>
          ))}
        </div>

        <p className="mt-10 pt-4 border-t border-line font-mono text-[11px] text-muted">
          Fiche n°{fiche.id} · mise à jour le{" "}
          {new Date(fiche.updated_at).toLocaleDateString("fr-FR")} ·{" "}
          {fiche.statut === "publiee" ? "publiée" : "brouillon"}
        </p>
      </article>
    </div>
  );
}
