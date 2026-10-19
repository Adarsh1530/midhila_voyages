<?php
/**
 * MIDHILA VOYAGES — Payment Success & Booking Confirmation Voucher
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
if (empty($ref)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$db = getDB();

// Fetch booking & payment record
$stmt = $db->prepare("
    SELECT b.*, p.title AS package_title, p.duration_days, p.duration_nights, d.name AS destination_name,
           pay.gateway_payment_id, pay.amount AS paid_amount, pay.status AS payment_status, pay.verified_at
    FROM `bookings` b
    LEFT JOIN `packages` p ON b.package_id = p.id
    LEFT JOIN `destinations` d ON p.destination_id = d.id
    LEFT JOIN `payments` pay ON (pay.booking_reference = b.booking_reference AND pay.status = 'captured')
    WHERE b.booking_reference = ?
    ORDER BY pay.id DESC LIMIT 1
");
$stmt->execute([$ref]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Booking Confirmed | Voucher #' . e($ref) . ' | Midhila Voyages';
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

$waMsg = "Hello MIDHILA VOYAGES, I have successfully completed the payment for Booking Reference #{$ref} ({$booking['package_title']}). Please share the official confirmed itinerary voucher.";
$waUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Dedicated Print & Screen Styling for Invoice */
.invoice-card {
  background: #FFF;
  padding: clamp(28px, 5vw, 44px);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
  border: 2px solid var(--color-gold);
  position: relative;
  box-sizing: border-box;
}

