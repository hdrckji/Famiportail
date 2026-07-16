import { NextRequest, NextResponse } from "next/server";
import Anthropic from "@anthropic-ai/sdk";
import { query } from "@/lib/db";
import type { Settings } from "@/lib/types";

export const dynamic = "force-dynamic";
export const maxDuration = 60;

export async function POST(req: NextRequest) {
  const apiKey = process.env.ANTHROPIC_API_KEY;
  if (!apiKey) {
    return NextResponse.json(
      {
        error:
          "La variable d'environnement ANTHROPIC_API_KEY n'est pas définie. Ajoutez-la dans les variables Railway."
      },
      { status: 500 }
    );
  }

  try {
    const { type, produit, categorie, notes } = await req.json();
    if (!produit?.trim()) {
      return NextResponse.json(
        { error: "Le produit / sujet est obligatoire." },
        { status: 400 }
      );
    }

    let settings: Settings = {
      entreprise: "",
      secteur: "",
      ton: "",
      instructions: ""
    };
    try {
      const rows = await query<Settings>("SELECT * FROM settings WHERE id = 1");
      if (rows[0]) settings = rows[0];
    } catch {
      // paramètres indisponibles : on génère quand même avec les valeurs par défaut
    }

    const isVente = type === "vente";

    const contexte = [
      settings.entreprise && `Entreprise : ${settings.entreprise}`,
      settings.secteur && `Secteur d'activité : ${settings.secteur}`,
      settings.ton && `Ton souhaité : ${settings.ton}`,
      settings.instructions && `Consignes internes : ${settings.instructions}`
    ]
      .filter(Boolean)
      .join("\n");

    const structure = isVente
      ? `Sections attendues pour une fiche de vente (adapte-les au produit) :
- "L'essentiel" : l'argument principal en quelques phrases
- "Points forts" : liste des bénéfices client (une ligne par point, avec des tirets)
- "Pour qui ?" : le profil de client à cibler
- "Argumentaire" : comment le présenter au client, réponses aux objections courantes
- "Prix et conditions" : si des informations de prix sont fournies, sinon omets cette section`
      : `Sections attendues pour une fiche informative interne (adapte-les au produit) :
- "Description" : ce que c'est, à quoi ça sert
- "Caractéristiques" : données techniques ou pratiques (une ligne par point, avec des tirets)
- "Conseils et utilisation" : ce que les collaborateurs doivent savoir pour renseigner un client
- "Stockage et manipulation" : si pertinent
- "Infos internes" : référence, fournisseur, réassort… uniquement si des informations sont fournies`;

    const prompt = `Tu rédiges des fiches produits pour l'outil interne d'une entreprise.
${contexte ? `\nContexte de l'entreprise :\n${contexte}\n` : ""}
Type de fiche : ${isVente ? "fiche de vente (argumentaire commercial destiné aux vendeurs)" : "fiche informative (documentation interne destinée aux collaborateurs)"}
Produit / sujet : ${produit}
${categorie ? `Catégorie : ${categorie}` : ""}
${notes?.trim() ? `Informations brutes fournies :\n${notes}` : "Aucune information brute fournie : rédige un contenu générique plausible et signale par [À vérifier] les éléments à confirmer."}

${structure}

Règles :
- Rédige en français, de façon claire et directe.
- N'invente pas de chiffres précis (prix, dimensions) s'ils ne sont pas fournis ; utilise [À compléter].
- Reste factuel pour une fiche informative, persuasif mais honnête pour une fiche de vente.

Réponds UNIQUEMENT avec un objet JSON valide, sans texte avant ni après, sans balises Markdown, au format :
{"titre": "...", "resume": "...", "sections": [{"titre": "...", "contenu": "..."}]}`;

    const client = new Anthropic({ apiKey });
    const message = await client.messages.create({
      model: process.env.ANTHROPIC_MODEL || "claude-sonnet-4-6",
      max_tokens: 2000,
      messages: [{ role: "user", content: prompt }]
    });

    const text = message.content
      .map((b) => (b.type === "text" ? b.text : ""))
      .join("\n");
    const clean = text.replace(/```json|```/g, "").trim();
    const parsed = JSON.parse(clean);

    return NextResponse.json({
      titre: String(parsed.titre ?? produit),
      resume: String(parsed.resume ?? ""),
      sections: Array.isArray(parsed.sections)
        ? parsed.sections.map((s: any) => ({
            titre: String(s.titre ?? ""),
            contenu: String(s.contenu ?? "")
          }))
        : []
    });
  } catch (e: any) {
    const msg =
      e instanceof SyntaxError
        ? "La réponse de l'IA n'a pas pu être interprétée. Relancez la génération."
        : e.message || "Erreur lors de la génération.";
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
