// ================= SPECIFICHE TECNICHE =================
// Valori ripresi dalle infografiche del distributore — DA CONFERMARE/CORREGGERE.
// Ogni riga: { it: ["Etichetta", "Valore"], en: ["Label", "Value"] }
const specsData = [
  { it: ["Velocità di lancio", "fino a 120 km/h"], en: ["Launch speed", "up to 120 km/h"] },
  { it: ["Altezza totale", "220–280 cm"],          en: ["Overall height", "220–280 cm"] },
  { it: ["Altezza dello stativo", "110 cm"],       en: ["Stand height", "110 cm"] },
  { it: ["Profondità macchina", "120 cm"],         en: ["Machine depth", "120 cm"] },
  { it: ["Base (ingombro)", "90 cm"],              en: ["Base (footprint)", "90 cm"] },
  { it: ["Inclinazione testa", "regolabile"],      en: ["Head tilt", "adjustable"] },
  { it: ["Controllo", "Bluetooth (app remota)"],   en: ["Control", "Bluetooth (remote app)"] },
  { it: ["Alimentazione", "a batteria, portatile"], en: ["Power", "battery, portable"] },
  { it: ["Durata batteria", "5–6 h in Float, 2–3 h a velocità max"], en: ["Battery life", "5–6 h in Float, 2–3 h at max speed"] },
  { it: ["Ricarica rapida", "0–80% in 1 ora"],     en: ["Fast charge", "0–80% in 1 hour"] },
  { it: ["Rulli", "alluminio liscio"],             en: ["Rollers", "smooth aluminium"] },
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
