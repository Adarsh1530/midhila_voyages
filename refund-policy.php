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

<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;">
  <div class="container" style="text-align:center;max-width:760px;">
    <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Transparent Policies</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4vw, 3.2rem);margin-bottom:12px;">Cancellation &amp; Refund Policy</h1>
    <p style="color:#CBD5E0;font-size:0.95rem;">Clear provisions for schedule changes and cancellations.</p>
  </div>
</section>

<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container container-narrow" style="background:#FFF;padding:clamp(28px, 5vw, 56px);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--color-border);line-height:1.8;color:var(--color-text-body);">
    
    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:12px;">1. Notice of Cancellation</h2>
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
