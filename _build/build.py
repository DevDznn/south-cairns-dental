#!/usr/bin/env python3
"""South Cairns Dental static site builder.

Reads page sources from _build/pages/*.html, wraps each in the shared head,
header and footer, expands {{tokens}}, and writes finished HTML to the
project root. Also writes assets/js/practice-data.js and IMAGE-MANIFEST.csv.

Usage:
    python3 _build/build.py
    python3 _build/build.py --site-url https://www.example.com.au   # adds canonicals, og:url, sitemap.xml, robots.txt

No third-party packages required (Python 3.8+).
"""
import argparse, html, json, re, sys
from pathlib import Path
from urllib.parse import quote_plus

ROOT = Path(__file__).resolve().parent.parent
BUILD = ROOT / "_build"
P = json.loads((BUILD / "practice.json").read_text(encoding="utf-8"))
IMAGES = {k: v for k, v in json.loads((BUILD / "images.json").read_text(encoding="utf-8")).items() if not k.startswith("_")}
BLOG = json.loads((BUILD / "blog.json").read_text(encoding="utf-8"))

ap = argparse.ArgumentParser()
ap.add_argument("--site-url", default=P.get("site_url", ""))
ap.add_argument("--noindex", action="store_true", help="keep every page out of search engines even with --site-url (staging or preview domain)")
ap.add_argument("--drafts", action="store_true", help="include draft blog posts (local preview only; marked as drafts and noindex)")
args = ap.parse_args()
SITE = args.site_url.rstrip("/")
INDEXABLE = bool(SITE) and not args.noindex   # production = a confirmed domain and no --noindex

ADDRESS_ONE_LINE = f'{P["address_line1"]}, {P["suburb"]} {P["state"]} {P["postcode"]}'
MAPS_LINK = "https://www.google.com/maps/search/?api=1&query=" + quote_plus(P["maps_query"])
# Directions to the coordinates of the practice's own Google Maps listing (linked from the official site)
MAPS_DIR = f'https://www.google.com/maps/dir/?api=1&destination={P["geo"]["lat"]},{P["geo"]["lng"]}' 
MAPS_EMBED = "https://maps.google.com/maps?q=" + quote_plus(P["maps_query"]) + "&z=16&output=embed"
ALL_SERVICES = [s for g in P["service_groups"] for s in g["items"]]

ICON = {
  "phone": '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>',
  "arrow": '<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M2 8h11M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  "chev": '<svg viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  "mail": '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M3.5 6.5h17v11h-17zM4 7l8 6 8-6"/></svg>',
  "pin": '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3" fill="currentColor"/></svg>',
}

def _i(d): return f'<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{d}</g></svg>'
ICONS = {
  "family": _i('<circle cx="8" cy="7" r="3"/><circle cx="17" cy="9" r="2.3"/><path d="M2.5 20c.5-4 2.8-6.2 5.5-6.2s5 2.2 5.5 6.2M13.5 20c.3-2.8 1.7-4.4 3.5-4.4s3.2 1.6 3.5 4.4"/>'),
  "heart": _i('<path d="M12 20s-7.5-4.6-7.5-10A4.2 4.2 0 0 1 12 7.6 4.2 4.2 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10z"/>'),
  "shield": _i('<path d="M12 3l7 3v5.5c0 4.4-3 8-7 9.5-4-1.5-7-5.1-7-9.5V6z"/><path d="M9 12l2 2 4-4"/>'),
  "spark": _i('<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>'),
  "calendar": _i('<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>'),
  "car": _i('<path d="M5 16V12l2-5h10l2 5v4"/><path d="M3.5 16h17v3h-17zM7 19v1.5M17 19v1.5"/><circle cx="8" cy="13.5" r=".6"/><circle cx="16" cy="13.5" r=".6"/>'),
  "chat": _i('<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9.5h8M8 12.5h5"/>'),
  "clipboard": _i('<rect x="5" y="4.5" width="14" height="16" rx="2"/><path d="M9 4.5V3h6v1.5M8.5 10h7M8.5 13.5h7M8.5 17h4"/>'),
  "tooth": _i('<path d="M7 3.5c2 0 3 1 5 1s3-1 5-1c2.5 0 3.5 2.2 3 5-.6 3-1.6 4-2 7-.4 3-1 5-2.3 5-1.6 0-1.1-5.5-3.7-5.5S9.9 20.5 8.3 20.5C7 20.5 6.4 18.5 6 15.5c-.4-3-1.4-4-2-7-.5-2.8.5-5 3-5z"/>'),
  "clock": _i('<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>'),
  "music": _i('<path d="M9 18V6l10-2v12"/><circle cx="7" cy="18" r="2.2"/><circle cx="17" cy="16" r="2.2"/>'),
  "pause": _i('<circle cx="12" cy="12" r="8.5"/><path d="M10 9v6M14 9v6"/>'),
  "question": _i('<circle cx="12" cy="12" r="8.5"/><path d="M9.6 9.5a2.5 2.5 0 1 1 3.6 2.3c-.8.4-1.2.9-1.2 1.8M12 16.6v.2"/>'),
  "alert": _i('<path d="M12 3.5 21 19.5H3z"/><path d="M12 10v4M12 16.8v.2"/>'),
  "wallet": _i('<rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M16 12.5h2.5M3 9.5h15"/>'),
  "phone": _i('<path d="M21 16.4v3a1.9 1.9 0 0 1-2.1 1.9A18.8 18.8 0 0 1 2.7 5.1 1.9 1.9 0 0 1 4.6 3h3a1.9 1.9 0 0 1 1.9 1.6c.1.9.4 1.8.7 2.7a1.9 1.9 0 0 1-.4 2L8.5 10.6a15 15 0 0 0 5 5l1.3-1.3a1.9 1.9 0 0 1 2-.4c.9.3 1.8.6 2.7.7a1.9 1.9 0 0 1 1.5 1.8z"/>'),
  "map": _i('<path d="M9 4 3.5 6v14L9 18l6 2 5.5-2V4L15 6z"/><path d="M9 4v14M15 6v14"/>'),
  "mail": _i('<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/>'),
}
def esc(s): return html.escape(s, quote=True)
AC = ' aria-current="page"'

def fmt_time(hhmm):
    h, m = map(int, hhmm.split(":")); suf = "pm" if h >= 12 else "am"; h = h % 12 or 12
    return f"{h}:{m:02d} {suf}"

# ---------- Reusable blocks ----------
def hours_table():
    rows = []
    for d in P["hours"]:
        val = f'{fmt_time(d["open"])} to {fmt_time(d["close"])}' if d["open"] else "Closed"
        rows.append(f'<tr data-day="{d["day"]}"><th scope="row">{d["day"]}</th><td>{val}</td></tr>')
    return '<table class="hours"><caption class="visually-hidden">Opening hours</caption><tbody>' + "".join(rows) + "</tbody></table>"

def hours_compact():
    return "Monday to Thursday 8:00 am to 5:00 pm<br>Friday 8:00 am to 4:30 pm<br>Saturday and Sunday closed"

def call_btn(cls="btn--lime", label=None, size=""):
    label = label or f'Call {P["phone_display"]}'
    return f'<a class="btn {cls} {size}" href="tel:{P["phone_tel"]}">{ICON["phone"]}<span>{label}</span></a>'

def book_buttons(cls_primary="btn--lime", cls_secondary="btn--ghost"):
    """Online booking (the practice's Centaur Portal link, from the official site) first, then phone."""
    if not P.get("booking_url"):
        return f'<div class="btn-row">{call_btn(cls_primary, "Call to book " + P["phone_display"])}</div>'
    return (f'<div class="btn-row"><a class="btn {cls_primary}" href="{esc(P["booking_url"])}" rel="noopener">{ICONS["calendar"]}<span>Book online</span></a>'
            f'{call_btn(cls_secondary, "Call " + P["phone_display"])}</div>')

