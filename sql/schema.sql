-- ==========================================
--  WAZLLEY — schema CRM / ordini (MySQL 8)
--  Importare via phpMyAdmin (scheda "Importa" o "SQL").
-- ==========================================

-- Trattativa: un record che evolve da lead (preventivo) a ordine.
CREATE TABLE IF NOT EXISTS deals (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token           CHAR(32) NOT NULL UNIQUE,                 -- link/identificativo personale
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status          VARCHAR(32) NOT NULL DEFAULT 'nuovo',

  -- Step 1: contatto / preventivo
  contact_name    VARCHAR(120) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  phone           VARCHAR(40)  NULL,
  country         VARCHAR(60)  NULL,
  quantity        INT UNSIGNED NOT NULL DEFAULT 1,
  variant         VARCHAR(40)  NULL,                        -- es. colore
  notes           TEXT         NULL,
  quoted_price    DECIMAL(10,2) NULL,
  currency        CHAR(3) NOT NULL DEFAULT 'EUR',

  -- Step 2: fatturazione (IT + internazionale)
  customer_type   ENUM('azienda','privato') NULL,
  company_name    VARCHAR(160) NULL,
  vat_number      VARCHAR(40)  NULL,                        -- P.IVA / VAT
  tax_code        VARCHAR(40)  NULL,                        -- codice fiscale
  sdi_code        VARCHAR(7)   NULL,                        -- codice destinatario SDI
  pec             VARCHAR(190) NULL,
  eori            VARCHAR(40)  NULL,
  bill_address    VARCHAR(190) NULL,
  bill_city       VARCHAR(90)  NULL,
  bill_zip        VARCHAR(20)  NULL,
  bill_province   VARCHAR(90)  NULL,
  bill_country    VARCHAR(60)  NULL,
  ship_same       TINYINT(1) NOT NULL DEFAULT 1,
  ship_address    VARCHAR(190) NULL,
  ship_city       VARCHAR(90)  NULL,
  ship_zip        VARCHAR(20)  NULL,
  ship_province   VARCHAR(90)  NULL,
  ship_country    VARCHAR(60)  NULL,

  -- Gestione vendita
  tracking_number VARCHAR(80)  NULL,
  admin_notes     TEXT         NULL,

  -- Meta
  consent         TINYINT(1) NOT NULL DEFAULT 0,
  ip              VARCHAR(45)  NULL,
  user_agent      VARCHAR(255) NULL,

  INDEX idx_status (status),
  INDEX idx_email (email),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Storico cambi di stato (per il CRM)
CREATE TABLE IF NOT EXISTS deal_history (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deal_id     INT UNSIGNED NOT NULL,
  changed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  old_status  VARCHAR(32) NULL,
  new_status  VARCHAR(32) NULL,
  note        VARCHAR(255) NULL,
  changed_by  VARCHAR(60) NULL,
  INDEX idx_deal (deal_id),
  CONSTRAINT fk_history_deal FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Utenti del pannello admin (login)
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
