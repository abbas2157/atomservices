<?php
/** All messages with search and filters. Expects $messages. */
$search = trim((string) ($_GET['q'] ?? ''));
$type = (string) ($_GET['type'] ?? '');
$status = (string) ($_GET['status'] ?? '');

$rows = array_values(array_filter($messages, function ($q) use ($search, $type, $status) {
    if ($type !== '' && message_type($q['subject']) !== $type) return false;
    if ($status !== '' && ($q['status'] ?? 'new') !== $status) return false;
    if ($search !== '') {
        $haystack = implode(' ', [$q['name'], $q['email'], $q['subject'], $q['message']]);
        if (mb_stripos($haystack, $search) === false) return false;
    }
    return true;
}));
$filtered = $search !== '' || $type !== '' || $status !== '';
if ($filtered) {
    $emptyTitle = 'No matching messages';
    $emptyText = 'Try a different search or clear the filters.';
}
?>
<?php if (isset($_GET['deleted'])): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Message deleted.</p>
<?php endif; ?>
<div class="card">
    <form class="filters" method="get" action="<?= url('/admin/messages') ?>">
        <label class="input-wrap grow"><?= admin_icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search name, email, topic or message" aria-label="Search"></label>
        <select name="type" aria-label="Form">
            <option value="">All forms</option>
<?php foreach (MESSAGE_TYPES as $value => [, $label]): ?>
            <option value="<?= e($value) ?>"<?= $type === $value ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
        <select name="status" aria-label="Status">
            <option value="">All statuses</option>
<?php foreach (MESSAGE_STATUSES as $value => $label): ?>
            <option value="<?= e($value) ?>"<?= $status === $value ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
<?php if ($filtered): ?>
        <a href="<?= url('/admin/messages') ?>" class="btn btn-ghost">Clear</a>
<?php endif; ?>
        <a href="<?= url('/admin/messages/export') ?>" class="btn btn-ghost" title="Download all messages as a CSV file for Excel"><?= admin_icon('download') ?> Export CSV</a>
    </form>
    <p class="result-count muted"><?= count($rows) ?> of <?= count($messages) ?> <?= count($messages) === 1 ? 'message' : 'messages' ?></p>
    <?php include __DIR__ . '/_table.php'; ?>
</div>