def img(spec):
    """{{img:id|extra-classes|caption}} renders a frame from images.json.
    Photos (authentic or official-stock) get alt text; original illustrations are decorative."""
    parts = spec.split("|"); iid = parts[0]; extra = parts[1] if len(parts) > 1 else ""
    caption = parts[2] if len(parts) > 2 else ""
    if iid not in IMAGES: sys.exit(f"Unknown image id: {iid}")
    im = IMAGES[iid]
    if not (ROOT / im["src"]).exists(): sys.exit(f"Image file missing for {iid}: {im['src']}")
    w, h = im.get("w", 1600), im.get("h", 1200)
    style = f'--ratio:{im.get("ratio", "4 / 3")}'
    if im.get("pos"): style += f';--pos:{im["pos"]}'
    cls = f'frame {extra}'
    if im.get("fit") == "contain": cls += " frame--contain"
    if im["status"] == "illustration": cls += " frame--illus"
    loading = 'fetchpriority="high"' if im.get("eager") else 'loading="lazy"'
    alt = "" if im["status"] == "illustration" else esc(im["alt"])
    cap = f'<figcaption class="frame__cap">{caption}</figcaption>' if caption else ""
    return (f'<figure class="{cls.strip()}" style="{style}" data-image-id="{iid}">'
            f'<img src="{im["src"]}" alt="{alt}" width="{w}" height="{h}" {loading} decoding="async">{cap}</figure>')

def illus(spec):
    """{{illus:name|classes|ratio}} renders an original illustration (decorative)."""
    p = spec.split("|"); name = p[0]; cls = p[1] if len(p) > 1 else ""; ratio = p[2] if len(p) > 2 else "4 / 3"
    if not (ROOT / f"assets/images/illustrations/{name}.svg").exists(): sys.exit(f"Missing illustration {name}")
    return (f'<figure class="frame frame--illus {cls}" style="--ratio:{ratio}"><img src="assets/images/illustrations/{name}.svg" alt="" width="400" height="300" loading="lazy" decoding="async"></figure>')

CATS = ["Preventive", "General", "Restorative", "Cosmetic", "Urgent"]
def thumb(s, cls=""):
    """Card/list image for a service: the official-site photo when there is one, otherwise its original illustration.
    Decorative (alt="") because the service name sits right beside it."""
    if s.get("photo"):
        return f'<img class="{cls} is-photo" src="{s["photo"]}" alt="" width="400" height="300" loading="lazy" decoding="async">'
    return f'<img class="{cls}" src="assets/images/illustrations/{s["illus"]}.svg" alt="" width="400" height="300" loading="lazy" decoding="async">'

def service_cards():
    chips = '<button type="button" class="chip" aria-pressed="true" data-filter="all">All treatments</button>' + "".join(
        f'<button type="button" class="chip" aria-pressed="false" data-filter="{c}">{c}</button>' for c in CATS)
    cards = []
    for i, s in enumerate(ALL_SERVICES):
        cards.append(f'<li class="svc-card reveal" style="--i:{i % 4}" data-cat="{s["category"]}"><a href="{s["file"]}">'
                     f'<span class="svc-card__media">{thumb(s)}</span>'
                     f'<span class="svc-card__body"><span class="svc-card__cat">{s["category"]}</span><strong>{s["name"]}</strong><span class="svc-card__desc">{s["desc"]}</span>'
                     f'<span class="svc-card__more">Learn more {ICON["arrow"]}</span></span></a></li>')
    cards.append('<li class="svc-card svc-card--urgent reveal" style="--i:2" data-cat="Urgent"><a href="emergency-dentist.html">'
                 '<span class="svc-card__media"><img class="is-photo" src="assets/images/treatments/toothache.jpg" alt="" width="400" height="300" loading="lazy" decoding="async"></span>'
                 '<span class="svc-card__body"><span class="svc-card__cat">Urgent</span><strong>Emergency dental care</strong>'
                 '<span class="svc-card__desc">Toothache, a broken tooth, swelling or lost dentures. We set aside appointments every day for urgent problems.</span>'
                 f'<span class="svc-card__more">What to do now {ICON["arrow"]}</span></span></a></li>')
    return (f'<div class="chips" role="group" aria-label="Filter treatments">{chips}</div>'
            f'<ul class="svc-grid" data-filter-grid>{"".join(cards)}</ul><p class="svc-grid__status visually-hidden" role="status" aria-live="polite"></p>')

def faq_html(items, open_first=False):
    out = ['<div class="faq">']
    for n, (q, a) in enumerate(items):
        out.append(f'<details{" open" if open_first and n == 0 else ""}><summary>{q}</summary><div class="faq__body">{a}</div></details>')
    return "".join(out) + "</div>"

# ---------- Service page template ----------
# Every treatment page is rendered by service_page() from _build/services/<slug>.json.
# The section order is fixed; sections without content are skipped, and backgrounds alternate automatically.
#   1 breadcrumbs + hero   2 introduction (+ "when" panel)   3 callout band   4 treatment explained
#   5 process steps        6 benefits / considerations       7 care          8 FAQs + cost
#   9 blog link           10 booking CTA                    11 related services
SERVICES = {p.stem: json.loads(p.read_text(encoding="utf-8")) for p in sorted((BUILD / "services").glob("*.json"))}

def svc_hero(s):
    h = s["hero"]; urgent = s.get("tone") == "urgent"
    if urgent:
        buttons = f'<div class="btn-row" data-sticky-anchor>{call_btn("btn--alert btn--lg", "Call " + P["phone_display"])}'
    else:
        buttons = (f'<div class="btn-row"><a class="btn btn--lime btn--lg" href="{esc(P["booking_url"])}" rel="noopener">{ICONS["calendar"]}<span>Book online</span></a>'
                   f'{call_btn("btn--ghost btn--lg", "Call " + P["phone_display"])}')
    if h.get("secondary"):
        buttons += f'<a class="textlink" href="{h["secondary"][1]}">{h["secondary"][0]}</a>'
    buttons += "</div>"
    return (f'<section class="phero svc-hero{" svc-hero--urgent er-hero" if urgent else ""}"><div class="wrap">'
            f'{crumbs({"crumbs": [["Services", "services.html"], [s["name"], s["file"]]]})}'
            f'<div class="svc-hero__grid"><div class="svc-hero__text"><p class="svc-hero__kicker">{s["group"]}</p><h1>{h["title"]}</h1>'
            f'<p class="lead">{h["lead"]}</p>{buttons}'
            f'<p class="svc-hero__status"><span class="status-dot" data-hours-dot aria-hidden="true"></span><span data-hours-status="text">Mon to Thu 8 am to 5 pm, Fri 8 am to 4:30 pm</span></p></div>'
            f'<div class="svc-hero__media">{img(h["image"] + "|frame--corner")}</div></div></div></section>')

def svc_card(it, i):
    label = f'<span class="tag">{it["label"]}</span>' if it.get("label") else ""
    lst = "<ul>" + "".join(f"<li>{x}</li>" for x in it["list"]) + "</ul>" if it.get("list") else ""
    flag = f'<p class="info-card__flag">{it["flag"]}</p>' if it.get("flag") else ""
    call = f'<div class="mt-auto">{call_btn("btn--lime")}</div>' if it.get("call") else ""
    return (f'<li class="info-card{" info-card--ask" if it.get("call") else ""} reveal" style="--i:{i % 3}">{label}<h3>{it["title"]}</h3>'
            f'<p>{it["text"]}</p>{lst}{flag}{call}</li>')

