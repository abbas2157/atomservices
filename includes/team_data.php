<?php
/**
 * Team members: the data behind the leadership section (includes/team.php) and the
 * /admin/team editor.
 *
 * Same pattern as includes/settings.php and includes/seo_meta.php: the rows live in the
 * as_team table, and the hard-coded defaults below are used whenever the database is
 * unavailable (or before the admin tables exist), so public pages survive a down DB.
 * The defaults are also the seed rows written by team_install() on the first admin visit.
 */

require_once __DIR__ . '/site.php';
if (is_file(__DIR__ . '/config.php')) {
  require_once __DIR__ . '/db.php';
}

const TEAM_PHOTO_SIZE = 500;            // square photos, as tools/build-images.php builds them
const TEAM_PHOTO_MAX_BYTES = 8388608;   // 8 MB upload cap
const TEAM_PHOTO_DIR = __DIR__ . '/../assets/img/team/';
const TEAM_RANKS = ['Founder', 'Director', 'Technology', 'Finance', 'Growth', 'Operations', 'Advisor'];
/**
 * Profile links on a team card: as_team column => [label, placeholder]. This list drives the
 * table columns, the /admin/team form, validation and the order the links appear on the card,
 * so a new network only has to be added here (admin_migrate() adds the column).
 */
const TEAM_LINK_FIELDS = [
  'linkedin' => ['LinkedIn', 'https://www.linkedin.com/in/…'],
  'facebook' => ['Facebook', 'https://www.facebook.com/…'],
  'instagram' => ['Instagram', 'https://www.instagram.com/…'],
  'twitter' => ['X (Twitter)', 'https://x.com/…'],
  'youtube' => ['YouTube', 'https://www.youtube.com/@…'],
  'github' => ['GitHub', 'https://github.com/…'],
  'website' => ['Website', 'https://…'],
];
const TEAM_NUMBER_WORDS = [1 => 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve'];

/**
 * The leadership as first written in code. 'exp' is trusted HTML (only <b>, for the years);
 * admin-entered experience is run through team_experience_html() before it is stored.
 */
function team_defaults(): array {
  $rows = [
    ['Adnan Zaka',                'AZ', 'Founder',    'Founder & CEO',                              'Engineering background. <b>10 yrs</b> managing a family product financing business.', 'team-1', 'https://www.linkedin.com/in/adnan-zaka/', ''],
    ['Mudassar Abbas',            'MA', 'Director',   'Director & CTO',                             '<b>14 yrs</b> as a full-stack developer.', 'team-2', 'https://www.linkedin.com/in/mudassar-abbas-8267b8113/', 'https://github.com/abbas2157'],
    ['Muhammad Sohaib Chaudhary', 'SC', 'Technology', 'Tech Consultancy & Implementation',          '<b>11 yrs</b> implementing tech in companies.', 'team-6', '', ''],
    ['Muhammad Javed Rafique',    'JR', 'Technology', 'Chief Application Technologist',             '<b>9 yrs</b> experience.', 'team-3', 'https://www.linkedin.com/in/muhammad-javed-rafique-902672216', 'https://github.com/Muhammadjaved7209'],
    ['Muhammad Ahmed Chaudhary',  'MC', 'Director',   'Director, Chief Marketing & Growth Officer', '<b>3–4 yrs</b> experience.', 'team-4', '', ''],
    ['Gulshair Murtaza',          'GM', 'Finance',    'Strategic Finance & Risk Control',           '<b>3–4 yrs</b> experience.', 'team-5', '', ''],
  ];
  $members = [];
  foreach ($rows as $i => [$name, $initials, $rank, $role, $exp, $photo, $linkedin, $github]) {
    $members[] = team_member([
      'id' => $i + 1,
      'name' => $name,
      'initials' => $initials,
      'rank' => $rank,
      'role' => $role,
      'exp' => $exp,
      'linkedin' => $linkedin,
      'github' => $github,
      'photo' => $photo,
      'published' => true,
      'sort' => ($i + 1) * 10,
    ]);
  }
  return $members;
}

/** An empty member, used by the "Add team member" form. This is the canonical member shape. */
function team_blank(): array {
  return [
    'id' => null, 'name' => '', 'initials' => '', 'rank' => '', 'role' => '', 'exp' => '',
    'email' => '', 'phone' => '',
  ] + array_fill_keys(array_keys(TEAM_LINK_FIELDS), '') + [
    'photo' => '', 'contact_private' => false, 'published' => true, 'sort' => 0,
  ];
}

/** $values on top of team_blank(), so every member has the same keys in the same order. */
function team_member(array $values): array {
  return array_replace(team_blank(), array_intersect_key($values, team_blank()));
}

/** One as_team row in the shape the rest of the site uses. */
function team_row_to_member(array $r): array {
  $links = [];
  foreach (array_keys(TEAM_LINK_FIELDS) as $key) {
    $links[$key] = (string) ($r[$key] ?? '');
  }
  return team_member($links + [
    'id' => (int) $r['id'],
    'name' => (string) $r['name'],
    'initials' => (string) $r['initials'],
    'rank' => (string) $r['rank_label'],
    'role' => (string) $r['role'],
    'exp' => (string) $r['experience'],
    'email' => (string) $r['email'],
    'phone' => (string) $r['phone'],
    'photo' => (string) $r['photo'],
    'contact_private' => (bool) $r['contact_private'],
    'published' => (bool) $r['is_published'],
    'sort' => (int) $r['sort_order'],
  ]);
}

/**
 * Team members in display order. $includeHidden is for the dashboard; public pages ask for
 * published members only. Falls back to team_defaults() when the table is missing or the DB
 * is down — an empty table is a deliberate state and stays empty.
 */
function team_members(bool $includeHidden = false): array {
  static $cache = [];
  $key = $includeHidden ? 'all' : 'public';
  if (isset($cache[$key])) {
    return $cache[$key];
  }
  $rows = function_exists('db_safe')
    ? db_safe(fn() => db_all('SELECT * FROM as_team ORDER BY sort_order ASC, id ASC'), null)
    : null;
  $members = $rows === null ? team_defaults() : array_map('team_row_to_member', $rows);
  if (!$includeHidden) {
    $members = array_values(array_filter($members, fn($m) => $m['published']));
  }
  return $cache[$key] = $members;
}

function team_find(int $id): ?array {
  $row = db_one('SELECT * FROM as_team WHERE id = ?', [$id]);
  return $row ? team_row_to_member($row) : null;
}

/** "Six" for 6, "26" for 26 — used in the team section intro. */
function team_count_word(int $n): string {
  return TEAM_NUMBER_WORDS[$n] ?? (string) $n;
}

/** Up to three uppercase letters: the given initials, else the first letters of the name. */
function team_initials(string $name, string $given = ''): string {
  $given = mb_strtoupper((string) preg_replace('/[^\p{L}]/u', '', $given));
  if ($given !== '') {
    return mb_substr($given, 0, 3);
  }
  $parts = array_values(array_filter(preg_split('/\s+/u', trim($name)) ?: []));
  $letters = array_map(fn($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
  return mb_strtoupper(implode('', $letters)) ?: '?';
}

/**
 * Admin-entered experience as safe HTML: everything is escaped except <b>/<strong>,
 * which become <b> (the years are shown in bold on the card).
 */
function team_experience_html(string $raw): string {
  $raw = (string) preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $raw);
  $text = strip_tags($raw, '<b><strong>');
  $text = trim((string) preg_replace('/\s+/u', ' ', $text));
  $html = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  return (string) preg_replace('#&lt;(/?)(?:b|strong)\s*&gt;#i', '<$1b>', $html);
}

/**
 * The stored experience HTML turned back into what the admin typed, so the editor round-trips
 * (the <b> tags stay visible, and &amp; becomes & again instead of escaping once more per save).
 */
function team_experience_source(string $html): string {
  $tagged = (string) preg_replace('#<(/?)b>#i', "$1b", $html);
  return str_replace(["", ""], ['<', '>'], htmlspecialchars_decode($tagged, ENT_QUOTES));
}

/** tel: href for a displayed number: digits only, keeping a leading +. */
function team_tel_href(string $phone): string {
  $digits = (string) preg_replace('/[^0-9]/', '', $phone);
  return 'tel:' . (str_starts_with(trim($phone), '+') ? '+' : '') . $digits;
}

/**
 * Public contact links for a card: [['type', 'label', 'href'], …].
 * Empty fields are left out, and "contact_private" hides both from the website.
 */
function team_member_contacts(array $m): array {
  if (!empty($m['contact_private'])) {
    return [];
  }
  $out = [];
  if (!empty($m['email'])) {
    $out[] = ['type' => 'email', 'label' => (string) $m['email'], 'href' => 'mailto:' . $m['email']];
  }
  if (!empty($m['phone'])) {
    $out[] = ['type' => 'phone', 'label' => (string) $m['phone'], 'href' => team_tel_href((string) $m['phone'])];
  }
  return $out;
}

/** Profile links for a card: label => URL, in TEAM_LINK_FIELDS order, empty ones left out. */
function team_member_links(array $m): array {
  $links = [];
  foreach (TEAM_LINK_FIELDS as $key => [$label]) {
    if (!empty($m[$key])) {
      $links[$label] = (string) $m[$key];
    }
  }
  return $links;
}

/** Small decorative icon for a contact link on a team card ('email' or 'phone'). */
function team_icon(string $type): string {
  $paths = [
    'email' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
  ];
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$type] ?? '') . '</svg>';
}

