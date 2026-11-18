<?php
/** SEO editor for one page. Expects $seoPath, $error, $posted (form values after a failed save). */
$d = seo_page_defaults($seoPath);
$o = seo_page_meta($seoPath);
$pick = fn(string $field, string $key) => $posted[$field] ?? ($o[$key] ?? $d[$field]);
$title = $pick('title', 'title');
$description = $pick('description', 'description');
$ogImage = $pick('og_image', 'og_image');
$schema = $posted['schema'] ?? (array_key_exists('schema_json', $o) ? ($o['schema_json'] === '' ? '[]' : $o['schema_json']) : $d['schema']);
$noindex = $posted !== null ? !empty($posted['noindex']) : !empty($o['noindex']);
$liveUrl = SITE_URL . $seoPath;
// Preview own images from this server, so it works before the live domain is up.
$ogPreview = str_starts_with($ogImage, SITE_URL . '/') ? url(substr($ogImage, strlen(SITE_URL))) : (str_starts_with($ogImage, '/') ? url($ogImage) : $ogImage);
?>
<a href="<?= url('/admin/seo') ?>" class="back-link"><?= admin_icon('arrow-left') ?> SEO</a>

<div class="editor-head">
    <div><p class="muted"><?= e($seoPath) ?> · <?= e(SEO_PAGES[$seoPath][0]) ?><?= isset($o['updated_at']) ? ' · last edited ' . e(time_ago($o['updated_at'])) : '' ?></p></div>
    <div class="editor-tools">
        <a class="btn btn-ghost" href="<?= url($seoPath === '/' ? '/' : $seoPath) ?>" target="_blank" rel="noopener"><?= admin_icon('external') ?> View page</a>
    </div>
</div>