def service_page(slug):
    s = SERVICES[slug]
    sections = []   # (id, inner html) pairs; wrapped with alternating backgrounds below
    intro = s["intro"]
    if intro.get("aside"):
        aside = (f'<div class="panel panel--white svc-aside reveal"><h3>{intro["aside_heading"]}</h3><ul class="ticks">'
                 + "".join(f"<li>{x}</li>" for x in intro["aside"]) + "</ul></div>")
        sections.append(("intro", f'<div class="wrap split split--7-5 split--top"><div class="prose"><h2 id="intro-h">{intro["heading"]}</h2>{intro["html"]}</div>{aside}</div>'))
    else:
        sections.append(("intro", f'<div class="wrap split split--5-7 split--top"><h2 id="intro-h">{intro["heading"]}</h2><div class="prose">{intro["html"]}</div></div>'))
    callout = ""
    if s.get("callout"):
        c = s["callout"]
        badge = f'<p class="callout__badge" aria-hidden="true">{c["badge"]}</p>' if c.get("badge") else ""
        btn = call_btn("btn--alert" if c["tone"] == "alert" else "btn--lime" if c["tone"] == "brand" else "btn--forest") if c.get("call") else ""
        callout = (f'<section class="callout callout--{c["tone"]}" aria-labelledby="callout-h"><div class="wrap callout__inner">{badge}'
                   f'<div><h2 id="callout-h" class="callout__title">{c["title"]}</h2>{c["html"]}</div>{btn}</div></section>')
    if s.get("explain"):
        e = s["explain"]
        body = ""
        if e.get("items"):
            n = len(e["items"]); cols = 4 if n == 4 else 2 if n == 2 else 3
            body += f'<ul class="info-cards info-cards--{cols}">' + "".join(svc_card(it, i) for i, it in enumerate(e["items"])) + "</ul>"
        if e.get("ticks"):
            body += '<ul class="ticks ticks--cols ticks--cards">' + "".join(f"<li>{x}</li>" for x in e["ticks"]) + "</ul>"
        media = "".join(img(m + "|frame--corner-br") for m in e.get("images", []))
        intro_p = f'<p class="section-head__intro">{e["intro"]}</p>' if e.get("intro") else ""
        if media and not body:
            sections.append(("explain", f'<div class="wrap split split--6-6"><div><h2 id="explain-h">{e["heading"]}</h2>{intro_p}</div>{media}</div>'))
        elif media:
            sections.append(("explain", f'<div class="wrap"><div class="split split--7-5"><div class="section-head"><h2 id="explain-h">{e["heading"]}</h2>{intro_p}</div>{media}</div><div class="mt-7">{body}</div></div>'))
        else:
            sections.append(("explain", f'<div class="wrap"><div class="section-head"><h2 id="explain-h">{e["heading"]}</h2>{intro_p}</div>{body}</div>'))
    if s.get("process"):
        pr = s["process"]
        steps = "".join(f'<li class="reveal" style="--i:{i % 4}"><h3>{st[0]}</h3><p>{st[1]}</p>' + (f'<span class="process__note">{st[2]}</span>' if len(st) > 2 else "") + "</li>"
                        for i, st in enumerate(pr["steps"]))
        intro_p = f'<p class="section-head__intro">{pr["intro"]}</p>' if pr.get("intro") else ""
        sections.append(("process", f'<div class="wrap"><div class="section-head"><h2 id="process-h">{pr["heading"]}</h2>{intro_p}</div><ol class="process">{steps}</ol></div>'))
    if s.get("weigh"):
        w = s["weigh"]
        cols = "".join(f'<div class="weigh__col reveal" style="--i:{n}"><h3>{ICONS[ic]}{h}</h3><ul>' + "".join(f"<li>{x}</li>" for x in items) + "</ul></div>"
                       for n, (ic, h, items) in enumerate([("heart", "Possible benefits", w.get("benefits", [])),
                                                            ("question", "Things to consider", w.get("consider", [])),
                                                            ("clipboard", "Alternatives to ask about", w.get("alternatives", []))]) if items)
        sections.append(("weigh", f'<div class="wrap"><div class="section-head"><h2 id="weigh-h">Benefits and things to consider</h2>'
                                  f'<p class="section-head__intro">Your dentist will explain the benefits, risks and alternatives that apply to you.</p></div><div class="weigh">{cols}</div></div>'))
    if s.get("care"):
        c = s["care"]
        items = "".join(f'<li class="reveal" style="--i:{n % 3}"><strong>{h}</strong><span>{t}</span></li>' for n, (h, t) in enumerate(c["items"]))
        note = '' if s.get("tone") == "urgent" else '<p class="muted">Always follow the instructions your dentist gives you for your own treatment.</p>'
        sections.append(("care", f'<div class="wrap split split--5-7 split--top"><div><h2 id="care-h">{c["heading"]}</h2>{note}</div><ul class="care-list">{items}</ul></div>'))
    faqs = faq_html(s["faqs"]) if s.get("faqs") else ""
    cost = ""
    if s.get("cost"):
        cost = (f'<div class="cost-card reveal">{ICONS["wallet"]}<h3>Cost and payment</h3><p>{s["cost"]} Fees are always explained before treatment begins.</p>'
                '<p class="mb-0">With private health insurance extras cover, your fund may contribute. <a href="faq.html#costs">Costs and payment</a></p></div>')
    if faqs or cost:
        left = f'<div><h2 id="faq-h">Questions about {s["short"]}</h2>{faqs}</div>' if faqs else f'<div><h2 id="faq-h">Cost and payment</h2></div>'
        sections.append(("faq", f'<div class="wrap split split--7-5 split--top">{left}{cost}</div>' if faqs else f'<div class="wrap split split--5-7 split--top">{left}{cost}</div>'))
    out = [svc_hero(s)]
    bg = ["", " sec--mist"]
    for n, (sid, inner) in enumerate(sections):
        if n == 1 and callout: out.append(callout)
        out.append(f'<section class="sec{bg[n % 2]}" id="{sid}" aria-labelledby="{sid}-h">{inner}</section>')
    if callout and len(sections) < 2: out.append(callout)
    if s.get("blog"):
        b = s["blog"]
        out.append(f'<section class="sec sec--tight" aria-label="From our blog"><div class="wrap"><a class="blog-teaser" href="{b[0]}"><span class="kicker">From our blog</span>'
                   f'<strong>{b[1]}</strong><span>{b[2]}</span>{ICON["arrow"]}</a></div></section>')
    out.append(cta(s))
    out.append(related(",".join(s["related"])))
    if s.get("sticky_call"):
        out.append(f'<div class="er-sticky">{call_btn("btn--alert btn--lg")}</div>')
    meta = {"title": s["title"], "description": s["description"], "nav": "emergency" if s.get("tone") == "urgent" else "services",
            "body_class": "page-service" + (" page-emergency" if s.get("tone") == "urgent" else ""),
            "ld": {"@context": "https://schema.org", "@type": "MedicalWebPage", "name": s["title"].split(" | ")[0], "about": {"@type": "MedicalProcedure", "name": html.unescape(s["name"])},
                   "provider": {"@type": "Dentist", "name": P["name"], "telephone": P["phone_tel"]}}}
    return render(meta, expand("".join(out)), s["file"])

def directory():
    out = ['<div class="directory">']
    for g in P["service_groups"]:
        out.append(f'<div class="directory__col"><div class="directory__group"><h3>{g["label"]}</h3><ul>')
        for s in g["items"]:
            out.append(f'<li><a href="{s["file"]}">{s["name"]}{ICON["arrow"]}</a></li>')
        out.append("</ul></div>")
        if g["id"] == "replace":  # short column: urgent care sits beneath it
            out.append('<div class="directory__group directory__group--urgent"><h3>Urgent care</h3><ul>'
                       f'<li><a href="emergency-dentist.html">Emergency dental care{ICON["arrow"]}</a></li></ul></div>')
        out.append("</div>")
    out.append("</div>")
    return "".join(out)

