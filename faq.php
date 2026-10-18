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
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80" alt="FAQ" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Information &amp; Guidance</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;">Frequently Asked Questions</h1>
    <p class="lead-text" style="color:#CBD5E0;">Transparent, accurate answers regarding our tour packages, payment procedures, travel documents, and pilgrimage arrangements.</p>
  </div>
</section>

<!-- FAQs Section -->
<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container container-narrow">
    
    <?php foreach ($groupedFaqs as $category => $items): ?>
      <div style="margin-bottom:44px;">
        <span class="section-subtitle no-after"><?= e($category) ?> Inquiries</span>
        <h2 style="font-size:1.6rem;color:var(--color-navy);margin-bottom:20px;"><?= e($category) ?> Questions</h2>
        
        <div style="display:flex;flex-direction:column;gap:14px;">
          <?php foreach ($items as $item): ?>
            <details class="faq-item" style="background:#FFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:18px 24px;box-shadow:var(--shadow-sm);">
              <summary style="font-family:var(--font-serif);font-size:1.05rem;font-weight:700;color:var(--color-navy);cursor:pointer;outline:none;display:flex;justify-content:space-between;align-items:center;">
                <span><?= e($item['question']) ?></span>
                <span style="color:var(--color-gold);font-size:1.4rem;font-weight:400;margin-left:12px;">+</span>
              </summary>
              <div style="padding-top:14px;color:var(--color-text-body);line-height:1.75;font-size:0.95rem;border-top:1px solid rgba(0,0,0,0.05);margin-top:12px;">
                <?= nl2br(e($item['answer'])) ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="background:#FFF;padding:32px;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--color-border);text-align:center;margin-top:48px;">
      <h3 style="font-size:1.3rem;color:var(--color-navy);margin-bottom:8px;">Have a Question Not Addressed Above?</h3>
      <p style="color:var(--color-text-muted);margin-bottom:20px;">Our travel desk will be pleased to assist with customized requirements or special arrangements.</p>
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary">Contact Travel Specialist</a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
