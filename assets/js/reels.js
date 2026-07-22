// ================= REELS INSTAGRAM =================
// Per aggiungere un reel: copia il link dal post Instagram
// (es. https://www.instagram.com/reel/ABC123xyz/) e incollalo qui sotto.
// L'ordine di questo array = ordine in home. Per nasconderne uno,
// commentalo con // davanti oppure aggiungi `active: false`.

const reels = [
  { url: "https://www.instagram.com/p/DZzhCAYuhRY/" },
  { url: "https://www.instagram.com/p/DaS6M3BDacz/" },
  { url: "https://www.instagram.com/p/DaLGvmzNjc4/" },
  { url: "https://www.instagram.com/p/DO21tY-jY5h/" },
  { url: "https://www.instagram.com/p/C3IWSpasvG6/" },
  { url: "https://www.instagram.com/p/DabFecnqmgO/" },
  { url: "https://www.instagram.com/p/DaxJE8sjYDo/" },
];

// ---------- render ----------
function renderReels() {
  const grid = document.getElementById("reels-grid");
  const section = document.getElementById("reels");
  if (!grid || !section) return;

  const list = reels.filter((r) => r.active !== false && r.url);

  // Nessun reel configurato → nascondo tutta la sezione
  if (!list.length) {
    section.style.display = "none";
    return;
  }
  section.style.display = "";

  // Gli embed pesano (uno script + un iframe Meta ciascuno): li costruisco
  // solo quando la sezione entra nello schermo, così la home resta leggera.
  const build = () => {
    if (grid.dataset.loaded) return;
    grid.dataset.loaded = "1";

    grid.innerHTML = list
      .map(
        (r) => `
    <blockquote class="instagram-media w-full"
      data-instgrm-permalink="${r.url}"
      data-instgrm-version="14"
      style="margin:0; max-width:340px; min-width:260px; width:100%; border-radius:1rem;">
    </blockquote>`
      )
      .join("");

    loadInstagramScript();
  };

  if (!("IntersectionObserver" in window)) {
    build();
    return;
  }
  const obs = new IntersectionObserver(
    (entries) => {
      if (entries.some((e) => e.isIntersecting)) {
        build();
        obs.disconnect();
      }
    },
    { rootMargin: "300px" }
  );
  obs.observe(section);
}

// Carica (una sola volta) lo script ufficiale di Instagram che trasforma
// i blockquote negli embed veri. Se è già caricato, ridisegna gli embed.
function loadInstagramScript() {
  if (window.instgrm) {
    window.instgrm.Embeds.process();
    return;
  }
  if (document.getElementById("ig-embed-script")) return;

  const s = document.createElement("script");
  s.id = "ig-embed-script";
  s.async = true;
  s.src = "https://www.instagram.com/embed.js";
  document.body.appendChild(s);
}

window.renderReels = renderReels;
document.addEventListener("DOMContentLoaded", renderReels);