def svc_list(group_id):
    g = next(g for g in P["service_groups"] if g["id"] == group_id)
    items = "".join(f'<li class="svc-item svc-item--thumb reveal"><a href="{s["file"]}">{thumb(s, "svc-item__thumb")}<strong>{s["name"]}</strong>'
                    f'<span>{s["desc"]}</span><span class="arrow">{ICON["arrow"]}</span></a></li>' for s in g["items"])
    return f'<ul class="svc-list">{items}</ul>'

def related(files):
    files = [f.strip() for f in files.split(",")]
    items = []
    for f in files:
        s = next((s for s in ALL_SERVICES if s["file"] == f), None)
        if s:
            items.append(f'<li class="related-card reveal" style="--i:{len(items)}"><a href="{s["file"]}"><span class="related-card__media">{thumb(s)}</span>'
                         f'<span class="related-card__body"><strong>{s["name"]}</strong><span>{s["blurb"]}</span></span></a></li>')
    return ('<section class="sec sec--tight sec--related" aria-labelledby="related-h"><div class="wrap">'
            '<div class="section-head section-head--row"><div><h2 id="related-h">Related treatments</h2>'
            '<p class="section-head__intro">Not sure which one you need? Your dentist will talk you through the options at your appointment.</p></div>'
            '<a class="btn btn--ghost" href="services.html">All services</a></div>'
            f'<ul class="related-cards">{"".join(items)}</ul></div></section>')

def cta(service=None):
    """Booking band. On a service page it names the treatment and links an enquiry form pre-filled with that treatment."""
    if service:
        urgent = service.get("tone") == "urgent"
        title = "In pain? Call us now" if urgent else "Ready to book?"
        topic = re.sub(r"&[a-z]+;", "'", service["name"])
        ask = f'<a class="textlink textlink--light" href="contact.html?topic={quote_plus(topic)}#message-form">Ask a question about {service["short"]}</a>'
        text = ("We reserve appointments every day for dental emergencies. Call as early as you can." if urgent else
                "Book online or call our Edmonton reception team. Not sure what you need? Book a consultation and your dentist will talk you through the options.")
        buttons = (f'<div class="btn-row">{call_btn("btn--lime btn--lg")}</div>' if urgent else book_buttons("btn--lime", "btn--ghost-light"))
        return (f'<section class="sec sec--tight" aria-labelledby="cta-h"><div class="wrap"><div class="cta-band">'
                f'<div><h2 id="cta-h">{title}</h2><p>{text}</p>{"" if urgent else ask}</div>{buttons}</div></div></section>')
    return (f'<section class="sec sec--tight" aria-labelledby="cta-h"><div class="wrap"><div class="cta-band">'
            '<div><h2 id="cta-h">Ready to book?</h2>'
            f'<p>Book online or call our Edmonton reception team and we&rsquo;ll find a time that suits you. '
            f'You can also email <a href="mailto:{P["email"]}">{P["email"]}</a>.</p></div>'
            f'{book_buttons("btn--lime", "btn--ghost-light")}</div></div></section>')

def urgent():
    return ('<section class="sec sec--tight" aria-labelledby="urgent-h"><div class="wrap"><div class="urgent-strip">'
            '<div><h2 id="urgent-h">In pain or broken a tooth?</h2>'
            '<p>We set aside appointments every day for dental emergencies. Call us as early as you can and we&rsquo;ll do our best to see you.</p></div>'
            f'<div class="btn-row">{call_btn("btn--alert")}<a class="btn btn--ghost" href="emergency-dentist.html">Emergency help</a></div>'
            '</div></div></section>')

def portrait(m, cls="", eager=False):
    """A team member's portrait. Ashlee has no published photo, so she gets a branded placeholder rather than someone else's picture."""
    loading = 'fetchpriority="high"' if eager else 'loading="lazy"'
    if m.get("photo"):
        return (f'<figure class="frame portrait {cls}" style="--ratio:4 / 5" data-image-id="team-{m["slug"]}">'
                f'<img src="{m["photo"]}" alt="{esc(m["name"])}, {esc(m["role"])}" width="900" height="900" {loading} decoding="async"></figure>')
    return (f'<figure class="frame portrait portrait--placeholder {cls}" style="--ratio:4 / 5" data-image-id="team-{m["slug"]}">'
            f'<img src="{PORTRAIT_PLACEHOLDER}" alt="" width="800" height="1000" {loading} decoding="async"></figure>')

PORTRAIT_PLACEHOLDER = "assets/images/team/portrait-placeholder.svg"

def team_cards():
    """Homepage: the four dentists, each linking to their profile on team.html."""
    out = ['<ul class="team-cards">']
    for i, m in enumerate(x for x in P["team"] if x["dentist"]):
        out.append(f'<li class="team-card reveal" style="--i:{i}"><a href="team.html#{m["slug"]}">{portrait(m, "frame--corner")}'
                   f'<h3>{m["name"]}</h3><p>{m["role"]}</p><p class="member__quals">{m["quals"]}</p></a></li>')
    return "".join(out) + "</ul>"

def portrait_strip():
    """About page: a row of real portraits linking to the team page."""
    lis = "".join(f'<li class="reveal" style="--i:{i}"><a href="team.html#{m["slug"]}"><img src="{m["photo"]}" alt="" width="900" height="900" decoding="async">'
                  f'<span>{m["name"]}</span></a></li>' for i, m in enumerate(P["team"]) if m.get("photo"))
    return f'<ul class="portrait-strip" aria-label="Our team">{lis}</ul>'

def team_profiles():
    """Team page: full profiles for dentists, compact cards for the support team. Only published details are used."""
    out = []
    dentists = [m for m in P["team"] if m["dentist"]]
    for i, m in enumerate(dentists):
        has_bio = bool(m["bio"])
        known = f' <span class="profile__aka">(known as {m["known_as"]})</span>' if m.get("known_as") else ""
        tags = "".join(f"<li>{t}</li>" for t in m.get("interests", []))
        tags = f'<ul class="tags" aria-label="Areas of interest">{tags}</ul>' if tags else ""
        quote = f'<blockquote class="profile__quote"><p>&ldquo;{m["quote"]}&rdquo;</p><footer>{m["name"]}</footer></blockquote>' if m.get("quote") else ""
        bio = "".join(f"<p>{p}</p>" for p in m["bio"]) if has_bio else (
            f'<p>{m["name"].split()[1]} is one of our dentists at South Cairns Dental and holds a {m["quals_long"]}. '
            'Like every dentist here, they aim to see the same patients from visit to visit, so you have a familiar face each time.</p>'
            f'<p>If you&rsquo;d like to see Dr {m["name"].split()[1]}, mention it when you call to book.</p>')
        corner = "frame--corner"   # same portrait shape for every dentist
        out.append(f'<article class="profile{" profile--full" if has_bio else ""} reveal" id="{m["slug"]}" aria-labelledby="{m["slug"]}-h">'
                   f'<div class="profile__media">{portrait(m, corner, eager=(i == 0))}</div>'
                   f'<div class="profile__body"><p class="profile__role">{m["role"]}</p><h2 id="{m["slug"]}-h">{m["name"]}{known}</h2>'
                   f'<p class="profile__quals">{m["quals_long"]} ({m["quals"]})</p>{tags}<div class="prose">{bio}</div>{quote}</div></article>')
    return "".join(out)

def support_team():
    lis = "".join(f'<li class="member" id="{m["slug"]}">{portrait(m, "frame--corner")}<h3>{m["name"]}</h3><p class="member__role">{m["role"]}</p></li>'
                  for m in P["team"] if not m["dentist"])
    return f'<ul class="team team--support">{lis}</ul>'

