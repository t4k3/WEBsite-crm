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
      const layout =
        section.side === "right" ? "md:flex-row-reverse" : "md:flex-row";
      const text = section[lang] || section.it; // fallback in italiano

      const block = document.createElement("section");
      block.className = `h-screen flex flex-col ${layout} items-center justify-center gap-10 px-6 bg-gradient-to-b from-black to-gray-900`;
      block.innerHTML = `
        <img src="${section.img}" alt="${text.title}"
             class="w-full md:w-1/2 max-h-[75vh] object-contain drop-shadow-2xl"
             loading="lazy"
             onerror="this.src='/assets/img/placeholder.svg'">
        <div class="text-center md:text-left max-w-lg">
          <h2 class="text-2xl md:text-2xl font-bold mb-4">${text.title}</h2>
          <p class="text-lg text-gray-300">${text.text}</p>
        </div>
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

  // Aggiorna lingua con le bandierine
  document.querySelectorAll(".lang-switch").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const lang = e.target.dataset.lang;
      localStorage.setItem("lang", lang);
      renderSections(lang);
    });
  });
});