/** Site-relative photo URL with a ?v= cache buster, or '' when the member has no photo file. */
function team_photo_url(array $m, string $ext = 'jpg'): string {
  $slug = (string) ($m['photo'] ?? '');
  if ($slug === '' || !preg_match('/^[a-z0-9-]+$/i', $slug)) {
    return '';
  }
  $file = TEAM_PHOTO_DIR . $slug . '.' . $ext;
  return is_file($file) ? 'assets/img/team/' . $slug . '.' . $ext . '?v=' . filemtime($file) : '';
}

// ── Admin: validation and saving ──────────────────────────────────────────────

/** Validate the /admin/team form. Returns the cleaned member, or null with $error set. */
function team_clean(array $post, ?string &$error): ?array {
  $text = fn($v, int $max) => mb_substr(trim((string) preg_replace('/\s+/u', ' ', (string) $v)), 0, $max);

  $name = $text($post['name'] ?? '', 120);
  if (mb_strlen($name) < 2) {
    $error = 'Name is required — nothing was saved.';
    return null;
  }
  $role = $text($post['role'] ?? '', 160);
  if ($role === '') {
    $error = 'Role is required (for example "Director & CTO") — nothing was saved.';
    return null;
  }

  $email = $text($post['email'] ?? '', 190);
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email is not a valid address — nothing was saved.';
    return null;
  }

  $phone = $text($post['phone'] ?? '', 40);
  if ($phone !== '' && !preg_match('/^\+?[0-9][0-9 ()\-]{6,24}$/', $phone)) {
    $error = 'Phone must be 7–25 digits, optionally with +, spaces, brackets or dashes — nothing was saved.';
    return null;
  }

  $links = [];
  foreach (TEAM_LINK_FIELDS as $key => [$label]) {
    $url = $text($post[$key] ?? '', 300);
    if ($url !== '' && !preg_match('#^https?://\S+$#', $url)) {
      $error = $label . ' must be a full link starting with https:// — nothing was saved.';
      return null;
    }
    $links[$key] = $url;
  }

  return team_member($links + [
    'name' => $name,
    'initials' => team_initials($name, (string) ($post['initials'] ?? '')),
    'rank' => $text($post['rank'] ?? '', 40),
    'role' => $role,
    'exp' => mb_substr(team_experience_html((string) ($post['exp'] ?? '')), 0, 400),
    'email' => $email,
    'phone' => $phone,
    'contact_private' => !empty($post['contact_private']),
    'published' => !empty($post['published']),
    'sort' => max(0, min(9999, (int) ($post['sort'] ?? 0))),
  ]);
}

