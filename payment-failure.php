<?php
/**
 * MIDHILA VOYAGES — Payment Failed / Incomplete
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
$pageTitle = 'Payment Incomplete | Midhila Voyages';
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

$waMsg = "Hello MIDHILA VOYAGES, my payment attempt for booking reference #{$ref} was unsuccessful. Could you please assist me with manual bank transfer or link checkout?";
$waUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-padding" style="background:#FFFFFF;padding-top:calc(var(--header-height) + 60px);padding-bottom:100px;">
  <div class="container container-narrow">
    
    <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:50px 40px;text-align:center;box-shadow:var(--shadow-card);">
      
      <div style="width:64px;height:64px;border-radius:50%;background:#FEE2E2;border:2px solid #EF4444;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:#DC2626;font-size:1.6rem;">
        ✕
      </div>

      <span style="font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;color:#DC2626;font-weight:700;">Transaction Unsuccessful</span>
      <h1 style="font-size:2.2rem;margin:10px 0 16px;">Payment Could Not Be Completed</h1>
      
      <p style="font-size:1rem;line-height:1.75;color:var(--color-text-secondary);max-width:560px;margin:0 auto 24px;">
        Your bank or card gateway declined the authorization. No funds have been captured from your account. Your booking reference (<strong>#<?= e($ref) ?></strong>) remains active in our system.
      </p>

      <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin-top:28px;">
        <a href="<?= BASE_URL ?>/booking.php" class="btn-cinematic-primary" style="padding:14px 28px;">
          <span>Retry Payment Online</span>
        </a>

        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="padding:14px 26px;">
          <span>Assisted WhatsApp Checkout</span>
        </a>
      </div>

      <div style="margin-top:36px;border-top:1px solid var(--color-border);padding-top:20px;font-size:0.86rem;color:var(--color-text-muted);">
        Need urgent assistance? Call our desk directly at <strong style="color:var(--color-text-headline);">+91 8075021427</strong>, <strong>9446154048</strong>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
