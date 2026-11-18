<?php /** Messages table. Expects $rows; optional $emptyTitle, $emptyText, $compact (hides the message column). */
$compact ??= false; ?>
<?php if (!$rows): ?>
<div class="empty">
    <span class="empty-icon"><?= admin_icon('inbox') ?></span>
    <h3><?= e($emptyTitle ?? 'No messages yet') ?></h3>
    <p class="muted"><?= e($emptyText ?? 'Meeting requests and enquiries sent through the website forms will appear here.') ?></p>
</div>
<?php else: ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>From</th>
                <th>Topic</th>
<?php if (!$compact): ?>
                <th class="hide-md">Message</th>
<?php endif; ?>
                <th class="hide-sm">Received</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($rows as $q): $href = url('/admin/messages/' . (int) $q['id']); ?>
            <tr class="<?= ($q['status'] ?? 'new') === 'new' ? 'is-new' : '' ?>">
                <td>
                    <a href="<?= $href ?>" class="person">
                        <span class="avatar avatar-sm"><?= e(initials($q['name'])) ?></span>
                        <span><strong><?= e($q['name']) ?></strong><small><?= e($q['email']) ?></small></span>
                    </a>
                </td>
                <td><span class="chip"><?= e(message_type_label($q['subject'])) ?></span><small class="via"><?= e(message_topic($q['subject'])) ?></small></td>
<?php if (!$compact): ?>
                <td class="hide-md"><a href="<?= $href ?>" class="excerpt"><?= e(mb_strimwidth($q['message'], 0, 90, '…')) ?></a></td>
<?php endif; ?>
                <td class="hide-sm nowrap muted" title="<?= e(date('M j, Y g:i A', strtotime($q['created_at']))) ?>"><?= e(time_ago($q['created_at'])) ?></td>
                <td><?= status_badge($q['status'] ?? 'new') ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
