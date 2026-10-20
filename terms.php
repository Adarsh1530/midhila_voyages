<?php
/**
 * MIDHILA VOYAGES — Terms and Conditions
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Terms & Conditions | MIDHILA VOYAGES';
$pageDescription = 'Terms of service, booking policies, traveller obligations, and operational provisions governing travel arrangements with Midhila Voyages.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'contact@midhilavoyages.com');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Terms Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:40px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.22;">
  </div>

  <div class="container" style="text-align:center;max-width:820px;position:relative;z-index:2;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Governance</span>
      <span class="chapter-coordinates">Terms of Service</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:12px;">Terms &amp; Conditions</h1>
    <p class="editorial-subtext" style="margin:0 auto;">Effective Date: October 2026 &bull; Midhila Voyages</p>
  </div>
</section>

<!-- Content Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">
    <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:clamp(32px, 5vw, 56px);box-shadow:var(--shadow-card);line-height:1.85;color:var(--color-text-body);">
      
      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-bottom:12px;">1. Scope of Agreement</h2>
      <p style="margin-bottom:24px;">
        These Terms and Conditions govern the booking, reservation, execution, and coordination of travel packages, pilgrimage tours, hotel stays, vehicle rentals, and related hospitality services offered by <strong><?= e($agencyName) ?></strong>. By submitting a travel booking, accepting an itinerary quotation, or paying a tour deposit, you agree to be bound by these provisions.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">2. Quotations &amp; Booking Confirmation</h2>
      <ul style="margin-left:24px;margin-bottom:24px;list-style-type:disc;">
        <li>All package tariffs displayed on the website or shared via WhatsApp/email are dynamic and subject to availability until an official deposit is received and confirmed in writing.</li>
        <li>A booking is officially confirmed only upon issuance of an official <?= e($agencyName) ?> Booking Confirmation Voucher bearing a valid Booking Reference Number.</li>
        <li>Any verbal or informal enquiry does not hold hotel inventory, flight tickets, or vehicle allocation.</li>
      </ul>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">3. Payments &amp; Settlement Schedules</h2>
      <p style="margin-bottom:12px;">Standard settlement schedules are defined as follows:</p>
      <ul style="margin-left:24px;margin-bottom:24px;list-style-type:disc;">
        <li><strong>Booking Deposit:</strong> An initial non-refundable reservation deposit (standard ₹5,000 per person or 25% of total package value) is required to secure hotel allocations and vehicle blocks.</li>
        <li><strong>Balance Payment:</strong> The remaining total balance must be cleared at least 15 calendar days before the scheduled departure date.</li>
        <li><strong>Late Settlements:</strong> Failure to clear the balance in the stipulated window may result in automatic cancellation of hotel and permit reservations without liability to <?= e($agencyName) ?>.</li>
      </ul>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">4. Traveller Conduct &amp; Temple Protocols</h2>
      <p style="margin-bottom:24px;">
        During pilgrimage yatras, travelers agree to observe local temple dress codes, sanctum protocols, and sanctity rules. <?= e($agencyName) ?> acts as an experienced hospitality facilitator and does not control internal temple trust decisions or VIP queue changes dictated by temple administration.
      </p>

      <h2 style="font-size:1.45rem;color:var(--color-text-headline);margin-top:28px;margin-bottom:12px;">5. Jurisdiction</h2>
      <p style="margin-bottom:0;">
        Any legal claims or disputes arising out of services provided shall be subject to the exclusive jurisdiction of the competent courts in <strong>Kerala, India</strong>.
      </p>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
