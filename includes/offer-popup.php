<?php
/**
 * MIDHILA VOYAGES — Promotional Offer Popup Modal
 * Pure-White Luxury Editorial Modal Showcasing Ongoing Offers & Tour Promotions
 */

if (!defined('BASE_URL')) {
    return;
}

$popupActive = get_setting('popup_offer_active', '1');
if ($popupActive !== '1') {
    return;
}

$popupBadge = get_setting('popup_offer_badge', 'EXCLUSIVE ONGOING OFFER');
$popupTitle = get_setting('popup_offer_title', 'Divine Destinations • 5-Day Sacred Yatra');
$popupSubtitle = get_setting('popup_offer_subtitle', 'Experience Rameswaram, Madurai, Dhanushkodi & Thanjavur at ₹6,500/person. Sanitized AC transport & curated stays included.');
$popupPrice = get_setting('popup_offer_price', '₹6,500');
$popupPriceSub = get_setting('popup_offer_price_sub', 'per person • 5D / 4N');
$popupDates = get_setting('popup_offer_dates', 'Upcoming Tour Dates: 23.12.26 to 27.12.26');
$popupImage = get_setting('popup_offer_image', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80');
$popupBtnText = get_setting('popup_offer_btn_text', 'Claim Offer on WhatsApp');
$popupBtnLink = get_setting('popup_offer_link', '');
$popupFrequency = get_setting('popup_offer_frequency', 'session'); // 'session' or 'always'

// Resolve image path if uploaded locally
if (!empty($popupImage) && !preg_match('#^https?://#i', $popupImage)) {
    $popupImage = BASE_URL . '/' . ltrim($popupImage, '/');
}

// Build WhatsApp URL if no custom link provided
if (empty($popupBtnLink)) {
    $agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
    $waText = "Hello MIDHILA VOYAGES, I saw your ongoing offer '{$popupTitle}' (" . ($popupPrice ? $popupPrice : 'Special Deal') . ") on your website and would like to claim / book it.";
    $popupBtnLink = build_whatsapp_url($agencyWhatsApp, $waText);
    $isWhatsApp = true;
} else {
    $isWhatsApp = (strpos($popupBtnLink, 'wa.me') !== false || strpos($popupBtnLink, 'whatsapp') !== false);
}
?>

<!-- Promotional Offer Popup Modal Component -->
<div id="mvOfferModal" class="mv-offer-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mvOfferTitle">
  <div class="mv-offer-dialog">
    
    <!-- Close Button -->
    <button type="button" class="mv-offer-close" id="mvOfferCloseBtn" aria-label="Close promotion popup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <!-- Modal Card Body -->
    <div class="mv-offer-card">
      
      <!-- Top Promotional Image Poster -->
      <?php if (!empty($popupImage)): ?>
        <div class="mv-offer-img-box">
          <img src="<?= e($popupImage) ?>" alt="<?= e($popupTitle) ?>" class="mv-offer-img" loading="eager">
          <?php if (!empty($popupBadge)): ?>
            <div class="mv-offer-badge-floating">
              <span class="mv-offer-badge-pulse"></span>
              <span><?= e($popupBadge) ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Text & Action Area -->
      <div class="mv-offer-body">
        
        <?php if (empty($popupImage) && !empty($popupBadge)): ?>
          <div class="mv-offer-badge-inline">
            <span class="mv-offer-badge-pulse"></span>
            <span><?= e($popupBadge) ?></span>
          </div>
        <?php endif; ?>

        <h3 id="mvOfferTitle" class="mv-offer-title"><?= e($popupTitle) ?></h3>

        <?php if (!empty($popupPrice)): ?>
          <div class="mv-offer-price-row">
            <div class="mv-offer-price"><?= e($popupPrice) ?></div>
            <?php if (!empty($popupPriceSub)): ?>
              <div class="mv-offer-price-sub"><?= e($popupPriceSub) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($popupDates)): ?>
          <div class="mv-offer-dates-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span><?= e($popupDates) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($popupSubtitle)): ?>
          <p class="mv-offer-desc">
            <?= nl2br(e($popupSubtitle)) ?>
          </p>
        <?php endif; ?>

        <!-- Primary Action Button -->
        <div class="mv-offer-actions">
          <a href="<?= e($popupBtnLink) ?>" target="_blank" rel="noopener noreferrer" class="mv-offer-btn-primary">
            <?php if ($isWhatsApp): ?>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7 8.5 7 9.71C7 10.93 7.89 12.1 8 12.27C8.14 12.44 9.76 14.94 12.25 16C12.84 16.27 13.3 16.42 13.66 16.53C14.25 16.72 14.79 16.69 15.22 16.63C15.7 16.56 16.68 16.03 16.89 15.45C17.1 14.87 17.1 14.38 17.04 14.27C16.97 14.17 16.81 14.11 16.56 13.99C16.32 13.86 15.12 13.27 14.9 13.19C14.67 13.1 14.5 13.06 14.34 13.31C14.18 13.56 13.71 14.11 13.56 14.27C13.42 14.44 13.27 14.46 13.03 14.34C12.78 14.21 11.74 13.87 10.51 12.77C9.56 11.91 8.91 10.86 8.79 10.65C8.66 10.45 8.77 10.33 8.89 10.21C9 10.1 9.15 9.91 9.28 9.76C9.41 9.62 9.45 9.5 9.53 9.35C9.62 9.2 9.58 9.06 9.52 8.94C9.45 8.82 9 7.72 8.81 7.25C8.63 6.79 8.44 6.86 8.3 6.85C8.17 6.85 8.01 6.85 7.85 6.85"/>
              </svg>
            <?php endif; ?>
            <span><?= e($popupBtnText) ?></span>
          </a>
          
          <button type="button" class="mv-offer-btn-skip" id="mvOfferSkipBtn">
            <span>Continue Browsing Website</span>
          </button>
        </div>

      </div>

    </div>

  </div>
