<?php
/**
 * Admin shell: sidebar + header + body. Expects $view, $pageTitle, $messages, $adminPath.
 */
$newCount ??= 0;
$nav = [
    ['/admin', 'Dashboard', 'dashboard', $adminPath === '/'],
    ['/admin/messages', 'Messages', 'inbox', str_starts_with($adminPath, '/messages')],
    ['/admin/subscribers', 'Subscribers', 'users', str_starts_with($adminPath, '/subscribers')],
    ['/admin/team', 'Team', 'user', str_starts_with($adminPath, '/team')],
    ['/admin/seo', 'SEO', 'file-text', str_starts_with($adminPath, '/seo')],
    ['/admin/settings', 'Site Settings', 'settings', str_starts_with($adminPath, '/settings')],
];
$user = admin_user();
include __DIR__ . '/head.php';
?>
<body class="admin">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="<?= url('/admin') ?>" class="brand">
                <?= logo_mark('sidebar-mark') ?>
                <span class="brand-name">Atom <em>Services</em></span>
                <span class="brand-tag">Admin</span>
            </a>
            <button type="button" class="icon-btn sidebar-close" data-sidebar-close aria-label="Close menu"><?= admin_icon('x') ?></button>
        </div>

        <nav class="sidebar-nav" aria-label="Admin">
            <p class="nav-label">Menu</p>
<?php foreach ($nav as [$href, $label, $icon, $active]): ?>
            <a href="<?= url($href) ?>" class="nav-link<?= $active ? ' is-active' : '' ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                <?= admin_icon($icon) ?><span><?= e($label) ?></span>
<?php if ($icon === 'inbox' && $newCount): ?>
                <span class="nav-count"><?= $newCount ?></span>
<?php endif; ?>
            </a>
<?php endforeach; ?>

            <p class="nav-label">Website</p>
            <a href="<?= url('/') ?>" class="nav-link" target="_blank" rel="noopener"><?= admin_icon('globe') ?><span>View website</span></a>
        </nav>

        <div class="sidebar-footer">
            <form method="post" action="<?= url('/admin/logout') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="nav-link nav-logout"><?= admin_icon('logout') ?><span>Log out</span></button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="icon-btn menu-btn" data-sidebar-open aria-label="Open menu" aria-controls="sidebar"><?= admin_icon('menu') ?></button>
            <h1 class="topbar-title"><?= e($pageTitle) ?></h1>

            <form class="topbar-search" method="get" action="<?= url('/admin/messages') ?>" role="search">
                <?= admin_icon('search') ?>
                <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search messages…" aria-label="Search messages">
            </form>

            <div class="topbar-user">
                <span class="avatar"><?= e(initials(ADMIN_DISPLAY_NAME)) ?></span>
                <span class="user-meta">
                    <strong><?= e(ADMIN_DISPLAY_NAME) ?></strong>
                    <small>@<?= e($user) ?></small>
                </span>
            </div>
        </header>

        <main class="content">
            <?php include __DIR__ . '/views/' . $view . '.php'; ?>
        </main>
    </div>

    <script>window.ADMIN_BASE = <?= json_encode(SITE_BASE) ?>;</script>
    <script src="<?= admin_asset('/assets/js/admin.js') ?>" defer></script>
</body>
</html>
