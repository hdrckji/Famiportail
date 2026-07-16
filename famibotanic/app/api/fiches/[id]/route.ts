import { NextRequest, NextResponse } from "next/server";
import { query } from "@/lib/db";

export const dynamic = "force-dynamic";

function parseId(raw: string) {
  const id = Number(raw);
  return Number.isInteger(id) && id > 0 ? id : null;
}

export async function GET(
  _req: NextRequest,
  { params }: { params: { id: string } }
) {
  const id = parseId(params.id);
  if (!id) return NextResponse.json({ error: "Id invalide." }, { status: 400 });
  try {
    const rows = await query("SELECT * FROM fiches WHERE id = $1", [id]);
    if (!rows[0])
      return NextResponse.json({ error: "Fiche introuvable." }, { status: 404 });
    return NextResponse.json(rows[0]);
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}

export async function PUT(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const id = parseId(params.id);
  if (!id) return NextResponse.json({ error: "Id invalide." }, { status: 400 });
  try {
    const b = await req.json();
    if (!b.titre?.trim()) {
      return NextResponse.json(
        { error: "Le titre est obligatoire." },
        { status: 400 }
      );
    }
    const type = b.type === "vente" ? "vente" : "info";
    const statut = b.statut === "publiee" ? "publiee" : "brouillon";
    const rows = await query(
      `UPDATE fiches
       SET type=$1, titre=$2, produit=$3, categorie=$4, resume=$5,
           sections=$6, statut=$7, updated_at=now()
       WHERE id=$8
       RETURNING *`,
      [
        type,
        b.titre.trim(),
        b.produit ?? "",
        b.categorie ?? "",
        b.resume ?? "",
        JSON.stringify(Array.isArray(b.sections) ? b.sections : []),
        statut,
        id
      ]
    );
    if (!rows[0])
      return NextResponse.json({ error: "Fiche introuvable." }, { status: 404 });
    return NextResponse.json(rows[0]);
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}

export async function DELETE(
  _req: NextRequest,
  { params }: { params: { id: string } }
) {
  const id = parseId(params.id);
  if (!id) return NextResponse.json({ error: "Id invalide." }, { status: 400 });
  try {
    await query("DELETE FROM fiches WHERE id = $1", [id]);
    return NextResponse.json({ ok: true });
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}
