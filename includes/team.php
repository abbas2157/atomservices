<?php
/**
 * Leadership section, shared by the homepage and the About page.
 * Photos live in assets/img/team/ (500×500 JPEG + WebP, built by tools/build-images.php);
 * the initials show until a photo exists. 'exp' is trusted HTML (only <b> for the years). URLs carry ?v=<file time> so a replaced photo shows at once
 * despite the one-year image cache.
 */
$leaders = [
  ['rank' => 'Founder',    'name' => 'Adnan Zaka',                'initials' => 'AZ', 'role' => 'Founder & CEO',                              'exp' => 'Engineering background. <b>10 yrs</b> managing a family product financing business.', 'img' => 'team-1', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/adnan-zaka/']],
  ['rank' => 'Director',   'name' => 'Mudassar Abbas',            'initials' => 'MA', 'role' => 'Director & CTO',                             'exp' => '<b>14 yrs</b> as a full-stack developer.',                 'img' => 'team-2', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/mudassar-abbas-8267b8113/', 'GitHub' => 'https://github.com/abbas2157']],
  ['rank' => 'Technology', 'name' => 'Muhammad Sohaib Chaudhary', 'initials' => 'SC', 'role' => 'Tech Consultancy & Implementation',          'exp' => '<b>11 yrs</b> implementing tech in companies.',            'img' => 'team-6', 'links' => []],
  ['rank' => 'Technology', 'name' => 'Muhammad Javed Rafique',    'initials' => 'JR', 'role' => 'Chief Application Technologist',             'exp' => '<b>9 yrs</b> experience.',                                 'img' => 'team-3', 'links' => ['LinkedIn' => 'https://www.linkedin.com/in/muhammad-javed-rafique-902672216', 'GitHub' => 'https://github.com/Muhammadjaved7209']],
  ['rank' => 'Director',   'name' => 'Muhammad Ahmed Chaudhary',  'initials' => 'MC', 'role' => 'Director, Chief Marketing & Growth Officer', 'exp' => '<b>3–4 yrs</b> experience.',                               'img' => 'team-4', 'links' => []],
  ['rank' => 'Finance',    'name' => 'Gulshair Murtaza',          'initials' => 'GM', 'role' => 'Strategic Finance & Risk Control',           'exp' => '<b>3–4 yrs</b> experience.',                               'img' => 'team-5', 'links' => []],
];
$teamDir = __DIR__ . '/../assets/img/team/';
?>
  <section class="section band leaders" id="team">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">The team</span>
        <h2>10 years running installments, plus the builders.</h2>
        <p>Six people across financing, technology, growth and risk, building Pakistan's pay-in-steps economy from Lahore. You'll meet them at GITEX.</p>
      </div>
      <div class="leader-grid">
<?php foreach ($leaders as $l): ?>
        <article class="leader lead">
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
            <p class="exp"><?php echo $l['exp']; ?></p>
<?php if ($l['links']): ?>
            <div class="leader-links"><?php foreach ($l['links'] as $label => $url): ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($l['name'] . ' on ' . $label); ?>"><?php echo e($label); ?></a><?php endforeach; ?></div>
<?php endif; ?>
          </div>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>
