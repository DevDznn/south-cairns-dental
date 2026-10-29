# South Cairns Dental — WordPress theme

A custom WordPress theme for South Cairns Dental, converted from the practice's static website. It uses the same design system, fonts, images, header, footer and interactions (dropdown menus, mobile menu, phone carousels, FAQ accordions, live opening status, enquiry form). Every page is an Elementor page built from **South Cairns Dental widgets**, so all text, images, buttons and cards can be edited visually.

- **Requires:** WordPress 6.4+ (tested on 7.1.3), PHP 7.4+, **Elementor** (free, tested on 4.3.5).
- **SEO:** **ThinkRank** (tested on 2.18.0). The theme prints no SEO tags of its own while ThinkRank is active.
- **Elementor Pro:** not required. If you have it, you can optionally replace the header and footer with Theme Builder templates (see below).

## Install

1. **Plugins first.** In *Plugins › Add New*, install and activate **Elementor** and **ThinkRank**.
2. **Theme.** In *Appearance › Themes › Add New › Upload Theme*, choose the latest `south-cairns-dental-v<version>.zip`, then **Activate**. To update an installed copy, upload the newer ZIP and choose *Replace active with uploaded*, then purge any page cache or CDN.
3. **Import the website.** Go to *Appearance › South Cairns setup* and press **Import website content**. This creates:
   - the homepage, About, Meet our dentists, New patients, Services, FAQ, Special offers, Payments, Contact and Privacy pages;
   - the 14 treatment pages;
   - the two campaign landing pages (Full arch dental implants and Cosmetic dentistry), kept out of menus and search results;
   - the Blog page and the published article;
   - about 24 images in the Media Library (with alt text);
   - the main and footer menus, and the logo;
   - the homepage and posts-page settings, and page-name permalinks such as `/fillings/`.

   The import is safe to run again. It reuses images and pages, and it never overwrites a page it didn't create unless you tick *Replace*.
4. **Check practice details.** In *Appearance › Customize › Practice details*, check the phone number, email, booking link, address, map coordinates and opening hours. The header, footer, booking and call buttons, maps and opening-hours status all use these settings.
5. **Set up ThinkRank** (see below).

Old addresses keep working. `/fillings.html`, `/about.html`, `/blog-why-choose-dental-implants.html` and the official site's older paths (such as `/about-us` and `/emergency-dental-care`) are redirected with a 301 to the new pages. Redirects only apply when a page would otherwise be "not found".

## Editing pages in Elementor

Open any page and choose **Edit with Elementor**. Each section is one **SCD** widget inside a full-width container. Click a section to edit its content in the left panel:

- **Content tab:** headings, text, images (*Choose image* opens the Media Library), buttons, and repeating items such as cards, questions, people and steps. In repeaters you can add, remove, duplicate and drag items to reorder them.
- **Style tab › Section:** background (white, mist, pale lime, dark green and so on), spacing, a custom colour or padding, and an anchor ID for links like `#costs`.
- **Style tab › Heading:** heading colour and typography.

Sections can be reordered by dragging, duplicated or removed. You can also add Elementor's own widgets (Heading, Text, Image, Button…) in new containers. The brand colours and fonts are set as Elementor's global colours and fonts.

**Buttons.** "Link to" can be *Online booking*, *Call the practice* or *Email the practice*. These follow Practice details automatically. A button can also have a custom link. In button labels and card text, `{phone}` shows the practice phone number.

**Text fields.** Text boxes accept short HTML for links and bold text, for example `<a href="/faq/#costs">Costs and payment</a>`. Leave a blank line between paragraphs.

### The widgets

