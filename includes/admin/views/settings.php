<?php
/**
 * Site settings editor (/admin/settings). Expects $error.
 * Field definitions live in includes/settings.php (site_setting_fields).
 */
$groups = site_setting_fields();
$current = site_setting_current();
?>
<section class="welcome">
    <div>
        <h2>Site settings</h2>
        <p class="muted">Contact details, WhatsApp numbers, social profiles, app store links and Google — saved here, live on the website immediately.</p>
    </div>
    <a href="<?= url('/') ?>" class="btn btn-ghost" target="_blank" rel="noopener"><?= admin_icon('external') ?> View website</a>
</section>

<?php if (isset($_GET['saved']) && !$error): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Settings saved and live on the website.</p>
<?php endif; ?>
<?php if ($error): ?>
<p class="alert" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" class="settings-form" data-dirty-form>
    <?= csrf_field() ?>

<?php foreach ($groups as [$icon, $title, $intro, $fields]): ?>
    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon($icon) ?> <?= e($title) ?></h3></div>
        <div class="settings-grid">
            <p class="muted settings-intro"><?= e($intro) ?></p>
<?php foreach ($fields as $key => [$label, $type, $placeholder, $help]): ?>
            <div class="seo-field<?= $type === 'textarea' ? ' settings-field-full' : '' ?>">
                <div class="field-top"><label for="set-<?= e($key) ?>"><?= e($label) ?></label></div>
<?php if ($type === 'textarea'): ?>
                <textarea class="text-input" id="set-<?= e($key) ?>" name="<?= e($key) ?>" rows="2" maxlength="500" placeholder="<?= e($placeholder) ?>"><?= e($current[$key]) ?></textarea>
<?php else: ?>
                <input class="text-input" id="set-<?= e($key) ?>" type="<?= $type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text') ?>" name="<?= e($key) ?>" value="<?= e($current[$key]) ?>" placeholder="<?= e($placeholder) ?>" maxlength="500">
<?php endif; ?>
<?php if ($help !== ''): ?>
                <div class="field-foot"><span class="muted"><?= e($help) ?></span></div>
<?php endif; ?>
            </div>
<?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

    <div class="save-bar">
        <span class="save-state" data-dirty-label>All changes saved</span>
        <a href="<?= url('/admin') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= admin_icon('check') ?> Save settings</button>
    </div>
</form>
