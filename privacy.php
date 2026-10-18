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

<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;">
  <div class="container" style="text-align:center;max-width:760px;">
    <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Legal &amp; Trust</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4vw, 3.2rem);margin-bottom:12px;">Privacy Policy</h1>
    <p style="color:#CBD5E0;font-size:0.95rem;">Last Updated: October 2026</p>
  </div>
</section>

<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container container-narrow" style="background:#FFF;padding:clamp(28px, 5vw, 56px);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--color-border);line-height:1.8;color:var(--color-text-body);">
    
    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:12px;">1. Introduction &amp; Commitment</h2>
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
