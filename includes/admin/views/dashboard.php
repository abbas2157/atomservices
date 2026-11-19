<?php
/** Dashboard overview. Expects $messages, $newCount. */
$weekAgo = strtotime('-7 days');
$byType = [];
foreach ($messages as $q) {
    $label = message_type_label($q['subject']);
    $byType[$label] = ($byType[$label] ?? 0) + 1;
}
arsort($byType);
$subscribers = db_safe(fn() => admin_subscribers(), []);
$stats = [
    ['Total messages', count($messages), 'inbox', 'All time'],
    ['New', $newCount, 'sparkle', 'Awaiting a reply'],
    ['This week', count(array_filter($messages, fn($q) => strtotime($q['created_at']) >= $weekAgo)), 'calendar', 'Last 7 days'],
    ['Subscribers', count($subscribers), 'users', 'Newsletter signups'],
];
$rows = array_slice($messages, 0, 6);
$compact = true;

// SEO health summary for the side card.
$all = seo_all_meta();
$seoIssues = 0;
foreach (SEO_PAGES as $p => $meta) {
    $d = seo_page_defaults($p);
    $o = array_filter($all[$p] ?? [], fn($v) => $v !== null);
    $seoIssues += (seo_length_state(mb_strlen($o['title'] ?? $d['title']), SEO_TITLE_RANGE) !== 'ok')
        + (seo_length_state(mb_strlen($o['description'] ?? $d['description']), SEO_DESCRIPTION_RANGE) !== 'ok')
        + (int) !empty($o['noindex']);
}

$bars = function (array $data): void {
    if (!$data) {
        echo '<p class="muted pad">No messages yet.</p>';
        return;
    }
    $max = max($data);
    echo '<ul class="bars">';
    foreach (array_slice($data, 0, 6, true) as $label => $count) {
        echo '<li><span class="bar-label">', e($label), '<strong>', $count, '</strong></span>',
             '<span class="bar"><span style="width: ', round($count / $max * 100), '%"></span></span></li>';
    }
    echo '</ul>';
};
?>
<section class="welcome">
    <div>
        <h2>Welcome back</h2>
        <p class="muted">Here&rsquo;s what&rsquo;s happening on atomservices.org.</p>
    </div>
    <a href="<?= url('/admin/messages') ?>" class="btn btn-primary">View all messages <?= admin_icon('arrow-right', 'icon icon-sm') ?></a>
</section>

<section class="stats">
<?php foreach ($stats as [$label, $value, $icon, $hint]): ?>
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon($icon) ?></span>
        <div>
            <p class="stat-label"><?= e($label) ?></p>
            <p class="stat-value<?= is_string($value) ? ' stat-value-text' : '' ?>"><?= e((string) $value) ?></p>
            <p class="stat-hint"><?= e($hint) ?></p>
        </div>
    </div>
<?php endforeach; ?>
</section>

<section class="grid-2">
    <div class="card">
        <div class="card-head">
            <h3>Recent messages</h3>
            <a href="<?= url('/admin/messages') ?>" class="link">See all <?= admin_icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php include __DIR__ . '/_table.php'; ?>
    </div>

    <div class="dash-side">
        <div class="card">
            <div class="card-head">
                <h3>Site setup</h3>
                <a href="<?= url('/admin/settings') ?>" class="link">Settings <?= admin_icon('arrow-right', 'icon icon-sm') ?></a>
            </div>
            <?php
            $socialSet = count(array_filter(SOCIAL_LINKS));
            $appsSet = array_sum(array_map(fn($stores) => count(array_filter($stores)), APP_LINKS));
            $appsTotal = array_sum(array_map('count', APP_LINKS));
            $team = team_members(true);
            $teamShown = count(array_filter($team, fn($m) => $m['published']));
            $setup = [
                ['SEO checks', $seoIssues === 0, $seoIssues === 0 ? 'All good' : $seoIssues . ' to review'],
                ['Team section', $teamShown > 0, $teamShown . ' of ' . count($team) . ' shown'],
                ['Social profiles', $socialSet > 0, $socialSet . ' of ' . count(SOCIAL_LINKS) . ' linked'],
                ['App store links', $appsSet > 0, $appsSet . ' of ' . $appsTotal . ' live'],
                ['Google Analytics', GA4_MEASUREMENT_ID !== '', GA4_MEASUREMENT_ID !== '' ? GA4_MEASUREMENT_ID : 'Off'],
                ['Search Console', GOOGLE_SITE_VERIFICATION !== '', GOOGLE_SITE_VERIFICATION !== '' ? 'Verified tag set' : 'Not verified'],
            ];
            ?>
            <ul class="setup-list">
<?php foreach ($setup as [$label, $done, $hint]): ?>
                <li>
                    <span class="setup-state <?= $done ? 'ok' : 'todo' ?>"><?= admin_icon($done ? 'check' : 'alert', 'icon') ?></span>
                    <span><?= e($label) ?></span>
                    <span class="muted"><?= e($hint) ?></span>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
        <div class="card">
            <div class="card-head"><h3>Messages by form</h3></div>
            <?php $bars($byType); ?>
        </div>
        <div class="card">
            <div class="card-head">
                <h3>SEO</h3>
                <a href="<?= url('/admin/seo') ?>" class="link">Open <?= admin_icon('arrow-right', 'icon icon-sm') ?></a>
            </div>
            <p class="muted pad" style="padding-top: 16px;"><?= count(SEO_PAGES) ?> editable pages · <?= $seoIssues ?> check<?= $seoIssues === 1 ? '' : 's' ?> to review. Titles, descriptions, social images and structured data.</p>
        </div>
    </div>
</section>
