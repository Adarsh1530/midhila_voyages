<?php
/**
 * MIDHILA VOYAGES — Cancellation & Refund Policy
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Cancellation & Refund Policy | MIDHILA VOYAGES';
$pageDescription = 'Official cancellation rules, timeline slabs, non-refundable components, and refund processing procedures of Midhila Voyages.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'contact@midhilavoyages.com');

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
    <h1 class="editorial-headline-large" style="margin-bottom:12px;">Cancellation &amp; Refund Policy</h1>
    <p class="editorial-subtext" style="margin:0 auto;">Clear provisions for schedule changes and cancellations &bull; Midhila Voyages</p>
  </div>
</section>

<!-- Content Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">
    <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:clamp(32px, 5vw, 56px);box-shadow:var(--shadow-card);line-height:1.85;color:var(--color-text-body);">
      
      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-bottom:12px;">1. Written Notice Required</h2>
      <p style="margin-bottom:24px;">
        All cancellation requests must be communicated in writing from the registered email address used at the time of booking to <strong><?= e($agencyEmail) ?></strong>, citing your unique <strong>Booking Reference Number</strong>. Verbal or SMS cancellations will not be considered valid.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:16px;">2. Standard Package Cancellation Slabs</h2>
      <p style="margin-bottom:16px;">Unless specific non-refundable hotel terms apply (such as peak Christmas, New Year, or peak temple festival periods):</p>

      <div style="overflow-x:auto;margin-bottom:28px;">
        <table style="width:100%;border-collapse:collapse;font-size:0.92rem;text-align:left;">
          <thead>
            <tr style="background:#FAFAFA;border-bottom:2px solid var(--color-border);">
              <th style="padding:14px 18px;font-weight:700;color:var(--color-text-headline);">Cancellation Timeline</th>
              <th style="padding:14px 18px;font-weight:700;color:var(--color-text-headline);">Applicable Refund</th>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom:1px solid var(--color-border-hairline);">
              <td style="padding:14px 18px;">30+ Days Prior to Departure</td>
              <td style="padding:14px 18px;color:#059669;font-weight:700;">85% of Total Tour Value</td>
            </tr>
            <tr style="border-bottom:1px solid var(--color-border-hairline);">
              <td style="padding:14px 18px;">15 to 29 Days Prior to Departure</td>
              <td style="padding:14px 18px;color:#059669;font-weight:700;">50% of Total Tour Value</td>
            </tr>
            <tr style="border-bottom:1px solid var(--color-border-hairline);">
              <td style="padding:14px 18px;">7 to 14 Days Prior to Departure</td>
              <td style="padding:14px 18px;color:var(--color-gold-deep);font-weight:700;">25% of Total Tour Value</td>
            </tr>
            <tr>
              <td style="padding:14px 18px;">Less than 7 Days or No-Show</td>
              <td style="padding:14px 18px;color:#DC2626;font-weight:700;">Non-Refundable (0%)</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">3. Refund Processing Turnaround</h2>
      <p style="margin-bottom:20px;">
        Approved refunds are credited back to the original source instrument (Debit/Credit Card, Net Banking, or UPI) within <strong>7 to 10 working days</strong> following written approval from our accounts desk.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">4. Force Majeure &amp; Weather Disruptions</h2>
      <p style="margin-bottom:0;">
        In the rare event of severe monsoon disruption, landslides, cyclone warnings, or government administrative closures, <?= e($agencyName) ?> will endeavor to reschedule travel dates or adjust routes with minimal penalty.
      </p>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
