<?php
/**
 * MIDHILA VOYAGES — Privacy Policy
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy | MIDHILA VOYAGES';
$pageDescription = 'Privacy Policy of Midhila Voyages detailing how we collect, handle, and protect your travel planning and enquiry information.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'contact@midhilavoyages.com');
$agencyAddress = get_setting('office_address', 'Midhila Voyages, Travel & Pilgrimage Hub, Kerala, India');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Policy Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:40px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.22;">
  </div>

  <div class="container" style="text-align:center;max-width:820px;position:relative;z-index:2;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Client Trust</span>
      <span class="chapter-coordinates">Data Privacy Standards</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:12px;">Privacy Policy</h1>
    <p class="editorial-subtext" style="margin:0 auto;">Last Updated: October 2026 &bull; Midhila Voyages</p>
  </div>
</section>

<!-- Content Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">
    <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:clamp(32px, 5vw, 56px);box-shadow:var(--shadow-card);line-height:1.85;color:var(--color-text-body);">
      
      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-bottom:12px;">1. Introduction &amp; Commitment</h2>
      <p style="margin-bottom:24px;">
        At <?= e($agencyName) ?>, we value your privacy and trust. This Privacy Policy outlines the types of personal data we gather when you browse our website, submit an itinerary enquiry, contact us through WhatsApp, or confirm a tour booking, and explains how this information is handled securely.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">2. Information We Collect</h2>
      <p style="margin-bottom:12px;">We collect only the data necessary to plan, organize, and execute your travel arrangements:</p>
      <ul style="margin-left:24px;margin-bottom:24px;list-style-type:disc;">
        <li><strong>Contact Information:</strong> Full name, telephone number, WhatsApp number, and email address.</li>
        <li><strong>Travel Requirements:</strong> Destination choices, departure and return dates, traveler counts, preferred accommodation category, and dietary or pilgrimage preferences.</li>
        <li><strong>Identity Verification:</strong> For confirmed domestic bookings (hotel check-in &amp; transport permits) and international travel, copies of government IDs as strictly required by law and transport operators.</li>
        <li><strong>Payment Records:</strong> Transaction reference IDs, payment status, and order totals. We do <em>not</em> store card numbers, CVVs, net banking credentials, or UPI PINs on our servers.</li>
      </ul>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">3. Purpose of Processing</h2>
      <p style="margin-bottom:12px;">Your information is utilized solely to:</p>
      <ul style="margin-left:24px;margin-bottom:24px;list-style-type:disc;">
        <li>Formulate and deliver customized tour proposals and cost quotations.</li>
        <li>Reserve hotels, houseboats, private vehicles, and guided tours in your name.</li>
        <li>Provide trip vouchers, itinerary updates, and urgent weather or operational notices.</li>
        <li>Comply with regulatory obligations under Indian law and local destination authorities.</li>
      </ul>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">4. Zero Third-Party Sale or Unsolicited Marketing</h2>
      <p style="margin-bottom:24px;">
        <?= e($agencyName) ?> does not sell, rent, lease, or trade your personal information to third-party telemarketers or advertisers. Information is disclosed only to verified service suppliers (e.g. booked hotels, licensed chauffeurs, or airline ticketing systems) strictly to execute your itinerary.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">5. Data Security &amp; Contact</h2>
      <p style="margin-bottom:20px;">
        We employ industry-standard TLS encryption across our website and administrative portal to protect personal information from unauthorized access or alteration.
      </p>
      <p style="margin-bottom:0;">
        For privacy-related questions or data deletion requests, contact our privacy desk at <strong><?= e($agencyEmail) ?></strong> or phone <strong>+91 8075021427</strong>.
      </p>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