/**
 * Create or update a member, then handle the photo upload / removal.
 * Returns the member id, or false with $error set.
 */
function team_save(?int $id, array $post, ?array $file, ?string &$error) {
  $m = team_clean($post, $error);
  if ($m === null) {
    return false;
  }
  // column => value, built from the member shape so a new link field needs no change here.
  $columns = ['name' => $m['name'], 'initials' => $m['initials'], 'rank_label' => $m['rank'], 'role' => $m['role'],
    'experience' => $m['exp'], 'email' => $m['email'], 'phone' => $m['phone']];
  foreach (array_keys(TEAM_LINK_FIELDS) as $key) {
    $columns[$key] = $m[$key];
  }
  $columns += ['contact_private' => (int) $m['contact_private'], 'is_published' => (int) $m['published'], 'sort_order' => $m['sort']];
  $names = array_keys($columns);

  try {
    if ($id === null) {
      if ($m['sort'] === 0) {
        $columns['sort_order'] = ((int) db_query('SELECT COALESCE(MAX(sort_order), 0) FROM as_team')->fetchColumn()) + 10;
      }
      db_query(
        'INSERT INTO as_team (' . implode(', ', $names) . ', photo, updated_at) VALUES ('
          . implode(', ', array_fill(0, count($names), '?')) . ", '', NOW())",
        array_values($columns)
      );
      $id = (int) db()->lastInsertId();
    } else {
      db_query(
        'UPDATE as_team SET ' . implode(' = ?, ', $names) . ' = ?, updated_at = NOW() WHERE id = ?',
        [...array_values($columns), $id]
      );
    }
  } catch (PDOException $e) {
    error_log('team_save: ' . $e->getMessage());
    $error = 'Could not save to the database. Please try again.';
    return false;
  }

  if (!empty($post['remove_photo'])) {
    db_query("UPDATE as_team SET photo = '' WHERE id = ?", [$id]);
  }
  if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    // The details are saved either way; a failed photo leaves $error set for the caller.
    $slug = team_save_photo($file, $id, $error);
    if ($slug !== null) {
      db_query('UPDATE as_team SET photo = ? WHERE id = ?', [$slug, $id]);
    }
  }
  return $id;
}

