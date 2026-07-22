// ================= LANG.JS =================
// Gestione multilingua per tutto il sito Takeoff.pro / Wazlley

const translations = {
  it: {
    // NAVBAR
    nav_home: "Home",
    nav_contact: "Contatti",
    nav_tutorials: "Tutorial",
    nav_privacy: "Privacy",

    // HOME / HERO
    title: "Takeoff.pro Volleyball Machine",
    subtitle: "sport innovation",
    cta: "Richiedi un preventivo",
    hero_watch: "Guarda in azione ›",

    // SEZIONI (features)
    speed: "Velocità massima 115 km/h",
    battery: "Batteria long life fino a 5 ore",
    safe: "Sicurezza integrata e controllo Bluetooth",
    learn_more: "Guarda i tutorial",

    // GALLERIA PRODOTTO
    gallery_title: "Il prodotto dal vivo",
    gallery_sub: "Il prodotto da ogni angolazione, dai render 3D ufficiali.",
    gallery_cap_front: "Vista frontale",
    gallery_cap_angle: "Vista laterale",
    gallery_cap_play: "Pronta al lancio",
    gallery_cap_controls: "Display e controlli",
    gallery_cap_head: "Testa di lancio",
    gallery_cap_rollers: "Rulli di lancio",
    gallery_cap_photo: "La macchina",

    // VIDEO + SPECIFICHE
    video_title: "Guarda in azione",
    specs_title: "Caratteristiche e specifiche",

    // REEL INSTAGRAM
    reels_title: "Dal nostro Instagram",
    reels_sub: "Allenamenti, test e novità dal campo.",
    reels_follow: "Seguici su Instagram ›",

    // COACH / CAROUSEL
    coach_title: "Cosa dicono gli allenatori",

    // TUTORIAL PAGE
    tutorial_page_title: "Tutorial – Takeoff.pro",
    tutorial_title: "Video Tutorial",
    tutorial_setup: "Montaggio e trasporto",
    tutorial_display: "Configurazione display",
    tutorial_bluetooth: "Connessione Bluetooth e App",

    // CONTACT PAGE
    contact_page_title: "Contatti – Takeoff.pro",
    contact_title: "Richiedi il tuo preventivo personalizzato",
    form_name: "Nome",
    form_email: "Email",
    form_country: "Paese",
    form_city: "Città",
    form_zip: "CAP",
    form_quantity: "Quantità",
    form_message: "Note aggiuntive",
    privacy_consent:
      'Acconsento al trattamento dei dati secondo la <a href="privacy.html" class="text-[#0096e0] underline hover:text-[#0077b3]">Privacy Policy</a>.',
    contact_alt:
      'Oppure scrivi a <a href="mailto:info@takeoff.pro" class="text-[#0096e0]">info@takeoff.pro</a>',

    // PRIVACY PAGE
    privacy_title: "Privacy Policy – Takeoff.pro",
    privacy_heading: "Informativa sulla Privacy",
    privacy_update: "Ultimo aggiornamento: Ottobre 2025",

    // THANK YOU PAGE
    thankyou_title: "Richiesta inviata – Takeoff.pro",
    thankyou_heading: "Grazie per averci contattato!",
    thankyou_message:
      "Abbiamo ricevuto la tua richiesta e ti risponderemo al più presto.",
    thankyou_back: "Torna alla Home",

    // FOOTER
    footer_text: "© 2025 Wazlley by Takeoff.pro — sport innovation",
  },

  en: {
    // NAVBAR
    nav_home: "Home",
    nav_contact: "Contact",
    nav_tutorials: "Tutorials",
    nav_privacy: "Privacy Policy",

    // HOME / HERO
    title: "Takeoff.pro Volleyball Machine",
    subtitle: "sport innovation",
    cta: "Request a quote",
    hero_watch: "See it in action ›",

    // SECTIONS (features)
    speed: "Top speed 115 km/h",
    battery: "Long-life sodium battery — up to 5 hours",
    safe: "Integrated safety and Bluetooth control",
    learn_more: "Watch tutorials",

    // PRODUCT GALLERY
    gallery_title: "The product up close",
    gallery_sub: "The product from every angle, from the official 3D renders.",
    gallery_cap_front: "Front view",
    gallery_cap_angle: "Side view",
    gallery_cap_play: "Ready to launch",
    gallery_cap_controls: "Display & controls",
    gallery_cap_head: "Throwing head",
    gallery_cap_rollers: "Launch rollers",
    gallery_cap_photo: "The machine",

    // VIDEO + SPECS
    video_title: "See it in action",
    specs_title: "Features & specs",

    // INSTAGRAM REELS
    reels_title: "From our Instagram",
    reels_sub: "Training, testing and news from the court.",
    reels_follow: "Follow us on Instagram ›",

    // COACH / CAROUSEL
    coach_title: "What coaches say",

    // TUTORIAL PAGE
    tutorial_page_title: "Tutorial – Takeoff.pro",
    tutorial_title: "Video Tutorials",
    tutorial_setup: "Assembly and transport",
    tutorial_display: "Display setup",
    tutorial_bluetooth: "Bluetooth connection and App",

    // CONTACT PAGE
    contact_page_title: "Contact – Takeoff.pro",
    contact_title: "Request your personalized quote",
    form_name: "Name",
    form_email: "Email",
    form_country: "Country",
    form_city: "City",
    form_zip: "ZIP Code",
    form_quantity: "Quantity",
    form_message: "Additional notes",
    privacy_consent:
      'I consent to data processing according to the <a href="privacy.html" class="text-[#0096e0] underline hover:text-[#0077b3]">Privacy Policy</a>.',
    contact_alt:
      'Or write to <a href="mailto:info@takeoff.pro" class="text-[#0096e0]">info@takeoff.pro</a>',

    // PRIVACY PAGE
    privacy_title: "Privacy Policy – Takeoff.pro",
    privacy_heading: "Privacy Policy",
    privacy_update: "Last updated: October 2025",

    // THANK YOU PAGE
    thankyou_title: "Request sent – Takeoff.pro",
    thankyou_heading: "Thank you for contacting us!",
    thankyou_message:
      "We’ve received your request and will get back to you shortly.",
    thankyou_back: "Back to Home",

    // FOOTER
    footer_text: "© 2025 Wazlley by Takeoff.pro — sport innovation",
  },
};