</div>

<style>
  /* Promotional Offer Popup Overlay & Card Styles */
  .mv-offer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s ease;
  }
  
  .mv-offer-overlay.is-visible {
    opacity: 1;
    visibility: visible;
  }

  .mv-offer-dialog {
    position: relative;
    max-width: 500px;
    width: 100%;
    transform: scale(0.92) translateY(12px);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .mv-offer-overlay.is-visible .mv-offer-dialog {
    transform: scale(1) translateY(0);
  }

  .mv-offer-close {
    position: absolute;
    top: -14px;
    right: -14px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #0F172A;
    color: #FFFFFF;
    border: 2px solid #FFFFFF;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 20;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s ease, background 0.2s ease;
  }
  .mv-offer-close:hover {
    transform: scale(1.1) rotate(90deg);
    background: #DC2626;
  }

  .mv-offer-card {
    background: #FFFFFF;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 60px -12px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(197, 160, 89, 0.3);
  }

  .mv-offer-img-box {
    position: relative;
    height: 220px;
    width: 100%;
    overflow: hidden;
    background: #0F172A;
  }
  .mv-offer-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
  }
  .mv-offer-dialog:hover .mv-offer-img {
    transform: scale(1.04);
  }

  .mv-offer-badge-floating {
    position: absolute;
    bottom: 14px;
    left: 16px;
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(6px);
    color: #FBBF24;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(251, 191, 36, 0.35);
  }

  .mv-offer-badge-inline {
    background: #FEF3C7;
    color: #92400E;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
  }

  .mv-offer-badge-pulse {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 8px #10B981;
  }

  .mv-offer-body {
    padding: 26px 28px 24px;
    text-align: center;
  }

  .mv-offer-title {
    font-family: 'Cinzel', serif;
    font-size: clamp(1.3rem, 2.5vw, 1.6rem);
    font-weight: 700;
    color: #0F172A;
    line-height: 1.25;
    margin-bottom: 8px;
  }

  .mv-offer-price-row {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 8px;
    margin-bottom: 10px;
  }
  .mv-offer-price {
    font-size: 2rem;
    font-weight: 800;
    color: #059669;
    line-height: 1;
    letter-spacing: -0.02em;
  }
  .mv-offer-price-sub {
    font-size: 0.82rem;
    color: #64748B;
    font-weight: 500;
  }

  .mv-offer-dates-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    padding: 5px 14px;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #334155;
    font-weight: 600;
    margin-bottom: 14px;
  }

  .mv-offer-desc {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #475569;
    margin-bottom: 22px;
  }

  .mv-offer-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
  }

  .mv-offer-btn-primary {
    width: 100%;
    padding: 13px 24px;
    background: #25D366;
    color: #FFFFFF !important;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    text-decoration: none;
    box-shadow: 0 4px 18px rgba(37, 211, 102, 0.35);
    transition: all 0.25s ease;
  }
  .mv-offer-btn-primary:hover {
    background: #1EBE5D;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.45);
  }

  .mv-offer-btn-skip {
    background: transparent;
    border: none;
    color: #94A3B8;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    padding: 6px 12px;
    transition: color 0.2s;
  }
  .mv-offer-btn-skip:hover {
    color: #475569;
    text-decoration: underline;
  }

  @media (max-width: 600px) {
    .mv-offer-dialog { max-width: 100%; }
    .mv-offer-img-box { height: 180px; }
    .mv-offer-body { padding: 20px 18px; }
    .mv-offer-close { top: 10px; right: 10px; }
  }
</style>

<script>
  (function() {
    const modal = document.getElementById('mvOfferModal');
    const closeBtn = document.getElementById('mvOfferCloseBtn');
    const skipBtn = document.getElementById('mvOfferSkipBtn');
    if (!modal) return;

    const frequency = '<?= e($popupFrequency) ?>'; // 'session' or 'always'
    const storageKey = 'mv_offer_dismissed_v1';
    
    // Check if query string forces open (e.g. ?offer=1 or ?promo=1 or #offer)
    const urlParams = new URLSearchParams(window.location.search);
    const forceShow = urlParams.has('offer') || urlParams.has('promo') || window.location.hash === '#offer';

    function openModal() {
      modal.classList.add('is-visible');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      modal.classList.remove('is-visible');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (frequency === 'session') {
        try { sessionStorage.setItem(storageKey, 'true'); } catch(e) {}
      }
    }

    // Auto open check
    if (forceShow) {
      setTimeout(openModal, 600);
    } else {
      let alreadySeen = false;
      try {
        alreadySeen = (frequency === 'session') && sessionStorage.getItem(storageKey);
      } catch(e) {}

      if (!alreadySeen) {
        setTimeout(openModal, 900);
      }
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (skipBtn) skipBtn.addEventListener('click', closeModal);

    // Backdrop click dismiss
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    // Escape key listener
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
        closeModal();
      }
    });

    // Global trigger: any button with [data-open-offer] opens it
    document.addEventListener('click', function(e) {
      const trigger = e.target.closest('[data-open-offer]');
      if (trigger) {
        e.preventDefault();
        openModal();
      }
    });
  })();
</script>