| Widget | Used for |
| --- | --- |
| SCD Home hero | Homepage hero: heading, buttons, trust points, photo, "Book today" card, phone action bar |
| SCD Page hero | Inner-page heroes: breadcrumbs, heading, introduction, buttons, side panel, note, jump links, image or portrait strip |
| SCD Treatment hero | Treatment pages: breadcrumbs, group label, booking/call buttons, live status, photo. The urgent tone gives the red emergency hero and sticky call bar |
| SCD Contact strip | The four contact-at-a-glance items |
| SCD Cards and lists | Every card and list layout: link cards, features, payment cards, ways to book, numbered steps, facts, ticks, treatment information cards, benefits columns, care lists, blog teaser |
| SCD Image and text | Homepage introduction (badge and counters), About story, nervous-patients section |
| SCD Service cards | Homepage treatment cards with category filter (edit them here, or fill them from the treatment pages) |
| SCD Treatment directory | Treatment directory and grouped lists (automatic, from the treatment pages) |
| SCD Related treatments | Related-treatment photo cards (choose the pages) |
| SCD Team cards / SCD Dentist profiles | Dentist cards, support team, full profiles with biography, interests and quote |
| SCD Questions (FAQ) / SCD FAQ by topic | Accordions; the FAQ page with topic navigation |
| SCD Emergency band, SCD Callout band, SCD Booking band | Emergency call card; lime, pale or red message bands; the booking band and urgent strip |
| SCD Text section, SCD Special offer, SCD Contact details and form, SCD Map | Text layouts, the new patient offer (up to four prices), contact details with hours and enquiry form, the map |
| SCD Flexible section | The campaign landing pages, and treatment-page sections with their own layout (introductions with an illustration, care and cost sections, the promo bands and risk notes). See below |

### Editing a Flexible section

A Flexible section is a list of rows, each holding one piece of content:

- **Content rows:** a heading, a paragraph, an image, an icon, buttons, the live opening status, or a list item. List items can be a tick, a title and text, an icon point, a trust point, a card, a fact, a contact row, a dentist, a step or a question.
- **Group rows:** "Group start" and "Group end" rows hold the columns, panels, lists and cards that the content sits in. Their classes set the layout.

How to edit:

- **Change content:** edit the text, image or link in the row.
- **Add an item:** duplicate a list-item row, then drag it into place. Keep it between the same group start and end rows.
- **Groups:** you don't normally need to change group rows. Deleting one without its matching end row will change the layout.

The section's background and spacing are under *Style › Section*, as for the other widgets.

These widgets are custom because each section's exact markup (and the shared stylesheet that styles it) can't be reproduced with Elementor's basic widgets without changing the design. All of their content is held in widget settings; nothing on the page is fixed in the theme's code.

## Treatment pages

The 14 treatments are ordinary pages built from the same widgets in the same order. That gives every treatment the same layout: hero, introduction, callout, explanation, process, benefits, care, FAQs with cost, booking band and related treatments. Each page keeps its own text and images.

The **Treatment details** box in the page editor's sidebar (*Edit page*, not Elementor) holds what lists and cards show:

- group and category;
- short name;
- card description and one-line summary;
- urgent styling.

The **Featured image** is the card photo. The page's **Order** sets its position in lists.

Related-treatment cards can use a treatment page, or be a *custom card* (image, title, text, link) for any other page, such as a campaign landing page.

To add a treatment:

1. Duplicate an existing treatment page.
2. Edit it in Elementor.
3. Set its Treatment details.
4. Add it to the Services menu.

## Campaign landing pages

**Full arch dental implants** (`/full-arch-dental-implants/`) and **Cosmetic dentistry** (`/cosmetic-dentistry/`) are landing pages for Meta advertising campaigns, not regular treatments.

- **Not listed as services:** they are not in the menus, the services directory or lists, or the homepage service cards, because they aren't marked as treatments.
- **How visitors reach them:** from the ad links, and from contextual links on the related treatment pages. Dental implants links to Full arch dental implants, and Cosmetic veneers links to Cosmetic dentistry. Each link appears in the text, a promo band and, for implants, a related-treatment card.
- **The "Campaign landing page" box:** in the page editor sidebar, it switches on the landing page styles (`page-lp`, plus a style name such as `full-arch`).
- **Search:** as on the static site, both pages are kept out of search results with **noindex, nofollow**, set in each page's **ThinkRank** robots settings by the importer. If ThinkRank is ever inactive, the theme keeps honouring that setting through WordPress's own robots tag.
- **Editing:** the hero, trust strip and most sections are Flexible sections. The questions use the FAQ widget.