def offer():
    """The new patient offer exactly as published on the official Special Offers page (SP-2.jpg), as real text."""
    items = ["Comprehensive dental check-up", "All necessary X-rays (including full-face X-rays)", "Scale, clean and polish", "Fluoride treatment", "Treatment plan"]
    lis = "".join(f"<li>{x}</li>" for x in items)
    return (f'<div class="offer reveal"><div class="offer__body"><p class="offer__kicker">New patient special offer</p>'
            f'<h2 class="offer__title" id="offer-h">Your first check-up, sorted</h2><ul class="ticks offer__list">{lis}</ul>'
            '<div class="offer__prices"><div><strong>Gap free</strong><span>with any health fund</span></div>'
            '<div><strong>$199</strong><span>for all of the above if you&rsquo;re not in a health fund</span></div></div>'
            '<p class="small offer__terms">Terms and conditions apply. Please mention the offer and confirm it&rsquo;s still current when you book.</p>'
            f'{book_buttons("btn--forest", "btn--ghost")}</div>'
            f'<div class="offer__media">{img("offer-flyer|frame--corner-tr")}</div></div>')

def map_block():
    """One map component: a keyless Google Maps embed, with the address and directions always visible beneath it,
    so nothing depends on the embed loading (the fallback tile underneath shows if the iframe is blank)."""
    return (f'<figure class="mapcard"><div class="map"><div class="map__fallback" aria-hidden="true">{ICONS["map"]}<p><strong>{ADDRESS_ONE_LINE}</strong></p></div>'
            f'<iframe title="Map showing South Cairns Dental at {esc(ADDRESS_ONE_LINE)}" src="{MAPS_EMBED}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>'
            f'<figcaption class="mapcard__bar"><span class="mapcard__addr">{ICON["pin"]}<span><strong>South Cairns Dental</strong><br>{P["address_line1"]}, {P["suburb"]} {P["state"]} {P["postcode"]}</span></span>'
            f'<span class="btn-row"><a class="btn btn--forest btn--sm" href="{MAPS_DIR}" rel="noopener">Get directions</a>'
            f'<a class="btn btn--ghost btn--sm" href="{MAPS_LINK}" rel="noopener">Open in Google Maps</a></span></figcaption></figure>')

TOKENS = {
  "phone": P["phone_display"], "tel": P["phone_tel"], "email": P["email"],
  "address": ADDRESS_ONE_LINE, "address_line1": P["address_line1"],
  "address_locality": f'{P["suburb"]} {P["state"]} {P["postcode"]}',
  "maps_link": MAPS_LINK, "maps_dir": MAPS_DIR, "official_url": P["official_url"],
  "hours_table": hours_table, "hours_compact": hours_compact,
  "directory": directory, "cta": cta, "urgent": urgent, "map": map_block,
  "team_cards": team_cards, "team_profiles": team_profiles, "support_team": support_team, "portrait_strip": portrait_strip,
  "offer": offer, "blog_list": lambda: blog_list(1), "blog_latest": lambda: blog_latest(),
  "booking_url": P["booking_url"],
  "book_online": lambda: f'<a class="btn btn--lime" href="{esc(P["booking_url"])}" rel="noopener">{ICONS["calendar"]}<span>Book online</span></a>',
  "book_buttons": book_buttons, "service_cards": service_cards,
  "call_btn": lambda: call_btn(),
  "call_btn_forest": lambda: call_btn("btn--forest"),
  "arrow": lambda: ICON["arrow"], "icon_phone": lambda: ICON["phone"],
  "icon_mail": lambda: ICON["mail"], "icon_pin": lambda: ICON["pin"],
}

def expand(text):
    def rep(m):
        key = m.group(1).strip()
        if ":" in key:
            name, arg = key.split(":", 1)
            fn = {"img": img, "svc_list": svc_list, "related": related, "illus": illus,
                  "icon": lambda n: ICONS[n]}.get(name)
            if not fn: sys.exit(f"Unknown token {key}")
            return fn(arg)
        v = TOKENS.get(key)
        if v is None: sys.exit(f"Unknown token {{{{{key}}}}}")
        return v() if callable(v) else v
    return re.sub(r"\{\{\s*([^}]+?)\s*\}\}", rep, text)

# ---------- Chrome ----------
def mega(current):
    cols = []
    for g in P["service_groups"]:
        lis = "".join('<li><a href="%s"%s>%s</a></li>' % (s["file"], ' aria-current="page"' if s["file"] == current else "", s["name"]) for s in g["items"])
        cols.append(f'<div class="mega__col"><h3>{g["label"]}</h3><ul>{lis}</ul></div>')
    return (f'<div class="mega" id="services-panel"><div class="wrap mega__grid">'
            f'<div class="mega__intro"><h2>Our services</h2><p>General, restorative and cosmetic dentistry for children and adults, all under one roof in Edmonton.</p>'
            f'<a class="textlink" href="services.html"{AC if current == "services.html" else ""}>Services overview</a>'
            f'<div class="mega__urgent"><a href="emergency-dentist.html"{AC if current == "emergency-dentist.html" else ""}>Emergency dental care</a></div></div>'
            f'{"".join(cols)}<ul class="mega__mobile-extra"><li><a href="services.html">Services overview</a></li><li><a class="mega__urgent-link" href="emergency-dentist.html">Emergency dental care</a></li></ul></div></div>')

# Dropdown groups. "nav" values in page meta map to the top-level item that should show as current.
NAV_GROUP = {"about": "about", "team": "about", "new": "about", "services": "services", "emergency": "services",
             "faq": "patients", "offers": "patients", "privacy": "patients", "blog": "blog", "contact": "contact", "home": "home"}
MENUS = {
  "about": ("About us", [("about.html", "About South Cairns Dental"), ("team.html", "Meet our dentists"), ("new-patients.html", "New patients")]),
  "patients": ("Patient information", [("faq.html", "Frequently asked questions"), ("special-offers.html", "Special offers"),
                                       ("emergency-dentist.html", "Emergency dental care")]),
}

def dropdown(key, current, nav):
    label, links = MENUS[key]
    cur = " is-current" if NAV_GROUP.get(nav) == key else ""
    lis = "".join(f'<li><a href="{h}"{AC if h == current else ""}>{t}</a></li>' for h, t in links)
    return (f'<li class="has-drop"><button class="nav__toggle{cur}" type="button" aria-expanded="false" aria-controls="menu-{key}" data-panel-toggle>{label} {ICON["chev"]}</button>'
            f'<div class="drop" id="menu-{key}"><ul>{lis}</ul></div></li>')

