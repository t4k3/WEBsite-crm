// ================= SPECIFICHE TECNICHE =================
// Valori ripresi dalle infografiche del distributore.
// Velocità di lancio: 115 km/h confermato dal titolare il 22/07/2026.
// Gli altri valori sono ancora DA CONFERMARE/CORREGGERE.
// Ogni riga: { it: ["Etichetta", "Valore"], en: ["Label", "Value"] }
const specsData = [
  { it: ["Velocità di lancio", "fino a 115 km/h"], en: ["Launch speed", "up to 115 km/h"], fr: ["Vitesse de lancement", "jusqu’à 115 km/h"] },
  { it: ["Altezza totale", "220–280 cm"],          en: ["Overall height", "220–280 cm"], fr: ["Hauteur totale", "220–280 cm"] },
  { it: ["Inclinazione testa", "regolabile"],      en: ["Head tilt", "adjustable"], fr: ["Inclinaison de la tête", "réglable"] },
  { it: ["Controllo", "Bluetooth (app remota)"],   en: ["Control", "Bluetooth (remote app)"], fr: ["Commande", "Bluetooth (application à distance)"] },
  { it: ["Alimentazione", "a batteria, portatile"], en: ["Power", "battery, portable"], fr: ["Alimentation", "sur batterie, portable"] },
  { it: ["Durata batteria", "5–6 h in Float, 2–3 h a velocità max"], en: ["Battery life", "5–6 h in Float, 2–3 h at max speed"], fr: ["Autonomie", "5–6 h en Float, 2–3 h à vitesse max"] },
  { it: ["Ricarica rapida", "0–80% in 1 ora"],     en: ["Fast charge", "0–80% in 1 hour"], fr: ["Charge rapide", "0–80 % en 1 heure"] },
  { it: ["Rulli", "alluminio liscio"],             en: ["Rollers", "smooth aluminium"], fr: ["Rouleaux", "aluminium lisse"] },
];

function renderSpecs(lang) {
  if (!lang) lang = (localStorage.getItem("lang") || "it").toLowerCase();
  const el = document.getElementById("specs-table");
  if (!el) return;

  const rows = specsData
    .map((s, i) => {
      const [label, value] = s[lang] || s.it;
      const last = i === specsData.length - 1;
      return `<tr class="${last ? "" : "border-b border-gray-200"}">
        <td class="py-3 pr-4 text-gray-500">${label}</td>
        <td class="py-3 text-right font-semibold text-gray-900">${value}</td>
      </tr>`;
    })
    .join("");

  el.innerHTML = `<table class="w-full text-sm md:text-base"><tbody>${rows}</tbody></table>`;
}

// Esporta per lang.js (aggiornamento al cambio lingua)
window.renderSpecs = renderSpecs;

document.addEventListener("DOMContentLoaded", () => renderSpecs());
