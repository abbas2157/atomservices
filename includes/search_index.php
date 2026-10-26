<?php
/**
 * Entries for the header site search. $home is '' on the homepage and 'index.php' elsewhere,
 * so in-page anchors scroll without a reload on the homepage.
 */
function search_index(string $home): array {
  return [
    ['title' => 'Atomshop.pk', 'url' => $home . '#products', 'keywords' => 'installments installment shopping bnpl buy now pay later mobiles laptops bikes appliances flagship', 'text' => 'Pakistan\'s installment shopping ecosystem. Plans of 3–12 months from a 20% advance.'],
    ['title' => 'AtomPay', 'url' => $home . '#ecosystem', 'keywords' => 'financing limit cnic credit wallet 0% markup no advance', 'text' => 'A smart spending limit after one CNIC and income check: 3 months, no advance, 0% markup.'],
    ['title' => 'Atom Seller', 'url' => $home . '#ecosystem', 'keywords' => 'sellers shops leads guarantors collections merchants', 'text' => 'Leads, deals, guarantors and collections for local sellers, all from a phone.'],
    ['title' => 'AtomBrand', 'url' => $home . '#ecosystem', 'keywords' => 'brands storefront stock orders bulk quotes retailers', 'text' => 'Brands run their storefront, stock, orders and bulk quotes across Pakistan.'],
    ['title' => 'ATOM Intelligence', 'url' => $home . '#ecosystem', 'keywords' => 'ai smart limit routing reminders recovery kyc data', 'text' => 'One login and one data layer behind every Atomshop app.'],
    ['title' => 'Mark Attendance', 'url' => $home . '#products', 'keywords' => 'face recognition attendance hr workforce saas offline buddy punching', 'text' => 'Face recognition attendance for offices, factories and warehouses.'],
    ['title' => 'KDP Score', 'url' => $home . '#products', 'keywords' => 'amazon kdp niche research books publishing ai', 'text' => 'Amazon KDP niche intelligence, powered by AI.'],
    ['title' => 'Publishing Guru', 'url' => $home . '#products', 'keywords' => 'amazon kdp publishing books ads accounts done for you', 'text' => 'Done-for-you Amazon KDP publishing. 100+ books launched.'],
    ['title' => 'GITEX Global 2026', 'url' => $home . '#gitex', 'keywords' => 'dubai event expo meeting book exhibition december', 'text' => 'Meet us at Dubai Exhibition Centre, 8–11 December 2026.'],
    ['title' => 'Book a meeting', 'url' => $home . '#meet', 'keywords' => 'contact meeting appointment investors partners', 'text' => 'Request a meeting with the team at GITEX Global 2026.'],
    ['title' => 'Services', 'url' => 'services', 'keywords' => 'custom software development mobile apps saas ui ux design hire build', 'text' => 'Custom software, mobile apps, SaaS platforms and UI/UX design.'],
    ['title' => 'Custom software development', 'url' => 'services#custom-software', 'keywords' => 'web systems erp crm business backend api', 'text' => 'Business systems built to scale.'],
    ['title' => 'Mobile app development', 'url' => 'services#mobile-apps', 'keywords' => 'android ios flutter react native app', 'text' => 'Android and iOS apps built for performance.'],
    ['title' => 'SaaS platform development', 'url' => 'services#saas', 'keywords' => 'cloud subscription multi-tenant product', 'text' => 'Cloud products, launch-ready.'],
    ['title' => 'UI/UX design', 'url' => 'services#ui-ux', 'keywords' => 'design user experience interface figma prototype', 'text' => 'User-centred digital experiences.'],
    ['title' => 'Leadership team', 'url' => $home . '#team', 'keywords' => 'founder ceo cto directors adnan zaka mudassar abbas ahmed chaudhary gulshair murtaza javed rafique', 'text' => 'The people you\'ll meet at GITEX.'],
    ['title' => 'Contact us', 'url' => 'contact', 'keywords' => 'email phone whatsapp address lahore wyoming office hours message', 'text' => CONTACT_EMAIL . ' · ' . CONTACT_PHONE],
    ['title' => 'About Atom Services', 'url' => 'about', 'keywords' => 'company story mission values who we are registered details secp ntn', 'text' => 'Smarter finance, better tomorrow. Our story, values and company details.'],
    ['title' => 'FAQs', 'url' => 'faqs', 'keywords' => 'questions help answers how does it work cities credit card hiring careers jobs', 'text' => 'Answers about our company, products and GITEX Global 2026.'],
    ['title' => 'Careers', 'url' => 'faqs#company', 'keywords' => 'jobs hiring cv engineer designer', 'text' => 'We hire engineers, designers and growth roles from time to time.'],
    ['title' => 'Privacy policy', 'url' => 'privacy-policy', 'keywords' => 'data gdpr cookies personal information', 'text' => 'How we collect, use and protect personal data.'],
    ['title' => 'Terms of use', 'url' => 'terms', 'keywords' => 'legal terms conditions', 'text' => 'The rules for using atomservices.org.'],
  ];
}
