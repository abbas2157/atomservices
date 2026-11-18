<?php
/** Single message detail. Expects $message. */
$topic = message_topic($message['subject']);
$typeLabel = message_type_label($message['subject']);
$firstName = explode(' ', trim($message['name']))[0];
?>
<a href="<?= url('/admin/messages') ?>" class="back-link"><?= admin_icon('arrow-left') ?> All messages</a>

<?php if (isset($_GET['saved'])): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Notes saved.</p>
<?php endif; ?>

<div class="grid-2 detail">
    <div class="detail-main">
        <article class="card">
            <div class="card-head">
                <div class="person">
                    <span class="avatar"><?= e(initials($message['name'])) ?></span>
                    <span><strong><?= e($message['name']) ?></strong><small><?= e(date('l, M j, Y \a\t g:i A', strtotime($message['created_at']))) ?></small></span>
                </div>
                <?= status_badge($message['status'] ?? 'new') ?>
            </div>
            <div class="message"><?= nl2br(e($message['message'])) ?></div>
            <div class="card-actions">
                <a class="btn btn-primary" href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject'] . ' — Atom Services') ?>&body=<?= rawurlencode("Hi {$firstName},\n\n") ?>"><?= admin_icon('mail') ?> Reply by email</a>
                <a class="btn btn-ghost" href="<?= e(whatsapp_url("Hi {$firstName}, this is Atom Services about your {$typeLabel}.", WHATSAPP_NUMBER)) ?>" target="_blank" rel="noopener"><?= admin_icon('message') ?> WhatsApp</a>
            </div>
        </article>

        <form method="post" action="<?= url('/admin/messages/' . (int) $message['id'] . '/notes') ?>" class="card notes-card">
            <?= csrf_field() ?>
            <div class="card-head"><h3>Internal notes</h3><span class="muted hide-sm">Only visible in the dashboard</span></div>
            <div class="seo-card-body">
                <textarea class="text-input" name="notes" rows="4" placeholder="e.g. Replied Monday — wants a GITEX slot on Tue 8 Dec, follow up Thursday."><?= e($message['notes'] ?? '') ?></textarea>
                <div class="notes-actions"><button type="submit" class="btn btn-ghost btn-sm"><?= admin_icon('check') ?> Save notes</button></div>
            </div>
        </form>
    </div>

    <aside class="card">
        <div class="card-head"><h3>Details</h3></div>
        <dl class="details">
            <dt>Form</dt><dd><span class="chip"><?= e($typeLabel) ?></span></dd>
            <dt>Topic</dt><dd><?= e($topic) ?></dd>
            <dt>Email</dt><dd><a href="mailto:<?= e($message['email']) ?>" class="link break"><?= e($message['email']) ?></a></dd>
            <dt>Received</dt><dd><?= e(time_ago($message['created_at'])) ?></dd>
            <dt>IP address</dt><dd><?= e($message['ip_address'] ?: '—') ?></dd>
            <dt>Email alert</dt><dd><?= $message['email_sent'] ? 'Sent to ' . e(FORM_RECIPIENTS) : '<span class="muted">Not sent (mail server unavailable)</span>' ?></dd>
        </dl>
        <form method="post" action="<?= url('/admin/messages/' . (int) $message['id'] . '/status') ?>" class="status-form">
            <?= csrf_field() ?>
            <span class="muted">Status</span>
            <div class="segmented segmented-3" role="radiogroup" aria-label="Status">
<?php foreach (MESSAGE_STATUSES as $value => $label): ?>
                <label><input type="radio" name="status" value="<?= $value ?>"<?= ($message['status'] ?? 'new') === $value ? ' checked' : '' ?> onchange="this.form.submit()"><span><?= $label ?></span></label>
<?php endforeach; ?>
            </div>
            <noscript><button type="submit" class="btn btn-ghost btn-sm">Update</button></noscript>
        </form>
        <form method="post" action="<?= url('/admin/messages/' . (int) $message['id'] . '/delete') ?>" class="delete-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-sm btn-danger" data-confirm="Delete this message permanently? This cannot be undone."><?= admin_icon('trash') ?> Delete message</button>
        </form>
    </aside>
</div>
