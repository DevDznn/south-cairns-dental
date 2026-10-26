#!/usr/bin/env python3
"""Static QA: broken local links/assets, duplicate ids, metadata, headings, tel/mailto links."""
import re, sys
from pathlib import Path
from html.parser import HTMLParser
ROOT = Path(__file__).resolve().parent.parent
pages = sorted(p for p in ROOT.glob("*.html"))
problems, titles, descs = [], {}, {}
class P(HTMLParser):
    def __init__(s): super().__init__(); s.ids=[]; s.refs=[]; s.h1=0; s.title=""; s.desc=""; s._t=False; s.anchors=set(); s.imgs_noalt=0
    def handle_starttag(s, tag, a):
        a=dict(a)
        if "id" in a: s.ids.append(a["id"])
        for k in ("href","src"):
            if k in a and a[k]: s.refs.append((tag,a[k]))
        if tag=="h1": s.h1+=1
        if tag=="title": s._t=True
        if tag=="meta" and a.get("name")=="description": s.desc=a.get("content","")
        if tag=="img" and "alt" not in a: s.imgs_noalt+=1
    def handle_endtag(s,tag):
        if tag=="title": s._t=False
    def handle_data(s,d):
        if s._t: s.title+=d
ids_by_page={}
pending_frags=[]
for p in pages:
    h=P(); h.feed(p.read_text(encoding="utf-8")); ids_by_page[p.name]=set(h.ids)
    dup={i for i in h.ids if h.ids.count(i)>1}
    if dup: problems.append(f"{p.name}: duplicate ids {dup}")
    if h.h1!=1: problems.append(f"{p.name}: {h.h1} h1 elements")
    if h.imgs_noalt: problems.append(f"{p.name}: {h.imgs_noalt} img without alt")
    titles.setdefault(h.title,[]).append(p.name); descs.setdefault(h.desc,[]).append(p.name)
    if not h.desc: problems.append(f"{p.name}: no meta description")
    for tag,ref in h.refs:
        if ref.startswith("tel:"):
            if ref not in ("tel:+61740554214","tel:000","tel:134325584"): problems.append(f"{p.name}: unexpected tel {ref}")
            continue
        if ref.startswith(("http","mailto:","data:")): continue
        path, _, frag = ref.partition("#"); path = path.split("?")[0]
        if path=="" : continue
        if not (ROOT/path).exists(): problems.append(f"{p.name}: missing {ref}")
        elif frag and path.endswith(".html"):
            pending_frags.append((p.name, path, frag))
    for tag,ref in h.refs:
        if ref.startswith("#") and len(ref) > 1: pending_frags.append((p.name, p.name, ref[1:]))
    text = p.read_text(encoding="utf-8")
    if "placeholders/" in text or "Photo to come" in text or "Portrait to come" in text:
        problems.append(f"{p.name}: placeholder artwork or label still present")
# Service pages must follow the shared template, in order
ORDER = ["intro", "explain", "process", "weigh", "care", "faq"]
booking = __import__("json").loads((ROOT / "_build/practice.json").read_text(encoding="utf-8"))["booking_url"]
for sj in sorted((ROOT / "_build/services").glob("*.json")):
    name = sj.stem + ".html"; t = (ROOT / name).read_text(encoding="utf-8")
    main = t.split('<main id="main"', 1)[1]
    hero = main.split("</section>", 1)[0]
    if 'class="phero svc-hero' not in hero: problems.append(f"{name}: hero is not the shared service hero")
    if '<nav class="crumbs"' not in hero or 'href="services.html"' not in hero: problems.append(f"{name}: breadcrumb missing or not Home > Services > page")
    if 'href="tel:+61740554214"' not in hero: problems.append(f"{name}: hero has no phone link")
    if "svc-hero--urgent" not in hero and booking.replace("&", "&amp;") not in hero: problems.append(f"{name}: hero has no online booking link")
    ids = re.findall(r'<section class="sec[^"]*" id="(\w+)"', main)
    if ids != [i for i in ORDER if i in ids] or "intro" not in ids: problems.append(f"{name}: template sections out of order: {ids}")
    tail = main[main.rfind('aria-labelledby="cta-h"'):]
    if 'aria-labelledby="cta-h"' not in main or 'id="related-h"' not in tail: problems.append(f"{name}: booking band and related services must close the page")
for src, target, frag in pending_frags:
    if frag not in ids_by_page.get(target, set()): problems.append(f"{src}: link to missing anchor {target}#{frag}")
for t,ps in titles.items():
    if len(ps)>1: problems.append(f"duplicate title {t!r}: {ps}")
for d,ps in descs.items():
    if len(ps)>1: problems.append(f"duplicate description: {ps}")
# CSS url() assets
css=(ROOT/"assets/css/style.css").read_text()
for u in re.findall(r'url\("?(\.\./[^")]+)"?\)', css):
    if not (ROOT/"assets/css"/u).resolve().exists(): problems.append(f"css missing {u}")
print(f"Checked {len(pages)} pages.")
print("\n".join(problems) if problems else "No problems found.")
sys.exit(1 if problems else 0)
