<?php
/**
 * Leadership section, shared by the homepage and the About page.
 * Photos live in assets/img/team/ (500×500 JPEG + WebP, built by tools/build-images.php);
 * the initials show until a photo exists. URLs carry ?v=<file time> so a replaced photo shows at once
 * despite the one-year image cache.
 */
$leaders = [
  ['lead' => true,  'rank' => 'Founder',    'name' => 'Adnan Zaka',               'initials' => 'AZ', 'role' => 'Chief Executive Officer',                        'img' => 'team-1', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/adnan-zaka/']],
  ['lead' => true,  'rank' => 'Director',   'name' => 'Muhammad Ahmed Chaudhary', 'initials' => 'AC', 'role' => 'Chief Marketing & Growth Officer',               'img' => 'team-4', 'links' => []],
  ['lead' => true,  'rank' => 'Director',   'name' => 'Mudassar Abbas',           'initials' => 'MA', 'role' => 'Chief Technology Officer',                       'img' => 'team-2', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/mudassar-abbas-8267b8113/', 'GitHub' => 'https://github.com/abbas2157']],
  ['lead' => false, 'rank' => 'Finance',    'name' => 'Gulshair Murtaza',         'initials' => 'GM', 'role' => 'Executive Manager, Strategic Finance & Risk Control', 'img' => 'team-5', 'links' => []],
  ['lead' => false, 'rank' => 'Technology', 'name' => 'Muhammad Javed Rafique',   'initials' => 'MJ', 'role' => 'Chief Application Technologist',                 'img' => 'team-3', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/muhammad-javed-rafique-902672216', 'GitHub' => 'https://github.com/Muhammadjaved7209']],
];
$teamDir = __DIR__ . '/../assets/img/team/';
?>
  <section class="section band leaders" id="team">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Leadership</span>
        <h2>The team you'll meet at GITEX.</h2>
        <p>Five people across strategy, growth, technology and finance, building Pakistan's pay-in-steps economy from Lahore.</p>
      </div>
      <div class="leader-grid">
<?php foreach ($leaders as $l): ?>
        <article class="leader<?php echo $l['lead'] ? ' lead' : ''; ?>">
          <div class="portrait">
            <span class="initials"><?php echo $l['initials']; ?></span>
<?php if (is_file($teamDir . $l['img'] . '.jpg')): ?>
            <picture>
<?php if (is_file($teamDir . $l['img'] . '.webp')): ?>
              <source srcset="assets/img/team/<?php echo $l['img']; ?>.webp?v=<?php echo filemtime($teamDir . $l['img'] . '.webp'); ?>" type="image/webp">
<?php endif; ?>
              <img src="assets/img/team/<?php echo $l['img']; ?>.jpg?v=<?php echo filemtime($teamDir . $l['img'] . '.jpg'); ?>" alt="Portrait of <?php echo e($l['name']); ?>, <?php echo e($l['role']); ?>" width="500" height="500" loading="lazy" decoding="async">
            </picture>
<?php endif; ?>
          </div>
          <div class="leader-body">
            <span class="rank"><?php echo e($l['rank']); ?></span>
            <h3><?php echo e($l['name']); ?></h3>
            <p class="role"><?php echo e($l['role']); ?></p>
<?php if ($l['links']): ?>
            <div class="leader-links"><?php foreach ($l['links'] as $label => $url): ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($l['name'] . ' on ' . $label); ?>"><?php echo e($label); ?></a><?php endforeach; ?></div>
<?php endif; ?>
          </div>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>
