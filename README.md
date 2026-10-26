# South Cairns Dental website

A static, multi-page website for South Cairns Dental (Edmonton, Cairns), built with HTML, CSS and vanilla JavaScript. It has no framework, no database and no runtime dependencies. A small Python script generates the pages from content files.

## Run it locally

```
cd south-cairns-dental
python3 -m http.server 8000
```

Open http://localhost:8000. Use a local server rather than opening files directly, so the 404 page and map behave as they will in production.

## Deploying (Vercel)

The site is deployed from the GitHub repository to Vercel as plain static files. There is no build step on Vercel: the generated HTML in the project root is committed and served as is.

1. Edit the content files (see "Editing content" below).
2. Build and check:
   ```
   python3 _build/build.py --site-url https://south-cairns.vercel.app --noindex
   python3 _build/check.py
   ```
3. Commit and push. Vercel redeploys automatically.

Two files control the deployment:

- **`.vercelignore`** keeps `_build/` and the Markdown documents (this README, VERIFICATION.md and IMAGE-MANIFEST.md) out of the public website. They stay in the repository.
- **`vercel.json`** permanently redirects the official site's old extensionless URLs to the new pages. For example, `/about-us` goes to `/about.html`, `/emergency-dental-care` to `/emergency-dentist.html`, and `/fillings` to `/fillings.html`. Existing `.html` URLs are unchanged.

### Search engines: staging vs production

| Build command | Robots | Canonical, Open Graph, sitemap |
| --- | --- | --- |
| `build.py` | noindex on every page, `Disallow: /` | relative URLs, no canonical, no sitemap |
| `build.py --site-url URL --noindex` | noindex on every page, `Disallow: /` | absolute canonical and Open Graph URLs, no sitemap |
| `build.py --site-url URL` (production) | indexable, except the 404 page and blog drafts | canonical, Open Graph, `sitemap.xml`, permissive `robots.txt` |

The vercel.app deployment is deliberately built with `--noindex`. The practice's current website is still live at southcairnsdental.com.au, and indexing a second copy on another domain could compete with it in search results. When the new site moves to the practice's domain, run:

```
python3 _build/build.py --site-url https://www.southcairnsdental.com.au
```

Then commit and push.

## Project structure

```
index.html, about.html, team.html, new-patients.html, services.html,
faq.html, special-offers.html, blog.html, blog-<slug>.html,
contact.html, privacy.html, 404.html          generated pages
14 service pages (check-ups-and-dental-cleans.html … emergency-dentist.html)
                                               generated from ONE template
assets/css/style.css     Design system and all components
assets/js/main.js        Navigation, dropdowns, opening hours, contact form, motion
assets/images/           team/, treatments/, offers/, brand/, illustrations/
_build/build.py          The generator, including the shared service-page template (service_page)
_build/check.py          QA: links, anchors, assets, metadata, placeholders, service template structure
_build/services/*.json   One content file per service page
_build/pages/*.html      Content for every other page
_build/practice.json     Practice facts: phone, address, coordinates, hours, booking link, services, team
_build/images.json       Every image slot: file, alt text, ratio, focal point, source
_build/blog.json         Blog posts
IMAGE-MANIFEST.md        Generated: every image, its source, licence status and where it's used
VERIFICATION.md          Facts to confirm with the practice
```

## The service-page template

All 14 treatment pages, including Emergency dental care, are rendered by `service_page()` in `_build/build.py` from `_build/services/<slug>.json`. Each page has the same components in the same order. A section is left out when its content isn't relevant, and section backgrounds alternate automatically.

| # | Section | JSON key |
| --- | --- | --- |
| 1 | Breadcrumbs (Home › Services › page) and hero: category, title, introduction, image, Book online and Call buttons, live opening status | `name`, `group`, `hero` |
| 2 | Introduction, with an optional "when it may be considered" panel | `intro` |
| 3 | Callout band (`brand`, `info` or `alert`), shown after the introduction | `callout` |
| 4 | The treatment explained: cards, tick list and/or supporting image | `explain` |
| 5 | Treatment process: numbered steps | `process` |
| 6 | Benefits, things to consider, alternatives | `weigh` |
| 7 | Care before and after, or at home | `care` |
| 8 | FAQs beside a cost and payment card | `faqs`, `cost` |
| 9 | Link to a related blog article (optional) | `blog` |
| 10 | Booking band, with a link to ask a question that pre-fills the contact form with the treatment | (automatic) |
| 11 | Related treatments, as photo cards | `related` |

