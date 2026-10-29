# Confirm before launch

In version 4 the official website (https://www.southcairnsdental.com.au) was reachable. On 26 October 2026 every page in its sitemap was downloaded and read: home, About Us, Services, all 14 treatment pages, Emergency, Special Offers, Payment Plans, Contact, Blog and the one blog article. Everything marked *verified* below was checked against that copy. Items still need the practice's sign-off, because a website can be out of date.

## Practice manager's update (29 October 2026)

Applied to the static site and the WordPress theme (1.0.2):

- [x] **Team.** Dr Alex Bornsztejn removed. Dr Max Ryu added as Principal Dentist. Support staff added: Catherine Pigott, Ebony Jamieson, Natalie Rea, Jasmine Spranklin and Freya Clark.
- [x] **Opening hours.** Mon and Tue 8 am to 5:30 pm, Wed and Thu 8 am to 6:30 pm, Fri 8 am to 4:30 pm, Sat 8 am to 2:30 pm. Sunday closed (as on the official site).
- [x] **No after-hours appointments.** The "after-hours and weekend appointments on request" wording is removed.
- [x] **CDBS.** The practice accepts eligible patients under the Child Dental Benefits Schedule.
- [x] **New patient offer.** Gap free with eligible private health insurance and dental cover; $199 adults; $120 children including X-rays; $99 children without X-rays.
- [x] **Same dentist at each visit.** Patients can see the same dentist at each visit.
- [x] **Full arch implants.** Provided by Dr Louis Fernandez and Dr Max Ryu. Local anaesthetic; oral sedation options including prescribed medication such as Valium (diazepam); general anaesthesia and other sedation by referral. Temporary bridges or dentures during healing, depending on the treatment plan and clinical suitability.
- [x] **Payment plans.** Afterpay, Zip, Fund My Dental and Denticare. No interest-free terms are stated.
- [x] **Cosmetic dentistry landing page.** "Invisalign" replaced with "clear aligners".
- [x] **Photo of Dr Alex and Maddy** (`team/alex-with-team-member.jpg`, from `maddyalexgood1.jpg`) removed from the homepage hero, the About page and both landing pages. It is replaced by the licensed stock photo already used on the Extractions page.

Still to confirm or supply:

- [ ] **A current practice photo** to replace the stock photo in the homepage hero and About page (and the side image on both landing pages).
- [ ] **Dr Max Ryu:** photo, qualifications and a short bio. The site shows a placeholder portrait and no qualifications.
- [ ] **Support staff roles and photos.** The five new support staff are shown as "Support team" with placeholder portraits. Supply each person's role and photo, and confirm each is happy to be listed.
- [ ] **Dr Louis Fernandez, Dr Hannah Dominique Gaza, Dr Casey Almond, Ashlee and Carol.** Their current employment wasn't confirmed, so they are unchanged.
- [ ] **Payment methods** (for example card, EFTPOS, HICAPS on-the-spot claims). None were confirmed, so the Payments page doesn't list any.
- [ ] **"Free kids dentistry for eligible children"** (from the official site) still appears beside the CDBS wording. Confirm whether eligible children's treatment is bulk billed, or remove the word "free".
- [ ] **Super Health / early release of superannuation.** Not published. Raised by the practice manager as a question, not approved.
- [ ] **Implant and cosmetic prices.** None confirmed; costs are given after consultation.
- [ ] **New logo or slogan.** None supplied; the existing ones are kept.
- [x] **Clear aligners site-wide.** "Invisalign" is replaced by "clear aligners" on every page, menu and footer (29 October 2026). The page keeps its address, `invisalign.html` (WordPress: `/invisalign/`), so existing links and bookmarks still work.
- [ ] **The printed new patient offer flyer** (`offers/new-patient-offer.jpg`) still shows the old terms ("gap free with any health fund", "$199"). The website now shows only its photo and heading, with the current terms as text. Supply an updated flyer if wanted.

## New in version 5 (confirm or decide)

- [ ] **Three Unsplash photos.** These are on the Fillings, Extractions and Whitening pages, and their service cards:
  - `dental-exam-close-up.jpg` by Caroline LM
  - `relaxed-patient-in-chair.jpg` by D Dental Office (the dentist wears another practice's name badge, which isn't legible at display size)
  - `natural-smile.jpg` by Lesly Juarez

  All three are free for commercial use under the Unsplash License, checked on each photo page on 26 October 2026. None shows South Cairns Dental, so replace them with practice photography when it's available.
- [ ] **Search indexing on vercel.app.** The deployment is deliberately `noindex`, because the official site is still live and a second indexed copy could compete with it. When the practice's domain points at the new site, rebuild without `--noindex` (see the README).
- [ ] **Map pin and directions.** Both use the coordinates from the practice's own Google Maps listing (-17.0195683, 145.7439008). Confirm the pin and directions bring patients to the right entrance.
- [ ] **Mouthguards.** The earlier claim that a mouthguard reduces concussion risk was removed, because the evidence doesn't support it. Ask a dentist before reinstating it.
- [ ] **Contact form.** It opens the patient's email app; nothing is sent from the website. Decide whether to connect a form service (for example, the official site's enquiry form provider).
- [ ] **Online booking.** The booking link can't pre-select a treatment. Service pages offer "Ask a question about …", which pre-fills the contact form with the treatment instead. Ask Centaur whether the practice's portal supports deep links per appointment type.