## Header, footer and menus

- **Logo:** *Customize › Site Identity*. The footer logo, for the dark background, is under *Customize › Practice details › Footer*.
- **Menus:** *Appearance › Menus* has the main navigation and four footer menus. The footer column headings are the menus' names. In the main menu:
  - an item with sub-items becomes a dropdown;
  - the Services item becomes the large services panel. Its second-level items that have their own sub-items are the columns, its other second-level items are the panel's links, and the CSS class `is-urgent` makes a link red. The item's *Description* is the panel's introduction.
- **Contact details, hours and footer text:** *Customize › Practice details*.
- **With Elementor Pro:** the theme registers Elementor's header and footer locations. A Theme Builder header or footer replaces the theme's own when one is published. That is optional and not needed for normal editing.

## SEO with ThinkRank

ThinkRank is responsible for SEO titles, meta descriptions, canonical URLs, robots directives, Open Graph and X tags, sitemaps, breadcrumbs schema and Local Business schema. While ThinkRank is active the theme prints none of these, so there are no duplicates. WordPress core provides the `<title>`, which ThinkRank overrides. If ThinkRank is ever inactive, the theme adds only a basic meta description and, on the homepage and Contact page, the practice's `Dentist` structured data.

The importer copies each page's SEO title and description from the static site into ThinkRank's fields (only where they're empty).

After importing:

1. **ThinkRank › Local SEO.** Choose the business type **Dentist** and enter the verified details: South Cairns Dental, Shop 6, 7 Stokes Street, Edmonton QLD 4869, (07) 4055 4214, reception@southcairnsdental.com.au, the opening hours, and coordinates −17.0195683, 145.7439008. ThinkRank then publishes the practice's LocalBusiness schema.
2. **Blog page.** Set its SEO title and description in ThinkRank. ThinkRank treats the posts page as the blog home, so the imported values don't apply there.
3. **Search visibility.** Check *Settings › Reading › Search engine visibility* is **not** ticked on the live site.
4. **Duplicate schema.** Run ThinkRank's schema-conflict health check.

## Booking, contact and map

- **Booking:** "Book online" buttons go to the practice's Centaur Portal booking page (the booking link in Practice details). No appointment is created on the website itself.
- **Phone and email:** all phone links are `tel:+61740554214`. Email links use the practice email.
- **Enquiry form:** the form on the Contact page sends nothing itself. When submitted, it opens the visitor's own email app with the message addressed to reception, exactly as on the static site. No data is stored on the website, so no form plugin is needed. To receive enquiries on the website instead, add a form plugin and replace the form section.
- **Map:** a keyless Google Maps embed, with no API key stored. The address, "Get directions" and "Open in Google Maps" stay visible underneath even if the map can't load.

## Images

The theme's images are imported into the Media Library so they can be replaced from any widget's image control.

- **Portraits:** shown in a fixed 4:5 frame with a focal point, so they line up whatever the original photo's size.
- **Placeholder:** Ashlee has no published photo, so a branded placeholder is shown until one is added.
- **Illustrations:** WordPress doesn't allow SVG uploads by default, so the decorative illustrations are used from the theme folder and are not in the Media Library.

Image sources and licence notes are in the repository's `IMAGE-MANIFEST.md`. Some stock images used on the official site need their licences confirmed.

## Files

- `style.css`: theme header.
- `assets/css/site.css`: the static site's design system, unchanged.
- `assets/css/landing.css`: the static site's campaign landing page styles, unchanged.
- `assets/css/wp.css`: WordPress and Elementor adaptations.
- `assets/js/main.js`: site behaviour, re-initialised for widgets that Elementor re-renders.
- `inc/`: practice settings, setup, navigation, treatment details, SEO, legacy redirects, and the Elementor widgets (`inc/elementor/`).
- `inc/import/`: the importer and the generated content (`content/site.json`).
- `template-parts/`: header, footer, and the page and post templates for pages not built in Elementor.
