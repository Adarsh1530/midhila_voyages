<?php
/**
 * MIDHILA VOYAGES — Privacy Policy
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy | MIDHILA VOYAGES';
$pageDescription = 'Privacy Policy of Midhila Voyages detailing how we collect, handle, and protect your travel planning and enquiry information.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'journeys@midhilavoyages.com');
$agencyAddress = get_setting('office_address', 'Midhila Voyages, Travel & Tourism Hub, MG Road, Kochi, Kerala - 682016, India');

require_once __DIR__ . '/includes/header.php';
?>

<section style="position:relative;background:linear-gradient(180deg, rgba(6,11,24,0.92) 0%, rgba(10,17,40,0.85) 100%);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;border-bottom:1px solid rgba(196,181,253,0.18);">
  <div class="container" style="text-align:center;max-width:780px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.12);backdrop-filter:blur(16px);border:1px solid rgba(196,181,253,0.3);padding:6px 16px;border-radius:var(--radius-pill);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.18em;color:var(--color-lavender);margin-bottom:14px;font-weight:600;box-shadow:0 0 20px rgba(139,92,246,0.2);">
      <span>✦ Legal &amp; Trust</span>
    </div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4.5vw, 3.4rem);margin-bottom:12px;">Privacy Policy</h1>
    <p style="color:#CBD5E1;font-size:0.95rem;">Last Updated: October 2026 &bull; Midhila Voyages</p>
  </div>
</section>

<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container container-narrow glass-card glass-specular" style="background:rgba(255,255,255,0.8);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);padding:clamp(32px, 5vw, 56px);border-radius:28px;box-shadow:0 20px 48px -10px rgba(0,0,0,0.06), inset 0 1px 1px rgba(255,255,255,0.85);border:1px solid rgba(196,181,253,0.38);line-height:1.85;color:var(--color-text-body);">
    
    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:12px;font-weight:700;">1. Introduction &amp; Commitment</h2>
    <p style="margin-bottom:20px;">
      At <?= e($agencyName) ?>, we value your privacy and trust. This Privacy Policy outlines the types of personal data we gather when you browse our website, submit an itinerary enquiry, contact us through WhatsApp, or confirm a tour booking, and explains how this information is handled securely.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">2. Information We Collect</h2>
    <p style="margin-bottom:12px;">We collect only the data necessary to plan, organize, and execute your travel arrangements:</p>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li><strong>Contact Information:</strong> Full name, telephone number, WhatsApp number, and email address.</li>
      <li><strong>Travel Requirements:</strong> Destination choices, tentative departure and return dates, traveller counts (adults and children), preferred accommodation category, and dietary or pilgrimage preferences.</li>
      <li><strong>Official Identity Verification:</strong> For confirmed domestic bookings (hotel check-in &amp; transport permits) and international travel (passport validity &amp; visa guidance), copies of government IDs as strictly required by law and transport operators.</li>
      <li><strong>Payment Records:</strong> Transaction reference IDs, payment status, and order totals. We do <em>not</em> store card numbers, CVVs, net banking credentials, or UPI PINs on our servers.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">3. Purpose of Processing</h2>
    <p style="margin-bottom:12px;">Your information is utilized solely to:</p>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li>Formulate and deliver customized tour proposals and cost quotations.</li>
      <li>Reserve hotels, houseboats, private vehicles, and guided tours in your name.</li>
      <li>Provide trip vouchers, itinerary updates, and urgent weather or operational notices.</li>
      <li>Comply with regulatory obligations under Indian law and local destination authorities.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">4. Zero Third-Party Sale or Unsolicited Marketing</h2>
    <p style="margin-bottom:20px;">
      <?= e($agencyName) ?> does not sell, rent, lease, or trade your personal information to third-party telemarketers or advertisers. Information is disclosed only to verified service suppliers (e.g. booked hotels, licensed chauffeurs, or airline ticketing systems) strictly to execute your itinerary.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">5. Data Security &amp; Retention</h2>
    <p style="margin-bottom:20px;">
      We implement industry-standard database safeguards, SSL/TLS encrypted transmissions, and strict administrative access controls. Enquiries and booking records are retained for historical audit, tax compliance, and reconciliation as prescribed by applicable Indian statutes.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">6. Contact for Privacy Inquiries</h2>
    <p>
      For questions regarding your data or to request corrections, please reach us at:<br>
      <strong>Data Privacy Desk &bull; <?= e($agencyName) ?></strong><br>
      Address: <?= e($agencyAddress) ?><br>
      Email: <a href="mailto:<?= e($agencyEmail) ?>" style="color:var(--color-gold-dark);"><?= e($agencyEmail) ?></a>
    </p>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
