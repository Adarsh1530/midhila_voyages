<?php
/**
 * MIDHILA VOYAGES — Reusable Package Card Component
 * Expects $package array
 */

if (!isset($package) || !is_array($package)) {
    return;
}

$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$pkgTitle = $package['title'] ?? 'Tour Package';
$pkgDest = $package['destination_name'] ?? $package['region_state'] ?? 'Destination';
$pkgSlug = $package['slug'] ?? '';
$pkgDuration = ($package['duration_days'] ?? 1) . 'D / ' . ($package['duration_nights'] ?? 0) . 'N';
$pkgImage = get_image_url($package['featured_image'] ?? '');
$pkgAlt = $package['alt_text'] ?? $pkgTitle;
$pkgPrice = !empty($package['is_price_on_request']) ? null : ($package['starting_price'] ?? null);
$pkgInclusions = !empty($package['inclusions']) ? array_slice(array_map('trim', explode(',', $package['inclusions'])), 0, 3) : ['Comfort Stays', 'Sightseeing Transfers', 'Breakfast'];

$waMsg = "Hello MIDHILA VOYAGES, I would like to enquire about the \"{$pkgTitle}\" package ({$pkgDuration}). Please share the full itinerary, inclusions, availability, and quotation.";
$waPkgUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);
$pkgDetailUrl = BASE_URL . '/package.php?slug=' . urlencode($pkgSlug);
?>

<div class="package-card-wrapper" 
     data-category="<?= e($package['category_slug'] ?? '') ?>" 
     data-pilgrimage="<?= !empty($package['is_pilgrimage']) ? '1' : '0' ?>"
     data-title="<?= e(strtolower($pkgTitle)) ?>"
     data-destination="<?= e(strtolower($pkgDest)) ?>">
  
  <article class="package-card card-3d">
    
    <!-- Media & Badges -->
    <div class="package-card__media">
      <img src="<?= e($pkgImage) ?>" alt="<?= e($pkgAlt) ?>" class="package-card__img" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
      <span class="package-card__badge"><?= e($package['category_name'] ?? 'Tour') ?></span>
      <span class="package-card__duration">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <?= e($pkgDuration) ?>
      </span>
    </div>

    <!-- Body -->
    <div class="package-card__body">
      <div class="package-card__destination"><?= e($pkgDest) ?></div>
      <h3 class="package-card__title">
        <a href="<?= e($pkgDetailUrl) ?>"><?= e($pkgTitle) ?></a>
      </h3>
      <p class="package-card__desc"><?= e($package['short_description'] ?? '') ?></p>

      <!-- Inclusions Snippet -->
      <div class="package-card__inclusions">
        <?php foreach ($pkgInclusions as $inc): ?>
          <span class="inclusion-pill">✓ <?= e($inc) ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Footer & Actions -->
      <div class="package-card__footer">
        <div class="package-card__price-box">
          <span class="package-card__price-label">Starting From</span>
          <?php if ($pkgPrice !== null): ?>
            <span class="package-card__price"><?= format_price((float)$pkgPrice, $package['currency'] ?? 'INR') ?></span>
          <?php else: ?>
            <span class="package-card__price package-card__price--quote">Request a Quote</span>
          <?php endif; ?>
        </div>

        <div class="package-card__actions">
          <a href="<?= e($waPkgUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-sm" title="Enquire on WhatsApp">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
            <span>WhatsApp</span>
          </a>
          <a href="<?= e($pkgDetailUrl) ?>" class="btn btn-outline-gold btn-sm">
            <span>View</span>
          </a>
        </div>
      </div>

    </div>

  </article>
</div>
