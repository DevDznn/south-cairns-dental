# Confirm before launch

In version 4 the official website (https://www.southcairnsdental.com.au) was reachable. On 26 October 2026 every page in its sitemap was downloaded and read: home, About Us, Services, all 14 treatment pages, Emergency, Special Offers, Payment Plans, Contact, Blog and the one blog article. Everything marked *verified* below was checked against that copy. Items still need the practice's sign-off, because a website can be out of date.

## Resolved in version 4

- [x] **Online booking link.** The official "Book Now" link is `https://www.centaurportal.com/d4w/org-1595/search?practiceID=1800`. It is now in `practice.json` and appears as "Book online" in the header, mobile menu, footer and every booking panel. Confirm it is still the correct link.
- [x] **Team portraits.** Each portrait sits directly above that person's name on the official About Us page, so they have been assigned by name: Dr Alex Bornsztejn, Dr Louis Fernandez, Dr Hannah Dominique Gaza, Dr Casey Almond and Carol. No photo of Ashlee is published, so she has a lettered tile. **Ask each person to confirm their photo and that they're happy for it to be used.**
- [x] **Whitening FAQ answers.** These are now taken from the official Whitening page and reworded conservatively.
- [x] **Special offers.** The official page shows the offer only as an image (`SP-2.jpg`). It is now reproduced as real text, with the flyer beside it. See "Fees and offers" below.
- [x] **Opening hours and address.** These match the official About and Contact pages: Mon to Thu 8 am to 5 pm, Fri 8 am to 4:30 pm, closed weekends, and Shop 6, 7 Stokes Street, Edmonton QLD 4869.
- [x] **Outbound links checked by script on 26 October 2026.** These returned success: teeth.org.au, teeth.org.au/first-dental-visits, healthdirect.gov.au (/dental-care and /gum-disease), oaic.gov.au and the Ahpra register. Two sites block automated checks, so please open them in a browser: servicesaustralia.gov.au/child-dental-benefits-schedule (timed out) and health.qld.gov.au (403).
- [x] **Toothpaste and first-visit guidance** matches the Australian Dental Association: no toothpaste until about 18 months unless a dentist advises otherwise, then a pea-sized amount of low-fluoride toothpaste until about 6, and a first visit when the first teeth appear or by age one.

## Fees, offers and payment (verified on the official site; confirm they are current)

- [ ] **New patient offer.** It covers a comprehensive check-up, all necessary X-rays (including full-face X-rays), a scale, clean and polish, fluoride treatment and a treatment plan. It is "gap free with any health fund", or "$199 if not in a health fund", and "T&Cs apply". Source: the official Special Offers flyer. It appears on Special Offers, New Patients, the FAQ and the homepage fees band. **Confirm the price, the conditions and the T&C wording.**
- [ ] **Zip interest-free payment plans.** The official homepage says "partners with ZipMoney… 12 month interest free payment plans", and the About page says "We can tailor interest free payment plans… see zip.co". The official Payment Plans page says "Coming soon". The new site only says "interest-free payment plans through Zip" and asks people to check with reception. It does not mention 12 months.
- [ ] **Complimentary implant consultation**, from the official Implants page. It is used on the Implants page and in the FAQ.
- [ ] **"Free kids dentistry" for eligible children.** Confirm which scheme or conditions apply.
- [ ] **CDBS.** The site says only that some families can claim it, that eligibility rules apply, and that families should ask reception.

## Team

- [ ] **Bios.** Those for Dr Alex Bornsztejn and Dr Louis Fernandez are lightly edited from the official About page; Louie's quote is reproduced. Ask each to approve.
- [ ] **Areas of interest tags** on the Team page. These come from each bio: Alex covers general dentistry, implants and wisdom teeth surgery; Louie covers general dentistry, oral surgery, wisdom teeth and implants. Confirm.
- [ ] **Dr Hannah Dominique Gaza and Dr Casey Almond.** No bios are published, so the site shows name, qualification and one neutral sentence. Supply bios if wanted.
- [ ] **Ashlee and Carol.** Only name and role are published. Supply a photo of Ashlee if wanted.
- [ ] **The team member in the group photo** with Dr Alex (`maddyalexgood1.jpg`, used on the homepage and About page) isn't named on the official site, and she isn't in the team list. Confirm the photo is still appropriate to use.
- [ ] **Hygienist/therapist.** The official homepage says the team includes "a Hygienist/Therapist", but none is named on the About page. The Team page mentions the role without a name.

## Blog

- [ ] **"Why choose dental implants?"** was first published 18 July 2023. It is kept with these edits, which need the practice's approval:
  - "A titanium screw is inserted into the gum" became "A titanium post is placed in the jawbone", for clinical accuracy.
  - "Prevents drifting…" became "Helps stop neighbouring teeth drifting…", and "Prevents shrinking…" became "Helps prevent the jawbone shrinking…", to avoid absolute claims.
  - A short "Is an implant right for me?" paragraph was added, pointing to alternatives and a consultation.
- [ ] **Future posts.** Add them to `_build/blog.json`. Posts with `"status": "draft"` are built only with `--drafts` and are labelled and noindexed. Never publish a draft without the practice's approval of its content, author and date.

## Images

- [ ] **Stock licences.** Fourteen image files on the new site are stock photos or illustrations that the practice already publishes (iStock, Shutterstock and others). Confirm the licences cover the new website. IMAGE-MANIFEST.md lists each one.
- [ ] **Check-ups hero.** It uses the official check-ups image, a before-and-after of a scale and clean that shows heavy tartar. It is clinically relevant but graphic. Swap in another image if preferred (slot `checkups` in `_build/images.json`).
- [ ] **No practice interior or exterior photos exist** on the official site. New photography of the building, reception and a treatment room is recommended.
- [ ] **The reversed (white) logo** in the footer and share image was made from the supplied logo.

## Claims taken from the official site

- [ ] **After-hours and weekend appointments** "available at request".
- [ ] **Patients may choose music or a movie** during treatment.
- [ ] **"You will always see the same dentist."**
- [ ] **"Affordable pricing", "competitive prices" and "the very best modern equipment"**, from the official homepage.
- [ ] **The practice is in the yellow building** next door to Edmonton Family Medical Centre.

## Clinical and emergency wording (dentist review required)

These items carry over from version 3:

- [ ] **First-aid steps on the Emergency page:** toothache, broken tooth, swelling, knocked-out tooth and broken dentures.
- [ ] **The 000 and hospital criteria** on the Emergency and Wisdom teeth pages, and the 13 HEALTH number (13 43 25 84).
- [ ] **New and non-registered patients and children** being seen for emergencies.
- [ ] **Implant healing of "about 3 months"** and **Invisalign taking "as little as 7 weeks; around 12 to 18 months is typical"**, both from the official pages.
- [ ] **Root canal steps, filling FAQs and children's tips.**
- [ ] **Treatment detail sections** (`_build/content/*.json`). These are general clinical information.
- [ ] **New Patients "what you may like to bring" list.** It is marked on the page as general guidance, not a requirement.
- [ ] **Privacy policy** (`privacy.html`). This is a general draft and needs the practice's review, contact details and a "last updated" date.
- [ ] **The Invisalign trademark.** The site uses the word only; no Align Technology logo is used.

## Going live

- [ ] **Production domain,** for canonical URLs, the sitemap and structured data. See the README.
- [ ] **Redirects from the old URLs:**
  - `/about-us` → `/about.html`
  - `/contact` → `/contact.html`
  - `/services` → `/services.html`
  - `/emergency-dental-care` → `/emergency-dentist.html`
  - `/special-offers` → `/special-offers.html`
  - `/payment-plans` → `/faq.html#costs`
  - `/blog/` → `/blog.html`
  - `/blog/why-choose-dental-implants` → `/blog-why-choose-dental-implants.html`
  - each treatment slug → the same slug with `.html`
