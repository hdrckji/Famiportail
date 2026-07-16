import { NextRequest, NextResponse } from "next/server";
import { query } from "@/lib/db";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    const rows = await query("SELECT * FROM settings WHERE id = 1");
    return NextResponse.json(
      rows[0] ?? { entreprise: "", secteur: "", ton: "", instructions: "" }
    );
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}

export async function PUT(req: NextRequest) {
  try {
    const b = await req.json();
    const rows = await query(
      `INSERT INTO settings (id, entreprise, secteur, ton, instructions)
       VALUES (1, $1, $2, $3, $4)
       ON CONFLICT (id) DO UPDATE
       SET entreprise=$1, secteur=$2, ton=$3, instructions=$4
       RETURNING *`,
      [b.entreprise ?? "", b.secteur ?? "", b.ton ?? "", b.instructions ?? ""]
    );
    return NextResponse.json(rows[0]);
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}
