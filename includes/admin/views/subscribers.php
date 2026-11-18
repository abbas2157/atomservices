<?php
/** Newsletter subscribers. */
$subscribers = admin_subscribers();
$search = trim((string) ($_GET['q'] ?? ''));
$rows = $search === ''
    ? $subscribers
    : array_values(array_filter($subscribers, fn($s) => mb_stripos($s['email'], $search) !== false));
?>
<?php if (isset($_GET['deleted'])): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Subscriber removed.</p>
<?php endif; ?>
<div class="card">
    <form class="filters" method="get" action="<?= url('/admin/subscribers') ?>">
        <label class="input-wrap grow"><?= admin_icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search email addresses" aria-label="Search"></label>
        <button type="submit" class="btn btn-primary">Filter</button>
<?php if ($search !== ''): ?>
        <a href="<?= url('/admin/subscribers') ?>" class="btn btn-ghost">Clear</a>
<?php endif; ?>
        <a href="<?= url('/admin/subscribers/export') ?>" class="btn btn-ghost" title="Download all subscribers as a CSV file for Excel"><?= admin_icon('download') ?> Export CSV</a>
    </form>
    <p class="result-count muted"><?= count($rows) ?> of <?= count($subscribers) ?> <?= count($subscribers) === 1 ? 'subscriber' : 'subscribers' ?></p>
<?php if (!$rows): ?>
    <div class="empty">
        <span class="empty-icon"><?= admin_icon('users') ?></span>
        <h3><?= $search !== '' ? 'No matching subscribers' : 'No subscribers yet' ?></h3>
        <p class="muted"><?= $search !== '' ? 'Try a different search.' : 'Newsletter signups from the website will appear here.' ?></p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th class="hide-sm">Subscribed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($rows as $s): ?>
                <tr>
                    <td>
                        <span class="person">
                            <span class="avatar avatar-sm"><?= e(initials($s['email'])) ?></span>
                            <span><strong><?= e($s['email']) ?></strong></span>
                        </span>
                    </td>
                    <td class="hide-sm nowrap muted" title="<?= e(date('M j, Y g:i A', strtotime($s['created_at']))) ?>"><?= e(time_ago($s['created_at'])) ?></td>
                    <td class="nowrap actions-cell">
                        <form method="post" action="<?= url('/admin/subscribers/' . (int) $s['id'] . '/delete') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm btn-danger" data-confirm="Remove <?= e($s['email']) ?> from the newsletter list?"><?= admin_icon('trash', 'icon icon-sm') ?> Remove</button>
                        </form>
                    </td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
