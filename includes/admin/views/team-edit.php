<?php
/**
 * Add / edit one team member (/admin/team/new, /admin/team/{id}). Expects $member (null when
 * adding), $error and $posted (the rejected POST, so nothing typed is lost).
 */
$isNew = $member === null;
$base = $member ?? team_blank();
$v = function (string $key, $default = '') use ($posted, $base) {
    if (is_array($posted)) {
        return (string) ($posted[$key] ?? '');
    }
    return (string) ($base[$key] ?? $default);
};
$checked = function (string $key) use ($posted, $base, $isNew): bool {
    if (is_array($posted)) {
        return !empty($posted[$key]);
    }
    return $isNew ? ($key === 'published') : !empty($base[$key]);
};
// The experience is stored as HTML with <b>; the textarea shows it the way it was typed.
$expValue = is_array($posted) ? (string) ($posted['exp'] ?? '') : team_experience_source((string) $base['exp']);
$photo = team_photo_url($base);
?>
<section class="welcome">
    <div>
        <h2><?= $isNew ? 'Add team member' : e($base['name']) ?></h2>
        <p class="muted"><?= $isNew ? 'They appear in the leadership section as soon as you save, unless you untick "Show on the website".' : 'Edit the details shown on the homepage and the About page.' ?></p>
    </div>
    <a href="<?= url('/admin/team') ?>" class="btn btn-ghost"><?= admin_icon('arrow-left') ?> Back to team</a>
</section>

