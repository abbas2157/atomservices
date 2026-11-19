<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

/** Run $fn against the database; on any DB error log it and return $fallback (public pages must survive a down DB). */
function db_safe(callable $fn, $fallback = null) {
  try {
    return $fn();
  } catch (Throwable $e) {
    error_log('Atom Services DB: ' . $e->getMessage());
    return $fallback;
  }
}

function db_query(string $sql, array $params = []): PDOStatement {
  $stmt = db()->prepare($sql);
  $stmt->execute($params);
  return $stmt;
}

function db_all(string $sql, array $params = []): array {
  return db_query($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array {
  return db_query($sql, $params)->fetch() ?: null;
}

/**
 * Tables and columns the admin dashboard needs (as_settings, as_page_meta, as_team, status/notes
 * on contact_messages). Called from admin.php only, guarded by a schema_version row, so public
 * pages never pay for it. Safe to run repeatedly.
 */
const ADMIN_SCHEMA_VERSION = 3;

function admin_migrate(): void {
  $pdo = db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS as_settings (
    name  VARCHAR(64) NOT NULL PRIMARY KEY,
    value TEXT NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  try {
    $version = (int) $pdo->query("SELECT value FROM as_settings WHERE name = 'schema_version'")->fetchColumn();
  } catch (PDOException $e) {
    $version = 0;
  }
  if ($version >= ADMIN_SCHEMA_VERSION) {
    return;
  }

  // SEO overrides per page. NULL = use the default written in the page file.
  $pdo->exec("CREATE TABLE IF NOT EXISTS as_page_meta (
    path        VARCHAR(100) NOT NULL PRIMARY KEY,
    title       VARCHAR(200) NULL,
    description VARCHAR(400) NULL,
    og_image    VARCHAR(300) NULL,
    schema_json MEDIUMTEXT NULL,
    noindex     TINYINT(1) NULL,
    updated_at  DATETIME NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  // Team members in the leadership section, seeded from the defaults in code (v2);
  // v3 adds the Facebook/Instagram/X/YouTube/website columns to an existing table.
  require_once __DIR__ . '/team_data.php';
  team_install($pdo);

  // Inbox workflow columns on the existing contact_messages table (1060 = column already there).
  foreach ([
    "ALTER TABLE contact_messages ADD COLUMN status VARCHAR(10) NOT NULL DEFAULT 'new'",
    'ALTER TABLE contact_messages ADD COLUMN notes TEXT NULL',
  ] as $sql) {
    try {
      $pdo->exec($sql);
    } catch (PDOException $e) {
      if ((int) ($e->errorInfo[1] ?? 0) !== 1060) {
        throw $e;
      }
    }
  }

  $pdo->prepare("REPLACE INTO as_settings (name, value) VALUES ('schema_version', ?)")
      ->execute([(string) ADMIN_SCHEMA_VERSION]);
}
