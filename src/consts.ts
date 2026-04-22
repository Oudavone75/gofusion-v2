export const SITE = {
  url: 'https://www.gofusion.fr',
  name: 'Go Fusion',
  legalName: 'Oudavone SAS',
  siren: '984 177 311',
  vat: 'FR50984177311',
  address: {
    street: '58 rue Monceau',
    zip: '75008',
    city: 'Paris',
    country: 'FR',
  },
  founder: {
    name: 'Oudavone Surot',
    linkedin: 'https://www.linkedin.com/in/oudavone-surot-79306283/',
  },
  founded: '2024',
  employees: '1-10',
  contact: {
    email: 'contact@gofusion.fr',
  },
  social: {
    linkedin: 'https://www.linkedin.com/company/go-fusion-application/',
  },
  googleSiteVerification: '_EJG1WD1TC5Sn_5i3N1iQIPfCZVRdohGgECncNU0Hf8',
  defaultLocale: 'fr-FR',
  ogImage: '/assets/og/og-image.png',
} as const;

export const NAV_ITEMS = [
  { href: '/solutions', label: 'Solutions', labelEn: 'Solutions' },
  { href: '/#usecases', label: "Cas d'usage", labelEn: 'Use cases' },
  { href: '/blog', label: 'Blog', labelEn: 'Blog' },
  { href: '/a-propos', label: 'À propos', labelEn: 'About' },
  { href: '/faq', label: 'FAQ', labelEn: 'FAQ' },
  { href: '/contact', label: 'Contact', labelEn: 'Contact' },
] as const;