<?php if (isset($_GET['saved']) && !$error): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Saved and live on the website.</p>
<?php endif; ?>
<?php if ($error): ?>
<p class="alert" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" class="settings-form" enctype="multipart/form-data" data-dirty-form>
    <?= csrf_field() ?>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('user') ?> Person</h3></div>
        <div class="settings-grid">
            <div class="seo-field">
                <div class="field-top"><label for="t-name">Full name</label></div>
                <input class="text-input" id="t-name" name="name" type="text" value="<?= e($v('name')) ?>" maxlength="120" required autocomplete="off">
            </div>
            <div class="seo-field">
                <div class="field-top"><label for="t-role">Role</label></div>
                <input class="text-input" id="t-role" name="role" type="text" value="<?= e($v('role')) ?>" maxlength="160" placeholder="Director &amp; CTO" required autocomplete="off">
            </div>
            <div class="seo-field">
                <div class="field-top"><label for="t-rank">Rank label</label></div>
                <input class="text-input" id="t-rank" name="rank" type="text" value="<?= e($v('rank')) ?>" maxlength="40" placeholder="Director" list="team-ranks" autocomplete="off">
                <datalist id="team-ranks"><?php foreach (TEAM_RANKS as $r): ?><option value="<?= e($r) ?>"></option><?php endforeach; ?></datalist>
                <div class="field-foot"><span class="muted">The small line above the name. Leave empty to hide it.</span></div>
            </div>
            <div class="seo-field">
                <div class="field-top"><label for="t-initials">Initials</label></div>
                <input class="text-input" id="t-initials" name="initials" type="text" value="<?= e($v('initials')) ?>" maxlength="3" placeholder="<?= e(team_initials($v('name') ?: 'A B')) ?>" autocomplete="off">
                <div class="field-foot"><span class="muted">Shown until a photo is uploaded. Empty = the first letters of the name.</span></div>
            </div>
            <div class="seo-field settings-field-full">
                <div class="field-top"><label for="t-exp">Experience</label></div>
                <textarea class="text-input" id="t-exp" name="exp" rows="2" maxlength="400" placeholder="&lt;b&gt;14 yrs&lt;/b&gt; as a full-stack developer."><?= e($expValue) ?></textarea>
                <div class="field-foot"><span class="muted">One line under the role. Wrap the years in &lt;b&gt;…&lt;/b&gt; to bold them; any other HTML is shown as plain text.</span></div>
            </div>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('mail') ?> Contact details</h3></div>
        <div class="settings-grid">
            <p class="muted settings-intro">Shown on the person&rsquo;s card as a <code>mailto:</code> and <code>tel:</code> link. Leave a field empty to show nothing, or tick &ldquo;Keep private&rdquo; to store the details here without publishing them.</p>
            <div class="seo-field">
                <div class="field-top"><label for="t-email">Email</label></div>
                <input class="text-input" id="t-email" name="email" type="email" value="<?= e($v('email')) ?>" maxlength="190" placeholder="name@atomservices.org" autocomplete="off">
            </div>
            <div class="seo-field">
                <div class="field-top"><label for="t-phone">Phone</label></div>
                <input class="text-input" id="t-phone" name="phone" type="tel" value="<?= e($v('phone')) ?>" maxlength="40" placeholder="+92 304 112 9252" autocomplete="off">
                <div class="field-foot"><span class="muted">As it should read on the site; the call link uses the digits.</span></div>
            </div>
            <div class="seo-field settings-field-full">
                <label class="check-row"><input type="checkbox" name="contact_private" value="1"<?= $checked('contact_private') ? ' checked' : '' ?>> <span>Keep private &mdash; save the email and phone here, but do not show them on the website</span></label>
            </div>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('share') ?> Profile links</h3></div>
        <div class="settings-grid">
            <p class="muted settings-intro">Full links only (https://…). Each one that is filled in becomes a small link on the person&rsquo;s card, in this order; the rest are simply not shown.</p>
<?php foreach (TEAM_LINK_FIELDS as $key => [$label, $placeholder]): ?>
            <div class="seo-field">
                <div class="field-top"><label for="t-<?= e($key) ?>"><?= e($label) ?></label></div>
                <input class="text-input" id="t-<?= e($key) ?>" name="<?= e($key) ?>" type="url" value="<?= e($v($key)) ?>" maxlength="300" placeholder="<?= e($placeholder) ?>">
            </div>
<?php endforeach; ?>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('image') ?> Photo</h3></div>
        <div class="settings-grid">
            <p class="muted settings-intro">JPEG, PNG or WebP up to 8&nbsp;MB. It is cropped square and saved at 500&times;500 as JPEG and WebP, exactly like <code>tools/build-images.php</code> does. Tall portraits keep the top of the photo, so heads are not cut off.</p>
<?php if (!$isNew): ?>
            <div class="seo-field team-photo-field">
                <div class="photo-preview">
<?php if ($photo !== ''): ?>
                    <img src="<?= e(url('/' . $photo)) ?>" alt="Current photo of <?= e($base['name']) ?>" width="120" height="120">
<?php else: ?>
                    <span class="avatar avatar-lg"><?= e(team_initials($base['name'], $base['initials'])) ?></span>
<?php endif; ?>
                </div>
                <div>
                    <div class="field-top"><label for="t-photo">Upload a photo</label></div>
                    <input class="text-input" id="t-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
<?php if ($photo !== ''): ?>
                    <label class="check-row"><input type="checkbox" name="remove_photo" value="1"> <span>Remove the current photo (show the initials instead)</span></label>
<?php endif; ?>
                </div>
            </div>
<?php else: ?>
            <p class="muted settings-intro">Save this person first, then upload their photo.</p>
<?php endif; ?>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('settings') ?> Placement</h3></div>
        <div class="settings-grid">
            <div class="seo-field">
                <div class="field-top"><label for="t-sort">Position</label></div>
                <input class="text-input" id="t-sort" name="sort" type="number" min="0" max="9999" value="<?= e($v('sort', '0')) ?>">
                <div class="field-foot"><span class="muted">Lowest first. The arrows on the team list change this for you.</span></div>
            </div>
            <div class="seo-field">
                <label class="check-row"><input type="checkbox" name="published" value="1"<?= $checked('published') ? ' checked' : '' ?>> <span>Show on the website</span></label>
                <div class="field-foot"><span class="muted">Unticked keeps the person here but off the homepage and About page.</span></div>
            </div>
        </div>
    </section>

    <div class="save-bar">
        <span class="save-state" data-dirty-label>All changes saved</span>
        <a href="<?= url('/admin/team') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= admin_icon('check') ?> <?= $isNew ? 'Add to the team' : 'Save changes' ?></button>
    </div>
</form>
