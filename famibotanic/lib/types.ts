export type FicheType = "info" | "vente";

export interface Section {
  titre: string;
  contenu: string;
}

export interface Fiche {
  id: number;
  type: FicheType;
  titre: string;
  produit: string;
  categorie: string;
  resume: string;
  sections: Section[];
  statut: "brouillon" | "publiee";
  created_at: string;
  updated_at: string;
}

export interface Settings {
  entreprise: string;
  secteur: string;
  ton: string;
  instructions: string;
}