<?php if (isset($_GET['saved']) && !$error): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Changes saved and live on the website.</p>
<?php endif; ?>
<?php if ($error): ?>
<p class="alert" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" class="seo-form" data-dirty-form>
    <?= csrf_field() ?>

    <section class="card seo-card">
        <div class="card-head"><h3><?= admin_icon('search') ?> Search appearance</h3></div>
        <div class="seo-card-body seo-meta">
            <div class="seo-meta-fields">
                <div class="seo-field" data-field>
                    <div class="field-top">
                        <label for="meta-title">Meta title</label>
                        <span class="counter" data-counter-for="meta-title" data-min="<?= SEO_TITLE_RANGE[0] ?>" data-max="<?= SEO_TITLE_RANGE[1] ?>"></span>
                    </div>
                    <input class="text-input" id="meta-title" name="title" value="<?= e($title) ?>" data-default="<?= e($d['title']) ?>" data-serp="title" maxlength="200">
                    <div class="field-foot"><span class="muted">The clickable headline in Google, and the title in link previews. Aim for <?= SEO_TITLE_RANGE[0] ?>–<?= SEO_TITLE_RANGE[1] ?> characters.</span><button type="button" class="reset-btn" data-reset><?= admin_icon('reset', 'icon icon-sm') ?> Default</button></div>
                </div>
                <div class="seo-field" data-field>
                    <div class="field-top">
                        <label for="meta-description">Meta description</label>
                        <span class="counter" data-counter-for="meta-description" data-min="<?= SEO_DESCRIPTION_RANGE[0] ?>" data-max="<?= SEO_DESCRIPTION_RANGE[1] ?>"></span>
                    </div>
                    <textarea class="text-input" id="meta-description" name="description" rows="3" data-default="<?= e($d['description']) ?>" data-serp="description" data-autosize maxlength="400"><?= e($description) ?></textarea>
                    <div class="field-foot"><span class="muted">The summary under the title in Google, WhatsApp and LinkedIn previews. Aim for <?= SEO_DESCRIPTION_RANGE[0] ?>–<?= SEO_DESCRIPTION_RANGE[1] ?> characters.</span><button type="button" class="reset-btn" data-reset><?= admin_icon('reset', 'icon icon-sm') ?> Default</button></div>
                </div>
                <label class="check-row">
                    <input type="checkbox" name="noindex" value="1"<?= $noindex ? ' checked' : '' ?>>
                    <span><strong>Hide this page from Google</strong> <span class="muted">(adds <code>noindex</code> and removes it from the sitemap)</span></span>
                </label>
            </div>
            <div class="serp" aria-label="Google search preview">
                <p class="serp-label">Google preview</p>
                <div class="serp-site">
                    <span class="serp-favicon"><img src="<?= admin_asset('/assets/img/favicon.png') ?>" alt="" width="18" height="18"></span>
                    <span><strong>Atom Services</strong><small><?= e(seo_breadcrumb_url($seoPath)) ?></small></span>
                </div>
                <p class="serp-title" data-serp-out="title"><?= e($title) ?></p>
                <p class="serp-desc" data-serp-out="description"><?= e($description) ?></p>
            </div>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head">
            <h3><?= admin_icon('share') ?> Link preview (WhatsApp, Facebook, LinkedIn)</h3>
            <a class="link hide-sm" href="https://developers.facebook.com/tools/debug/?q=<?= rawurlencode($liveUrl) ?>" target="_blank" rel="noopener">Refresh Facebook cache <?= admin_icon('external', 'icon icon-sm') ?></a>
        </div>
        <div class="seo-card-body seo-meta">
            <div class="seo-meta-fields">
                <div class="seo-field" data-field>
                    <div class="field-top"><label for="og-image">Social image URL</label></div>
                    <input class="text-input" id="og-image" name="og_image" value="<?= e($ogImage) ?>" data-default="<?= e($d['og_image']) ?>" data-og-image maxlength="300" placeholder="https://atomservices.org/assets/img/og-image.png">
                    <div class="field-foot"><span class="muted">1200 × 630 px JPG or PNG, under 300 KB. Upload it to <code>assets/img/</code> and paste its address, or start the path with <code>/</code>.</span><button type="button" class="reset-btn" data-reset><?= admin_icon('reset', 'icon icon-sm') ?> Default</button></div>
                </div>
            </div>
            <div class="og-card" aria-label="Link preview">
                <img src="<?= e($ogPreview) ?>" alt="" data-og-preview data-site-url="<?= e(SITE_URL) ?>" width="1200" height="630">
                <div class="og-card-body">
                    <small><?= e(preg_replace('#^https?://#', '', SITE_URL)) ?></small>
                    <strong data-serp-out="title"><?= e($title) ?></strong>
                    <span data-serp-out="description"><?= e($description) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="card seo-card">
        <div class="card-head">
            <h3><?= admin_icon('code') ?> Schema markup (JSON-LD)</h3>
            <a class="link hide-sm" href="https://search.google.com/test/rich-results?url=<?= rawurlencode($liveUrl) ?>" target="_blank" rel="noopener">Test live page <?= admin_icon('external', 'icon icon-sm') ?></a>
        </div>
        <div class="seo-card-body">
            <div class="seo-field" data-field>
                <div class="field-top">
                    <label for="meta-schema">Structured data for this page</label>
                    <span class="json-state" data-json-state></span>
                </div>
                <textarea class="text-input code-input" id="meta-schema" name="schema" rows="12" spellcheck="false" data-json data-autosize data-default="<?= e($d['schema']) ?>" placeholder='{ "@context": "https://schema.org", "@type": "WebPage", "name": "…" }'><?= e($schema) ?></textarea>
                <div class="field-foot">
                    <span class="muted">One JSON object or a list of objects (Organization, WebPage, FAQPage…). It replaces the default Organization schema on this page. Leave empty to use the default; enter <code>[]</code> for no schema. Only describe things that are true: Google penalises fake ratings. The FAQs page also emits its own FAQPage schema, which this box does not touch.</span>
                    <button type="button" class="reset-btn" data-reset><?= admin_icon('reset', 'icon icon-sm') ?> Default</button>
                </div>
            </div>
        </div>
    </section>

    <div class="save-bar">
        <span class="save-state" data-dirty-label>All changes saved</span>
        <a href="<?= url('/admin/seo') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= admin_icon('check') ?> Save changes</button>
    </div>
</form>
