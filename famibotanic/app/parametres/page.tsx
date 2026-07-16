"use client";

import { useEffect, useState } from "react";
import type { Settings } from "@/lib/types";

export default function ParametresPage() {
  const [settings, setSettings] = useState<Settings>({
    entreprise: "",
    secteur: "",
    ton: "",
    instructions: ""
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState("");

  useEffect(() => {
    fetch("/api/settings")
      .then((r) => r.json())
      .then((data) => {
        if (!data.error) setSettings(data);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  async function enregistrer() {
    setSaving(true);
    setMessage("");
    try {
      const res = await fetch("/api/settings", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(settings)
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec de l'enregistrement.");
      setMessage("Paramètres enregistrés.");
    } catch (e: any) {
      setMessage(e.message);
    } finally {
      setSaving(false);
    }
  }

  function set(field: keyof Settings) {
    return (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => setSettings((s) => ({ ...s, [field]: e.target.value }));
  }

  return (
    <div className="max-w-2xl">
      <h1 className="font-display text-3xl font-bold mb-1">Paramètres</h1>
      <p className="text-muted text-sm mb-6">
        Ces informations sont utilisées par l'IA pour adapter le contenu des
        fiches à votre entreprise.
      </p>

      {loading ? (
        <p className="text-muted text-sm">Chargement…</p>
      ) : (
        <div className="rounded-lg border border-line bg-white p-6 space-y-4">
          <div>
            <label className="block text-sm font-semibold mb-1">
              Nom de l'entreprise
            </label>
            <input
              className="input"
              value={settings.entreprise}
              onChange={set("entreprise")}
              placeholder="Ex. : Jardinerie Dupont"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold mb-1">
              Secteur d'activité
            </label>
            <input
              className="input"
              value={settings.secteur}
              onChange={set("secteur")}
              placeholder="Ex. : Jardinerie et aménagement extérieur"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold mb-1">
              Ton des fiches
            </label>
            <input
              className="input"
              value={settings.ton}
              onChange={set("ton")}
              placeholder="Ex. : professionnel et accessible, tutoiement interdit"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold mb-1">
              Consignes supplémentaires pour l'IA
            </label>
            <textarea
              className="input min-h-[120px]"
              value={settings.instructions}
              onChange={set("instructions")}
              placeholder="Ex. : toujours mentionner la garantie 2 ans, ne jamais comparer aux concurrents…"
            />
          </div>
          <div className="flex items-center gap-3">
            <button
              className="btn btn-primary"
              onClick={enregistrer}
              disabled={saving}
            >
              {saving ? "Enregistrement…" : "Enregistrer"}
            </button>
            {message && <span className="text-sm text-muted">{message}</span>}
          </div>
        </div>
      )}
    </div>
  );
}
