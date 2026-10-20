<?php
/**
 * MIDHILA VOYAGES — Frequently Asked Questions
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Frequently Asked Questions | Midhila Voyages';
$pageDescription = 'Transparent answers regarding tour packages, payment procedures, travel documents, and pilgrimage arrangements with Midhila Voyages.';

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

<!-- FAQ Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.22;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Frequently Asked Questions
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      Clear, straightforward answers regarding booking, private chauffeured transfers, elderly pilgrimage care, and payment protection.
    </p>
  </div>
</section>

<!-- FAQs Accordion Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">
    
    <?php foreach ($groupedFaqs as $category => $items): ?>
      <div style="margin-bottom:48px;">
        <div class="chapter-badge">
          <span class="chapter-number"><?= e($category) ?> Questions</span>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:16px;">
          <?php foreach ($items as $item): ?>
            <details style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:22px 26px;box-shadow:var(--shadow-ambient);transition:all var(--transition-fast);">
              <summary style="font-family:var(--font-serif);font-size:1.15rem;font-weight:600;color:var(--color-text-headline);cursor:pointer;outline:none;display:flex;justify-content:space-between;align-items:center;user-select:none;">
                <span><?= e($item['question']) ?></span>
                <span style="color:var(--color-gold-deep);font-size:1.3rem;font-weight:700;">+</span>
              </summary>
              <div style="padding-top:16px;color:var(--color-text-body);line-height:1.8;font-size:0.95rem;border-top:1px solid var(--color-border-hairline);margin-top:14px;">
                <?= nl2br(e($item['answer'])) ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Still Have Questions Box -->
    <div style="text-align:center;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:40px;margin-top:60px;">
      <h3 style="font-size:1.35rem;margin-bottom:8px;">Have a Question Not Listed Here?</h3>
      <p style="font-size:0.95rem;color:var(--color-text-muted);margin-bottom:20px;">Our Kerala travel desk is available daily from 9:00 AM to 7:30 PM.</p>
      <a href="<?= BASE_URL ?>/contact.php" class="btn-cinematic-primary">
        <span>Speak to Our Travel Desk</span>
      </a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
