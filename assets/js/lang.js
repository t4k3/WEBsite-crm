// ================= LANG.JS =================
// Gestione multilingua per tutto il sito Takeoff.pro / V12

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

    // TESTO INTRODUTTIVO (SEO)
    intro_title:
      "La macchina spara palloni pensata per allenare ricezione e difesa",
    intro_p1:
      "Takeoff.pro è una macchina spara palloni professionale per il volley, pensata per essere usata in allenamento: allena ricezione, difesa e lettura del gioco con la massima realisticità. Simula battute float, spin e reverse spin fino a 115 km/h, con altezza regolabile da 220 a 280 cm — per riprodurre in allenamento le situazioni che si incontrano davvero in partita.",
    intro_p2:
      "Si smonta in tre parti in circa 60 secondi e sta comodamente nel bagagliaio di un’auto normale, senza bisogno di furgoni o mezzi dedicati: pronta all’uso a ogni allenamento, senza complicazioni logistiche.",
    intro_p3:
      "Il sistema di lancio è progettato per essere delicato sui palloni, riducendone l’usura nel tempo.",

    // REEL INSTAGRAM
    reels_title: "Dal nostro Instagram",
    reels_sub: "Allenamenti, test e novità dal campo.",
    reels_follow: "Seguici su Instagram ›",

    // COACH / CAROUSEL
    coach_title: "Cosa dicono gli allenatori",

    // TUTORIAL PAGE
    tutorial_page_title: "Video Tutorial: come usare la macchina spara palloni | Takeoff.pro",
    tutorial_title: "Video Tutorial",
    tutorial_setup: "Montaggio e trasporto",
    tutorial_display: "Configurazione display",
    tutorial_bluetooth: "Connessione Bluetooth e App",

    // CONTACT PAGE
    contact_page_title: "Contatti e preventivo macchina spara palloni | Takeoff.pro",
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

    // RICHIESTA PREVENTIVO (richiesta.php)
    req_page_title: "Richiedi un preventivo | Takeoff.pro",
    req_heading: "Richiedi un preventivo",
    req_intro_tail: "ti invieremo un preventivo personalizzato.",
    req_name: "Nome e cognome *",
    req_email: "Email *",
    req_phone: "Telefono",
    req_country: "Paese *",
    req_quantity: "Quantità",
    req_notes: "Note (esigenze, tempistiche, domande)",
    req_call: "Vorrei essere ricontattato con una call",
    req_avail: "Quando sei reperibile?",
    req_avail_ph: "es. lun–ven 9–13, oppure un orario preciso",
    req_submit: "Invia richiesta",
    req_done_title: "Grazie! ✅",
    req_done_msg:
      "Abbiamo ricevuto la tua richiesta. Ti invieremo al più presto un preventivo personalizzato.",

    // FOOTER
    footer_text: "© 2025 V12 by Takeoff.pro — sport innovation",
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

    // INTRO TEXT (SEO)
    intro_title:
      "The ball machine designed to train reception and defence",
    intro_p1:
      "Takeoff.pro is a professional volleyball ball machine built for training: it develops reception, defence and game reading with maximum realism. It simulates float, spin and reverse spin serves up to 115 km/h, with height adjustable from 220 to 280 cm — to recreate in training the situations you actually face in a match.",
    intro_p2:
      "It breaks down into three parts in about 60 seconds and fits comfortably in the trunk of an ordinary car, with no need for vans or dedicated vehicles: ready to use at every session, without logistical complications.",
    intro_p3:
      "The launch system is designed to be gentle on the balls, reducing wear over time.",

    // INSTAGRAM REELS
    reels_title: "From our Instagram",
    reels_sub: "Training, testing and news from the court.",
    reels_follow: "Follow us on Instagram ›",

    // COACH / CAROUSEL
    coach_title: "What coaches say",

    // TUTORIAL PAGE
    tutorial_page_title: "Video Tutorials: how to use the volleyball machine | Takeoff.pro",
    tutorial_title: "Video Tutorials",
    tutorial_setup: "Assembly and transport",
    tutorial_display: "Display setup",
    tutorial_bluetooth: "Bluetooth connection and App",

    // CONTACT PAGE
    contact_page_title: "Contact and quote for the volleyball machine | Takeoff.pro",
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

    // RICHIESTA PREVENTIVO (richiesta.php)
    req_page_title: "Request a quote | Takeoff.pro",
    req_heading: "Request a quote",
    req_intro_tail: "we’ll send you a personalized quote.",
    req_name: "Full name *",
    req_email: "Email *",
    req_phone: "Phone",
    req_country: "Country *",
    req_quantity: "Quantity",
    req_notes: "Notes (needs, timing, questions)",
    req_call: "I’d like to be contacted for a call",
    req_avail: "When are you available?",
    req_avail_ph: "e.g. Mon–Fri 9–13, or a specific time",
    req_submit: "Send request",
    req_done_title: "Thank you! ✅",
    req_done_msg:
      "We’ve received your request. We’ll send you a personalized quote as soon as possible.",

    // FOOTER
    footer_text: "© 2025 V12 by Takeoff.pro — sport innovation",
  },

  fr: {
    // NAVBAR
    nav_home: "Accueil",
    nav_contact: "Contact",
    nav_tutorials: "Tutoriels",
    nav_privacy: "Confidentialité",

    // HOME / HERO
    title: "Takeoff.pro Volleyball Machine",
    subtitle: "sport innovation",
    cta: "Demander un devis",
    hero_watch: "Voir en action ›",

    // SECTIONS (features)
    speed: "Vitesse maximale 115 km/h",
    battery: "Batterie longue durée jusqu’à 5 heures",
    safe: "Sécurité intégrée et contrôle Bluetooth",
    learn_more: "Voir les tutoriels",

    // GALERIE PRODUIT
    gallery_title: "Le produit en détail",
    gallery_sub: "Le produit sous tous les angles, d’après les rendus 3D officiels.",
    gallery_cap_front: "Vue de face",
    gallery_cap_angle: "Vue latérale",
    gallery_cap_play: "Prête à lancer",
    gallery_cap_controls: "Écran et commandes",
    gallery_cap_head: "Tête de lancement",
    gallery_cap_rollers: "Rouleaux de lancement",
    gallery_cap_photo: "La machine",

    // VIDÉO + SPÉCIFICATIONS
    video_title: "Voir en action",
    specs_title: "Caractéristiques et spécifications",

    // TEXTE D’INTRODUCTION (SEO)
    intro_title:
      "La machine à lancer les ballons conçue pour travailler la réception et la défense",
    intro_p1:
      "Takeoff.pro est une machine à lancer les ballons professionnelle pour le volley-ball, pensée pour l’entraînement : elle travaille la réception, la défense et la lecture du jeu avec un réalisme maximal. Elle simule des services float, spin et reverse spin jusqu’à 115 km/h, avec une hauteur réglable de 220 à 280 cm — pour reproduire à l’entraînement les situations réellement rencontrées en match.",
    intro_p2:
      "Elle se démonte en trois parties en une soixantaine de secondes et tient aisément dans le coffre d’une voiture ordinaire, sans camionnette ni véhicule dédié : prête à l’emploi à chaque entraînement, sans complication logistique.",
    intro_p3:
      "Le système de lancement est conçu pour être délicat avec les ballons, réduisant leur usure dans le temps.",

    // REELS INSTAGRAM
    reels_title: "Sur notre Instagram",
    reels_sub: "Entraînements, tests et actualités du terrain.",
    reels_follow: "Suivez-nous sur Instagram ›",

    // COACH / CAROUSEL
    coach_title: "Ce que disent les entraîneurs",

    // PAGE TUTORIELS
    tutorial_page_title: "Tutoriels vidéo : utiliser la machine à lancer les ballons | Takeoff.pro",
    tutorial_title: "Tutoriels vidéo",
    tutorial_setup: "Montage et transport",
    tutorial_display: "Configuration de l’écran",
    tutorial_bluetooth: "Connexion Bluetooth et application",

    // PAGE CONTACT
    contact_page_title: "Contact et devis machine à lancer les ballons | Takeoff.pro",
    contact_title: "Demandez votre devis personnalisé",
    form_name: "Nom",
    form_email: "E-mail",
    form_country: "Pays",
    form_city: "Ville",
    form_zip: "Code postal",
    form_quantity: "Quantité",
    form_message: "Notes complémentaires",
    privacy_consent:
      'J’accepte le traitement de mes données conformément à la <a href="privacy.html" class="text-[#0096e0] underline hover:text-[#0077b3]">Politique de confidentialité</a>.',
    contact_alt:
      'Ou écrivez à <a href="mailto:info@takeoff.pro" class="text-[#0096e0]">info@takeoff.pro</a>',

    // PAGE CONFIDENTIALITÉ
    privacy_title: "Politique de confidentialité – Takeoff.pro",
    privacy_heading: "Politique de confidentialité",
    privacy_update: "Dernière mise à jour : octobre 2025",

    // PAGE REMERCIEMENT
    thankyou_title: "Demande envoyée – Takeoff.pro",
    thankyou_heading: "Merci de nous avoir contactés !",
    thankyou_message:
      "Nous avons bien reçu votre demande et nous vous répondrons dans les plus brefs délais.",
    thankyou_back: "Retour à l’accueil",

    // RICHIESTA PREVENTIVO (richiesta.php)
    req_page_title: "Demander un devis | Takeoff.pro",
    req_heading: "Demander un devis",
    req_intro_tail: "nous vous enverrons un devis personnalisé.",
    req_name: "Nom et prénom *",
    req_email: "E-mail *",
    req_phone: "Téléphone",
    req_country: "Pays *",
    req_quantity: "Quantité",
    req_notes: "Notes (besoins, délais, questions)",
    req_call: "Je souhaite être recontacté par téléphone",
    req_avail: "Quand êtes-vous disponible ?",
    req_avail_ph: "ex. lun–ven 9h–13h, ou un horaire précis",
    req_submit: "Envoyer la demande",
    req_done_title: "Merci ! ✅",
    req_done_msg:
      "Nous avons bien reçu votre demande. Nous vous enverrons un devis personnalisé dans les plus brefs délais.",

    // FOOTER
    footer_text: "© 2025 V12 by Takeoff.pro — sport innovation",
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

  // ✅ Menu paesi (solo sulla pagina preventivo, dove esiste #country)
  if (typeof window.renderCountrySelect === "function")
    window.renderCountrySelect(lang);

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
  // Lingua salvata, altrimenti quella del browser (it di default)
  const browserLang = (navigator.language || "it").slice(0, 2).toLowerCase();
  const savedLang =
    localStorage.getItem("lang") ||
    (translations[browserLang] ? browserLang : "it");

  applyTranslations(savedLang);

  // ✅ Cambio lingua con bandierine 🇮🇹 / 🇺🇸 / 🇫🇷 (usa event delegation per evitare duplicati)
  document.body.addEventListener("click", (e) => {
    const btn = e.target.closest(".lang-switch");
    if (btn && btn.dataset.lang) {
      applyTranslations(btn.dataset.lang);
    }
  });

  // ✅ Menu mobile (hamburger): apre/chiude la tendina su schermi piccoli
  const menuToggle = document.getElementById("menu-toggle");
  const mobileMenu = document.getElementById("mobile-menu");
  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener("click", () => {
      const isOpen = mobileMenu.classList.toggle("hidden") === false;
      menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
    // Chiudi la tendina quando si tocca una voce di menu
    mobileMenu.querySelectorAll("a").forEach((a) =>
      a.addEventListener("click", () => {
        mobileMenu.classList.add("hidden");
        menuToggle.setAttribute("aria-expanded", "false");
      })
    );
  }
});

// ==============================
// COMPATIBILITÀ CON INDEX.HTML
// ==============================
window.setLanguage = applyTranslations;