@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 14mm;
  }

  /* Universal removal of non-invoice elements */
  .site-header,
  .site-footer,
  .fab-whatsapp,
  .no-print,
  .flash-messages-container,
  .mobile-drawer,
  nav,
  button {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Strip all backgrounds, frames, shadows, and margins from html/body/containers */
  html, body {
    background: #FFFFFF !important;
    color: #06101E !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: auto !important;
    min-height: auto !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .voucher-section {
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .voucher-container {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  /* Exactly the Invoice Card itself on A4 */
  #printableInvoice {
    box-shadow: none !important;
    border: 2px solid #C5A059 !important;
    border-radius: 12px !important;
    padding: 22px 26px !important;
    margin: 0 auto !important;
    width: 100% !important;
    max-width: 100% !important;
    background: #FFFFFF !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    box-sizing: border-box !important;
  }

  .invoice-details-box {
    background: #FAF8F5 !important;
    border: 1px solid rgba(197, 160, 89, 0.35) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .invoice-details-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 16px 24px !important;
  }

  .invoice-badge {
    background: rgba(0, 168, 150, 0.12) !important;
    color: #008A7B !important;
    border: 1px solid rgba(0, 168, 150, 0.35) !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
@media screen {
  .invoice-card {
    background: rgba(255, 255, 255, 0.88) !important;
    backdrop-filter: blur(28px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(28px) saturate(180%) !important;
    border: 1.5px solid rgba(196, 181, 253, 0.45) !important;
    box-shadow: 0 24px 50px -10px rgba(139, 92, 246, 0.18), inset 0 1px 1px 0 rgba(255, 255, 255, 0.85) !important;
  }
}
</style>

<section class="section-padding voucher-section" style="background:transparent;padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;position:relative;z-index:2;">
  <div class="container container-narrow voucher-container">
    
    <!-- EXACT INVOICE CARD (Targeted for A4 Print) -->
    <div id="printableInvoice" class="invoice-card">
      
      <!-- Voucher Header -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;border-bottom:1.5px solid var(--color-border);padding-bottom:20px;margin-bottom:22px;">
        <div class="brand-logo" style="display:flex;align-items:center;gap:12px;">
          <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Midhila Voyages Emblem" class="brand-logo__symbol" width="46" height="46" style="display:block;">
          <div class="brand-logo__text">
            <span class="brand-logo__name" style="color:var(--color-navy);font-size:1.3rem;font-weight:700;letter-spacing:0.04em;">MIDHILA</span>
            <span class="brand-logo__subtitle" style="color:var(--color-gold);letter-spacing:0.22em;font-size:0.75rem;">VOYAGES</span>
          </div>
        </div>

        <div style="text-align:right;">
          <span class="invoice-badge" style="display:inline-block;background:rgba(0,168,150,0.12);color:#008A7B;font-weight:700;font-size:0.75rem;padding:4px 14px;border-radius:var(--radius-pill);text-transform:uppercase;margin-bottom:6px;border:1px solid rgba(0,168,150,0.3);">
            Booking Confirmed
          </span>
          <div style="font-family:var(--font-serif);font-size:1.25rem;font-weight:700;color:var(--color-navy);letter-spacing:0.02em;">
            #<?= e($booking['booking_reference']) ?>
          </div>
          <div style="font-size:0.8rem;color:var(--color-text-muted);">
            Issued: <?= date('d M Y, h:i A', strtotime($booking['verified_at'] ?? $booking['created_at'] ?? 'now')) ?>
          </div>
        </div>
      </div>

      <!-- Success Heading -->
      <div style="text-align:center;margin-bottom:24px;">
        <div style="width:54px;height:54px;border-radius:50%;background:rgba(0,168,150,0.12);color:var(--color-teal);font-size:1.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;border:1.5px solid rgba(0,168,150,0.35);">
          ✓
        </div>
        <h1 style="font-family:var(--font-serif);font-size:clamp(1.6rem, 3vw, 2.1rem);color:var(--color-navy);margin-bottom:6px;font-weight:700;letter-spacing:0.03em;text-transform:uppercase;">
          Payment Received Successfully!
        </h1>
        <p style="color:var(--color-text-muted);font-size:0.95rem;max-width:560px;margin:0 auto;line-height:1.5;">
          Your holiday reservation has been confirmed. Below is your official travel confirmation voucher.
        </p>
      </div>

      <!-- Voucher Information Table -->
      <div class="invoice-details-box" style="background:var(--color-ivory-warm);border-radius:var(--radius-md);padding:22px 24px;margin-bottom:22px;border:1px solid rgba(197,160,89,0.3);">
        <div class="invoice-details-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:18px 24px;">
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Package Title</span>
            <h4 style="font-size:0.98rem;color:var(--color-navy);margin:0;font-weight:700;line-height:1.35;text-transform:uppercase;"><?= e($booking['package_title'] ?? 'Custom Package') ?></h4>
          </div>
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Destination</span>
            <h4 style="font-size:0.98rem;color:var(--color-navy);margin:0;font-weight:700;line-height:1.35;text-transform:uppercase;"><?= e($booking['destination_name'] ?? 'Multiple Destinations') ?></h4>
          </div>
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Primary Traveller</span>
            <h4 style="font-size:0.98rem;color:var(--color-navy);margin:0;font-weight:700;text-transform:uppercase;"><?= e($booking['customer_name']) ?></h4>
          </div>
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Travel Date</span>
            <h4 style="font-size:0.98rem;color:var(--color-navy);margin:0;font-weight:700;text-transform:uppercase;"><?= date('d F Y', strtotime($booking['travel_date'])) ?></h4>
          </div>
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Travellers Count</span>
            <h4 style="font-size:0.98rem;color:var(--color-navy);margin:0;font-weight:700;text-transform:uppercase;"><?= (int)$booking['travellers_count'] ?> Guest(s)</h4>
          </div>
          <div>
            <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Amount Paid</span>
            <h4 style="font-size:1.25rem;color:var(--color-teal);margin:0;font-weight:800;">
              <?= format_price((float)$booking['paid_amount'], $booking['currency']) ?>
            </h4>
          </div>
          <?php if (!empty($booking['gateway_payment_id'])): ?>
            <div style="grid-column: 1 / -1; border-top: 1px dashed rgba(197,160,89,0.3); padding-top: 14px; margin-top: 2px;">
              <span style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-text-muted);margin-bottom:3px;">Transaction / Payment ID</span>
              <div style="font-size:0.92rem;color:var(--color-navy);font-family:monospace;font-weight:600;">
                <?= e($booking['gateway_payment_id']) ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Official Verification & Contact Details Inside Invoice -->
      <div style="border-top:1px dashed rgba(197,160,89,0.35);padding-top:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:0.8rem;color:var(--color-text-muted);">
        <div>
          <div style="font-weight:700;color:var(--color-navy);margin-bottom:2px;">MIDHILA VOYAGES TRAVEL DESK</div>
          <div>Helpline: <strong style="color:var(--color-navy);">+91 12345 67890</strong> &bull; Email: <strong style="color:var(--color-navy);">journeys@midhilavoyages.com</strong></div>
          <div>Office: MG Road, Kochi, Kerala - 682016, India</div>
        </div>
        <div style="text-align:right;max-width:280px;font-size:0.72rem;color:var(--color-text-muted);line-height:1.4;">
          Please present this booking voucher along with a valid government photo ID upon arrival / hotel check-in.
        </div>
      </div>

    </div>

    <!-- Action Buttons (OUTSIDE THE INVOICE - HIDDEN IN PRINT) -->
    <div class="no-print" style="display:flex;justify-content:center;gap:14px;margin-top:28px;flex-wrap:wrap;">
      <button type="button" onclick="window.print()" class="btn btn-outline-gold btn-lg">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        <span>Print / Save Voucher</span>
      </button>

      <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
        <span>Notify Coordinator on WhatsApp</span>
      </a>

      <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-gold btn-lg">
        <span>Back to Home</span>
      </a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