Notes:

- `"tone": "urgent"` (Emergency) gives the hero a red treatment and a "Call now" button instead of online booking, and adds a sticky call bar on phones.
- `check.py` fails the build if a service page breaks this structure: wrong hero, missing breadcrumb, booking or phone link, out-of-order sections, or a missing booking band or related services.
- To add a service, create `_build/services/<slug>.json` (copy an existing one), add it to `service_groups` in `practice.json`, then rebuild.
- Don't add a service the practice doesn't offer. The list matches the official site.

## Editing content

The HTML files in the project root are **generated**, so edit the files in `_build/` and rebuild. Tokens such as `{{phone}}`, `{{booking_url}}`, `{{book_buttons}}`, `{{map}}`, `{{offer}}`, `{{team_cards}}` and `{{img:slot|classes}}` are expanded by the build. Python 3.8+ is needed, with no packages. On Windows without Python installed, the official "embeddable" zip from python.org runs the build unchanged.

## Design system

Tokens in `:root` define:

- **Colour:** Canopy, Forest, Fern, SCD Lime, Mist, Stone, Ink, and Alert (urgent information only).
- **Type:** Archivo for display text and Figtree for body text, on a fluid scale: `--t-title`, `--t-h2`, `--t-h3`, `--t-card`, `--t-lead`.
- **Spacing:** `--section`, `--card-gap`, `--card-pad`, `--gutter`.
- **Width:** a 1260px container and a 40rem text measure.
- **Shape:** `--radius-card`, `--radius-pill` and the signature `--corner`.
- **Elevation and motion:** `--shadow-sm`, `--shadow-hover`, `--lift`, `--ease-out`.

The v5 layer at the end of `style.css` applies these shared tokens to every card-like component, so cards, buttons, headings and sections look the same on every page.

## Images

`_build/images.json` registers every image slot. Statuses:

- **authentic:** the practice's own photos and artwork.
- **official-stock:** stock images already used on the practice's website. Their licences need confirming.
- **licensed-stock:** Unsplash-licensed photos, used where the practice has no suitable image.
- **illustration:** original artwork.

IMAGE-MANIFEST.md is regenerated on every build. It lists each file, its source, its status and the pages that use it.

## Map and location

The address and coordinates are verified from the practice's own Google Maps link (on the official site): 7 Stokes St, Edmonton QLD 4869, at -17.0195683, 145.7439008.

- The map is a keyless Google Maps embed, so no API key or environment variable is needed.
- An always-visible bar under the map shows the address with **Get directions** and **Open in Google Maps** buttons, so patients can find the practice even if the embed doesn't load.
- The same coordinates are used in the structured data.

## Booking and contact

- **Online booking:** every "Book online" button goes to the practice's Centaur Portal link (`booking_url` in `practice.json`, taken from the official site). That system doesn't accept a pre-selected service, so service pages pass their context through the "Ask a question about …" enquiry link instead.
- **Contact form:** it has labels, required-field validation, accessible error messages and a status message. It doesn't store or send anything itself. It opens the visitor's email app with the message filled in and says so plainly. To make it send directly, connect a form service and keep the same fields.

## Testing (version 5)

Everything was run against a local server, in headless Microsoft Edge driven by Puppeteer:

- **Render suite:** all 26 pages at 1440, 820 and 390px, which is 78 renders. There were no console errors, failed requests, broken images or horizontal overflow, and each page has one h1, a title and a description.
- **54 interaction checks passed**, covering:
  - dropdowns: mouse, hover, keyboard and touch
  - the mobile menu
  - the FAQ accordion
  - sticky header and anchors
  - reduced motion and no-JS
  - booking links and the contact-form pre-fill
  - form validation and feedback
  - the emergency sticky call bar
  - every link on every page
- **Accessibility:** an axe-core audit (WCAG 2.1 A/AA plus best practice) found no violations on any page at 1440 or 390px.
- **CSS pruning:** removing dead CSS was verified by comparing the computed styles and geometry of every element, before and after, on every page at two widths. There were no differences.
- **Map:** the live Google embed was loaded and checked, both the pin location and that it fits its container.
- **`_build/check.py` passed:** links, anchors, assets, metadata, placeholders and service template structure.
- **Not done:**
  - testing on real devices
  - testing with screen-reader software
  - testing on the Vercel deployment itself, which happens after you push, including the `vercel.json` redirects
