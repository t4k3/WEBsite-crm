// ================= CAROUSEL =================

// 🔸 Recupera la lingua corrente dal localStorage
function getCurrentLang() {
  return (localStorage.getItem("lang") || "it").toLowerCase();
}

// 🔸 Frase del coach nella lingua corrente (fallback italiano)
function coachQuote(coach, lang) {
  return (coach.quote && (coach.quote[lang] || coach.quote.it)) || "";
}

// 🔸 Modale "frase completa" — creata una sola volta, riusata
function ensureCoachModal() {
  let modal = document.getElementById("coach-modal");
  if (modal) return modal;

  modal = document.createElement("div");
  modal.id = "coach-modal";
  modal.className =
    "fixed inset-0 z-[100] bg-black/50 p-4 flex items-center justify-center";
  modal.style.display = "none";
  modal.innerHTML = `
    <div class="bg-white rounded-2xl max-w-md w-full p-8 text-center relative shadow-2xl">
      <button type="button" class="coach-modal-close absolute top-2 right-4 text-gray-400 hover:text-gray-700 text-3xl leading-none" aria-label="Chiudi">&times;</button>
      <img class="coach-modal-img w-28 h-28 rounded-full mx-auto mb-4 object-cover" alt="" />
      <h3 class="coach-modal-name text-xl font-semibold mb-3"></h3>
      <p class="coach-modal-quote text-gray-700 italic leading-relaxed"></p>
    </div>`;
  document.body.appendChild(modal);

  const close = () => (modal.style.display = "none");
  modal.querySelector(".coach-modal-close").addEventListener("click", close);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) close();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });
  modal.querySelector(".coach-modal-img").addEventListener("error", function () {
    this.src = "/assets/img/placeholder.svg";
  });
  return modal;
}

function openCoachModal(coach, quote) {
  const modal = ensureCoachModal();
  const img = modal.querySelector(".coach-modal-img");
  img.src = coach.img;
  img.alt = coach.name;
  img.className =
    "coach-modal-img mx-auto mb-4 object-cover w-36 h-36 rounded-2xl";
  modal.querySelector(".coach-modal-name").textContent = coach.name;
  modal.querySelector(".coach-modal-quote").textContent = "“" + quote + "”";
  modal.style.display = "flex";
}

// Timer dell'auto-rotazione (uno solo, ricreato a ogni render)
let autoTimer = null;

// 🔸 Render principale
function renderCarousel(lang) {
  if (!lang) lang = getCurrentLang();
  const container = document.getElementById("coach-carousel");
  if (!container) return;

  const fallback = "/assets/img/placeholder.svg";

  // Rigenera le card. La frase è troncata (line-clamp): si vede l'inizio,
  // poi al click/tap si apre la card completa con tutta la frase.
  // Mostra solo gli allenatori attivi (gli altri sono sospesi con active:false
  // finché non arriva la loro frase reale).
  const list = coaches.filter((c) => c.active !== false);

  container.innerHTML = list
    .map((coach) => {
      const quote = coachQuote(coach, lang);
      const more = lang === "en" ? "Read more" : "Leggi tutto";
      const imgCls = coach.imgClass || "w-24 h-24 rounded-xl";
      return `
    <div class="coach-card absolute w-[26%] min-w-[240px] bg-white border border-gray-200 rounded-2xl shadow-lg p-5 text-center transition-all duration-700 opacity-0 cursor-pointer">
      <img src="${coach.img}" alt="${coach.name}"
           class="coach-photo ${imgCls} mx-auto mb-3 object-cover opacity-0 transition-opacity duration-500" />
      <h3 class="text-lg font-semibold mb-1">${coach.name}</h3>
      <p class="text-gray-600 italic text-sm line-clamp-3">“${quote}”</p>
      <span class="block mt-2 text-xs text-[#0096e0] font-medium">${more}</span>
    </div>
  `;
    })
    .join("");

  // Gestione immagini (fade-in + fallback)
  const imgs = container.querySelectorAll(".coach-photo");
  imgs.forEach((img) => {
    img.addEventListener("error", () => {
      if (!img.dataset.fallbackApplied) {
        img.dataset.fallbackApplied = "true";
        img.src = fallback;
      }
    });
    img.addEventListener("load", () => {
      img.style.opacity = "1";
    });
    setTimeout(() => (img.style.opacity = "1"), 1000);
  });

  // === LOGICA CAROSELLO ===
  const cards = [...container.querySelectorAll(".coach-card")];
  let index = 0;

  // Click/tap sulla card → apre la frase completa
  cards.forEach((card, i) => {
    const coach = list[i];
    card.addEventListener("click", () => openCoachModal(coach, coachQuote(coach, lang)));
  });

  const updateCarousel = () => {
    cards.forEach((card, i) => {
      const offset = (i - index + cards.length) % cards.length;

      if (offset === 0) {
        card.style.transform = "translateX(0%) scale(1)";
        card.style.opacity = "1";
        card.style.zIndex = "3";
        card.style.pointerEvents = "auto";
      } else if (offset === 1 || offset === cards.length - 1) {
        card.style.transform = `translateX(${offset === 1 ? "120%" : "-120%"}) scale(0.9)`;
        card.style.opacity = "0.6";
        card.style.zIndex = "2";
        card.style.pointerEvents = "auto";
      } else {
        card.style.transform = `translateX(${offset * 100}%) scale(0.8)`;
        card.style.opacity = "0";
        card.style.zIndex = "1";
        card.style.pointerEvents = "none";
      }
    });
  };

  const next = document.getElementById("carousel-next");
  const prev = document.getElementById("carousel-prev");

  if (next && prev) {
    next.onclick = () => {
      index = (index + 1) % cards.length;
      updateCarousel();
    };
    prev.onclick = () => {
      index = (index - 1 + cards.length) % cards.length;
      updateCarousel();
    };
  }

  updateCarousel();

  // Auto-rotazione ogni 3 secondi (in pausa quando il mouse è sopra il carosello)
  clearInterval(autoTimer);
  const advance = () => {
    index = (index + 1) % cards.length;
    updateCarousel();
  };
  autoTimer = setInterval(advance, 3000);
  container.onmouseenter = () => clearInterval(autoTimer);
  container.onmouseleave = () => {
    clearInterval(autoTimer);
    autoTimer = setInterval(advance, 3000);
  };
}

// === Esporta globalmente per lang.js ===
window.renderCoaches = renderCarousel;

// === Inizializzazione ===
document.addEventListener("DOMContentLoaded", () => renderCarousel());

// Il cambio lingua è gestito da lang.js (applyTranslations → window.renderCoaches).
