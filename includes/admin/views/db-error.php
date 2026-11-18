<div class="empty">
    <span class="empty-icon"><?= admin_icon('alert') ?></span>
    <h3>Database not connected</h3>
    <p class="muted">The dashboard could not reach MySQL. Check that the database server is running and that the
    credentials in <code>includes/config.php</code> are correct, then reload this page.</p>
    <a href="<?= url('/admin') ?>" class="btn btn-primary"><?= admin_icon('reset', 'icon icon-sm') ?> Try again</a>
</div>
