// ================== COSTRUISCE LE SEZIONI IN BASE ALLA LINGUA ==================
document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("sections-container");
  if (!container || typeof wazlleySections === "undefined") return;

  // Determina lingua browser o quella scelta
  let currentLang =
    localStorage.getItem("lang") ||
    (navigator.language.startsWith("it") ? "it" : "en");

  function renderSections(lang) {
    container.innerHTML = "";
    wazlleySections.forEach((section) => {
      const text = section[lang] || section.it; // fallback in italiano

      // Stile "pagina prodotto Apple": titolo grande centrato + testo + immagine sotto
      const block = document.createElement("section");
      block.className = "py-16 md:py-24 px-6 text-center";
      block.innerHTML = `
        <h2 class="text-3xl md:text-5xl font-semibold tracking-tight max-w-3xl mx-auto">${text.title}</h2>
        <p class="mt-4 text-lg md:text-xl text-[#6e6e73] max-w-2xl mx-auto">${text.text}</p>
        <img src="${section.img}" alt="${text.title}"
             class="mx-auto mt-10 max-h-[54vh] w-auto object-contain rounded-3xl bg-[#333333] p-3"
             loading="lazy"
             onerror="this.src='/assets/img/placeholder.svg'">
      `;
      container.appendChild(block);
    });

    // Riattiva animazioni GSAP
    if (window.gsap && window.ScrollTrigger) {
      // Cleanup vecchi trigger
      ScrollTrigger.getAll().forEach((t) => t.kill());

      gsap.utils.toArray("#sections-container > section").forEach((sec) => {
        gsap.from(sec, {
          opacity: 0,
          y: 100,
          duration: 1,
          ease: "power2.out",
          scrollTrigger: {
            trigger: sec,
            start: "top 85%",
            end: "bottom 70%",
            toggleActions: "play none none reverse",
          },
        });
      });
    }
  }

  // Esporta renderSections globalmente per lang.js
  window.renderSections = renderSections;

  // Prima renderizzazione
  renderSections(currentLang);

  // Il cambio lingua è gestito da lang.js (applyTranslations → window.renderSections).
});
