import { NextRequest, NextResponse } from "next/server";
import { query, exec, SETTINGS } from "@/lib/db";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    const rows = await query(`SELECT * FROM ${SETTINGS} WHERE id = 1`);
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
    await exec(
      `INSERT INTO ${SETTINGS} (id, entreprise, secteur, ton, instructions)
       VALUES (1, ?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE
         entreprise=VALUES(entreprise), secteur=VALUES(secteur),
         ton=VALUES(ton), instructions=VALUES(instructions)`,
      [b.entreprise ?? "", b.secteur ?? "", b.ton ?? "", b.instructions ?? ""]
    );
    const rows = await query(`SELECT * FROM ${SETTINGS} WHERE id = 1`);
    return NextResponse.json(rows[0]);
  } catch (e: any) {
    return NextResponse.json({ error: e.message }, { status: 500 });
  }
}
