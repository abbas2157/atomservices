<?php
/** Team members (/admin/team): the leadership section on the homepage and the About page. */
$members = team_members(true);
$published = array_values(array_filter($members, fn($m) => $m['published']));
$withPhoto = array_values(array_filter($members, fn($m) => team_photo_url($m) !== ''));
$last = count($members) - 1;
?>
<section class="welcome">
    <div>
        <h2>Team</h2>
        <p class="muted">The leadership section on the homepage and the About page. Changes are live on the website immediately.</p>
    </div>
    <a href="<?= url('/admin/team/new') ?>" class="btn btn-primary"><?= admin_icon('user') ?> Add team member</a>
</section>

<?php if (isset($_GET['deleted'])): ?>
<p class="notice notice-ok" role="status"><?= admin_icon('check') ?> Team member removed. Their photo file is still in assets/img/team/.</p>
<?php endif; ?>

<div class="stats stats-3">
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon('users') ?></span>
        <div><p class="stat-label">On the website</p><p class="stat-value"><?= count($published) ?></p><p class="stat-hint"><?= count($members) - count($published) ?> hidden</p></div>
    </div>
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon('image') ?></span>
        <div><p class="stat-label">With a photo</p><p class="stat-value"><?= count($withPhoto) ?></p><p class="stat-hint">The rest show their initials</p></div>
    </div>
    <div class="card stat">
        <span class="stat-icon"><?= admin_icon('mail') ?></span>
        <div><p class="stat-label">Contact shown</p><p class="stat-value"><?= count(array_filter($members, fn($m) => team_member_contacts($m) !== [])) ?></p><p class="stat-hint">Email or phone on the card</p></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3>People</h3>
        <a class="link hide-sm" href="<?= url('/') ?>#team" target="_blank" rel="noopener">View on the website <?= admin_icon('external', 'icon icon-sm') ?></a>
    </div>
<?php if (!$members): ?>
    <div class="empty">
        <span class="empty-icon"><?= admin_icon('users') ?></span>
        <h3>Nobody on the team yet</h3>
        <p class="muted">The leadership section is empty on the website. Add the first person to bring it back.</p>
        <p><a href="<?= url('/admin/team/new') ?>" class="btn btn-primary">Add team member</a></p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Person</th>
                    <th class="hide-md">Role</th>
                    <th class="hide-sm">Email &amp; phone</th>
                    <th>On site</th>
                    <th>Order</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($members as $i => $m): $edit = url('/admin/team/' . (int) $m['id']); $photo = team_photo_url($m); ?>
                <tr>
                    <td>
                        <a href="<?= $edit ?>" class="person">
<?php if ($photo !== ''): ?>
                            <img class="avatar avatar-sm avatar-photo" src="<?= e(url('/' . $photo)) ?>" alt="" width="34" height="34">
<?php else: ?>
                            <span class="avatar avatar-sm"><?= e(team_initials($m['name'], $m['initials'])) ?></span>
<?php endif; ?>
                            <span><strong><?= e($m['name']) ?></strong><?php if ($m['rank'] !== ''): ?><small><?= e($m['rank']) ?></small><?php endif; ?></span>
                        </a>
                    </td>
                    <td class="hide-md"><span class="excerpt"><?= e($m['role']) ?></span></td>
                    <td class="hide-sm">
<?php if ($m['email'] === '' && $m['phone'] === ''): ?>
                        <span class="muted">—</span>
<?php else: ?>
                        <span class="stack-sm">
<?php if ($m['email'] !== ''): ?><small><?= e($m['email']) ?></small><?php endif; ?>
<?php if ($m['phone'] !== ''): ?><small><?= e($m['phone']) ?></small><?php endif; ?>
                        </span>
<?php if ($m['contact_private']): ?>
                        <span class="chip" title="Saved here, but not shown on the website">Private</span>
<?php endif; ?>
<?php endif; ?>
                    </td>
                    <td><?= $m['published'] ? '<span class="len len-ok">Shown</span>' : '<span class="len len-warn">Hidden</span>' ?></td>
                    <td class="nowrap">
                        <form method="post" action="<?= url('/admin/team/' . (int) $m['id'] . '/move') ?>" class="order-form">
                            <?= csrf_field() ?>
                            <button type="submit" name="dir" value="up" class="btn btn-ghost btn-sm" aria-label="Move <?= e($m['name']) ?> up"<?= $i === 0 ? ' disabled' : '' ?>>↑</button>
                            <button type="submit" name="dir" value="down" class="btn btn-ghost btn-sm" aria-label="Move <?= e($m['name']) ?> down"<?= $i === $last ? ' disabled' : '' ?>>↓</button>
                        </form>
                    </td>
                    <td class="nowrap actions-cell">
                        <a href="<?= $edit ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="post" action="<?= url('/admin/team/' . (int) $m['id'] . '/delete') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm btn-danger" data-confirm="Remove <?= e($m['name']) ?> from the team section?"><?= admin_icon('trash', 'icon icon-sm') ?></button>
                        </form>
                    </td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