def header(meta, current):
    nav = meta.get("nav", "")
    group = NAV_GROUP.get(nav, "")
    def item(key, href, label, extra=""):
        cur = ' aria-current="page"' if current == href else ""
        return f'<li><a class="nav__link{extra}" href="{href}"{cur}>{label}</a></li>'
    svc_cur = " is-current" if group == "services" else ""
    book = P.get("booking_url")
    book_btn = f'<a class="btn btn--lime btn--sm" href="{esc(book)}" rel="noopener">Book online</a>' if book else ""
    book_mobile = f'<a class="btn btn--lime btn--lg" href="{esc(book)}" rel="noopener">{ICONS["calendar"]}<span>Book online</span></a>' if book else ""
    blog_cur = " is-current" if group == "blog" and current != "blog.html" else ""
    return f'''<a class="skip-link" href="#main">Skip to content</a>
<aside class="utility" aria-label="Opening hours and contact details"><div class="wrap">
  <div class="utility__group"><span class="utility__item"><span class="status-dot" data-hours-dot aria-hidden="true"></span><span data-hours-status="text">Mon to Thu 8 am to 5 pm, Fri 8 am to 4:30 pm</span></span>
  <a class="utility__hide-sm utility__item" href="{MAPS_LINK}" rel="noopener">{ICONS["map"]}{P["address_line1"]}, {P["suburb"]}</a></div>
  <div class="utility__group"><a class="utility__urgent" href="emergency-dentist.html">{ICONS["alert"]}<span>Dental emergency?</span></a><a class="utility__hide-sm utility__email" href="mailto:{P["email"]}">{P["email"]}</a><a href="tel:{P["phone_tel"]}">{P["phone_display"]}</a></div>
</div></aside>
<header class="site-header">
  <div class="wrap header-bar">
    <a class="brand" href="index.html"><img src="assets/images/brand/scd-logo.png" width="640" height="116" alt="South Cairns Dental home"></a>
    <button class="menu-button" type="button" aria-expanded="false" aria-controls="site-nav"><span class="menu-button__bars" aria-hidden="true"><span></span><span></span><span></span></span><span class="menu-button__text">Menu</span></button>
    <nav class="nav" id="site-nav" aria-label="Main">
      <ul class="nav__list">
        {item("home", "index.html", "Home", " nav__link--home")}
        {dropdown("about", current, nav)}
        <li class="has-panel"><button class="nav__toggle{svc_cur}" type="button" aria-expanded="false" aria-controls="services-panel" data-panel-toggle>Services {ICON["chev"]}</button>{mega(current)}</li>
        {dropdown("patients", current, nav)}
        {item("blog", "blog.html", "Blog", blog_cur)}
        {item("contact", "contact.html", "Contact us")}
      </ul>
      <div class="nav__mobile-cta">{book_mobile}{call_btn("btn--forest btn--lg")}<a class="btn btn--alert-ghost btn--lg" href="emergency-dentist.html">Dental emergency</a></div>
    </nav>
    <div class="header-cta">{book_btn}<a class="btn btn--forest btn--sm" href="tel:{P["phone_tel"]}" aria-label="Call {P["phone_display"]}">{ICON["phone"]}<span class="btn__long" aria-hidden="true">{P["phone_display"]}</span></a></div>
  </div>
</header>'''

def footer():
    svc = "".join(f'<li><a href="{s["file"]}">{s["name"]}</a></li>' for s in ALL_SERVICES)
    return f'''<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="assets/images/brand/scd-logo-reversed.png" width="640" height="116" alt="South Cairns Dental">
        <p>A modern, family-owned dental practice in Edmonton, caring for families from Bentley Park, Mount Sheridan, Gordonvale and across the southern suburbs of Cairns.</p>
        <div class="btn-row mt-5">{call_btn("btn--lime")}{f'<a class="btn btn--ghost-light" href="{esc(P["booking_url"])}" rel="noopener">Book online</a>' if P.get("booking_url") else ""}</div>
      </div>
      <div class="footer-cols">
        <div><h2>Visit</h2><address>{P["address_line1"]}<br>{P["suburb"]} {P["state"]} {P["postcode"]}</address><p class="mt-5 mb-0"><a href="{MAPS_DIR}" rel="noopener">Get directions</a></p>
          <h2 class="mt-6">Hours</h2><p class="mb-0">{hours_compact()}</p></div>
        <div class="footer-services"><h2>Services</h2><ul><li><a href="services.html">Services overview</a></li>{svc}<li><a href="emergency-dentist.html">Emergency dental care</a></li></ul></div>
        <div><h2>About us</h2><ul><li><a href="about.html">About South Cairns Dental</a></li><li><a href="team.html">Meet our dentists</a></li><li><a href="new-patients.html">New patients</a></li></ul>
          <h2 class="mt-6">Patients</h2><ul><li><a href="faq.html">Frequently asked questions</a></li><li><a href="special-offers.html">Special offers</a></li><li><a href="blog.html">Blog</a></li><li><a href="contact.html">Contact us</a></li><li><a href="privacy.html">Privacy policy</a></li></ul></div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="mb-0">&copy; <span data-year>2026</span> South Cairns Dental. {ADDRESS_ONE_LINE}.</p>
      <p class="mb-0"><a href="privacy.html">Privacy policy</a> &nbsp; The information on this website doesn&rsquo;t replace advice from your dentist.</p>
    </div>
  </div>
</footer>'''

def schema():
    days = {"Monday":"Mo","Tuesday":"Tu","Wednesday":"We","Thursday":"Th","Friday":"Fr","Saturday":"Sa","Sunday":"Su"}
    spec = [{"@type": "OpeningHoursSpecification", "dayOfWeek": d["day"], "opens": d["open"], "closes": d["close"]} for d in P["hours"] if d["open"]]
    data = {
        "@context": "https://schema.org", "@type": "Dentist", "name": P["name"],
        "url": (SITE + "/") if SITE else P["official_url"],
        "telephone": P["phone_tel"], "email": P["email"],
        "image": (SITE + "/assets/images/brand/og-image.jpg") if SITE else None,
        "logo": (SITE + "/assets/images/brand/scd-logo.png") if SITE else None,
        "address": {"@type": "PostalAddress", "streetAddress": P["address_line1"], "addressLocality": P["suburb"],
                    "addressRegion": P["state"], "postalCode": P["postcode"], "addressCountry": "AU"},
        "geo": {"@type": "GeoCoordinates", "latitude": P["geo"]["lat"], "longitude": P["geo"]["lng"]},
        "hasMap": MAPS_LINK,
        "sameAs": [P["facebook_url"]] if P.get("facebook_url") else None,
        "areaServed": ["Edmonton", "Bentley Park", "Mount Sheridan", "Gordonvale", "Cairns"],
        "openingHoursSpecification": spec,
    }
    data = {k: v for k, v in data.items() if v is not None}
    return '<script type="application/ld+json">' + json.dumps(data, ensure_ascii=False) + "</script>"

def crumbs(meta):
    c = meta.get("crumbs")
    if not c: return ""
    lis = ['<li><a href="index.html">Home</a></li>']
    for label, href in c[:-1]:
        lis.append(f'<li><a href="{href}">{label}</a></li>')
    lis.append(f'<li aria-current="page">{c[-1][0]}</li>')
    return f'<nav class="crumbs" aria-label="Breadcrumb"><ol>{"".join(lis)}</ol></nav>'

# ---------- Blog ----------
# Posts live in _build/blog.json. Only "published" posts are built; "draft" posts appear only with --drafts,
# clearly labelled and noindexed, so sample content can never be mistaken for a published article.
PER_PAGE = 9
MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
POSTS = sorted([p for p in BLOG["posts"] if p["status"] == "published" or (args.drafts and p["status"] == "draft")],
               key=lambda p: p["date"], reverse=True)
def post_url(p): return f'blog-{p["slug"]}.html'
def fmt_date(iso):
    y, m, d = map(int, iso.split("-")); return f"{d} {MONTHS[m - 1]} {y}"
def draft_flag(p): return '<span class="tag tag--draft">Draft: not published</span>' if p["status"] == "draft" else ""

def post_card(p, i=0, featured=False):
    im = IMAGES[p["image"]]
    return (f'<li class="post-card{" post-card--featured" if featured else ""} reveal" style="--i:{i % 3}"><a href="{post_url(p)}">'
            f'<span class="post-card__media"><img src="{im["src"]}" alt="" width="{im["w"]}" height="{im["h"]}" loading="lazy" decoding="async"></span>'
            f'<span class="post-card__body"><span class="post-card__meta"><span class="tag">{p["category"]}</span>{draft_flag(p)}'
            f'<time datetime="{p["date"]}">{fmt_date(p["date"])}</time></span>'
            f'<strong class="post-card__title">{p["title"]}</strong><span class="post-card__excerpt">{p["excerpt"]}</span>'
            f'<span class="post-card__more">Read the article {ICON["arrow"]}</span></span></a></li>')

