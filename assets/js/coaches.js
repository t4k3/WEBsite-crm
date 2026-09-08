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
      fr: "Elle transforme chaque entraînement en opportunité : des services parfaits, répétables et à intensité maximale.",
    },
  },
  {
    name: "Giovanni Guidetti",
    img: "/assets/img/coaches/coach_14.webp",
    quote: {
      it: "La ricezione è un fondamentale difficile: serve talento, ma anche tanto, tanto lavoro. Con la Volleyball Machine uniamo qualità e quantità.",
      en: "Reception is a difficult skill: it takes talent, but also a lot — a lot — of work. With the Volleyball Machine we combine quality and quantity.",
      fr: "La réception est un geste technique difficile : il faut du talent, mais aussi beaucoup, beaucoup de travail. Avec la Volleyball Machine, nous associons qualité et quantité.",
    },
  },
  {
    name: "Daniele Santarelli",
    img: "/assets/img/coaches/coach_07.webp",
    quote: {
      it: "Ho sempre considerato la sparapalloni uno strumento essenziale per la pallavolo, ma la maneggevolezza e la precisione di questa macchina hanno reso il nostro lavoro ancora più semplice.",
      en: "I've always considered the ball machine an essential tool for volleyball, but the handling and precision of this one have made our work even easier.",
      fr: "J’ai toujours considéré la machine à lancer les ballons comme un outil essentiel pour le volley-ball, mais la maniabilité et la précision de celle-ci ont rendu notre travail encore plus simple.",
    },
  },
  {
    name: "Nicola Negro",
    img: "/assets/img/coaches/coach_01.webp",
    quote: {
      it: "Sono molti anni che la uso: è diventata uno strumento essenziale, ci permette di ottimizzare tempi e lavoro.",
      en: "I've been using it for many years: it has become an essential tool, helping us optimise time and effort.",
      fr: "Je l’utilise depuis de nombreuses années : elle est devenue un outil essentiel, elle nous permet d’optimiser le temps et le travail.",
    },
  },
  {
    name: "Gianni Caprara",
    img: "/assets/img/coaches/coach_06.webp",
    quote: {
      it: "Uno strumento utile per gli allenatori di ogni livello, dai professionisti a chi insegna all'Under 13, per consolidare e facilitare l'apprendimento delle tecniche di ricezione e difesa in particolare.",
      en: "A useful tool for coaches at every level, from professionals to those teaching the Under-13s, especially to build and ease the learning of receiving and defensive techniques.",
      fr: "Un outil utile pour les entraîneurs de tous niveaux, des professionnels à ceux qui enseignent aux moins de 13 ans, en particulier pour consolider et faciliter l’apprentissage des techniques de réception et de défense.",
    },
  },
  {
    name: "Alessandro Chiappini",
    img: "/assets/img/coaches/coach_04.webp",
    quote: {
      it: "L'utilizzo della sparapalloni in allenamento ci permette di mantenere un ritmo di lavoro molto elevato grazie alla sua precisione e continuità. La possibilità di effettuare un alto numero di ripetizioni, lavorando su zone specifiche del campo e sulla lateralità, rappresenta un grande valore per lo sviluppo tecnico. Inoltre, la duttilità della macchina consente di allenare in modo efficace diversi fondamentali, adattandosi alle esigenze del lavoro quotidiano. Per noi è ormai diventata una parte integrante dell'allenamento giornaliero.",
      en: "Using the ball machine in training lets us keep a very high work rate thanks to its precision and consistency. Being able to perform a high number of repetitions, working on specific areas of the court and on lateral movement, is a great asset for technical development. What's more, the machine's versatility makes it possible to train several fundamentals effectively, adapting to the needs of daily work. For us it has now become an integral part of everyday training.",
      fr: "L’utilisation de la machine à l’entraînement nous permet de maintenir un rythme de travail très élevé grâce à sa précision et à sa régularité. La possibilité d’effectuer un grand nombre de répétitions, en travaillant sur des zones spécifiques du terrain et sur la latéralité, représente une grande valeur pour le développement technique. De plus, la polyvalence de la machine permet d’entraîner efficacement plusieurs fondamentaux, en s’adaptant aux exigences du travail quotidien. Pour nous, elle fait désormais partie intégrante de l’entraînement journalier.",
    },
  },
  {
    name: "Mick Haley",
    img: "/assets/img/coaches/coach_11.webp",
    quote: {
      it: "Semplicemente magica.",
      en: "Simply magic.",
      fr: "Tout simplement magique.",
    },
  },
  {
    name: "Claudio Busato",
    img: "/assets/img/coaches/coach_12.webp",
    quote: {
      it: "Un vero salto di qualità in allenamento.",
      en: "A real step up in training.",
      fr: "Un véritable saut de qualité à l’entraînement.",
    },
  },
  {
    name: "Marco Sinibaldi",
    img: "/assets/img/coaches/coach_05.webp",
    quote: {
      it: "Trasferisce sicurezza ai giocatori e aiuta ad avere più controllo sulla palla. Semplicemente affidabile.",
      en: "It gives players confidence and helps them control the ball better. Simply reliable.",
      fr: "Elle donne confiance aux joueurs et les aide à mieux contrôler le ballon. Tout simplement fiable.",
    },
  },
  {
    name: "Rossano Bertocco",
    img: "/assets/img/coaches/coach_13.webp",
    quote: {
      it: "Studiata con i pro, ma di aiuto a tutti.",
      en: "Designed with the pros, but helpful for everyone.",
      fr: "Conçue avec les pros, mais utile à tous.",
    },
  },

  {
    name: "Marcello Abbondanza",
    img: "/assets/img/coaches/coach_03.webp",
    quote: {
      it: "La uso in momenti analitici, concentrati in brevi periodi mirati: lì alza intensità e precisione del lavoro e fa davvero la differenza.",
      en: "I use it in focused, analytical moments, concentrated into short targeted bursts: that's where it raises the intensity and precision of the work and really makes the difference.",
      fr: "Je l’utilise lors de séquences analytiques, concentrées sur de courtes périodes ciblées : c’est là qu’elle augmente l’intensité et la précision du travail et qu’elle fait vraiment la différence.",
    },
  },

  {
    name: "Alessandra Campedelli",
    img: "/assets/img/coaches/coach_15.webp",
    quote: {
      it: "Nei contesti in cui ho lavorato — Iran, Pakistan, Tunisia — i settori femminili sono spesso i meno valorizzati, con staff tecnici ridotti all'essenziale. La macchina spara-palloni Takeoff.pro è stata per me uno strumento prezioso: mi ha permesso di sopperire alle mancanze di uno staff completo, garantendo comunque qualità e continuità negli allenamenti.",
      en: "In the contexts where I've worked — Iran, Pakistan, Tunisia — women's programs are often the least valued, with technical staff cut to the bare minimum. The Takeoff.pro ball machine has been a precious tool for me: it let me make up for the lack of a full staff, still ensuring quality and continuity in training.",
      fr: "Dans les contextes où j’ai travaillé — Iran, Pakistan, Tunisie — les sections féminines sont souvent les moins valorisées, avec des staffs techniques réduits à l’essentiel. La machine à lancer les ballons Takeoff.pro a été pour moi un outil précieux : elle m’a permis de pallier l’absence d’un staff complet, tout en garantissant qualité et continuité dans les entraînements.",
    },
  },

  // ===== Sospesi (in attesa della frase reale) =====
  {
    name: "Enrico Barbolini",
    img: "/assets/img/coaches/coach_08.webp",
    active: false,
    quote: {
      it: "Controllo tutto in tempo reale.",
      en: "I control everything in real time.",
      fr: "Je contrôle tout en temps réel.",
    },
  },
  {
    name: "Angelo Lorenzetti",
    img: "/assets/img/coaches/coach_09.webp",
    active: false,
    quote: {
      it: "Intuitiva, la usano tutti subito.",
      en: "Intuitive — everyone picks it up right away.",
      fr: "Intuitive, tout le monde la prend en main immédiatement.",
    },
  },
  {
    name: "Zanin",
    img: "/assets/img/coaches/coach_10.webp",
    active: false,
    quote: {
      it: "Si vede che la conosce chi gioca a volley.",
      en: "You can tell it's made by volleyball people.",
      fr: "On voit qu’elle est conçue par des gens du volley.",
    },
  },
];
