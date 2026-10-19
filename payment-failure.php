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

<section class="section-padding" style="background:transparent;padding-top:calc(var(--header-height) + 50px);position:relative;z-index:2;">
  <div class="container container-narrow">
    <div class="glass-card card-3d glass-specular" style="background:rgba(255,255,255,0.8);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);padding:clamp(32px, 6vw, 56px);border-radius:28px;box-shadow:0 24px 50px -10px rgba(239,68,68,0.15), inset 0 1px 1px rgba(255,255,255,0.85);border:1.5px solid rgba(239,68,68,0.3);text-align:center;">
      
      <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,0.12);border:1.5px solid rgba(239,68,68,0.3);color:#DC2626;font-size:2.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 0 24px rgba(239,68,68,0.2);">
        ⚠
      </div>

      <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,0.12);padding:4px 14px;border-radius:var(--radius-pill);border:1px solid rgba(239,68,68,0.25);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:#DC2626;font-weight:700;margin-bottom:12px;">
        Transaction Incomplete
      </div>
      <h1 style="font-size:clamp(1.8rem, 3.5vw, 2.4rem);color:var(--color-navy);margin-bottom:12px;font-weight:700;">Payment Could Not Be Completed</h1>
      <p class="lead-text" style="margin-bottom:28px;color:var(--color-text-body);font-size:1.05rem;">
        Your transaction was declined by the issuing bank or interrupted during authorization. 
        <?php if (!empty($ref)): ?>
          No money has been debited for reference <strong>#<?= e($ref) ?></strong>.
        <?php endif; ?>
      </p>

      <div style="background:rgba(196,181,253,0.1);backdrop-filter:blur(12px);border-radius:18px;padding:24px;max-width:540px;margin:0 auto 36px;text-align:left;font-size:0.94rem;color:var(--color-text-body);border:1px solid rgba(196,181,253,0.25);">
        <h4 style="color:var(--color-navy);margin-bottom:12px;font-size:1.05rem;font-weight:700;">Common Reasons for Payment Interruption:</h4>
        <ul style="margin-left:20px;line-height:1.75;list-style-type:disc;">
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

      <div style="margin-top:24px;font-size:0.88rem;color:var(--color-text-muted);">
        Or call our accounts desk: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencyPhone) ?>" style="color:var(--color-navy);font-weight:700;"><?= e($agencyPhone) ?></a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
