// Checks every link and asset on the public pages, including external sites.
// Run: npm run test:links            Against live: BASE_URL=https://atomservices.org/ npm run test:links
// Exits non-zero if any link is broken. LinkedIn/Facebook block bots (999/400/403), so those are
// reported as "unverifiable" instead of failing the run.

const BASE = (process.env.BASE_URL || 'http://localhost/atomservices/').replace(/\/?$/, '/');
const PAGES = ['', 'services.php', 'about.php', 'contact.php', 'faqs.php', 'privacy-policy.php', 'terms.php'];
const LIVE = 'https://atomservices.org';
const BOT_BLOCKERS =/(^|\.)(linkedin\.com|facebook\.com)$/;

function extract(html) {
  const out = [];
  const re = /\s(?:href|src|srcset)="([^"]+)"/g;
  let m;
  while ((m = re.exec(html))) out.push(m[1].replace(/&amp;/g, '&'));
  return out;
}

// Retry once: slow product sites occasionally time out when hit with several parallel requests.
async function check(url) {
  const first = await fetchStatus(url);
  return first === 200 ? first : fetchStatus(url);
}

async function fetchStatus(url) {
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), 15000);
  try {
    const res = await fetch(url, {
      redirect: 'follow',
      signal: ctrl.signal,
      headers: { 'User-Agent': 'Mozilla/5.0 (atomservices link check)' },
    });
    return res.status;
  } catch (e) {
    return 'ERR ' + (e.cause?.code || e.name);
  } finally {
    clearTimeout(timer);
  }
}

(async () => {
  const links = new Map(); // url -> pages it appears on
  for (const page of PAGES) {
    const res = await fetch(BASE + page);
    if (res.status !== 200) throw new Error(`${BASE + page} returned ${res.status}`);
    for (const raw of extract(await res.text())) {
      if (/^(mailto:|tel:|#)/.test(raw)) continue;
      const url = new URL(raw, BASE + page);
      url.hash = '';
      // Canonical / og:url point at the live domain; they only resolve once this version is deployed.
      if (url.origin === LIVE && new URL(BASE).origin !== LIVE) continue;
      const key = url.toString();
      if (!links.has(key)) links.set(key, new Set());
      links.get(key).add('/' + page);
    }
  }

  let broken = 0;
  const results = await Promise.all([...links.keys()].map(async (url) => [url, await check(url)]));
  for (const [url, status] of results.sort()) {
    const host = new URL(url).hostname;
    let label = 'ok';
    if (status !== 200) {
      if (BOT_BLOCKERS.test(host)) label = 'unverifiable (site blocks bots)';
      else { label = 'BROKEN'; broken++; }
    }
    console.log(`${String(status).padEnd(8)} ${label.padEnd(10)} ${url}  [${[...links.get(url)].join(', ')}]`);
  }
  console.log(`\n${links.size} links checked, ${broken} broken.`);
  process.exit(broken ? 1 : 0);
})();