## Resolved in version 4

- [x] **Online booking link.** The official "Book Now" link is `https://www.centaurportal.com/d4w/org-1595/search?practiceID=1800`. It is now in `practice.json` and appears as "Book online" in the header, mobile menu, footer and every booking panel. Confirm it is still the correct link.
- [x] **Team portraits.** Each portrait sits directly above that person's name on the official About Us page, so they have been assigned by name: Dr Alex Bornsztejn, Dr Louis Fernandez, Dr Hannah Dominique Gaza, Dr Casey Almond and Carol. No photo of Ashlee is published, so she has a lettered tile. **Ask each person to confirm their photo and that they're happy for it to be used.**
- [x] **Whitening FAQ answers.** These are now taken from the official Whitening page and reworded conservatively.
- [x] **Special offers.** The official page shows the offer only as an image (`SP-2.jpg`). It is now reproduced as real text, with the flyer beside it. See "Fees and offers" below.
- [x] **Opening hours and address.** The address matches the official About and Contact pages: Shop 6, 7 Stokes Street, Edmonton QLD 4869. The hours were updated on 29 October 2026 (see above).
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

- [x] **After-hours and weekend appointments** "available at request": removed on 29 October 2026; the practice doesn't offer them.
- [ ] **Patients may choose music or a movie** during treatment.
- [x] **"You will always see the same dentist."** Now "you can see the same dentist at each visit", as confirmed on 29 October 2026.
- [ ] **"Affordable pricing", "competitive prices" and "the very best modern equipment"**, from the official homepage.
- [ ] **The practice is in the yellow building** next door to Edmonton Family Medical Centre.

## Clinical and emergency wording (dentist review required)

These items carry over from version 3:

- [ ] **First-aid steps on the Emergency page:** toothache, broken tooth, swelling, knocked-out tooth and broken dentures.
- [ ] **The 000 and hospital criteria** on the Emergency and Wisdom teeth pages, and the 13 HEALTH number (13 43 25 84).
- [ ] **New and non-registered patients and children** being seen for emergencies.
- [ ] **Implant healing of "about 3 months"** and **clear aligner treatment taking "as little as 7 weeks; around 12 to 18 months is typical"**, both from the official pages.
- [ ] **Root canal steps, filling FAQs and children's tips.**
- [ ] **Treatment page content** (`_build/services/*.json`): clinical wording on every treatment page.
- [ ] **New Patients "what you may like to bring" list.** It is marked on the page as general guidance, not a requirement.
- [ ] **Privacy policy** (`privacy.html`). This is a general draft and needs the practice's review, contact details and a "last updated" date.
- [x] **The Invisalign trademark.** No longer used on the site; the treatment is called clear aligners.

## Going live

- [ ] **Production domain,** for canonical URLs, the sitemap and structured data. See the README.
- [x] **Redirects from the old URLs** are now set up in `vercel.json` (Vercel applies them after a push; test them once deployed):
  - `/about-us` → `/about.html`
  - `/contact` → `/contact.html`
  - `/services` → `/services.html`
  - `/emergency-dental-care` → `/emergency-dentist.html`
  - `/special-offers` → `/special-offers.html`
  - `/payment-plans` → `/payments.html`
  - `/blog/` → `/blog.html`
  - `/blog/why-choose-dental-implants` → `/blog-why-choose-dental-implants.html`
  - each treatment slug → the same slug with `.html`
