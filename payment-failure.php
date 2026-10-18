<?php
/**
 * MIDHILA VOYAGES — Payment Failure Guidance
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
$pageTitle = 'Payment Incomplete | Midhila Voyages';

$agencyPhone = get_setting('primary_phone', '+91 12345 67890');
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

$waMsg = "Hello MIDHILA VOYAGES, my online payment for Reference #{$ref} was unsuccessful. Could you please share alternative bank transfer details or assist me?";
$waUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-padding" style="background-color:var(--color-ivory);padding-top:calc(var(--header-height) + 40px);">
  <div class="container container-narrow">
    <div style="background:#FFF;padding:clamp(28px, 6vw, 56px);border-radius:var(--radius-lg);box-shadow:var(--shadow-md);border:1px solid #FCA5A5;text-align:center;">
      
      <div style="width:68px;height:68px;border-radius:50%;background:#FEF2F2;color:#E53E3E;font-size:2.2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        ⚠
      </div>

      <span class="section-subtitle" style="color:#E53E3E;">Transaction Incomplete</span>
      <h1 style="font-size:clamp(1.8rem, 3.5vw, 2.4rem);color:var(--color-navy);margin-bottom:12px;">Payment Could Not Be Completed</h1>
      <p class="lead-text" style="margin-bottom:28px;">
        Your transaction was declined by the issuing bank or interrupted during authorization. 
        <?php if (!empty($ref)): ?>
          No money has been debited for reference <strong>#<?= e($ref) ?></strong>.
        <?php endif; ?>
      </p>

      <div style="background:var(--color-ivory-warm);border-radius:var(--radius-md);padding:24px;max-width:540px;margin:0 auto 36px;text-align:left;font-size:0.92rem;color:var(--color-text-body);border:1px solid rgba(0,0,0,0.06);">
        <h4 style="color:var(--color-navy);margin-bottom:10px;font-size:1.05rem;">Common Reasons for Payment Failure:</h4>
        <ul style="margin-left:20px;line-height:1.7;list-style-type:disc;">
          <li>Temporary network timeout or OTP expiration.</li>
          <li>Daily online banking or card transaction limit reached.</li>
          <li>International cards needing two-factor authentication approval.</li>
        </ul>
      </div>

      <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/booking.php" class="btn btn-primary btn-lg">
          <span>Retry Payment</span>
        </a>
        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
          <span>Assistance via WhatsApp</span>
        </a>
      </div>

      <div style="margin-top:24px;font-size:0.85rem;color:var(--color-text-muted);">
        Or call our accounts desk: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencyPhone) ?>" style="color:var(--color-navy);font-weight:600;"><?= e($agencyPhone) ?></a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
