// ================= CAROUSEL =================

// 🔸 Recupera la lingua corrente dal localStorage
function getCurrentLang() {
  return (localStorage.getItem("lang") || "it").toLowerCase();
}

// 🔸 Render principale
function renderCarousel(lang) {
  // Usa la lingua passata o quella corrente
  if (!lang) lang = getCurrentLang();
  const container = document.getElementById("coach-carousel");
  if (!container) return;

  const fallback = "/assets/img/placeholder.svg";
  const list = coaches[lang] || coaches.it;

  // Rigenera tutte le card dinamicamente
  container.innerHTML = list
    .map(
      (coach) => `
    <div class="coach-card absolute w-[28%] min-w-[280px] bg-gray-900 rounded-2xl shadow-lg p-6 text-center transition-all duration-700 opacity-0">
      <img src="${coach.img}" alt="${coach.name}"
           class="coach-photo w-32 h-32 rounded-full mx-auto mb-4 object-cover opacity-0 transition-opacity duration-500" />
      <h3 class="text-xl font-semibold mb-2">${coach.name}</h3>
      <p class="text-gray-300 italic">${coach.quote}</p>
    </div>
  `,
    )
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

  const updateCarousel = () => {
    cards.forEach((card, i) => {
      const offset = (i - index + cards.length) % cards.length;

      if (offset === 0) {
        // Card centrale
        card.style.transform = "translateX(0%) scale(1)";
        card.style.opacity = "1";
        card.style.zIndex = "3";
      } else if (offset === 1 || offset === cards.length - 1) {
        // Laterali visibili
        card.style.transform = `translateX(${offset === 1 ? "120%" : "-120%"}) scale(0.9)`;
        card.style.opacity = "0.6";
        card.style.zIndex = "2";
      } else {
        // Nascoste
        card.style.transform = `translateX(${offset * 100}%) scale(0.8)`;
        card.style.opacity = "0";
        card.style.zIndex = "1";
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
}

// === Esporta globalmente per lang.js ===
window.renderCoaches = renderCarousel;

// === Inizializzazione ===
document.addEventListener("DOMContentLoaded", () => renderCarousel());

// === Ricarica carosello dopo cambio lingua ===
document.querySelectorAll(".lang-switch").forEach((btn) => {
  btn.addEventListener("click", () => {
    const newLang = btn.dataset.lang;
    localStorage.setItem("lang", newLang);
    document.documentElement.setAttribute("data-lang", newLang);
    setTimeout(() => renderCarousel(newLang), 300);
  });
});
