CREATE DATABASE IF NOT EXISTS atomservices
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE atomservices;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  email_sent TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin dashboard tables. Also created automatically by admin_migrate() (includes/db.php)
-- the first time a signed-in admin opens /admin, which additionally adds the status/notes
-- columns to contact_messages.

-- Site settings edited in /admin/settings ('site_*') plus 'schema_version'.
CREATE TABLE IF NOT EXISTS as_settings (
  name  VARCHAR(64) NOT NULL PRIMARY KEY,
  value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO overrides per page, edited in /admin/seo. NULL = use the default written in the page file.
CREATE TABLE IF NOT EXISTS as_page_meta (
  path        VARCHAR(100) NOT NULL PRIMARY KEY,
  title       VARCHAR(200) NULL,
  description VARCHAR(400) NULL,
  og_image    VARCHAR(300) NULL,
  schema_json MEDIUMTEXT NULL,
  noindex     TINYINT(1) NULL,
  updated_at  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
