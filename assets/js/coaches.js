// ================= COACHES DATA =================
// Un oggetto per coach: nome, foto e frase in tutte le lingue.
// L'ordine di questo array = ordine nel carosello (solo gli attivi).
// Per sospendere un coach: aggiungi `active: false,`.
const coaches = [
  {
    name: "Vincenzo Mallia",
    img: "/assets/img/coaches/coach_02.webp",
    quote: {
      it: "Trasforma ogni allenamento in un'opportunità: servizi perfetti, ripetibili e alla massima intensità.",
      en: "It turns every training session into an opportunity: perfect, repeatable serves at maximum intensity.",
    },
  },
  {
    name: "Giovanni Guidetti",
    img: "/assets/img/coaches/coach_14.webp",
    quote: {
      it: "La ricezione è un fondamentale difficile: serve talento, ma anche tanto, tanto lavoro. Con la Volleyball Machine uniamo qualità e quantità.",
      en: "Reception is a difficult skill: it takes talent, but also a lot — a lot — of work. With the Volleyball Machine we combine quality and quantity.",
    },
  },
  {
    name: "Daniele Santarelli",
    img: "/assets/img/coaches/coach_07.webp",
    quote: {
      it: "Float e spin sembrano veri.",
      en: "Float and spin feel real.",
    },
  },
  {
    name: "Nicola Negro",
    img: "/assets/img/coaches/coach_01.webp",
    quote: {
      it: "Sono molti anni che la uso: è diventata uno strumento essenziale, ci permette di ottimizzare tempi e lavoro.",
      en: "I've been using it for many years: it has become an essential tool, helping us optimise time and effort.",
    },
  },
  {
    name: "Gianni Caprara",
    img: "/assets/img/coaches/coach_06.webp",
    quote: {
      it: "Uno strumento utile per gli allenatori di ogni livello, dai professionisti a chi insegna all'Under 13, per consolidare e facilitare l'apprendimento delle tecniche di ricezione e difesa in particolare.",
      en: "A useful tool for coaches at every level, from professionals to those teaching the Under-13s, especially to build and ease the learning of receiving and defensive techniques.",
    },
  },
  {
    name: "Alessandro Chiappini",
    img: "/assets/img/coaches/coach_04.webp",
    quote: {
      it: "L'utilizzo della sparapalloni in allenamento ci permette di mantenere un ritmo di lavoro molto elevato grazie alla sua precisione e continuità. La possibilità di effettuare un alto numero di ripetizioni, lavorando su zone specifiche del campo e sulla lateralità, rappresenta un grande valore per lo sviluppo tecnico. Inoltre, la duttilità della macchina consente di allenare in modo efficace diversi fondamentali, adattandosi alle esigenze del lavoro quotidiano. Per noi è ormai diventata una parte integrante dell'allenamento giornaliero.",
      en: "Using the ball machine in training lets us keep a very high work rate thanks to its precision and consistency. Being able to perform a high number of repetitions, working on specific areas of the court and on lateral movement, is a great asset for technical development. What's more, the machine's versatility makes it possible to train several fundamentals effectively, adapting to the needs of daily work. For us it has now become an integral part of everyday training.",
    },
  },
  {
    name: "Mic Halley",
    img: "/assets/img/coaches/coach_11.webp",
    quote: {
      it: "Semplicemente magica.",
      en: "Simply magic.",
    },
  },
  {
    name: "Claudio Busato",
    img: "/assets/img/coaches/coach_12.webp",
    quote: {
      it: "Un vero salto di qualità in allenamento.",
      en: "A real step up in training.",
    },
  },
  {
    name: "Marco Sinibaldi",
    img: "/assets/img/coaches/coach_05.webp",
    quote: {
      it: "Trasferisce sicurezza ai giocatori e aiuta ad avere più controllo sulla palla. Semplicemente affidabile.",
      en: "It gives players confidence and helps them control the ball better. Simply reliable.",
    },
  },
  {
    name: "Rossano Bertocco",
    img: "/assets/img/coaches/coach_13.webp",
    quote: {
      it: "Studiata con i pro, ma di aiuto a tutti.",
      en: "Designed with the pros, but helpful for everyone.",
    },
  },

  // ===== Sospesi (in attesa della frase reale) =====
  {
    name: "Marcello Abbondanza",
    img: "/assets/img/coaches/coach_03.webp",
    active: false,
    quote: {
      it: "Ora le battute le tira la macchina.",
      en: "The machine handles the serves now.",
    },
  },
  {
    name: "Enrico Barbolini",
    img: "/assets/img/coaches/coach_08.webp",
    active: false,
    quote: {
      it: "Controllo tutto in tempo reale.",
      en: "I control everything in real time.",
    },
  },
  {
    name: "Angelo Lorenzetti",
    img: "/assets/img/coaches/coach_09.webp",
    active: false,
    quote: {
      it: "Intuitiva, la usano tutti subito.",
      en: "Intuitive — everyone picks it up right away.",
    },
  },
  {
    name: "Zanin",
    img: "/assets/img/coaches/coach_10.webp",
    active: false,
    quote: {
      it: "Si vede che la conosce chi gioca a volley.",
      en: "You can tell it's made by volleyball people.",
    },
  },
];