/**
 * Store an uploaded photo as assets/img/team/team-<id>.jpg + .webp, cropped square at
 * 500×500 like tools/build-images.php. Returns the slug, or null with $error set.
 */
function team_save_photo(array $file, int $id, ?string &$error): ?string {
  $codes = [
    UPLOAD_ERR_INI_SIZE => 'The photo is larger than this server allows.',
    UPLOAD_ERR_FORM_SIZE => 'The photo is too large.',
    UPLOAD_ERR_PARTIAL => 'The photo was only partly uploaded. Please try again.',
    UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder for uploads.',
    UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded photo.',
  ];
  if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
    $error = $codes[$file['error']] ?? 'The photo could not be uploaded.';
    return null;
  }
  if (($file['size'] ?? 0) > TEAM_PHOTO_MAX_BYTES) {
    $error = 'The photo must be smaller than 8 MB.';
    return null;
  }
  $tmp = (string) ($file['tmp_name'] ?? '');
  if (!is_uploaded_file($tmp)) {
    $error = 'The photo could not be read. Please try again.';
    return null;
  }
  $info = @getimagesize($tmp);
  $readers = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
  if (!$info || !isset($readers[$info[2]])) {
    $error = 'The photo must be a JPEG, PNG or WebP image.';
    return null;
  }
  if (!function_exists('imagecreatetruecolor')) {
    $error = 'This server cannot resize images (PHP GD is not installed). Add the photo to assets/img/team/ instead.';
    return null;
  }
  $src = @$readers[$info[2]]($tmp);
  if (!$src) {
    $error = 'The photo could not be read. Please try a different file.';
    return null;
  }
  $slug = 'team-' . $id;
  $out = team_square($src, TEAM_PHOTO_SIZE);
  $ok = @imagejpeg($out, TEAM_PHOTO_DIR . $slug . '.jpg', 80) && @imagewebp($out, TEAM_PHOTO_DIR . $slug . '.webp', 78);
  imagedestroy($src);
  imagedestroy($out);
  if (!$ok) {
    $error = 'The photo could not be saved. Check that assets/img/team/ is writable.';
    return null;
  }
  return $slug;
}

/**
 * Crop to a square and resize — the same crop as tools/build-images.php: wide photos are
 * taken from the centre, tall portraits keep the top so heads are never cut off.
 */
