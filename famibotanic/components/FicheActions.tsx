"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";

export default function FicheActions({ id }: { id: number }) {
  const router = useRouter();
  const [deleting, setDeleting] = useState(false);

  async function supprimer() {
    if (!confirm("Supprimer définitivement cette fiche ?")) return;
    setDeleting(true);
    const res = await fetch(`/api/fiches/${id}`, { method: "DELETE" });
    if (res.ok) {
      router.push("/");
      router.refresh();
    } else {
      setDeleting(false);
      alert("La suppression a échoué. Réessayez.");
    }
  }

  return (
    <div className="flex items-center gap-2">
      <button className="btn btn-ghost" onClick={() => window.print()}>
        Imprimer / PDF
      </button>
      <Link href={`/fiches/${id}/edit`} className="btn btn-ghost">
        Modifier
      </Link>
      <button className="btn btn-danger" onClick={supprimer} disabled={deleting}>
        {deleting ? "Suppression…" : "Supprimer"}
      </button>
    </div>
  );
}
