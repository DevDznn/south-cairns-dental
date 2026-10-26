/* South Cairns Dental — site behaviour (vanilla JS, no dependencies) */
(function () {
  "use strict";
  var doc = document.documentElement;
  doc.classList.add("js");

  var data = window.SCD || { hours: [] };

  /* ---------- Opening hours (always computed in Cairns time) ---------- */
  function cairnsNow() {
    try {
      var parts = new Intl.DateTimeFormat("en-AU", {
        timeZone: "Australia/Brisbane", weekday: "long", hour: "2-digit", minute: "2-digit", hourCycle: "h23"
      }).formatToParts(new Date());
      var get = function (t) { var p = parts.find(function (x) { return x.type === t; }); return p ? p.value : ""; };
      return { day: get("weekday"), minutes: parseInt(get("hour"), 10) * 60 + parseInt(get("minute"), 10) };
    } catch (e) {
      var d = new Date();
      return { day: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][d.getDay()], minutes: d.getHours() * 60 + d.getMinutes() };
    }
  }
  function toMin(hhmm) { var p = hhmm.split(":"); return +p[0] * 60 + +p[1]; }
  function fmt(hhmm) {
    var m = toMin(hhmm), h = Math.floor(m / 60), mm = m % 60, suffix = h >= 12 ? "pm" : "am";
    h = h % 12 || 12;
    return h + (mm ? ":" + String(mm).padStart(2, "0") : "") + " " + suffix;
  }
  function nextOpening(fromIndex) {
    for (var i = 1; i <= 7; i++) {
      var d = data.hours[(fromIndex + i) % 7];
      if (d && d.open) return d.day + " at " + fmt(d.open);
    }
    return "";
  }
  function status() {
    var now = cairnsNow();
    var idx = data.hours.findIndex(function (h) { return h.day === now.day; });
    var today = data.hours[idx];
    if (!today) return null;
    if (today.open && now.minutes >= toMin(today.open) && now.minutes < toMin(today.close)) {
      return { open: true, idx: idx, text: "Open now until " + fmt(today.close), short: "Open today " + fmt(today.open) + " to " + fmt(today.close) };
    }
    if (today.open && now.minutes < toMin(today.open)) {
      return { open: false, idx: idx, text: "Opens today at " + fmt(today.open), short: "Open today " + fmt(today.open) + " to " + fmt(today.close) };
    }
    return { open: false, idx: idx, text: "Closed now. Opens " + nextOpening(idx), short: "Closed today. Opens " + nextOpening(idx) };
  }
  var st = status();
  if (st) {
    document.querySelectorAll("[data-hours-status]").forEach(function (el) {
      el.textContent = el.getAttribute("data-hours-status") === "short" ? st.short : st.text;
    });
    document.querySelectorAll("[data-hours-dot]").forEach(function (el) { el.classList.toggle("is-open", st.open); });
    document.querySelectorAll(".hours tr[data-day]").forEach(function (tr) {
      if (tr.getAttribute("data-day") === data.hours[st.idx].day) tr.classList.add("is-today");
    });
  }

  /* ---------- Header: scrolled state + utility height ---------- */
  var header = document.querySelector(".site-header");
  var utility = document.querySelector(".utility");
  function onScroll() { if (header) header.classList.toggle("is-scrolled", window.scrollY > 8); }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  function setUtilityOffset() {
    var h = utility ? Math.max(0, utility.getBoundingClientRect().bottom) : 0;
    doc.style.setProperty("--utility-h", h + "px");
    // The header compacts on scroll, so the mobile menu opens from its real bottom edge
    if (header) doc.style.setProperty("--nav-top", Math.round(header.getBoundingClientRect().bottom) + "px");
  }

  /* ---------- Services panel ---------- */
  var toggles = document.querySelectorAll("[data-panel-toggle]");
  function closePanels(except) {
    toggles.forEach(function (btn) {
      if (btn === except) return;
      btn.setAttribute("aria-expanded", "false");
      var p = document.getElementById(btn.getAttribute("aria-controls"));
      if (p) p.classList.remove("is-open");
    });
  }
  toggles.forEach(function (btn) {
    var panel = document.getElementById(btn.getAttribute("aria-controls"));
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      var open = btn.getAttribute("aria-expanded") === "true";
      closePanels(btn);
      btn.setAttribute("aria-expanded", String(!open));
      if (panel) panel.classList.toggle("is-open", !open);
    });
    if (panel) {
      panel.addEventListener("click", function (e) { e.stopPropagation(); });
      panel.addEventListener("keydown", function (e) {
        if (e.key === "Escape") { closePanels(); btn.focus(); }
      });
      // Close when focus leaves the panel and its toggle (desktop)
      panel.addEventListener("focusout", function (e) {
        if (window.matchMedia("(max-width: 1020px)").matches) return;
        var to = e.relatedTarget;
        if (to && !panel.contains(to) && to !== btn) closePanels();
      });
    }
  });
  document.addEventListener("click", function () {
    if (!window.matchMedia("(max-width: 1020px)").matches) closePanels();
  });

  /* Desktop extras for the dropdowns: open on hover (with a short delay so menus don't flicker),
     and arrow keys to move through links. Click, tap, Enter and Space still toggle as before. */
  var desktopHover = window.matchMedia("(hover: hover) and (pointer: fine) and (min-width: 1021px)");
  toggles.forEach(function (btn) {
    var item = btn.parentElement, panel = document.getElementById(btn.getAttribute("aria-controls"));
    if (!panel) return;
    var timer, hoverOpenedAt = 0;
    function open() { closePanels(btn); btn.setAttribute("aria-expanded", "true"); panel.classList.add("is-open"); }
    function close() { btn.setAttribute("aria-expanded", "false"); panel.classList.remove("is-open"); }
    item.addEventListener("mouseenter", function () {
      if (!desktopHover.matches) return;
      clearTimeout(timer);
      timer = setTimeout(function () { if (btn.getAttribute("aria-expanded") !== "true") { open(); hoverOpenedAt = Date.now(); } }, 90);
    });
    item.addEventListener("mouseleave", function () {
      if (!desktopHover.matches) return;
      clearTimeout(timer);
      timer = setTimeout(function () { if (!panel.contains(document.activeElement)) close(); }, 220);
    });
    // A click straight after hover-opening shouldn't immediately close the menu again
    btn.addEventListener("click", function (e) {
      if (desktopHover.matches && Date.now() - hoverOpenedAt < 400 && btn.getAttribute("aria-expanded") === "false") {
        open(); hoverOpenedAt = 0;
      }
    });
    function links() { return Array.prototype.slice.call(panel.querySelectorAll("a")).filter(function (a) { return a.offsetParent !== null; }); }
    btn.addEventListener("keydown", function (e) {
      if (e.key === "ArrowDown") { e.preventDefault(); open(); var l = links(); if (l[0]) l[0].focus(); }
    });
    panel.addEventListener("keydown", function (e) {
      if (e.key !== "ArrowDown" && e.key !== "ArrowUp") return;
      var l = links(), i = l.indexOf(document.activeElement);
      if (i < 0) return;
      e.preventDefault();
      if (e.key === "ArrowUp" && i === 0) { btn.focus(); return; }
      l[Math.max(0, Math.min(l.length - 1, i + (e.key === "ArrowDown" ? 1 : -1)))].focus();
    });
  });

  /* ---------- Mobile menu ---------- */
  var menuBtn = document.querySelector(".menu-button");
  var nav = document.getElementById("site-nav");
  function setMenu(open) {
    if (!menuBtn || !nav) return;
    setUtilityOffset();
    menuBtn.setAttribute("aria-expanded", String(open));
    menuBtn.querySelector(".menu-button__text").textContent = open ? "Close" : "Menu";
    nav.classList.toggle("is-open", open);
    document.body.classList.toggle("menu-open", open);
    if (open) { var first = nav.querySelector("a, button"); if (first) first.focus(); }
  }
  if (menuBtn && nav) {
    menuBtn.addEventListener("click", function () { setMenu(menuBtn.getAttribute("aria-expanded") !== "true"); });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        if (menuBtn.getAttribute("aria-expanded") === "true") { setMenu(false); menuBtn.focus(); }
        else closePanels();
      }
    });
    window.addEventListener("resize", function () {
      if (!window.matchMedia("(max-width: 1020px)").matches && nav.classList.contains("is-open")) setMenu(false);
    });
  }

  /* ---------- Contact form: opens the visitor's email app, sends nothing itself ---------- */
  var form = document.querySelector("[data-mailto-form]");
  // Service pages link here with ?topic=<treatment>, so the enquiry keeps its context
  if (form) {
    try {
      var topic = new URLSearchParams(window.location.search).get("topic");
      if (topic && topic.length < 60) {
        form.elements.topic.value = "Treatment question";
        if (!form.elements.message.value) form.elements.message.value = "I have a question about " + topic + ": ";
      }
    } catch (e) { /* older browsers: the form still works without the prefill */ }
  }
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var ok = true;
      form.querySelectorAll("[required]").forEach(function (f) {
        var err = document.getElementById(f.id + "-error");
        var valid = f.value.trim() !== "" && (f.type !== "email" || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.value.trim()));
        f.setAttribute("aria-invalid", String(!valid));
        if (err) err.hidden = valid;
        if (!valid && ok) { f.focus(); ok = false; }
      });
      if (!ok) return;
      var v = function (n) { var el = form.elements[n]; return el ? el.value.trim() : ""; };
      var subject = "Website enquiry: " + (v("topic") || "General") + " (" + v("name") + ")";
      var body = [
        "Name: " + v("name"),
        "Phone: " + (v("phone") || "Not given"),
        "Email: " + v("email"),
        "Enquiry about: " + (v("topic") || "General"),
        "",
        v("message")
      ].join("\n");
      var href = "mailto:" + form.getAttribute("data-mailto-form") + "?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
      var status = form.querySelector(".form__status");
      if (status) {
        status.hidden = false;
        status.textContent = "Your email app should now open with your message ready. Press send there to send it to reception. If it doesn't open, email " +
          form.getAttribute("data-mailto-form") + " or call (07) 4055 4214.";
      }
      window.location.href = href;
    });
  }

  /* ---------- Sticky call bar (emergency page, homepage) once the main call buttons are off screen ---------- */
  var sticky = document.querySelector(".er-sticky, .mbar"), card = document.querySelector("[data-sticky-anchor]");
  if (sticky && card && "IntersectionObserver" in window) {
    new IntersectionObserver(function (entries) {
      sticky.classList.toggle("is-visible", !entries[0].isIntersecting);
    }).observe(card);
  } else if (sticky) { sticky.classList.add("is-visible"); }

  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- Scroll reveals (content stays visible without JS or with reduced motion) ---------- */
  var reveals = document.querySelectorAll(".reveal");
  if (reveals.length && "IntersectionObserver" in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add("is-in"); io.unobserve(en.target); } });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ---------- Service filter chips ---------- */
  var chips = document.querySelectorAll(".chip[data-filter]");
  var grid = document.querySelector("[data-filter-grid]");
  if (chips.length && grid) {
    var live = document.querySelector(".svc-grid__status");
    chips.forEach(function (chip) {
      chip.addEventListener("click", function () {
        var f = chip.getAttribute("data-filter"), shown = 0;
        chips.forEach(function (c) { c.setAttribute("aria-pressed", String(c === chip)); });
        grid.querySelectorAll(".svc-card").forEach(function (card) {
          var match = f === "all" || card.getAttribute("data-cat") === f;
          card.hidden = !match;
          if (match) { shown++; card.classList.add("is-in"); }
        });
        if (live) live.textContent = "Showing " + shown + (shown === 1 ? " treatment" : " treatments");
        // On phones the grid is a carousel: start the filtered set from the beginning
        grid.scrollLeft = 0;
        grid.dispatchEvent(new Event("scroll"));
      });
    });
  }

  /* ---------- Homepage card carousels on phones: swipe, or use the previous/next buttons ---------- */
  var chevron = function (d) { return '<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="' + d + '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'; };
  document.querySelectorAll(".page-home .concierge, .page-home .svc-grid, .page-home .team-cards, .page-home .pay").forEach(function (list, n) {
    var heading = list.closest("section") && list.closest("section").querySelector("h2");
    var label = heading ? heading.textContent.trim() : "cards";
    if (!list.id) list.id = "carousel-" + n;
    list.classList.add("is-carousel");
    var nav = document.createElement("div");
    nav.className = "carousel-nav";
    nav.innerHTML = '<div class="carousel-nav__track" aria-hidden="true"><span></span></div>' +
      '<button type="button" class="carousel-nav__btn" data-dir="-1" aria-controls="' + list.id + '" aria-label="Previous: ' + label + '">' + chevron("M10 3 5 8l5 5") + '</button>' +
      '<button type="button" class="carousel-nav__btn" data-dir="1" aria-controls="' + list.id + '" aria-label="Next: ' + label + '">' + chevron("M6 3l5 5-5 5") + '</button>';
    list.parentNode.insertBefore(nav, list.nextSibling);
    var bar = nav.querySelector(".carousel-nav__track span"), btns = nav.querySelectorAll("button");
    function update() {
      var max = list.scrollWidth - list.clientWidth;
      nav.hidden = max <= 2; // nothing to scroll, e.g. on tablet and desktop where the cards sit in a grid
      if (nav.hidden) return;
      btns[0].disabled = list.scrollLeft <= 2;
      btns[1].disabled = list.scrollLeft >= max - 2;
      // Keep keyboard focus on a usable button when one end is reached
      if (btns[0].disabled && document.activeElement === btns[0]) btns[1].focus();
      if (btns[1].disabled && document.activeElement === btns[1]) btns[0].focus();
      bar.style.width = (list.clientWidth / list.scrollWidth * 100) + "%";
      bar.style.left = (list.scrollLeft / list.scrollWidth * 100) + "%";
    }
    nav.addEventListener("click", function (e) {
      var b = e.target.closest("button");
      if (!b) return;
      var card = Array.prototype.find.call(list.children, function (c) { return !c.hidden; });
      var step = card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(list).columnGap || 0) : list.clientWidth;
      list.scrollBy({ left: step * +b.getAttribute("data-dir"), behavior: reduce ? "auto" : "smooth" });
    });
    list.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    update();
  });

  /* ---------- Smooth accordion open/close (native <details> still works without JS) ---------- */
  if (!reduce) {
    document.querySelectorAll(".faq details").forEach(function (d) {
      var summary = d.querySelector("summary"), body = d.querySelector(".faq__body");
      if (!summary || !body) return;
      summary.addEventListener("click", function (e) {
        e.preventDefault();
        if (d.dataset.animating) return;
        d.dataset.animating = "1";
        if (d.open) {
          var h = body.scrollHeight;
          body.animate([{ height: h + "px", opacity: 1 }, { height: "0px", opacity: 0 }], { duration: 220, easing: "ease" })
            .onfinish = function () { d.open = false; delete d.dataset.animating; };
        } else {
          d.open = true;
          var h2 = body.scrollHeight;
          body.animate([{ height: "0px", opacity: 0 }, { height: h2 + "px", opacity: 1 }], { duration: 260, easing: "ease" })
            .onfinish = function () { delete d.dataset.animating; };
        }
      });
    });
  }

  /* ---------- Hero card tilt (pointer devices only) ---------- */
  var tilt = document.querySelector("[data-tilt]");
  if (tilt && !reduce && window.matchMedia("(hover: hover) and (min-width: 980px)").matches) {
    var host = tilt.parentElement;
    host.addEventListener("mousemove", function (e) {
      var r = host.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      tilt.style.transform = "perspective(900px) rotateY(" + (x * 6).toFixed(2) + "deg) rotateX(" + (-y * 6).toFixed(2) + "deg)";
    });
    host.addEventListener("mouseleave", function () { tilt.style.transform = ""; });
  }

  /* ---------- Count-up stats (final value is in the HTML, so it's correct without JS) ---------- */
  var counters = document.querySelectorAll("[data-count]");
  if (counters.length && "IntersectionObserver" in window && !reduce) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target, end = parseInt(el.getAttribute("data-count"), 10), t0 = null;
        cio.unobserve(el);
        (function step(t) {
          if (!t0) t0 = t;
          var k = Math.min(1, (t - t0) / 900);
          el.textContent = Math.round(end * (1 - Math.pow(1 - k, 3)));
          if (k < 1) requestAnimationFrame(step);
        })(performance.now());
      });
    }, { threshold: .6 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  /* ---------- Footer year ---------- */
  document.querySelectorAll("[data-year]").forEach(function (el) { el.textContent = new Date().getFullYear(); });
})();
