# South Cairns Dental website

A static, multi-page website for South Cairns Dental (Edmonton, Cairns), built with HTML, CSS and vanilla JavaScript. It has no framework, no database and no runtime dependencies.

## Run it locally

```
cd south-cairns-dental
python3 -m http.server 8000
```

Open http://localhost:8000. Use a local server rather than opening files directly, so the 404 page and map behave as they will in production.

## Project structure

```
index.html                         Homepage
about.html                         About South Cairns Dental
team.html                          Meet our dentists
new-patients.html                  New patients
services.html                      Services overview
13 treatment pages + emergency-dentist.html  (file names match the official URL slugs)
faq.html                           Frequently asked questions
special-offers.html                New patient offer
blog.html, blog-<slug>.html        Blog listing and articles (blog-page-N.html appears after 9 posts)
contact.html, privacy.html, 404.html
assets/css/style.css               Design system and all components
assets/js/main.js                  Navigation, dropdowns, live opening hours, mailto contact form, motion
assets/js/practice-data.js         Generated: opening hours for main.js
assets/fonts/                      Archivo and Figtree (variable, self-hosted, SIL OFL)
assets/images/team/                Team portraits and group photo (from the official About page)
assets/images/treatments/          Treatment images (stock already used on the official site)
assets/images/offers/              New patient offer flyer
assets/images/brand/               Logo, reversed logo, favicon, share image
assets/images/illustrations/       Original SVG illustrations (used where no suitable photo exists)
_build/                            Page sources, data files, build and QA scripts
IMAGE-MANIFEST.md                  Generated: every image, its source and where it is used
VERIFICATION.md                    Facts to confirm with the practice before launch
```

## Navigation

```
Home
About us ▾             About South Cairns Dental · Meet our dentists · New patients
Services ▾             Services overview · all 13 treatments in four groups · Emergency dental care
Patient information ▾  Frequently asked questions · Special offers · Emergency dental care
Blog
Contact us
[Book online] [Call]   Header buttons. "Dental emergency?" sits in the top bar on every page.
```

The navigation is defined once in `_build/build.py` (`MENUS` and `header()`).

- **Desktop:** dropdowns open on click, tap, Enter or Space. With a mouse they also open on hover, with a short delay so they don't flicker. Arrow keys move through the links. Escape closes a dropdown and returns focus to its button. Clicking outside, or tabbing out, closes it too.
- **Mobile (1020px and below):** a full-screen menu with collapsible groups. It includes Book online, Call and Dental emergency buttons, and page scrolling is locked while it's open.
- **Current page:** the active page is marked with `aria-current="page"`, and its parent group is highlighted.

## Version 4: what changed

- **Real images.** Every image was downloaded from the official site, optimised and stored locally. No placeholder artwork or "Photo to come" labels remain.
  - Team portraits are matched to names by their position on the official About page.
  - Treatment pages use the images the official site already uses for each treatment.
  - See IMAGE-MANIFEST.md.
- **Grouped navigation** with accessible dropdowns, as described above.
- **New pages:**
  - Meet our dentists (`team.html`), with full verified bios.
  - Blog (`blog.html`) and the practice's one published article.
- **Rewritten pages:**
  - About South Cairns Dental, now an editorial page built from the official copy.
  - New Patients.
  - FAQ, regrouped into seven topics.
  - Special Offers, which now shows the verified new patient offer as text.
- **Online booking.** The verified Centaur Portal link now appears everywhere next to the phone number.
- **Official content added:**
  - whitening FAQs
  - the implant consultation and 3D X-ray steps
  - the Emergency page introduction
  - the "yellow building" directions
- **Layout fixes:**
  - Treatment pages no longer leave an empty column when a section has no side panel.
  - The footer has been regrouped.
  - The dentures image is no longer stretched.
  - The mobile header no longer overflows.
- **Removed:** placeholder SVGs, `make_placeholders.py` and the old `IMAGE-MANIFEST.csv`. They are superseded by real images and the Markdown manifest.

## Editing content

The HTML files in the project root are **generated**. Edit these instead:

