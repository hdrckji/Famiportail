"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import type { Fiche, FicheType, Section } from "@/lib/types";

interface Props {
  fiche?: Fiche;
}

export default function FicheEditor({ fiche }: Props) {
  const router = useRouter();
  const isEdit = Boolean(fiche);

  const [type, setType] = useState<FicheType>(fiche?.type ?? "info");
  const [produit, setProduit] = useState(fiche?.produit ?? "");
  const [categorie, setCategorie] = useState(fiche?.categorie ?? "");
  const [notes, setNotes] = useState("");
  const [titre, setTitre] = useState(fiche?.titre ?? "");
  const [resume, setResume] = useState(fiche?.resume ?? "");
  const [sections, setSections] = useState<Section[]>(fiche?.sections ?? []);
  const [statut, setStatut] = useState(fiche?.statut ?? "brouillon");

  const [generating, setGenerating] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  const hasContent = titre || sections.length > 0;

  async function generer() {
    setError("");
    if (!produit.trim()) {
      setError("Indiquez au moins le nom du produit ou du sujet de la fiche.");
      return;
    }
    setGenerating(true);
    try {
      const res = await fetch("/api/generate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type, produit, categorie, notes })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec de la génération.");
      setTitre(data.titre ?? "");
      setResume(data.resume ?? "");
      setSections(Array.isArray(data.sections) ? data.sections : []);
    } catch (e: any) {
      setError(e.message);
    } finally {
      setGenerating(false);
    }
  }

  async function enregistrer() {
    setError("");
    if (!titre.trim()) {
      setError("La fiche doit avoir un titre.");
      return;
    }
    setSaving(true);
    try {
      const payload = { type, produit, categorie, titre, resume, sections, statut };
      const res = await fetch(isEdit ? `/api/fiches/${fiche!.id}` : "/api/fiches", {
        method: isEdit ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec de l'enregistrement.");
      router.push(`/fiches/${data.id}`);
      router.refresh();
    } catch (e: any) {
      setError(e.message);
      setSaving(false);
    }
  }

  function updateSection(i: number, patch: Partial<Section>) {
    setSections((s) => s.map((sec, j) => (j === i ? { ...sec, ...patch } : sec)));
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 items-start">
      {/* Colonne gauche : infos de base + IA */}
      <div className="rounded-lg border border-line bg-white p-5 space-y-4">
        <div>
          <label className="block text-sm font-semibold mb-2">
            Type de fiche
          </label>
          <div className="grid grid-cols-2 gap-2">
            <button
              type="button"
              onClick={() => setType("info")}
              className={`rounded-md border px-3 py-2 text-sm font-medium transition ${
                type === "info"
                  ? "border-pine bg-[#e3efe6] text-pine-dark"
                  : "border-line bg-white text-muted"
              }`}
            >
              Informative
              <span className="block font-normal text-[11px]">
                pour les collaborateurs
              </span>
            </button>
            <button
              type="button"
              onClick={() => setType("vente")}
              className={`rounded-md border px-3 py-2 text-sm font-medium transition ${
                type === "vente"
                  ? "border-amber bg-[#f6ead4] text-amber-dark"
                  : "border-line bg-white text-muted"
              }`}
            >
              Vente
              <span className="block font-normal text-[11px]">
                argumentaire commercial
              </span>
            </button>
          </div>
        </div>

        <div>
          <label className="block text-sm font-semibold mb-1">
            Produit / sujet *
          </label>
          <input
            className="input"
            value={produit}
            onChange={(e) => setProduit(e.target.value)}
            placeholder="Ex. : Terreau universel 50 L"
          />
        </div>

        <div>
          <label className="block text-sm font-semibold mb-1">Catégorie</label>
          <input
            className="input"
            value={categorie}
            onChange={(e) => setCategorie(e.target.value)}
            placeholder="Ex. : Jardinage, Outillage…"
          />
        </div>

        <div>
          <label className="block text-sm font-semibold mb-1">
            Informations brutes
          </label>
          <textarea
            className="input min-h-[140px]"
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            placeholder="Collez ici tout ce que vous savez : caractéristiques, prix, fournisseur, points forts, contraintes… L'IA s'en servira pour rédiger la fiche."
          />
        </div>

        <button
          type="button"
          onClick={generer}
          disabled={generating}
          className="btn btn-primary w-full justify-center"
        >
          {generating
            ? "Génération en cours…"
            : hasContent
              ? "Régénérer avec l'IA"
              : "Générer la fiche avec l'IA"}
        </button>

        {error && (
          <p className="text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">
            {error}
          </p>
        )}
      </div>

      {/* Colonne droite : contenu éditable */}
      <div className="space-y-4">
        {!hasContent ? (
          <div className="rounded-lg border border-dashed border-line bg-white p-10 text-center text-muted text-sm">
            Le contenu de la fiche apparaîtra ici après génération.
            <br />
            Vous pourrez tout modifier avant d'enregistrer.
          </div>
        ) : (
          <div
            className={`rounded-lg border border-line bg-white p-6 space-y-4 ${
              type === "info" ? "spine-info" : "spine-vente"
            }`}
          >
            <input
              className="input font-display text-xl font-bold"
              value={titre}
              onChange={(e) => setTitre(e.target.value)}
              placeholder="Titre de la fiche"
            />
            <textarea
              className="input"
              value={resume}
              onChange={(e) => setResume(e.target.value)}
              placeholder="Résumé / accroche"
            />
            {sections.map((s, i) => (
              <div key={i} className="rounded-md border border-line p-3 space-y-2">
                <div className="flex gap-2">
                  <input
                    className="input font-semibold"
                    value={s.titre}
                    onChange={(e) => updateSection(i, { titre: e.target.value })}
                  />
                  <button
                    type="button"
                    className="btn btn-danger shrink-0"
                    onClick={() =>
                      setSections((secs) => secs.filter((_, j) => j !== i))
                    }
                  >
                    Supprimer
                  </button>
                </div>
                <textarea
                  className="input min-h-[110px]"
                  value={s.contenu}
                  onChange={(e) => updateSection(i, { contenu: e.target.value })}
                />
              </div>
            ))}
            <button
              type="button"
              className="btn btn-ghost"
              onClick={() =>
                setSections((s) => [...s, { titre: "Nouvelle section", contenu: "" }])
              }
            >
              + Ajouter une section
            </button>
          </div>
        )}

        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={enregistrer}
            disabled={saving || !hasContent}
            className="btn btn-primary"
          >
            {saving ? "Enregistrement…" : isEdit ? "Enregistrer les modifications" : "Enregistrer la fiche"}
          </button>
          <label className="flex items-center gap-2 text-sm text-muted">
            <input
              type="checkbox"
              checked={statut === "publiee"}
              onChange={(e) => setStatut(e.target.checked ? "publiee" : "brouillon")}
            />
            Marquer comme publiée
          </label>
        </div>
      </div>
    </div>
  );
}