// ========== FUNZIONE PRINCIPALE ==========
function applyTranslations(lang) {
  document.documentElement.setAttribute("data-lang", lang);
  localStorage.setItem("lang", lang);

  const strings = translations[lang] || translations.it;

  // ✅ Aggiorna testi statici (data-i18n)
  document.querySelectorAll("[data-i18n]").forEach((el) => {
    const key = el.getAttribute("data-i18n");
    if (strings[key]) el.innerHTML = strings[key]; // innerHTML per gestire i link HTML nei testi
  });

  // ✅ Aggiorna placeholder dei form (data-i18n-placeholder)
  document.querySelectorAll("[data-i18n-placeholder]").forEach((el) => {
    const key = el.getAttribute("data-i18n-placeholder");
    if (strings[key]) el.placeholder = strings[key];
  });

  // 🔁 Aggiorna sezioni e carosello dopo che tutto è pronto
  setTimeout(() => {
    if (typeof window.renderSections === "function")
      window.renderSections(lang);
    if (typeof window.renderCoaches === "function") window.renderCoaches(lang);
    if (typeof window.renderSpecs === "function") window.renderSpecs(lang);
  }, 200);
}

// ========== INIZIALIZZAZIONE ==========
document.addEventListener("DOMContentLoaded", () => {
  const savedLang =
    localStorage.getItem("lang") ||
    (navigator.language.startsWith("en") ? "en" : "it");

  applyTranslations(savedLang);

  // ✅ Cambio lingua con bandierine 🇮🇹 / 🇺🇸 (usa event delegation per evitare duplicati)
  document.body.addEventListener("click", (e) => {
    const btn = e.target.closest(".lang-switch");
    if (btn && btn.dataset.lang) {
      applyTranslations(btn.dataset.lang);
    }
  });
});

// ==============================
// COMPATIBILITÀ CON INDEX.HTML
// ==============================
window.setLanguage = applyTranslations;
