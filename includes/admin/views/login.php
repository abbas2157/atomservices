<?php
/** Admin sign-in page. Expects $error, $username. */
$pageTitle = 'Sign in';
include __DIR__ . '/../head.php';
?>
<body class="login">
    <section class="login-art">
        <div class="login-art-inner">
            <?= logo_mark('login-mark') ?>
            <p class="login-quote">Smarter finance, better tomorrow.</p>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <span class="login-logo-atom"><?= logo_mark('login-mark-sm') ?> Atom Services</span>
            <h1>Welcome back</h1>
            <p class="muted">Sign in to manage messages, SEO and site settings.</p>

<?php if (!admin_login_enabled()): ?>
            <div class="alert" role="alert">Admin login isn't set up yet. Run <code>php tools/set-admin-password.php</code> on the server to create a password.</div>
<?php elseif ($error): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
<?php endif; ?>

            <form method="post" action="<?= url('/admin/login') ?>" class="login-form">
                <?= csrf_field() ?>
                <label class="field">
                    <span>Username</span>
                    <span class="input-wrap"><?= admin_icon('user') ?><input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus></span>
                </label>
                <label class="field">
                    <span>Password</span>
                    <span class="input-wrap"><?= admin_icon('lock') ?><input type="password" name="password" id="admin-password" autocomplete="current-password" required><button type="button" class="pw-toggle" aria-controls="admin-password" aria-label="Show password" aria-pressed="false" data-password-toggle><span class="pw-show"><?= admin_icon('eye') ?></span><span class="pw-hide" hidden><?= admin_icon('eye-off') ?></span></button></span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">Sign in <?= admin_icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <a href="<?= url('/') ?>" class="back-link"><?= admin_icon('arrow-left') ?> Back to website</a>
        </div>
    </section>
    <script src="<?= admin_asset('/assets/js/admin.js') ?>" defer></script>
</body>
</html>
