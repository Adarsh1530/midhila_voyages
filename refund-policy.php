<?php
/**
 * MIDHILA VOYAGES — Cancellation & Refund Policy
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Cancellation & Refund Policy | MIDHILA VOYAGES';
$pageDescription = 'Official cancellation rules, timeline slabs, non-refundable components, and refund processing procedures of Midhila Voyages.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'journeys@midhilavoyages.com');

require_once __DIR__ . '/includes/header.php';
?>

<section style="position:relative;background:linear-gradient(180deg, rgba(6,11,24,0.92) 0%, rgba(10,17,40,0.85) 100%);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;border-bottom:1px solid rgba(196,181,253,0.18);">
  <div class="container" style="text-align:center;max-width:780px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.12);backdrop-filter:blur(16px);border:1px solid rgba(196,181,253,0.3);padding:6px 16px;border-radius:var(--radius-pill);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.18em;color:var(--color-lavender);margin-bottom:14px;font-weight:600;box-shadow:0 0 20px rgba(139,92,246,0.2);">
      <span>✦ Transparent Policies</span>
    </div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4.5vw, 3.4rem);margin-bottom:12px;">Cancellation &amp; Refund Policy</h1>
    <p style="color:#CBD5E1;font-size:0.95rem;">Clear provisions for schedule changes and cancellations &bull; Midhila Voyages</p>
  </div>
</section>

<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container container-narrow glass-card glass-specular" style="background:rgba(255,255,255,0.8);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);padding:clamp(32px, 5vw, 56px);border-radius:28px;box-shadow:0 20px 48px -10px rgba(0,0,0,0.06), inset 0 1px 1px rgba(255,255,255,0.85);border:1px solid rgba(196,181,253,0.38);line-height:1.85;color:var(--color-text-body);">
    
    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:12px;font-weight:700;">1. Notice of Cancellation</h2>
    <p style="margin-bottom:20px;">
      All cancellation requests must be communicated in writing from the registered email address used at the time of booking to <a href="mailto:<?= e($agencyEmail) ?>" style="color:var(--color-gold-dark);font-weight:600;"><?= e($agencyEmail) ?></a>, citing your unique <strong>Booking Reference Number</strong>. Verbal or SMS cancellations will not be considered valid.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:16px;">2. Standard Standard Package Cancellation Slabs</h2>
    <p style="margin-bottom:14px;">Unless specific non-refundable hotel terms apply (such as peak Christmas, New Year, or festival periods):</p>

    <div style="overflow-x:auto;margin-bottom:28px;">
      <table style="width:100%;border-collapse:collapse;font-size:0.92rem;text-align:left;">
        <thead>
          <tr style="background:var(--color-navy-deep);color:#FFF;">
            <th style="padding:12px 16px;border-radius:6px 0 0 0;">Notice Period Prior to Travel</th>
            <th style="padding:12px 16px;border-radius:0 6px 0 0;">Cancellation Charge / Retention</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom:1px solid var(--color-border);background:var(--color-ivory-warm);">
            <td style="padding:12px 16px;">30 days or more prior to departure</td>
            <td style="padding:12px 16px;color:var(--color-teal);font-weight:600;">Administrative fee only (10% of package cost) + non-refundable flight/hotel supplier fees</td>
          </tr>
          <tr style="border-bottom:1px solid var(--color-border);">
            <td style="padding:12px 16px;">15 to 29 days prior to departure</td>
            <td style="padding:12px 16px;color:var(--color-gold-dark);font-weight:600;">30% of total tour package cost</td>
          </tr>
          <tr style="border-bottom:1px solid var(--color-border);background:var(--color-ivory-warm);">
            <td style="padding:12px 16px;">07 to 14 days prior to departure</td>
            <td style="padding:12px 16px;color:#E53E3E;font-weight:600;">60% of total tour package cost</td>
          </tr>
          <tr>
            <td style="padding:12px 16px;">Less than 07 days or No-Show</td>
            <td style="padding:12px 16px;color:#E53E3E;font-weight:600;">100% of total tour cost (No refund)</td>
          </tr>
        </tbody>
      </table>
    </div>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">3. Non-Refundable Components</h2>
    <p style="margin-bottom:12px;">Certain travel services are strictly non-refundable irrespective of cancellation date:</p>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li>Issued flight tickets, train reservations, or luxury catamaran tickets (subject to airline/railway cancellation rules).</li>
      <li>Entry permits for Lakshadweep and Andaman Islands once processed by government bodies.</li>
      <li>Peak season non-refundable hotel stays (December 20 – January 5, Diwali, Pongal, Onam).</li>
      <li>Visa processing charges and embassy fees.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">4. Refund Processing Timeframe</h2>
    <p style="margin-bottom:20px;">
      Approved refunds will be processed within <strong>7 to 14 business days</strong> following written confirmation of cancellation. Refunds are credited back through the original payment mode (bank transfer or payment gateway refund).
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">5. Postponement &amp; Rescheduling</h2>
    <p>
      If you need to reschedule your travel dates due to unforeseen medical emergencies or unavoidable reasons, we will coordinate with partner hotels and transport providers to minimize retention costs wherever feasible.
    </p>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
