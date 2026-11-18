<?php
/** SEO overview: every editable page with its current title/description and checks. */
$all = seo_all_meta();
$rows = [];
$issues = 0;
$edited = 0;
foreach (SEO_PAGES as $path => [$file, $label]) {
    $d = seo_page_defaults($path);
    $o = array_filter($all[$path] ?? [], fn($v) => $v !== null);
    $title = $o['title'] ?? $d['title'];
    $desc = $o['description'] ?? $d['description'];
    $schema = array_key_exists('schema_json', $o) ? $o['schema_json'] : $d['schema'];
    $row = [
        'path' => $path, 'label' => $label, 'title' => $title, 'description' => $desc,
        'title_state' => seo_length_state(mb_strlen($title), SEO_TITLE_RANGE),
        'desc_state' => seo_length_state(mb_strlen($desc), SEO_DESCRIPTION_RANGE),
        'schema' => $schema !== '' && $schema !== null,
        'noindex' => !empty($o['noindex']),
        'edits' => count(array_intersect_key($o, array_flip(['title', 'description', 'og_image', 'schema_json', 'noindex']))),
        'updated' => $o['updated_at'] ?? null,
    ];
    $issues += ($row['title_state'] !== 'ok') + ($row['desc_state'] !== 'ok') + (int) $row['noindex'];
    $edited += $row['edits'] ? 1 : 0;
    $rows[] = $row;
}
$editUrl = fn(string $p) => url('/admin/seo/edit?path=' . rawurlencode($p));
$lengthBadge = function (string $state, int $length, array $range): string {
    $hint = $state === 'ok' ? 'Good length' : "Aim for {$range[0]}–{$range[1]} characters";
    return '<span class="len len-' . $state . '" title="' . e($hint) . '">' . $length . '</span>';
};
?>
<div class="stats stats-3">
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon('file-text') ?></span>
        <div><p class="stat-label">Pages</p><p class="stat-value"><?= count($rows) ?></p><p class="stat-hint">Title, description, social image &amp; schema</p></div>
    </div>
    <div class="card stat">
        <span class="stat-icon<?= $issues ? ' stat-icon-warn' : '' ?>"><?= admin_icon($issues ? 'alert' : 'check') ?></span>
        <div><p class="stat-label">SEO checks to review</p><p class="stat-value"><?= $issues ?></p><p class="stat-hint">Title &amp; description length, hidden pages</p></div>
    </div>
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon('tag') ?></span>
        <div><p class="stat-label">Customised pages</p><p class="stat-value"><?= $edited ?></p><p class="stat-hint">Changed here instead of in the code</p></div>
    </div>
</div>

<div class="card seo-table-card">
    <div class="card-head">
        <h3>Pages</h3>
        <a class="link hide-sm" href="<?= url('/sitemap.xml') ?>" target="_blank" rel="noopener">View sitemap <?= admin_icon('external', 'icon icon-sm') ?></a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th class="hide-md">Meta title</th>
                    <th title="Title length">Title</th>
                    <th title="Description length">Desc.</th>
                    <th class="hide-sm">Schema</th>
                    <th class="hide-sm">Google</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($rows as $row): ?>
                <tr>
                    <td><a href="<?= e($editUrl($row['path'])) ?>" class="page-cell"><strong><?= e($row['label']) ?></strong><small><?= e($row['path']) ?></small></a></td>
                    <td class="hide-md"><span class="excerpt"><?= e($row['title']) ?></span></td>
                    <td><?= $lengthBadge($row['title_state'], mb_strlen($row['title']), SEO_TITLE_RANGE) ?></td>
                    <td><?= $lengthBadge($row['desc_state'], mb_strlen($row['description']), SEO_DESCRIPTION_RANGE) ?></td>
                    <td class="hide-sm"><?= $row['schema'] ? '<span class="chip">JSON-LD</span>' : '<span class="muted">—</span>' ?></td>
                    <td class="hide-sm"><?= $row['noindex'] ? '<span class="len len-warn" title="Hidden from search engines">Hidden</span>' : '<span class="len len-ok">Indexed</span>' ?></td>
                    <td class="nowrap actions-cell">
<?php if ($row['edits']): ?>
                        <span class="chip chip-accent" title="Last edited <?= e(time_ago($row['updated'])) ?>">Edited</span>
<?php endif; ?>
                        <a href="<?= e($editUrl($row['path'])) ?>" class="btn btn-ghost btn-sm">Edit</a>
                    </td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
