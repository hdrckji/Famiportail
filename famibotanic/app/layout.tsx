import type { Metadata } from "next";
import Link from "next/link";
import "./globals.css";

export const metadata: Metadata = {
  title: "Famibotanic — Fiches produits internes & vente",
  description:
    "Créez des fiches informatives pour vos équipes et des fiches de vente, générées avec l'IA."
};

export default function RootLayout({
  children
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="fr">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
          rel="preconnect"
          href="https://fonts.gstatic.com"
          crossOrigin="anonymous"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Public+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="min-h-screen font-body">
        <div className="flex min-h-screen">
          <aside className="no-print w-56 shrink-0 border-r border-line bg-white flex flex-col">
            <div className="px-5 py-6 border-b border-line">
              <Link href="/" className="block">
                <span className="font-display text-xl font-bold text-pine-dark">
                  Fami<span className="text-amber">botanic</span>
                </span>
                <span className="block font-mono text-[10px] uppercase tracking-widest text-muted mt-1">
                  Outil interne
                </span>
              </Link>
            </div>
            <nav className="flex-1 px-3 py-4 space-y-1 text-sm">
              <Link
                href="/"
                className="block rounded-md px-3 py-2 font-medium hover:bg-paper"
              >
                Toutes les fiches
              </Link>
              <Link
                href="/?type=info"
                className="block rounded-md px-3 py-2 hover:bg-paper"
              >
                <span className="inline-block w-2 h-2 rounded-full mr-2 align-middle" style={{ background: "var(--pine)" }} />
                Fiches informatives
              </Link>
              <Link
                href="/?type=vente"
                className="block rounded-md px-3 py-2 hover:bg-paper"
              >
                <span className="inline-block w-2 h-2 rounded-full mr-2 align-middle" style={{ background: "var(--amber)" }} />
                Fiches de vente
              </Link>
              <Link
                href="/fiches/new"
                className="block rounded-md px-3 py-2 hover:bg-paper"
              >
                + Nouvelle fiche
              </Link>
            </nav>
            <div className="px-3 py-4 border-t border-line">
              <Link
                href="/parametres"
                className="block rounded-md px-3 py-2 text-sm text-muted hover:bg-paper"
              >
                Paramètres
              </Link>
            </div>
          </aside>
          <main className="flex-1 px-8 py-8 max-w-5xl">{children}</main>
        </div>
      </body>
    </html>
  );
}
