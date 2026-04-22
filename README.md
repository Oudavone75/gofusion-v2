# Go Fusion — Website

Site officiel de [Go Fusion](https://www.gofusion.fr) (Oudavone SAS).
Plateforme SaaS gamifiée d'engagement et de formation collaborative pour ETI de 500 à 4 000 collaborateurs.

---

## 🚀 Stack

- **Astro 5** (static-site generator, SEO-first)
- **Tailwind CSS** + charte graphique Go Fusion (teal / purple / magenta gradient, Fraunces + DM Sans)
- **React** (islands pour animations ponctuelles)
- **Framer Motion** (animations premium)
- **MDX** pour les articles de blog (à venir)
- **@astrojs/sitemap** pour sitemap automatique
- **Vercel** pour l'hébergement + déploiement continu

## 📁 Structure

```
gofusion-v2/
├── public/                     # Assets servis tels quels
│   ├── assets/logo/            # Logo PNG + favicons
│   ├── assets/og/              # Image Open Graph 1200x630
│   ├── legal/                  # PDFs officiels téléchargeables
│   ├── home.css                # CSS legacy homepage
│   └── robots.txt
├── src/
│   ├── components/
│   │   ├── HomeBody.astro      # Body homepage migré du Webflow
│   │   └── SEO.astro           # Meta + schema.org (Organization, WebSite…)
│   ├── content/legal/          # HTML + TOC JSON extraits des PDFs
│   ├── layouts/
│   │   ├── BaseLayout.astro
│   │   └── LegalLayout.astro
│   ├── pages/
│   │   ├── index.astro
│   │   ├── mentions-legales.astro
│   │   ├── cgu.astro
│   │   └── politique-de-confidentialite.astro
│   ├── styles/
│   └── consts.ts               # SIREN, adresse, etc.
├── astro.config.mjs
├── tailwind.config.mjs
├── vercel.json                 # Redirections 301 + headers sécurité
└── package.json
```

## 🛠 Développement

```bash
npm install
npm run dev           # http://localhost:4321
npm run build         # génère dist/
npm run preview       # preview local de la version build
```

## 🌐 Déploiement

Voir [DEPLOY.md](./DEPLOY.md) pour la procédure complète (Vercel + bascule DNS OVH).

## ✍️ Mise à jour des documents légaux

Les PDFs officiels sont stockés dans `public/legal/` (versions du 30 juillet 2025 de l'avocat).

Pour publier une nouvelle version :
1. Remplacer le PDF dans `public/legal/`
2. Extraire le texte : `pdftotext -layout -nopgbrk public/legal/<file>.pdf /tmp/<slug>.txt`
3. Regénérer le HTML + TOC dans `src/content/legal/<slug>.html` et `<slug>.toc.json`
4. Mettre à jour la date dans `src/pages/<slug>.astro`
5. Commit + push → Vercel redéploie automatiquement

## 🎯 Présentation du produit Go Fusion

**La plateforme gamifiée qui engage.**
Go Fusion transforme n'importe quel sujet (IA, cybersécurité, climat, soft skills, avantages RH) en parcours de micro-défis gamifiés. 3 minutes par jour suffisent.

### La boucle auto-apprenante
L'IA analyse votre stratégie, capte les bonnes pratiques terrain, et transforme le tout en expériences gamifiées. Plus on joue, plus la plateforme devient pertinente.

- 🤖 Contenu généré par IA
- 📱 Multi-support natif (iOS, Android, Web)
- 📊 Dashboard temps réel
- 🔄 Le collaborateur devient formateur
- 🎨 100% personnalisable
- ⚡ Déploiement express (1 semaine)

### Contact
[Réserver une démo](mailto:contact@gofusion.fr)
