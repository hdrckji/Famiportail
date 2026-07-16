import { NextRequest, NextResponse } from "next/server";
import { query } from "@/lib/db";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    const fiches = await query(
      "SELECT * FROM fiches ORDER BY updated_at DESC"
    );
    return NextResponse.json(fiches);
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
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
      `INSERT INTO fiches (type, titre, produit, categorie, resume, sections, statut)
       VALUES ($1, $2, $3, $4, $5, $6, $7)
       RETURNING *`,
      [
        type,
        b.titre.trim(),
        b.produit ?? "",
        b.categorie ?? "",
        b.resume ?? "",
        JSON.stringify(Array.isArray(b.sections) ? b.sections : []),
        statut
      ]
    );
    return NextResponse.json(rows[0], { status: 201 });
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}
