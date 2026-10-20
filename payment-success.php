<?php
/**
 * MIDHILA VOYAGES — Payment Success & Booking Confirmation Voucher
 * Pure White Editorial Hospitality Canvas
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
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

$waMsg = "Hello MIDHILA VOYAGES, I have successfully completed the payment for Booking Reference #{$ref} ({$booking['package_title']}). Please share the official confirmed itinerary voucher.";
$waUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Dedicated Print & Screen Styling for Invoice */
.invoice-card {
  background: #FFFFFF;
  padding: clamp(28px, 5vw, 44px);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  border: 1.5px solid var(--color-gold);
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
  .no-print,
  .flash-messages-container,
  .mobile-drawer,
  nav,
  button,
  a.no-print {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Strip all backgrounds and margins from html/body */
  html, body {
    background: #FFFFFF !important;
    color: #0F172A !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: auto !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .voucher-section {
    background: #FFFFFF !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .voucher-container {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  /* Exact Invoice Card on A4 */
  #printableInvoice {
    box-shadow: none !important;
    border: 2px solid #C5A059 !important;
    border-radius: 10px !important;
    padding: 22px 26px !important;
    margin: 0 auto !important;
    width: 100% !important;
    background: #FFFFFF !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .invoice-details-box {
    background: #FAFAFA !important;
    border: 1px solid rgba(197, 160, 89, 0.35) !important;
  }

  .invoice-details-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 16px 24px !important;
  }

  .invoice-badge {
    background: rgba(5, 150, 105, 0.12) !important;
    color: #059669 !important;
    border: 1px solid rgba(5, 150, 105, 0.35) !important;
  }
}
</style>

<section class="section-padding voucher-section" style="background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:80px;position:relative;">
  <div class="container container-narrow voucher-container">
    
    <!-- Action Bar (Screen Only) -->
    <div class="no-print" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
      <a href="<?= BASE_URL ?>/packages.php" class="link-editorial-arrow">
        &larr; Return to Packages
      </a>
      
      <div style="display:flex;gap:12px;">
        <button type="button" onclick="window.print()" class="btn-cinematic-primary" style="padding:10px 22px;font-size:0.86rem;">
          <span>Print / Save PDF</span>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
        </button>
        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="padding:10px 20px;font-size:0.86rem;">
          <span>WhatsApp Voucher</span>
        </a>
      </div>
    </div>

    <!-- TARGET PRINTABLE INVOICE CARD -->
    <div id="printableInvoice" class="invoice-card">
      
      <!-- Voucher Header -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;border-bottom:1.5px solid var(--color-border);padding-bottom:22px;margin-bottom:26px;">
        <div class="brand-logo" style="display:flex;align-items:center;">
          <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
               srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
               alt="Midhila Voyages" 
               class="brand-logo__img" 
               height="48" 
               style="height:48px;width:auto;display:block;">
        </div>

        <div style="text-align:right;">
          <span class="invoice-badge" style="display:inline-block;background:rgba(5,150,105,0.1);color:#059669;font-weight:700;font-size:0.75rem;padding:4px 14px;border-radius:var(--radius-pill);text-transform:uppercase;margin-bottom:6px;border:1px solid rgba(5,150,105,0.3);">
            Booking Confirmed
          </span>
          <div style="font-family:var(--font-serif);font-size:1.25rem;font-weight:700;color:var(--color-text-headline);">
            Ref: #<?= e($booking['booking_reference']) ?>
          </div>
          <div style="font-size:0.8rem;color:var(--color-text-muted);">
            Issued: <?= date('d M Y, h:i A') ?>
          </div>
        </div>
      </div>

      <!-- Tour Package Details Box -->
      <div class="invoice-details-box" style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:24px 28px;margin-bottom:28px;">
        <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-gold-deep);font-weight:700;display:block;margin-bottom:6px;">
          Reserved Itinerary
        </span>
        <h2 style="font-family:var(--font-serif);font-size:1.55rem;color:var(--color-text-headline);margin-bottom:10px;">
          <?= e($booking['package_title'] ?? 'Custom Expedition') ?>
        </h2>
        <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:0.9rem;color:var(--color-text-secondary);">
          <span>⏱ Duration: <strong><?= (int)($booking['duration_days'] ?? 1) ?> Days / <?= (int)($booking['duration_nights'] ?? 0) ?> Nights</strong></span>
          <span>📍 Destination: <strong><?= e($booking['destination_name'] ?? 'India') ?></strong></span>
          <span>📅 Travel Date: <strong><?= e($booking['travel_date']) ?></strong></span>
        </div>
      </div>

      <!-- Guest & Booking Summary Grid -->
      <div class="invoice-details-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;">
        
        <div>
          <h4 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);margin-bottom:12px;font-weight:700;">
            Lead Traveler Information
          </h4>
          <div style="line-height:1.75;font-size:0.92rem;color:var(--color-text-body);">
            <div><strong>Name:</strong> <?= e($booking['customer_name']) ?></div>
            <div><strong>Phone / WhatsApp:</strong> <?= e($booking['customer_phone']) ?></div>
            <div><strong>Email:</strong> <?= e($booking['customer_email']) ?></div>
            <div><strong>Party Size:</strong> <?= (int)$booking['travellers_count'] ?> Traveler(s)</div>
          </div>
        </div>

        <div>
          <h4 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);margin-bottom:12px;font-weight:700;">
            Tariff &amp; Payment Status
          </h4>
          <div style="line-height:1.75;font-size:0.92rem;color:var(--color-text-body);">
            <div><strong>Total Package Cost:</strong> <?= format_price((float)$booking['total_amount']) ?></div>
            <div><strong>Amount Received:</strong> <span style="color:#059669;font-weight:700;"><?= format_price((float)$booking['paid_amount']) ?></span></div>
            <div><strong>Balance Remaining:</strong> <?= format_price(max(0, (float)$booking['total_amount'] - (float)$booking['paid_amount'])) ?></div>
            <div><strong>Payment Mode:</strong> <?= ucfirst(e($booking['payment_type'] ?? 'Deposit')) ?> Online</div>
            <?php if (!empty($booking['gateway_payment_id'])): ?>
              <div style="font-size:0.8rem;color:var(--color-text-muted);">Txn ID: <?= e($booking['gateway_payment_id']) ?></div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <?php if (!empty($booking['special_requests'])): ?>
        <div style="background:#FAFAFA;border-left:3px solid var(--color-gold-deep);padding:14px 18px;border-radius:4px;margin-bottom:28px;font-size:0.88rem;color:var(--color-text-body);">
          <strong>Special Requests:</strong> <?= nl2br(e($booking['special_requests'])) ?>
        </div>
      <?php endif; ?>

      <!-- Terms & Signature Footer -->
      <div style="border-top:1px solid var(--color-border);padding-top:20px;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;font-size:0.8rem;color:var(--color-text-muted);">
        <div style="max-width:440px;line-height:1.6;">
          <strong style="color:var(--color-text-headline);display:block;margin-bottom:4px;">MIDHILA VOYAGES &bull; Travel Management</strong>
          Hotlines: +91 8075021427, 9446154048, 8943669027 &bull; Kerala, India<br>
          This is an electronically generated and authorized travel confirmation voucher.
        </div>
        <div style="text-align:right;">
          <div style="font-family:var(--font-serif);color:var(--color-gold-deep);font-size:0.95rem;font-weight:700;">Authorized Stamp</div>
          <div style="font-size:0.75rem;color:var(--color-text-light);">Midhila Voyages Operations Desk</div>
        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
