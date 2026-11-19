<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/team_data.php';

/** Unit tests for the team library (includes/team_data.php). No database writes. */
final class TeamTest extends TestCase
{
  private function post(array $overrides = []): array
  {
    return $overrides + ['name' => 'Jane Doe', 'role' => 'Chief Financial Officer', 'published' => '1'];
  }

  public function testDefaultsAreTheSixPeopleInOrderWithTheirPhotos(): void
  {
    $names = array_column(team_defaults(), 'name');
    $this->assertSame([
      'Adnan Zaka', 'Mudassar Abbas', 'Muhammad Sohaib Chaudhary',
      'Muhammad Javed Rafique', 'Muhammad Ahmed Chaudhary', 'Gulshair Murtaza',
    ], $names);

    foreach (team_defaults() as $m) {
      $this->assertSame(array_keys(team_blank()), array_keys($m), 'every default has the full member shape');
      $this->assertNotSame('', team_photo_url($m), $m['name'] . ' has a photo file in assets/img/team/');
      $this->assertStringContainsString('<b>', $m['exp'], $m['name'] . ' shows their years in bold');
      $this->assertTrue($m['published']);
    }
  }

  public function testDefaultSortOrderIsUniqueAndAscending(): void
  {
    $sorts = array_column(team_defaults(), 'sort');
    $this->assertSame($sorts, array_unique($sorts));
    $sorted = $sorts;
    sort($sorted);
    $this->assertSame($sorted, $sorts);
  }

  public function testMembersAlwaysComeBackPublishedInShapeAndInOrder(): void
  {
    // With the as_team table this reads the saved people; without it (or with the DB down)
    // it falls back to team_defaults(). Either way the public contract holds.
    $members = team_members();
    $this->assertNotEmpty($members, 'the leadership section is never empty by accident');
    $sorts = array_column($members, 'sort');
    $sorted = $sorts;
    sort($sorted);
    $this->assertSame($sorted, $sorts, 'members come back in display order');
    foreach ($members as $m) {
      $this->assertSame(array_keys(team_blank()), array_keys($m));
      $this->assertTrue($m['published'], 'team_members() hides unpublished people from the website');
      $this->assertNotSame('', $m['name']);
    }
  }

  public function testPhotoUrlIsCacheBustedAndEmptyWhenTheFileIsMissing(): void
  {
    $this->assertMatchesRegularExpression('#^assets/img/team/team-1\.jpg\?v=\d+$#', team_photo_url(['photo' => 'team-1']));
    $this->assertMatchesRegularExpression('#^assets/img/team/team-1\.webp\?v=\d+$#', team_photo_url(['photo' => 'team-1'], 'webp'));
    $this->assertSame('', team_photo_url(['photo' => '']));
    $this->assertSame('', team_photo_url(['photo' => 'nobody-here']));
    $this->assertSame('', team_photo_url(['photo' => '../../config']), 'a path cannot escape the team folder');
  }

  public function testInitialsComeFromTheNameUnlessGiven(): void
  {
    $this->assertSame('MA', team_initials('Mudassar Abbas'));
    $this->assertSame('SC', team_initials('Muhammad Sohaib Chaudhary', 'sc'), 'the given initials win');
    $this->assertSame('MJ', team_initials('Muhammad Javed Rafique'));
    $this->assertSame('A', team_initials('Adnan'));
    $this->assertSame('?', team_initials('   '));
    $this->assertSame('ABC', team_initials('Someone', 'a.b.c.d'), 'capped at three letters');
  }

