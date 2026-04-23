import { defineConfig } from 'astro/config';
import react from '@astrojs/react';
import mdx from '@astrojs/mdx';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  site: 'https://www.gofusion.fr',
  integrations: [
    react(),
    mdx(),
    tailwind({ applyBaseStyles: false }),
    sitemap({
      i18n: {
        defaultLocale: 'fr',
        locales: { fr: 'fr-FR', en: 'en-US' },
      },
      changefreq: 'weekly',
      serialize(item) {
        // Homepage — maximum priority
        if (item.url === 'https://www.gofusion.fr/') {
          return { ...item, priority: 1.0, lastmod: new Date().toISOString() };
        }
        // Blog articles and news — high priority
        if (item.url.includes('/blog/') || item.url.includes('/articles/')) {
          return { ...item, priority: 0.8, lastmod: new Date().toISOString() };
        }
        // Legal pages — low priority
        if (
          item.url.includes('/cgu') ||
          item.url.includes('/mentions-legales') ||
          item.url.includes('/politique-') ||
          item.url.includes('/legal/')
        ) {
          return { ...item, priority: 0.3, lastmod: new Date().toISOString() };
        }
        // Everything else
        return { ...item, priority: 0.7, lastmod: new Date().toISOString() };
      },
    }),
  ],
  i18n: {
    defaultLocale: 'fr',
    locales: ['fr', 'en'],
    routing: { prefixDefaultLocale: false },
  },
  build: { format: 'directory' },
  vite: {
    ssr: { noExternal: ['framer-motion'] },
  },
});