- `_build/practice.json`: phone, email, address, hours, booking link, service list (with each service's card photo), and the team (photo, qualifications, bio, interests).
- `_build/pages/*.html`: the content of each page. Each file starts with a `<!--meta {...} -->` block containing its title, description, `nav` key and breadcrumbs.
- `_build/images.json`: one entry per image slot. It records status, file, alt text, aspect ratio, focal point (`pos`), `fit: contain` for diagrams, and source.
- `_build/blog.json`: blog posts.
- `_build/content/*.json`: the extra detail sections on each treatment page.

Then rebuild and check:

```
python3 _build/build.py
python3 _build/check.py      # broken links and anchors, missing assets, placeholders, duplicate ids, metadata
```

Python 3.8+ is needed, with no packages. (On Windows without Python installed, the official "embeddable" zip from python.org runs the build unchanged.)

Tokens such as `{{phone}}`, `{{booking_url}}`, `{{book_buttons}}`, `{{offer}}`, `{{team_cards}}`, `{{img:home-hero|frame--corner}}` and `{{related:a.html,b.html}}` are expanded by the build.

## Images

- **Replacing an image:** save the optimised file (about 1600px on the long side and under 300 KB, or 900px for portraits) in the right `assets/images/` folder. Update its slot in `_build/images.json`, then rebuild. IMAGE-MANIFEST.md regenerates and records where every file is used.
- **Statuses:**
  - *authentic* is the practice's own photo.
  - *official-stock* is stock already used on the official site; its licence needs confirming.
  - *illustration* is original artwork. It is decorative and uses `alt=""`.
- **Team photos** are set per person in `practice.json` (`photo`). Only use a confirmed photo of that person.
- **Loading:** hero images are `fetchpriority="high"`, and every other image is `loading="lazy"`. Every image has width and height attributes, so layout doesn't shift.

## Blog

Posts live in `_build/blog.json`. Each post is built with a shared article layout at `blog-<slug>.html`. The layout includes:

- breadcrumbs, date and category
- a booking panel and related treatments
- "More articles"
- `BlogPosting` structured data

The listing features the newest post and paginates every 9 posts. If there are no posts, it shows an empty state.

Set `"status": "draft"` to keep a post out of normal builds. `python3 _build/build.py --drafts` builds drafts for local review, labelled "Draft: not published" and noindexed. Never publish fabricated articles, authors or dates.

## Motion

Motion is all CSS and vanilla JS:

- scroll reveals with a short stagger (service cards, team, portrait strip, blog cards)
- hover lift and gentle image zoom on cards and portraits
- button press feedback
- a header that compacts on scroll
- dropdowns that fade and slide in
- smooth accordion opening and closing
- smooth anchor scrolling, which clears the sticky header

All motion is switched off under `prefers-reduced-motion`. Reveals only apply after JavaScript has run, so content is visible with JS disabled. Both cases are tested.

## Staging vs production indexing

A default build (`python3 _build/build.py`) is a **staging** build. Every page gets `noindex, nofollow`, and `robots.txt` disallows all crawling.

A production build (`python3 _build/build.py --site-url https://…`) removes the noindex from every page except the 404 and drafts. It also writes a permissive `robots.txt` and `sitemap.xml`, and adds canonical URLs.

## Going live

Run `python3 _build/build.py --site-url https://www.southcairnsdental.com.au`, using the confirmed domain. Then:

- Configure the server to serve `404.html` for missing pages. That page uses `<base href="/">`, which assumes the site sits at the domain root.
- Set up redirects from the old URLs. They are listed at the end of VERIFICATION.md.

## Design system summary

- **Colour:** Canopy `#12302A`, Forest `#1E4A3F` (primary), SCD Lime `#AEC953` (from the logo), Mist `#EEF3EC`, Stone `#535A57` (secondary text), Ink `#1B2622`, and Alert `#A3302A`, used for urgent information only.
- **Type:** Archivo at an expanded width for headings, which echoes the logo's wide lettering; Figtree for text. The fluid type scale is defined in `:root`.
- **Signature:** the "SCD corner", one large rounded corner drawn from the logo mark, used on key imagery, cards and panels.
- **Layers in `style.css`:** the base system, then v2 (components and motion), v3 (dark heroes), and v4 (dropdowns, team, offer, blog, image framing). Later layers refine earlier ones.

## Behaviour notes

- Opening-hours status ("Open now until 5 pm") is calculated in `Australia/Brisbane` time, wherever the visitor is.
- The contact form does **not** send anything. It validates the fields, then opens the visitor's email app with the message filled in, and says so on screen.
- The map is a Google Maps embed. If it can't load, a fallback with the address and a directions link shows underneath.

## Testing done (version 4)

All of this was run against a local server, in headless Microsoft Edge (Chromium 155) driven by Puppeteer:

- **Every page** (26) was rendered at 1440, 820 and 390px, which is 78 renders. There were no console errors, no failed local requests, no broken images (lazy images were forced to load) and no horizontal overflow. Every page has one h1, a title and a meta description.
- **45 interaction checks passed:**
  - **Dropdowns:** click, outside-click, hover in, hover out and hover-then-click; Enter, Tab, ArrowDown and Escape with focus return; tabbing out closes the menu; menus are not clipped or covered.
  - **Active states:** About, Patient information, Services and Blog.
  - **Header:** the logo links home; the header stays sticky and compacts on scroll; anchors clear the header.
  - **Mobile menu with touch emulation:** open, groups, scroll lock, buttons, Escape with focus return, no overflow.
  - **FAQ:** Enter, Space, click and tap, with a visible keyboard focus.
  - **Reduced motion and no JavaScript:** all content visible.
- **Screenshots** of key pages were reviewed at desktop and mobile, along with header captures at 1100, 1030, 768 and 390px.
- **`_build/check.py` passed:** links, anchors, assets, placeholders, ids, h1s and metadata.
- **Not done:**
  - testing on real iOS or Android devices
  - testing with screen-reader software
  - checking live Google Maps rendering (third-party requests were blocked during tests)
  - checking the external booking site's own behaviour