  public function testExperienceKeepsBoldAndEscapesEverythingElse(): void
  {
    $this->assertSame('<b>14 yrs</b> as a full-stack developer.', team_experience_html('<b>14 yrs</b> as a full-stack developer.'));
    $this->assertSame('<b>9 yrs</b>', team_experience_html('<strong>9 yrs</strong>'), 'strong becomes b');
    $this->assertStringNotContainsString('<script', team_experience_html('<script>alert(1)</script><b>5 yrs</b>'));
    $this->assertSame('<b>5 yrs</b>', team_experience_html('<script>alert(1)</script><b>5 yrs</b>'), 'script contents go too');
    $this->assertSame('<b>5 yrs</b>', team_experience_html('<img src=x onerror=y><b>5 yrs</b>'), 'other tags are dropped');
    $this->assertSame('R&amp;D lead', team_experience_html('R&D lead'), 'plain text is escaped');
    $this->assertSame('a b', team_experience_html("a\n   b"), 'whitespace collapses');
  }

  public function testExperienceSurvivesAnEditRoundTrip(): void
  {
    // Stored HTML → what the editor shows → saved again must not escape twice.
    foreach (['R&D lead, <b>5 yrs</b>', '<b>14 yrs</b> as a full-stack developer.', 'Plain text', ''] as $typed) {
      $stored = team_experience_html($typed);
      $this->assertSame($stored, team_experience_html(team_experience_source($stored)), $typed);
    }
    $this->assertSame('R&D <b>5 yrs</b>', team_experience_source(team_experience_html('R&D <b>5 yrs</b>')));

    foreach (team_defaults() as $m) {
      $this->assertSame($m['exp'], team_experience_html(team_experience_source($m['exp'])), $m['name'] . "'s experience survives a save");
    }
  }

  public function testTelHrefKeepsTheCountryCode(): void
  {
    $this->assertSame('tel:+923041129252', team_tel_href('+92 304-112 9252'));
    $this->assertSame('tel:04235551234', team_tel_href('(042) 3555 1234'));
  }

  public function testContactsAreOnlyPublishedWhenFilledInAndNotPrivate(): void
  {
    $m = ['email' => 'a@b.com', 'phone' => '+92 300 1234567', 'contact_private' => false];
    $this->assertSame(['email', 'phone'], array_column(team_member_contacts($m), 'type'));
    $this->assertSame('mailto:a@b.com', team_member_contacts($m)[0]['href']);

    $this->assertSame([], team_member_contacts(['email' => 'a@b.com', 'contact_private' => true]), 'private hides both');
    $this->assertSame(['phone'], array_column(team_member_contacts(['email' => '', 'phone' => '0300', 'contact_private' => false]), 'type'));
    $this->assertSame([], team_member_contacts(team_blank()));
  }

  public function testEveryProfileLinkFieldIsPartOfAMember(): void
  {
    $blank = team_blank();
    foreach (TEAM_LINK_FIELDS as $key => $def) {
      $this->assertArrayHasKey($key, $blank, "$key must be part of the member shape");
      $this->assertCount(2, $def, "$key must define a label and a placeholder");
      $this->assertNotSame('', $def[0]);
    }
    foreach (['linkedin', 'facebook', 'instagram', 'twitter', 'youtube', 'github', 'website'] as $key) {
      $this->assertArrayHasKey($key, TEAM_LINK_FIELDS, "$key is offered in /admin/team");
    }
  }

  public function testProfileLinksKeepTheirOrderAndDropEmptyOnes(): void
  {
    $m = team_member(['facebook' => 'https://fb', 'linkedin' => 'https://li', 'instagram' => 'https://ig']);
    $this->assertSame(['LinkedIn' => 'https://li', 'Facebook' => 'https://fb', 'Instagram' => 'https://ig'], team_member_links($m),
      'links follow TEAM_LINK_FIELDS order, not the order they were set');
    $this->assertSame(['GitHub' => 'https://gh'], team_member_links(team_member(['github' => 'https://gh'])));
    $this->assertSame([], team_member_links(team_blank()));
  }

  public function testEveryLinkFieldIsValidatedAndSaved(): void
  {
    foreach (array_keys(TEAM_LINK_FIELDS) as $key) {
      $error = null;
      $m = team_clean($this->post([$key => 'https://example.com/me']), $error);
      $this->assertNull($error, $key);
      $this->assertSame('https://example.com/me', $m[$key], "$key is kept");

      $error = null;
      $this->assertNull(team_clean($this->post([$key => 'example.com/me']), $error), "$key needs the scheme");
      $this->assertStringContainsString(TEAM_LINK_FIELDS[$key][0], $error);
    }
  }

