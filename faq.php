<?php
/**
 * MIDHILA VOYAGES — Frequently Asked Questions
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Frequently Asked Questions | Midhila Voyages';
$pageDescription = 'Find answers to common questions about booking, customized itineraries, pilgrimage arrangements, payments, and travel documents with Midhila Voyages.';

$db = getDB();
$faqs = $db->query("SELECT * FROM `faqs` WHERE `is_published` = 1 ORDER BY `display_order` ASC, `id` ASC")->fetchAll();

// Group by category
$groupedFaqs = [];
foreach ($faqs as $faq) {
    $cat = $faq['category'] ?: 'General';
    $groupedFaqs[$cat][] = $faq;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Header -->
<section style="position:relative;background:linear-gradient(180deg, rgba(15,23,42,0.7) 0%, rgba(30,27,75,0.45) 100%);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,255,255,0.12);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.22;">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80" alt="FAQ" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(15,23,42,0.5) 0%, rgba(30,27,75,0.7) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:780px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.15);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.35);padding:6px 16px;border-radius:var(--radius-pill);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.18em;color:var(--color-lavender);margin-bottom:14px;font-weight:600;box-shadow:0 0 20px rgba(139,92,246,0.25);">
      <span>✦ Information &amp; Guidance</span>
    </div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px;">Frequently Asked Questions</h1>
    <p class="lead-text" style="color:#CBD5E1;font-size:1.1rem;line-height:1.7;">Transparent, accurate answers regarding our tour packages, payment procedures, travel documents, and pilgrimage arrangements.</p>
  </div>
</section>

<!-- FAQs Section -->
<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container container-narrow">
    
    <?php foreach ($groupedFaqs as $category => $items): ?>
      <div style="margin-bottom:48px;">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.18);backdrop-filter:blur(10px);padding:4px 14px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.4);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-violet-dark);font-weight:700;margin-bottom:10px;">
          <?= e($category) ?> Inquiries
        </div>
        <h2 style="font-size:1.75rem;color:var(--color-navy);margin-bottom:22px;"><?= e($category) ?> Questions</h2>
        
        <div style="display:flex;flex-direction:column;gap:16px;">
          <?php foreach ($items as $item): ?>
            <details class="faq-item glass-card glass-specular" style="background:rgba(255,255,255,0.45);backdrop-filter:blur(24px) saturate(190%);-webkit-backdrop-filter:blur(24px) saturate(190%);border:1.5px solid rgba(255,255,255,0.7);border-radius:18px;padding:20px 26px;box-shadow:0 16px 36px -8px rgba(10,17,40,0.1), inset 0 1px 1px rgba(255,255,255,0.9);transition:all 0.3s cubic-bezier(0.16,1,0.3,1);">
              <summary style="font-family:var(--font-serif);font-size:1.1rem;font-weight:700;color:var(--color-navy);cursor:pointer;outline:none;display:flex;justify-content:space-between;align-items:center;user-select:none;">
                <span style="padding-right:16px;"><?= e($item['question']) ?></span>
                <span class="faq-toggle-icon" style="color:var(--color-violet);font-size:1.4rem;font-weight:600;display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(196,181,253,0.25);transition:transform 0.3s ease;">+</span>
              </summary>
              <div style="padding-top:16px;color:var(--color-text-body);line-height:1.8;font-size:0.98rem;border-top:1px solid rgba(196,181,253,0.25);margin-top:14px;">
                <?= nl2br(e($item['answer'])) ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Ask Us Box -->
    <div class="glass-card card-3d glass-specular" style="background:linear-gradient(135deg, rgba(255,255,255,0.52) 0%, rgba(243,238,255,0.4) 100%);backdrop-filter:blur(28px) saturate(190%);-webkit-backdrop-filter:blur(28px) saturate(190%);padding:40px 32px;border-radius:24px;border:1.5px solid rgba(255,255,255,0.75);box-shadow:0 24px 50px -10px rgba(139,92,246,0.2), inset 0 1px 1px rgba(255,255,255,0.95);text-align:center;margin-top:54px;">
      <h3 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:10px;font-weight:700;">Have a Question Not Addressed Above?</h3>
      <p style="color:var(--color-text-muted);margin-bottom:24px;font-size:1.02rem;max-width:560px;margin-left:auto;margin-right:auto;">Our travel designers and pilgrimage advisors will be pleased to assist with customized requirements or special arrangements.</p>
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary" style="padding:12px 32px;font-size:0.95rem;">Contact Travel Specialist</a>
    </div>

  </div>
</section>
<style>
details[open] .faq-toggle-icon {
  transform: rotate(45deg);
  background: rgba(139,92,246,0.25) !important;
}
details[open] {
  border-color: rgba(141,164,239,0.6) !important;
  box-shadow: 0 16px 40px -8px rgba(139,92,246,0.15), inset 0 1px 1px rgba(255,255,255,0.8) !important;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
