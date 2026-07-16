import FicheEditor from "@/components/FicheEditor";

export default function NewFichePage() {
  return (
    <div>
      <h1 className="font-display text-3xl font-bold mb-1">Nouvelle fiche</h1>
      <p className="text-muted text-sm mb-6">
        Renseignez les informations de base, générez le contenu avec l'IA, puis
        ajustez avant d'enregistrer.
      </p>
      <FicheEditor />
    </div>
  );
}
