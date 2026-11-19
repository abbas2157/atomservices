<?php
/**
 * Leadership section, shared by the homepage and the About page.
 * The people come from includes/team_data.php (as_team table, editable in /admin/team,
 * falling back to the hard-coded defaults when the database is unavailable).
 * Photos live in assets/img/team/ (500×500 JPEG + WebP, uploaded in the dashboard or built by
 * tools/build-images.php); the initials show until a photo exists. 'exp' is trusted HTML (only
 * <b> for the years, enforced by team_experience_html()). Photo URLs carry ?v=<file time> so a
 * replaced photo shows at once despite the one-year image cache.
 */
require_once __DIR__ . '/team_data.php';
$leaders = team_members();
?>
  <section class="section band leaders" id="team">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">The team</span>
        <h2>10 years running installments, plus the builders.</h2>
        <p><?php echo e(team_count_word(count($leaders))); ?> people across financing, technology, growth and risk, building Pakistan's pay-in-steps economy from Lahore. You'll meet them at GITEX.</p>
      </div>
      <div class="leader-grid">
<?php foreach ($leaders as $l): $jpg = team_photo_url($l); $webp = team_photo_url($l, 'webp'); ?>
        <article class="leader lead">
          <div class="portrait">
            <span class="initials"><?php echo e(team_initials($l['name'], $l['initials'])); ?></span>
<?php if ($jpg !== ''): ?>
            <picture>
<?php if ($webp !== ''): ?>
              <source srcset="<?php echo e($webp); ?>" type="image/webp">
<?php endif; ?>
              <img src="<?php echo e($jpg); ?>" alt="Portrait of <?php echo e($l['name']); ?>, <?php echo e($l['role']); ?>" width="500" height="500" loading="lazy" decoding="async">
            </picture>
<?php endif; ?>
          </div>
          <div class="leader-body">
<?php if ($l['rank'] !== ''): ?>
            <span class="rank"><?php echo e($l['rank']); ?></span>
<?php endif; ?>
            <h3><?php echo e($l['name']); ?></h3>
            <p class="role"><?php echo e($l['role']); ?></p>
<?php if ($l['exp'] !== ''): ?>
            <p class="exp"><?php echo $l['exp']; ?></p>
<?php endif; ?>
<?php if ($contacts = team_member_contacts($l)): ?>
            <ul class="leader-contact">
<?php foreach ($contacts as $c): ?>
              <li><a href="<?php echo e($c['href']); ?>" aria-label="<?php echo e($c['type'] === 'email' ? 'Email ' . $l['name'] : 'Call ' . $l['name']); ?>"><?php echo team_icon($c['type']); ?><span><?php echo e($c['label']); ?></span></a></li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
<?php if ($links = team_member_links($l)): ?>
            <div class="leader-links"><?php foreach ($links as $label => $url): ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($l['name'] . ' on ' . $label); ?>"><?php echo e($label); ?></a><?php endforeach; ?></div>
<?php endif; ?>
          </div>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>
