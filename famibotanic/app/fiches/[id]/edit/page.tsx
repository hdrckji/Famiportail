import { notFound } from "next/navigation";
import { query, FICHES } from "@/lib/db";
import type { Fiche } from "@/lib/types";
import FicheEditor from "@/components/FicheEditor";

export const dynamic = "force-dynamic";

export default async function EditFichePage({
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
      <h1 className="font-display text-3xl font-bold mb-1">Modifier la fiche</h1>
      <p className="text-muted text-sm mb-6">
        Ajustez le contenu ou régénérez-le avec l'IA à partir de nouvelles
        informations.
      </p>
      <FicheEditor fiche={fiche} />
    </div>
  );
}