function team_square(GdImage $src, int $size): GdImage {
  $w = imagesx($src);
  $h = imagesy($src);
  $side = min($w, $h);
  $out = imagecreatetruecolor($size, $size);
  imagecopyresampled($out, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) * 0.1), $size, $size, $side, $side);
  return $out;
}

/** Remove a member. Photo files are left in assets/img/team/ so nothing is lost by accident. */
function team_delete(int $id): void {
  db_query('DELETE FROM as_team WHERE id = ?', [$id]);
}

/** Swap a member with the one above ('up') or below ('down') in display order. */
function team_move(int $id, string $direction): void {
  $me = team_find($id);
  if (!$me) {
    return;
  }
  $neighbour = $direction === 'up'
    ? db_one('SELECT * FROM as_team WHERE sort_order < ? OR (sort_order = ? AND id < ?) ORDER BY sort_order DESC, id DESC LIMIT 1', [$me['sort'], $me['sort'], $id])
    : db_one('SELECT * FROM as_team WHERE sort_order > ? OR (sort_order = ? AND id > ?) ORDER BY sort_order ASC, id ASC LIMIT 1', [$me['sort'], $me['sort'], $id]);
  if (!$neighbour) {
    return;
  }
  $other = team_row_to_member($neighbour);
  if ($other['sort'] === $me['sort']) { // sharing a position: nudge them apart first
    $other['sort'] += $direction === 'up' ? -1 : 1;
  }
  db_query('UPDATE as_team SET sort_order = ? WHERE id = ?', [$other['sort'], $id]);
  db_query('UPDATE as_team SET sort_order = ? WHERE id = ?', [$me['sort'], $other['id']]);
}

/**
 * Create the as_team table, add any profile-link column that is new since the table was made,
 * and seed the people from team_defaults() when the table is brand new.
 */
function team_install(PDO $pdo): void {
  $linkColumns = '';
  foreach (array_keys(TEAM_LINK_FIELDS) as $key) {
    $linkColumns .= "    $key VARCHAR(300) NOT NULL DEFAULT '',\n";
  }
  $pdo->exec("CREATE TABLE IF NOT EXISTS as_team (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    initials        VARCHAR(4) NOT NULL DEFAULT '',
    rank_label      VARCHAR(40) NOT NULL DEFAULT '',
    role            VARCHAR(160) NOT NULL DEFAULT '',
    experience      VARCHAR(400) NOT NULL DEFAULT '',
    email           VARCHAR(190) NOT NULL DEFAULT '',
    phone           VARCHAR(40) NOT NULL DEFAULT '',
$linkColumns    photo           VARCHAR(60) NOT NULL DEFAULT '',
    contact_private TINYINT(1) NOT NULL DEFAULT 0,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY sort_idx (sort_order, id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  // Tables created before a network was added to TEAM_LINK_FIELDS (1060 = column already there).
  foreach (array_keys(TEAM_LINK_FIELDS) as $key) {
    try {
      $pdo->exec("ALTER TABLE as_team ADD COLUMN $key VARCHAR(300) NOT NULL DEFAULT ''");
    } catch (PDOException $e) {
      if ((int) ($e->errorInfo[1] ?? 0) !== 1060) {
        throw $e;
      }
    }
  }

  if ((int) $pdo->query('SELECT COUNT(*) FROM as_team')->fetchColumn() > 0) {
    return;
  }
  $links = array_keys(TEAM_LINK_FIELDS);
  $names = array_merge(['id', 'name', 'initials', 'rank_label', 'role', 'experience', 'email', 'phone'],
    $links, ['photo', 'contact_private', 'is_published', 'sort_order']);
  $stmt = $pdo->prepare('INSERT INTO as_team (' . implode(', ', $names) . ', updated_at) VALUES ('
    . implode(', ', array_fill(0, count($names), '?')) . ', NOW())');
  foreach (team_defaults() as $m) {
    $values = [$m['id'], $m['name'], $m['initials'], $m['rank'], $m['role'], $m['exp'], $m['email'], $m['phone']];
    foreach ($links as $key) {
      $values[] = $m[$key];
    }
    $stmt->execute([...$values, $m['photo'], (int) $m['contact_private'], (int) $m['published'], $m['sort']]);
  }
}
