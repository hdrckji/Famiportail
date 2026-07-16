import { NextRequest, NextResponse } from "next/server";
import { query, exec, FICHES } from "@/lib/db";

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
    const rows = await query(`SELECT * FROM ${FICHES} WHERE id = ?`, [id]);
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
    await exec(
      `UPDATE ${FICHES}
       SET type=?, titre=?, produit=?, categorie=?, resume=?,
           sections=?, statut=?, updated_at=CURRENT_TIMESTAMP
       WHERE id=?`,
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
    const rows = await query(`SELECT * FROM ${FICHES} WHERE id = ?`, [id]);
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
    await exec(`DELETE FROM ${FICHES} WHERE id = ?`, [id]);
    return NextResponse.json({ ok: true });
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}
