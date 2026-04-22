# Déploiement du site Go Fusion

## 🎯 Objectif

Remplacer l'hébergement **Webflow** actuel par **Vercel + GitHub**, sans perdre le domaine `gofusion.fr`, sans casser les emails Outlook, sans perdre le SEO.

## Schéma de l'architecture cible

```
Utilisateur → gofusion.fr (DNS OVH)
                  │
                  ├─► Vercel (pages HTML du site)       ← NOUVEAU
                  └─► Microsoft 365 (MX records)        ← inchangé, les emails continuent de fonctionner
```

---

## ÉTAPE 1 — Preview Vercel (aucun risque, aucun impact sur le site live)

### 1.a Pousser le code sur GitHub

Le repo est déjà configuré : https://github.com/Oudavone75/gofusion-v2

```bash
cd /Users/oudavonesurot/Developer/gofusion-v2
git add -A
git commit -m "Migration Webflow → Astro"
git push origin main
```

### 1.b Connecter Vercel à GitHub

1. Aller sur https://vercel.com/new
2. Se connecter avec **GitHub** (créer un compte gratuit si besoin)
3. Cliquer **Import** sur le repo **`Oudavone75/gofusion-v2`**
4. Vercel détecte automatiquement Astro. Laisser les options par défaut :
   - Framework Preset : **Astro**
   - Build Command : `npm run build` (auto-détecté)
   - Output Directory : `dist` (auto-détecté)
5. Cliquer **Deploy**.

**En 60 secondes**, Vercel te donne une URL de preview du type :

- `gofusion-v2.vercel.app`
- `gofusion-v2-oudavone75.vercel.app`

Toutes les pushes sur `main` déclenchent un redéploiement automatique. Les pushes sur d'autres branches créent des **deploy previews** sur des URLs uniques.

---

## ÉTAPE 2 — Validation complète du preview

Tester sur la preview URL :

- [ ] `/` — homepage pixel-perfect
- [ ] `/cgu` — CGU avec TOC + download PDF fonctionnel
- [ ] `/mentions-legales` — mentions légales + download PDF
- [ ] `/politique-de-confidentialite` — politique + download PDF
- [ ] `/solutions` → doit rediriger vers `/#solution` (301)
- [ ] `/a-propos` → doit rediriger vers `/#a-propos` (301)
- [ ] `/faq` → doit rediriger vers `/#faq` (301)
- [ ] `/contact` → doit rediriger vers `/#contact` (301)
- [ ] `/conditions-generales-dutilisation` → `/cgu` (301)
- [ ] `/mentions-legales-et-politique-de-confidentialite` → `/mentions-legales` (301)
- [ ] `/sitemap-index.xml` — doit lister toutes les pages
- [ ] `/robots.txt` — doit être accessible

---

## ÉTAPE 3 — Bascule DNS (cutover)

⚠️ **À faire UNIQUEMENT quand le preview est 100% validé**. Propagation DNS = 5 min à 1h.

### 3.a Ajouter le domaine dans Vercel

1. Dashboard Vercel → projet `gofusion-v2` → **Settings** → **Domains**
2. Entrer `gofusion.fr` puis cliquer **Add**
3. Répéter pour `www.gofusion.fr`
4. Vercel affiche les enregistrements DNS à configurer sur OVH. **Il te donne deux choix :**
   - **(A)** Utiliser les nameservers Vercel (déconseillé : on perd la gestion DNS OVH et on risque de casser les emails)
   - **(B)** Ajouter un enregistrement **A** (et/ou **CNAME**) sur la zone OVH existante — **c'est cette option qu'on prend**

### 3.b Modifier la zone DNS sur OVH

Connecte-toi sur [ovh.com/manager](https://www.ovh.com/manager/) → **Web Cloud** → **Noms de domaine** → `gofusion.fr` → onglet **Zone DNS**.

**Trois enregistrements à MODIFIER (pas supprimer, juste changer la cible) :**

| Type  | Sous-domaine | Cible avant | Cible après (Vercel) |
|-------|--------------|-------------|----------------------|
| A     | `@` (gofusion.fr) | 198.202.211.1 (Webflow) | **76.76.21.21** |
| CNAME | `www` | `cdn.webflow.com.` | **`cname.vercel-dns.com.`** |

⚠️ Les valeurs exactes sont affichées par Vercel dans l'étape 3.a — **utilise celles qu'il te donne, pas celles ici** (elles peuvent varier).

### 3.c ⛔ À NE SURTOUT PAS TOUCHER

Laisse **tel quel** tous les enregistrements ci-dessous — ils font tourner tes emails Microsoft 365 :

| Type | Sous-domaine | Valeur |
|------|--------------|--------|
| MX   | `@`          | `gofusion-fr.mail.protection.outlook.com.` |
| TXT  | `@`          | Les SPF `v=spf1 include:spf.protection.outlook.com...` |
| CNAME | `autodiscover` | `autodiscover.outlook.com.` |
| CNAME | `selector1._domainkey`, `selector2._domainkey` | DKIM Microsoft |
| TXT  | `_dmarc` | Politique DMARC |

**Si tu touches les MX, tes emails cassent.** On ne touche que A et CNAME du www.

### 3.d Attendre la propagation

- Après sauvegarde OVH, compter **5 minutes à 1 heure** pour que le DNS se propage.
- Tester avec `dig gofusion.fr` depuis le terminal, ou sur https://dnschecker.org
- Vercel génère automatiquement un **certificat SSL** (HTTPS) dès que le DNS pointe correctement.

---

## ÉTAPE 4 — Post-migration

### 4.a Vérifier tout

- [ ] `https://www.gofusion.fr` affiche le nouveau site
- [ ] `https://gofusion.fr` redirige vers `www.gofusion.fr` (ou l'inverse selon préférence)
- [ ] Les emails `contact@gofusion.fr` arrivent encore dans Outlook
- [ ] Tester une réception d'email + un envoi d'email

### 4.b Google Search Console

1. Se reconnecter à [search.google.com/search-console](https://search.google.com/search-console)
2. Propriété : `https://www.gofusion.fr` (doit déjà exister — la balise `google-site-verification` est préservée dans le `<head>`)
3. Menu **Sitemaps** → soumettre `https://www.gofusion.fr/sitemap-index.xml`
4. Menu **Inspection d'URL** → tester quelques URLs pour forcer un recrawl
5. Menu **Couverture** → vérifier qu'il n'y a pas d'erreurs 404 massives

### 4.c Arrêter Webflow

- Une fois le site confirmé stable sur Vercel pendant **7 jours** (sans régression SEO)
- Désabonner du plan Webflow
- Exporter une dernière sauvegarde depuis Webflow si besoin (au cas où)

---

## 🧯 Rollback — et si ça casse ?

Si problème majeur après la bascule DNS :

1. **Rollback rapide DNS** : repasser sur OVH l'enregistrement A à `198.202.211.1` et le CNAME `www` à `cdn.webflow.com.` → Webflow reprend la main en 5 minutes.
2. Les emails n'ont jamais été touchés, donc rien à refaire côté MX.

---

## 📞 Contacts support

- **OVH** : https://help.ovhcloud.com
- **Vercel** : https://vercel.com/support
- **GitHub** : https://github.com/Oudavone75/gofusion-v2