  public function testCleanAcceptsAFullMember(): void
  {
    $error = null;
    $m = team_clean($this->post([
      'rank' => 'Finance', 'initials' => 'jd', 'exp' => '<b>5 yrs</b> in banking',
      'email' => ' jane@atomservices.org ', 'phone' => '+92 304 112 9252',
      'linkedin' => 'https://www.linkedin.com/in/jane/', 'contact_private' => '1', 'sort' => '25',
    ]), $error);
    $this->assertNull($error);
    $this->assertSame('jane@atomservices.org', $m['email'], 'trimmed');
    $this->assertSame('JD', $m['initials']);
    $this->assertSame(25, $m['sort']);
    $this->assertTrue($m['contact_private']);
    $this->assertTrue($m['published']);
    $this->assertSame('', $m['github']);
  }

  public function testCleanRequiresNameAndRole(): void
  {
    $error = null;
    $this->assertNull(team_clean(['name' => '', 'role' => 'CFO'], $error));
    $this->assertStringContainsString('Name', $error);

    $error = null;
    $this->assertNull(team_clean(['name' => 'Jane Doe', 'role' => '  '], $error));
    $this->assertStringContainsString('Role', $error);
  }

  public function testCleanValidatesEmailPhoneAndLinks(): void
  {
    $error = null;
    $this->assertNull(team_clean($this->post(['email' => 'not-an-email']), $error));
    $this->assertStringContainsString('Email', $error);

    $error = null;
    $this->assertNull(team_clean($this->post(['phone' => '12345']), $error), 'too short');
    $this->assertStringContainsString('Phone', $error);

    $error = null;
    $this->assertNull(team_clean($this->post(['phone' => 'call me']), $error));

    $error = null;
    $this->assertNull(team_clean($this->post(['linkedin' => 'linkedin.com/in/jane']), $error), 'needs the scheme');
    $this->assertStringContainsString('LinkedIn', $error);

    $error = null;
    $this->assertNull(team_clean($this->post(['facebook' => 'javascript:alert(1)']), $error));
    $this->assertStringContainsString('Facebook', $error);
  }

  public function testCleanEmptiesAreAllowedAndCheckboxesDefaultOff(): void
  {
    $error = null;
    $m = team_clean(['name' => 'Jane Doe', 'role' => 'CFO'], $error);
    $this->assertNull($error);
    $this->assertSame(['', '', ''], [$m['email'], $m['phone'], $m['rank']]);
    $this->assertSame([], team_member_links($m), 'no profile links by default');
    $this->assertFalse($m['published'], 'an unticked "Show on the website" keeps the person off the site');
    $this->assertFalse($m['contact_private']);
    $this->assertSame(0, $m['sort']);
  }

  public function testCleanCapsLongValues(): void
  {
    $error = null;
    $m = team_clean($this->post(['name' => str_repeat('a', 300), 'role' => str_repeat('b', 300), 'sort' => '99999']), $error);
    $this->assertSame(120, mb_strlen($m['name']));
    $this->assertSame(160, mb_strlen($m['role']));
    $this->assertSame(9999, $m['sort']);
  }

  public function testCountWord(): void
  {
    $this->assertSame('Six', team_count_word(6));
    $this->assertSame('One', team_count_word(1));
    $this->assertSame('13', team_count_word(13));
  }

  public function testContactIconsAreDecorativeSvg(): void
  {
    foreach (['email', 'phone'] as $type) {
      $icon = team_icon($type);
      $this->assertStringStartsWith('<svg', $icon);
      $this->assertStringContainsString('aria-hidden="true"', $icon);
      $this->assertStringContainsString('<path', $icon);
    }
  }
}
