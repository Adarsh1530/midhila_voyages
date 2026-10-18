<?php
/**
 * MIDHILA VOYAGES — Terms and Conditions
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Terms & Conditions | MIDHILA VOYAGES';
$pageDescription = 'Terms of service, booking policies, traveller obligations, and operational provisions governing travel arrangements with Midhila Voyages.';

$agencyName = get_setting('site_name', 'MIDHILA VOYAGES');
$agencyEmail = get_setting('primary_email', 'journeys@midhilavoyages.com');

require_once __DIR__ . '/includes/header.php';
?>

<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;">
  <div class="container" style="text-align:center;max-width:760px;">
    <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Agreements &amp; Governance</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4vw, 3.2rem);margin-bottom:12px;">Terms &amp; Conditions</h1>
    <p style="color:#CBD5E0;font-size:0.95rem;">Effective Date: October 2026</p>
  </div>
</section>

<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container container-narrow" style="background:#FFF;padding:clamp(28px, 5vw, 56px);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--color-border);line-height:1.8;color:var(--color-text-body);">
    
    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:12px;">1. Scope of Agreement</h2>
    <p style="margin-bottom:20px;">
      These Terms and Conditions govern the booking, reservation, execution, and coordination of travel packages, pilgrimage tours, hotel stays, vehicle rentals, and related hospitality services offered by <strong><?= e($agencyName) ?></strong>. By submitting a travel booking, accepting an itinerary quotation, or paying a tour deposit, you agree to be bound by these provisions.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">2. Quotations &amp; Booking Confirmation</h2>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li>All package tariffs displayed on the website or shared via WhatsApp/email are dynamic and subject to availability until an official deposit is received and confirmed in writing.</li>
      <li>A booking is officially confirmed only upon issuance of an official <?= e($agencyName) ?> Booking Confirmation Voucher bearing a valid Booking Reference Number.</li>
      <li>Any verbal or informal enquiry does not hold hotel inventory, flight tickets, or vehicle allocation.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">3. Payment Schedule</h2>
    <p style="margin-bottom:12px;">Unless otherwise stipulated in your written proposal:</p>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li><strong>Advance Deposit:</strong> A minimum of 30% to 50% of the total tour cost is payable upon booking confirmation to secure hotel room allocations and transport vehicles.</li>
      <li><strong>Balance Payment:</strong> The remaining balance must be settled at least 15 days prior to the travel commencement date, or upon arrival check-in as agreed in your specific voucher terms.</li>
      <li>Failure to remit payments within the stipulated timelines may lead to automated release of hotel reservations without liability to <?= e($agencyName) ?>.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">4. Traveller Obligations &amp; Identification</h2>
    <ul style="margin-left:24px;margin-bottom:20px;list-style-type:disc;">
      <li>All travellers must carry original, valid photo identification (Aadhaar Card, Passport, Voter ID, or Driver's License) for domestic travel. PAN card is generally not recognized for hotel check-ins.</li>
      <li>For international journeys, a valid passport with at least 6 months remaining validity from the return date is mandatory, along with approved visas or entry permits.</li>
      <li>Travellers are responsible for verifying their personal medical fitness, vaccination requirements, and dietary restrictions before commencement of tour activities.</li>
    </ul>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">5. Unforeseen Events &amp; Force Majeure</h2>
    <p style="margin-bottom:20px;">
      <?= e($agencyName) ?> shall not be held liable for itinerary changes, delays, cancellations, or incidental expenses caused by events beyond our reasonable control, including but not limited to severe weather conditions, landslides, road closures, strikes, civil unrest, flight cancellations, or governmental restrictions. In such situations, our operations team will make every reasonable effort to provide safe alternative logistics.
    </p>

    <h2 style="font-size:1.45rem;color:var(--color-navy);margin-top:28px;margin-bottom:12px;">6. Jurisdiction</h2>
    <p>
      Any dispute or claim arising out of or in connection with travel services provided by <?= e($agencyName) ?> shall be subject to the exclusive jurisdiction of the competent courts in Ernakulam, Kerala, India.
    </p>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
