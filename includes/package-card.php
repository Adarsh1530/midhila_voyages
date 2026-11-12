<?php
/**
 * MIDHILA VOYAGES — Reusable Editorial Tour Package Card
 * Pure White Surface with 100% Full-Opacity Inspection Photography
 */
if (!isset($package)) return;

$pkgId = $package['id'] ?? 0;
$pkgTitle = $package['title'] ?? 'Signature Journey';
$pkgSlug = $package['slug'] ?? 'package-' . $pkgId;
$pkgDuration = $package['duration_days'] ?? 5;
$pkgNights = max(1, $pkgDuration - 1);
$pkgPrice = $package['price'] ?? 0;
$pkgCategory = $package['category_name'] ?? 'Curated';
$pkgDestination = $package['destination_name'] ?? 'India';
$pkgExcerpt = $package['short_description'] ?? 'An immersive luxury itinerary crafted with private chauffeured transfers, curated stays, and authentic cultural exploration.';
$pkgImage = !empty($package['featured_image']) ? $package['featured_image'] : (ASSETS_URL . '/images/placeholder.svg');

$waMessage = "Hello MIDHILA VOYAGES, I am interested in the \"" . $pkgTitle . "\" package (" . $pkgDuration . " Days). Please share the complete itinerary and pricing details.";
$cardWaUrl = build_whatsapp_url(get_setting('whatsapp_number', '+918075021427'), $waMessage);
$cardDetailUrl = BASE_URL . '/package.php?slug=' . urlencode($pkgSlug);
?>

<div class="editorial-pkg-card reveal-fade-up">
  <!-- 100% Full Opacity Photography for Customer Inspection -->
  <div class="editorial-pkg-card__media">
    <a href="<?= e($cardDetailUrl) ?>" aria-label="View <?= e($pkgTitle) ?>">
      <img src="<?= e($pkgImage) ?>" 
           alt="<?= e($pkgTitle) ?>" 
           class="editorial-pkg-card__img" 
           loading="lazy" 
           width="420" 
           height="260">
    </a>

    <!-- Duration Pill -->
    <span class="editorial-pkg-card__duration">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      <?= (int)$pkgDuration ?>D / <?= (int)$pkgNights ?>N
    </span>

    <!-- Category Pill -->
    <span class="editorial-pkg-card__category">
      <?= e($pkgCategory) ?>
    </span>
  </div>

  <!-- Package Content -->
  <div class="editorial-pkg-card__body">
    <div class="editorial-pkg-card__region">
      <?= e($pkgDestination) ?>
    </div>

    <h3 class="editorial-pkg-card__title">
      <a href="<?= e($cardDetailUrl) ?>">
        <?= e($pkgTitle) ?>
      </a>
    </h3>

    <p class="editorial-pkg-card__excerpt">
      <?= e($pkgExcerpt) ?>
    </p>

    <!-- Pricing & Dual CTAs -->
    <div class="editorial-pkg-card__footer">
      <div class="editorial-pkg-card__price-box">
        <span class="editorial-pkg-card__price-label">Starting From</span>
        <div class="editorial-pkg-card__price-value">
          <?php if ($pkgPrice > 0): ?>
            <?= format_price($pkgPrice) ?>
          <?php else: ?>
            <span style="font-size:1rem;color:var(--color-gold-deep);">On Request</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="editorial-pkg-card__actions">
        <a href="<?= e($cardDetailUrl) ?>" class="editorial-pkg-card__btn-view">
          Itinerary &rarr;
        </a>
        <a href="<?= e($cardWaUrl) ?>" target="_blank" rel="noopener noreferrer" class="editorial-pkg-card__btn-wa" title="WhatsApp Enquiry" aria-label="WhatsApp Enquiry for <?= e($pkgTitle) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</div>