def blog_pages(): return max(1, -(-len(POSTS) // PER_PAGE))
def blog_page_url(n): return "blog.html" if n == 1 else f"blog-page-{n}.html"

def blog_list(n):
    if not POSTS:
        return ('<div class="empty-state"><h2 class="h3-size">No articles yet</h2><p>We haven&rsquo;t published any articles yet. '
                'In the meantime, our treatment pages explain what each treatment involves.</p><a class="btn" href="services.html">Browse our services</a></div>')
    chunk = POSTS[(n - 1) * PER_PAGE: n * PER_PAGE]
    cards = "".join(post_card(p, i, featured=(n == 1 and i == 0)) for i, p in enumerate(chunk))
    pager = ""
    if blog_pages() > 1:
        links = "".join(f'<li><a href="{blog_page_url(k)}"{AC if k == n else ""}>{k}</a></li>' for k in range(1, blog_pages() + 1))
        prev = f'<a class="pager__step" href="{blog_page_url(n - 1)}">Newer articles</a>' if n > 1 else ""
        nxt = f'<a class="pager__step" href="{blog_page_url(n + 1)}">Older articles</a>' if n < blog_pages() else ""
        pager = f'<nav class="pager" aria-label="Blog pages">{prev}<ol>{links}</ol>{nxt}</nav>'
    return f'<ul class="post-grid">{cards}</ul>{pager}'

def blog_latest():
    return f'<ul class="post-grid post-grid--compact">{"".join(post_card(p, i) for i, p in enumerate(POSTS[:3]))}</ul>' if POSTS else ""

def article(p):
    """Shared article layout for every blog post."""
    related_svc = [s for s in ALL_SERVICES if s["file"] in p.get("related", [])]
    rel = "".join(f'<li><a href="{s["file"]}">{thumb(s, "aside-list__thumb")}<span>{s["name"]}</span></a></li>' for s in related_svc)
    banner = ('<div class="note note--alert mb-6"><p class="mb-0"><strong>Draft for local review.</strong> This article has not been published '
              'and is only included in builds made with --drafts.</p></div>') if p["status"] == "draft" else ""
    sources = ""
    if p.get("sources"):
        sources = '<div class="article__sources"><h2 class="h4-size">Sources</h2><ul>' + "".join(
            f'<li><a href="{u}" rel="noopener">{t}</a></li>' for t, u in p["sources"]) + "</ul></div>"
    others = [q for q in POSTS if q is not p][:3]
    more = (f'<section class="sec sec--mist sec--tight" aria-labelledby="more-h"><div class="wrap"><h2 id="more-h">More articles</h2>'
            f'<ul class="post-grid post-grid--compact">{"".join(post_card(q, i) for i, q in enumerate(others))}</ul></div></section>') if others else ""
    body = (f'<section class="phero phero--article"><div class="wrap wrap--article">{crumbs({"crumbs": [["Blog", "blog.html"], [p["title"], post_url(p)]]})}'
            f'<p class="post-meta"><span class="tag tag--light">{p["category"]}</span>{draft_flag(p)}<time datetime="{p["date"]}">{fmt_date(p["date"])}</time><span>{p.get("author", "South Cairns Dental")}</span></p>'
            f'<h1>{p["title"]}</h1><p class="lead">{p["excerpt"]}</p></div></section>'
            f'<section class="sec sec--article"><div class="wrap article-layout"><article class="article">{banner}{img(p["image"] + "|frame--corner")}'
            f'<div class="prose article__body">{p["body"]}</div>{sources}'
            f'<p class="article__note small muted">{p.get("note", "Your dentist can tell you which options suit your situation.")}</p></article>'
            f'<div class="article-aside"><div class="book-panel"><h2 class="h3-size">Talk to us about it</h2>'
            f'<p>Have a question about this topic? Book a consultation and your dentist will talk you through your options.</p>{book_buttons("btn--lime", "btn--ghost-light")}</div>'
            + (f'<div class="aside-list"><h2 class="h4-size">Related treatments</h2><ul>{rel}</ul></div>' if rel else "") +
            f'<a class="textlink" href="blog.html">All articles</a></div></div></section>{more}{{{{cta}}}}')
    meta = {"title": f'{p["title"]} | South Cairns Dental blog', "description": p["description"], "nav": "blog",
            "og_type": "article", "noindex": p["status"] == "draft",
            "ld": {"@context": "https://schema.org", "@type": "BlogPosting", "headline": p["title"], "datePublished": p["date"],
                   "author": {"@type": "Organization", "name": "South Cairns Dental"},
                   "publisher": {"@type": "Organization", "name": "South Cairns Dental"}}}
    return render(meta, expand(body), post_url(p))

def page(src, out_name=None, extra=None):
    raw = src.read_text(encoding="utf-8")
    m = re.match(r"\s*<!--meta\s*(\{.*?\})\s*-->", raw, re.S)
    if not m: sys.exit(f"{src.name}: missing <!--meta {{...}} --> block")
    meta = json.loads(m.group(1)); body = raw[m.end():]
    meta.update(extra or {})
    out_name = out_name or meta.get("out", src.name)
    body = body.replace("[[crumbs]]", crumbs(meta))
    return render(meta, expand(body), out_name)

def blog_listing(src):
    """blog.html plus blog-page-N.html when there are more than PER_PAGE posts."""
    built = []
    for n in range(1, blog_pages() + 1):
        extra = {} if n == 1 else {"title": f"Blog, page {n} | South Cairns Dental", "description": f"Dental health articles from South Cairns Dental, page {n}."}
        raw = src.read_text(encoding="utf-8").replace("{{blog_list}}", blog_list(n))
        tmp = BUILD / f".blog-{n}.tmp"; tmp.write_text(raw, encoding="utf-8")
        try: built.append(page(tmp, blog_page_url(n), extra))
        finally: tmp.unlink()
    return built

def render(meta, body, out_name):
    title = meta["title"]; desc = meta["description"]
    canonical = f'<link rel="canonical" href="{SITE}/{"" if out_name == "index.html" else out_name}">' if SITE and out_name != "404.html" else ""
    og_url = f'<meta property="og:url" content="{SITE}/{"" if out_name == "index.html" else out_name}">' if SITE else ""
    og_img = f'{SITE}/assets/images/brand/og-image.jpg' if SITE else "assets/images/brand/og-image.jpg"
    # Staging (no --site-url, or --noindex): every page is noindex. Production: only the 404 and drafts are noindex.
    robots = '<meta name="robots" content="noindex, nofollow">' if (not INDEXABLE or out_name == "404.html" or meta.get("noindex")) else ""
    base = '<base href="/">' if out_name == "404.html" else ""
    ld = schema() if meta.get("schema") else ""
    if meta.get("ld"):
        ld += '<script type="application/ld+json">' + json.dumps(meta["ld"], ensure_ascii=False) + "</script>"
    body_cls = meta.get("body_class", "")
    doc = f'''<!doctype html>
<html lang="en-AU">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{base}<title>{esc(title)}</title>
<meta name="description" content="{esc(desc)}">
{robots}{canonical}
<meta name="theme-color" content="#1E4A3F">
<meta property="og:type" content="{meta.get("og_type", "website")}">
<meta property="og:site_name" content="South Cairns Dental">
<meta property="og:locale" content="en_AU">
<meta property="og:title" content="{esc(meta.get("og_title", title))}">
<meta property="og:description" content="{esc(desc)}">
<meta property="og:image" content="{og_img}">
{og_url}
<link rel="icon" type="image/png" sizes="32x32" href="assets/images/brand/favicon-32.png">
<link rel="apple-touch-icon" href="assets/images/brand/apple-touch-icon.png">
<link rel="preload" href="assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/figtree-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/css/style.css">
{ld}
</head>
<body class="{body_cls}">
{header(meta, out_name)}
<main id="main" tabindex="-1">
{body.strip()}
</main>
{footer()}
<script src="assets/js/practice-data.js"></script>
<script src="assets/js/main.js" defer></script>
</body>
</html>
'''
    doc = re.sub(r"\n{3,}", "\n\n", doc)
    (ROOT / out_name).write_text(doc, encoding="utf-8")
    return out_name, meta

def write_manifest(built_names):
    """IMAGE-MANIFEST.md: every image file, what it is, where it came from and which pages use it (read from the built HTML)."""
    used = {}
    for n in built_names:
        for src in set(re.findall(r'src="(assets/images/[^"]+)"', (ROOT / n).read_text(encoding="utf-8"))):
            used.setdefault(src, []).append(n)
    def where(src): return ", ".join(sorted(used.get(src, []))) or "not used"
    def row(*cells): return "| " + " | ".join(str(c).replace("|", "/").replace("\n", " ") for c in cells) + " |"
    L = ["# Image manifest", "",
         "Generated by `_build/build.py` from `_build/images.json` and `_build/practice.json`. Do not edit by hand. Rebuild after changing images.", "",
         f"Images were downloaded from https://www.southcairnsdental.com.au on 26 October 2026, optimised (progressive JPEG, quality 82, metadata removed) and saved locally. "
         "No page links to images on the official site.", "",
         "**Status key:** *authentic* is the practice's own photograph or artwork. *official-stock* is a stock photo or illustration the practice already publishes on its website; "
         "it is not a photo of the practice or its patients, and the stock licence must be confirmed for the new site. *licensed-stock* is a photo licensed for this website (Unsplash License), used where the practice has no suitable image; it is not a photo of the practice. *illustration* is original artwork.", "",
         "## Team portraits (authentic)", "",
         "Each portrait sits directly above that person's name and qualifications on the official About Us page. The man in Dr Alex Bornsztejn's portrait also appears in the "
         "official group photo named `maddyalexgood1.jpg`. Ask each person to confirm their photo before launch.", "",
         row("Person", "Local file", "Source", "Used on"), row("---", "---", "---", "---")]
    srcmap = {"alex-bornsztejn": "IMG_1615.jpg", "louis-fernandez": "IMG_07913.jpg", "hannah-dominique-gaza": "IMG_730628229.jpg",
              "casey-almond": "IMG_727628329.jpg", "carol": "IMG_699728229.jpg"}
    for m in P["team"]:
        if m.get("photo"):
            L.append(row(f'{m["name"]}, {m["role"]}', f'`{m["photo"]}`', f'https://www.southcairnsdental.com.au/thumbnaillarge/{srcmap[m["slug"]]}', where(m["photo"])))
        else:
            L.append(row(f'{m["name"]}, {m["role"]}', f"`{PORTRAIT_PLACEHOLDER}` (placeholder)", "No photo published on the official site; branded placeholder until one is supplied", where(PORTRAIT_PLACEHOLDER)))
    L += ["", "## Page images", "", row("Slot", "Status", "Subject", "Local file", "Source", "Used on", "Notes"), row(*["---"] * 7)]
    for iid, im in IMAGES.items():
        L.append(row(iid, im["status"], im["subject"], f'`{im["src"]}`', im.get("source_url") or "original artwork", where(im["src"]), im.get("notes", "")))
    L += ["", "## Service card and list thumbnails", "",
          "Service cards (homepage) and service lists (Services overview) show the same file as the treatment page where one exists, otherwise the treatment's original illustration. They are decorative (`alt=\"\"`) because the treatment name sits beside them.", "",
          row("Treatment", "Thumbnail"), row("---", "---")]
    for s in ALL_SERVICES:
        L.append(row(s["name"], f'`{s.get("photo") or "assets/images/illustrations/" + s["illus"] + ".svg"}`'))
    L.append(row("Emergency dental care", "`assets/images/treatments/toothache.jpg`"))
    L += ["", "## Brand assets (authentic, supplied by the practice)", "", row("Asset", "Local file", "Notes"), row("---", "---", "---"),
          row("Logo", "`assets/images/brand/scd-logo.png`", "Trimmed from the supplied South_Cairn_Dental__SCD_.png"),
          row("Reversed logo (footer)", "`assets/images/brand/scd-logo-reversed.png`", "Grey lettering made white for dark backgrounds. Confirm the practice is happy with it."),
          row("Favicon and touch icon", "`assets/images/brand/favicon-32.png`, `apple-touch-icon.png`", "Cropped from the logo mark"),
          row("Share image", "`assets/images/brand/og-image.jpg`", "Reversed logo on Canopy green"),
          row("Watermark mark", "`assets/images/brand/scd-mark-512.png`", "Faint decorative mark in dark hero sections (CSS)"),
          "", "## Original illustrations still in use", "",
          "Generated by `_build/make_illustrations.py`. They are decorative artwork, not photographs, and are used only where no suitable image exists or where the only candidate is already used nearby.", ""]
    for svg in sorted((ROOT / "assets/images/illustrations").glob("*.svg")):
        u = where(f"assets/images/illustrations/{svg.name}")
        if u != "not used": L.append(f"- `assets/images/illustrations/{svg.name}`: {u}")
    L += ["", "## Official-site images not used", "",
          "- `services-invisalign-banner-o278v.jpg` (Invisalign page): mostly the Invisalign logo and tagline, which are Align Technology branding.",
          "- `before_after.jpg` (Whitening page): 450px wide, carries Philips Zoom branding, and a stock before-and-after could be read as a promised result.",
          "- `_homeSubBanner.gif` (homepage CSS): a 1px-high decorative strip, not a photograph.", "",
          "## Remaining placeholders", ""]
    leftovers = sorted({s for s in used if "placeholders/" in s})
    L.append("None. No page uses placeholder artwork or a \"Photo to come\" label." if not leftovers else "Still in use: " + ", ".join(leftovers))
    L += ["", "## Photos worth commissioning", "",
          "The official site has no photos of the building, reception, waiting room or treatment rooms. New photography of these would strengthen the "
          "About, Contact and New Patients pages, and could replace the stock images on the treatment pages.", ""]
    (ROOT / "IMAGE-MANIFEST.md").write_text("\n".join(L), encoding="utf-8")

def main():
    built = []
    for src in sorted((BUILD / "pages").glob("*.html")):
        if src.name in {f"{k}.html" for k in SERVICES}: sys.exit(f"{src.name} is a service page: edit _build/services/{src.stem}.json instead")
        if src.name == "blog.html": built += blog_listing(src)
        else: built.append(page(src))
    for slug in SERVICES:
        built.append(service_page(slug))
    for p in POSTS:
        built.append(article(p))
    (ROOT / "assets/js/practice-data.js").write_text(
        "/* Generated by _build/build.py from practice.json. Do not edit by hand. */\n"
        "window.SCD = " + json.dumps({"hours": P["hours"], "phone": P["phone_tel"]}) + ";\n", encoding="utf-8")
    # Remove pages for posts that are no longer built (e.g. after a --drafts preview)
    names = {n for n, _ in built}
    for old in ROOT.glob("blog-*.html"):
        if old.name not in names: old.unlink()
    write_manifest([n for n, _ in built])
    # Sitemap / robots only when the deployment URL is known
    if not INDEXABLE:
        (ROOT / "robots.txt").write_text("# Staging build: keep out of search engines. Rebuild with --site-url (and without --noindex) for production.\nUser-agent: *\nDisallow: /\n", encoding="utf-8")
        (ROOT / "sitemap.xml").unlink(missing_ok=True)
    if INDEXABLE:
        urls = "".join(f"<url><loc>{SITE}/{'' if n == 'index.html' else n}</loc></url>" for n, m in built if n != "404.html" and not m.get("noindex"))
        (ROOT / "sitemap.xml").write_text(f'<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">{urls}</urlset>\n', encoding="utf-8")
        (ROOT / "robots.txt").write_text(f"User-agent: *\nAllow: /\n\nSitemap: {SITE}/sitemap.xml\n", encoding="utf-8")
    print(f"Built {len(built)} pages:", ", ".join(n for n, _ in built))

if __name__ == "__main__":
    main()
