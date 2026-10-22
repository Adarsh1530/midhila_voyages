<?php
/**
 * MIDHILA VOYAGES — Website Content Editor
 * User-Friendly Visual Management of All Public Website Sections
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $activeTab = trim($_POST['active_tab'] ?? 'tab-home');

        // Complete dictionary of editable public content
        $contentFields = [
            // 1. Homepage Hero
            'hero_headline_1' => ['group' => 'home_hero', 'default' => 'Travelled by Scroll.'],
            'hero_headline_2' => ['group' => 'home_hero', 'default' => 'Unfolding Sacred & Sublime Horizons.'],
            'hero_subtext' => ['group' => 'home_hero', 'default' => 'Where bespoke travel is an art of quiet luxury, reverence, and unhurried discovery. Handcrafted journeys across India’s most storied sanctuaries.'],
            'hero_bg_image' => ['group' => 'home_hero', 'default' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1920&q=80'],

            // 2. Homepage Destinations Showcase Intro
            'dest_heading_1' => ['group' => 'home_dest', 'default' => 'Six Sacred Lands.'],
            'dest_heading_2' => ['group' => 'home_dest', 'default' => 'Crafted for the Discerning Explorer.'],
            'dest_link_text' => ['group' => 'home_dest', 'default' => 'View All Horizons'],

            // 3. Homepage Packages Catalog Intro
            'pkg_heading_1' => ['group' => 'home_pkg', 'default' => 'Handcrafted Journeys.'],
            'pkg_heading_2' => ['group' => 'home_pkg', 'default' => 'Every detail verified with private chauffeur & verified stays.'],
            'pkg_btn_text' => ['group' => 'home_pkg', 'default' => 'Complete Catalog'],

            // 4. Pilgrimage & Sacred Circuit (Homepage & Pilgrimage Page)
            'pilgrim_badge' => ['group' => 'pilgrim', 'default' => 'Sacred Vedic Circuit • Tamil Nadu & Kashi'],
            'pilgrim_title' => ['group' => 'pilgrim', 'default' => 'Divine Destinations...'],
            'pilgrim_subtitle' => ['group' => 'pilgrim', 'default' => 'A Journey of Deep Reverence & Faith.'],
            'pilgrim_desc' => ['group' => 'pilgrim', 'default' => 'Experience seamlessly managed temple yatras across Sri Murugan Temple Thiruchendur, Sri Ramanathaswamy Rameswaram, Dhanushkodi, Brihadeeswarar Thanjavur, and Meenakshi Amman Madurai. Verified sanitised accommodations, elderly-first ground coordination, and zero hidden costs.'],
            'pilgrim_featured_price' => ['group' => 'pilgrim', 'default' => '₹6,500'],
            'pilgrim_featured_duration' => ['group' => 'pilgrim', 'default' => 'per person • 5 Days / 4 Nights'],
            'pilgrim_upcoming_dates' => ['group' => 'pilgrim', 'default' => '23.12.26 to 27.12.26'],
            'pilgrim_special_note' => ['group' => 'pilgrim', 'default' => 'Hurry, limited slots on first come, first served basis!'],
            'pilgrim_hotlines' => ['group' => 'pilgrim', 'default' => '+91 8075021427, 9446154048'],
            'pilgrim_hero_title' => ['group' => 'pilgrim', 'default' => 'Journeys of Faith, Serenity & Devotion'],
            'pilgrim_hero_subtext' => ['group' => 'pilgrim', 'default' => 'Thoughtfully planned pilgrimage trails honoring your spiritual quest. Elderly comfort, dedicated private AC transport, verified sanitized stays, and respectful unhurried pacing.'],

            // 5. Brand Philosophy & 4 Pillars (Homepage)
            'pillars_title' => ['group' => 'pillars', 'default' => 'Hospitality Defined by Stillness, Depth & Care.'],
            'pillars_subtitle' => ['group' => 'pillars', 'default' => 'We believe a journey should be deeply felt, never rushed.'],
            'pillar_1_title' => ['group' => 'pillars', 'default' => 'Handcrafted Routing'],
            'pillar_1_desc' => ['group' => 'pillars', 'default' => 'Every route is custom paced to balance sacred exploration, cultural depth, and gentle rest.'],
            'pillar_2_title' => ['group' => 'pillars', 'default' => 'Verified Sanctuaries'],
            'pillar_2_desc' => ['group' => 'pillars', 'default' => 'Personally vetted heritage resorts, clean temple guest houses, and scenic boutique retreats.'],
            'pillar_3_title' => ['group' => 'pillars', 'default' => 'Dedicated Chauffeurs'],
            'pillar_3_desc' => ['group' => 'pillars', 'default' => 'Courteous, verified regional drivers with well-maintained AC fleets ensuring seamless road transit.'],
            'pillar_4_title' => ['group' => 'pillars', 'default' => 'Transparent Integrity'],
            'pillar_4_desc' => ['group' => 'pillars', 'default' => 'Zero hidden surcharges, honest pricing, and complete written inclusions on every voucher.'],

            // 6. Homepage Booking / Enquiry Callout
            'cta_title' => ['group' => 'cta', 'default' => 'Plan Your Bespoke Journey'],
            'cta_subtitle' => ['group' => 'cta', 'default' => 'Tell our travel specialists your preferred destinations, dates, and rhythm. We respond within hours with a tailored itinerary.'],
            'cta_btn_text' => ['group' => 'cta', 'default' => 'Submit Travel Request'],

            // 7. About Us Page
            'about_hero_title' => ['group' => 'about', 'default' => 'The Midhila Voyages Philosophy'],
            'about_hero_subtitle' => ['group' => 'about', 'default' => 'Born from a profound respect for India’s timeless landscapes and sacred heritage, we curate journeys defined by grace, stillness, and human warmth.'],
            'about_quote_headline' => ['group' => 'about', 'default' => 'We are not merely arranging transit. We are guiding you into moments that linger for a lifetime.'],
            'about_story_p1' => ['group' => 'about', 'default' => 'MIDHILA VOYAGES began with a fundamental realization: modern travel had become hurried, transactional, and exhausting. Pilgrims were left navigating overwhelming temple crowds with little care for elder comfort, while families found themselves trapped in cookie-cutter packages devoid of authentic connection.'],
            'about_story_p2' => ['group' => 'about', 'default' => 'We chose a different path. Rooted in the rich cultural soil of Kerala, our agency designs every journey like an editorial narrative: unhurried, transparent, respectful of local traditions, and supported by a 24/7 dedicated private chauffeur network.'],

            // 8. Services Page
            'services_hero_title' => ['group' => 'services', 'default' => 'Seamless Travel Curation'],
            'services_hero_subtitle' => ['group' => 'services', 'default' => 'From private chauffeured road transit to sacred darshan planning and luxury boutique stays, our concierge manages every detail.'],
            'services_cta_text' => ['group' => 'services', 'default' => 'Consult with a Travel Specialist'],

            // 9. Agency Details, Contact Page & Footer
            'contact_hero_title' => ['group' => 'contact', 'default' => 'Direct Agency Assistance'],
            'contact_hero_subtext' => ['group' => 'contact', 'default' => 'Whether reserving a sacred yatra or planning a bespoke holiday, our coordinators are ready to assist.'],
            'site_name' => ['group' => 'agency', 'default' => 'MIDHILA VOYAGES'],
            'primary_phone' => ['group' => 'agency', 'default' => '+91 8075021427'],
            'secondary_phone' => ['group' => 'agency', 'default' => '+91 9446154048'],
            'hotline_phone' => ['group' => 'agency', 'default' => '+91 8943669027'],
            'whatsapp_number' => ['group' => 'agency', 'default' => '+918075021427'],
            'primary_email' => ['group' => 'agency', 'default' => 'contact@midhilavoyages.com'],
            'office_address' => ['group' => 'agency', 'default' => 'Midhila Voyages, Travel & Pilgrimage Hub, Kerala, India'],
            'working_hours' => ['group' => 'agency', 'default' => 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)'],
            'footer_description' => ['group' => 'agency', 'default' => 'A premier travel and pilgrimage management house dedicated to curating bespoke domestic journeys, sacred temple yatras, and luxury escapes with uncompromised integrity.'],
            'footer_tagline' => ['group' => 'agency', 'default' => 'Beyond Destinations. Into Experiences.'],
        ];

        foreach ($contentFields as $key => $meta) {
            if (isset($_POST[$key])) {
                $val = trim((string)$_POST[$key]);
                update_setting($key, $val, $meta['group']);
            }
        }

        log_activity($_SESSION['admin_user_id'] ?? null, 'update_content', 'Updated public website page sections and copy.');
        set_flash_message('success', 'Website content updated successfully! All public pages reflect your changes immediately.');
        header('Location: ' . ADMIN_URL . '/content.php#' . urlencode($activeTab));
        exit;
    }
}

// Fetch current site settings
$settings = [];
try {
    $stmt = $db->query("SELECT `setting_key`, `setting_value` FROM `site_settings`");
    while ($r = $stmt->fetch()) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
} catch (Exception $e) {}

// Quick value getter with fallback
function val(string $key, string $default = ''): string {
    global $settings;
    return $settings[$key] ?? $default;
}

$adminTitle = 'Page Sections Editor | MIDHILA VOYAGES';
$adminHeading = 'Website Page Sections';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:1040px;margin:0 auto;padding-bottom:100px;">
  
  <!-- Header Banner -->
  <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;padding:24px 28px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <h3 style="font-size:1.35rem;font-weight:700;color:#0F172A;margin-bottom:4px;display:flex;align-items:center;gap:10px;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#C5A059" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        <span>Public Website Content Editor</span>
      </h3>
      <p style="font-size:0.86rem;color:#64748B;margin:0;">
        Easily edit headlines, descriptions, highlights, and contact details across the public website. Everything updates live.
      </p>
    </div>

    <div style="display:flex;align-items:center;gap:10px;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:600;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
        <span>Preview Website ↗</span>
      </a>
    </div>
  </div>

  <!-- Interactive Section Tabs Navigation -->
  <div style="display:flex;gap:8px;margin-bottom:20px;overflow-x:auto;padding-bottom:4px;" id="contentTabs">
    <button type="button" class="tab-btn is-active" data-tab="tab-home" style="padding:10px 18px;border-radius:8px;border:1px solid #CBD5E1;background:#0F172A;color:#FFFFFF;font-weight:600;font-size:0.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;">
      <span>🏠 Homepage Sections</span>
    </button>
    <button type="button" class="tab-btn" data-tab="tab-pilgrim" style="padding:10px 18px;border-radius:8px;border:1px solid #CBD5E1;background:#FFFFFF;color:#475569;font-weight:600;font-size:0.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;">
      <span>🛕 Pilgrimage &amp; Sacred Circuit</span>
    </button>
    <button type="button" class="tab-btn" data-tab="tab-about" style="padding:10px 18px;border-radius:8px;border:1px solid #CBD5E1;background:#FFFFFF;color:#475569;font-weight:600;font-size:0.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;">
      <span>📖 About Us &amp; Story</span>
    </button>
    <button type="button" class="tab-btn" data-tab="tab-services" style="padding:10px 18px;border-radius:8px;border:1px solid #CBD5E1;background:#FFFFFF;color:#475569;font-weight:600;font-size:0.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;">
      <span>🧭 Services Page</span>
    </button>
    <button type="button" class="tab-btn" data-tab="tab-contact" style="padding:10px 18px;border-radius:8px;border:1px solid #CBD5E1;background:#FFFFFF;color:#475569;font-weight:600;font-size:0.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;">
      <span>📞 Contact &amp; Footer</span>
    </button>
  </div>

  <form method="POST" action="" id="contentForm">
    <?= csrf_field() ?>
    <input type="hidden" name="active_tab" id="activeTabInput" value="tab-home">

    <!-- ===================================================================
         TAB 1: HOMEPAGE SECTIONS
         =================================================================== -->
    <div class="tab-content" id="tab-home" style="display:block;">
      
      <!-- 1.1 Homepage Hero -->
      <div class="admin-card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">1. Homepage Hero Banner</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">The large top headline and introduction visitors see first.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Homepage Top</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">
              Headline (Line 1 - Normal Text)
            </label>
            <input type="text" name="hero_headline_1" value="<?= e(val('hero_headline_1', 'Travelled by Scroll.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
            <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Main opening headline phrase</span>
          </div>

          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">
              Headline (Line 2 - Gold Editorial Accent)
            </label>
            <input type="text" name="hero_headline_2" value="<?= e(val('hero_headline_2', 'Unfolding Sacred & Sublime Horizons.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
            <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Appears in gold italic editorial typography</span>
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">
            Hero Editorial Paragraph
          </label>
          <textarea name="hero_subtext" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;line-height:1.6;"><?= e(val('hero_subtext', 'Where bespoke travel is an art of quiet luxury, reverence, and unhurried discovery. Handcrafted journeys across India’s most storied sanctuaries.')) ?></textarea>
          <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Supporting description beneath the main headline</span>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">
            Background Photographic Backdrop URL
          </label>
          <input type="url" name="hero_bg_image" value="<?= e(val('hero_bg_image', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1920&q=80')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Subtle 32% layered backdrop behind the Three.js coordinate globe</span>
        </div>
      </div>

      <!-- 1.2 Destinations Showcase Header -->
      <div class="admin-card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">2. Geographic Sanctuaries (Destinations Showcase)</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">The title and button text introducing featured regions.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Homepage Chapter II</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Heading Line 1</label>
            <input type="text" name="dest_heading_1" value="<?= e(val('dest_heading_1', 'Six Sacred Lands.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Heading Line 2 (Subtitle Accent)</label>
            <input type="text" name="dest_heading_2" value="<?= e(val('dest_heading_2', 'Crafted for the Discerning Explorer.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">View All Link Button Text</label>
          <input type="text" name="dest_link_text" value="<?= e(val('dest_link_text', 'View All Horizons')) ?>" style="width:100%;max-width:320px;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>
      </div>

      <!-- 1.3 Signature Tour Packages Header -->
      <div class="admin-card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">3. Signature Tour Packages Catalog Section</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">The heading above the tour cards grid.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Homepage Chapter III</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Heading Line 1</label>
            <input type="text" name="pkg_heading_1" value="<?= e(val('pkg_heading_1', 'Handcrafted Journeys.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Heading Line 2 (Subtitle Accent)</label>
            <input type="text" name="pkg_heading_2" value="<?= e(val('pkg_heading_2', 'Every detail verified with private chauffeur & verified stays.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Complete Catalog Button Text</label>
          <input type="text" name="pkg_btn_text" value="<?= e(val('pkg_btn_text', 'Complete Catalog')) ?>" style="width:100%;max-width:320px;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>
      </div>

      <!-- 1.4 Homepage 4 Pillars / Concierge Craft -->
      <div class="admin-card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">4. Concierge Craft &amp; 4 Brand Pillars</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">The core values displayed in 4 distinct white cards.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Homepage Chapter V</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Section Main Heading</label>
            <input type="text" name="pillars_title" value="<?= e(val('pillars_title', 'Hospitality Defined by Stillness, Depth & Care.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Section Subtitle</label>
            <input type="text" name="pillars_subtitle" value="<?= e(val('pillars_subtitle', 'We believe a journey should be deeply felt, never rushed.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          <!-- Pillar 1 -->
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">✦ Pillar 1 Title</label>
            <input type="text" name="pillar_1_title" value="<?= e(val('pillar_1_title', 'Handcrafted Routing')) ?>" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;margin-bottom:10px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Pillar 1 Description</label>
            <textarea name="pillar_1_desc" rows="2" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;"><?= e(val('pillar_1_desc', 'Every route is custom paced to balance sacred exploration, cultural depth, and gentle rest.')) ?></textarea>
          </div>

          <!-- Pillar 2 -->
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">❖ Pillar 2 Title</label>
            <input type="text" name="pillar_2_title" value="<?= e(val('pillar_2_title', 'Verified Sanctuaries')) ?>" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;margin-bottom:10px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Pillar 2 Description</label>
            <textarea name="pillar_2_desc" rows="2" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;"><?= e(val('pillar_2_desc', 'Personally vetted heritage resorts, clean temple guest houses, and scenic boutique retreats.')) ?></textarea>
          </div>

          <!-- Pillar 3 -->
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">◈ Pillar 3 Title</label>
            <input type="text" name="pillar_3_title" value="<?= e(val('pillar_3_title', 'Dedicated Chauffeurs')) ?>" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;margin-bottom:10px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Pillar 3 Description</label>
            <textarea name="pillar_3_desc" rows="2" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;"><?= e(val('pillar_3_desc', 'Courteous, verified regional drivers with well-maintained AC fleets ensuring seamless road transit.')) ?></textarea>
          </div>

          <!-- Pillar 4 -->
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">❂ Pillar 4 Title</label>
            <input type="text" name="pillar_4_title" value="<?= e(val('pillar_4_title', 'Transparent Integrity')) ?>" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;margin-bottom:10px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Pillar 4 Description</label>
            <textarea name="pillar_4_desc" rows="2" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;"><?= e(val('pillar_4_desc', 'Zero hidden surcharges, honest pricing, and complete written inclusions on every voucher.')) ?></textarea>
          </div>
        </div>
      </div>

      <!-- 1.5 Homepage Plan Your Journey Form Banner -->
      <div class="admin-card" style="padding:28px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">5. Booking / Travel Enquiry Callout Box</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">The custom planning contact box at the bottom of the homepage.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Homepage Bottom</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Form Section Heading</label>
            <input type="text" name="cta_title" value="<?= e(val('cta_title', 'Plan Your Bespoke Journey')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Submit Button Label</label>
            <input type="text" name="cta_btn_text" value="<?= e(val('cta_btn_text', 'Submit Travel Request')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Form Supporting Subtext</label>
          <textarea name="cta_subtitle" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('cta_subtitle', 'Tell our travel specialists your preferred destinations, dates, and rhythm. We respond within hours with a tailored itinerary.')) ?></textarea>
        </div>
      </div>

    </div>

    <!-- ===================================================================
         TAB 2: PILGRIMAGE & SACRED CIRCUIT
         =================================================================== -->
    <div class="tab-content" id="tab-pilgrim" style="display:none;">
      
      <div class="admin-card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">Sacred Vedic Circuit &bull; Special Yatra Offer</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">Configures the highlighted pilgrimage card on both the Homepage and Pilgrimage Portal.</p>
          </div>
          <span style="background:#FFFBEB;color:#D97706;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;border:1px solid #FDE68A;">Pilgrim Highlight</span>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Top Sacred Circuit Badge Text</label>
          <input type="text" name="pilgrim_badge" value="<?= e(val('pilgrim_badge', 'Sacred Vedic Circuit • Tamil Nadu & Kashi')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Main Title</label>
            <input type="text" name="pilgrim_title" value="<?= e(val('pilgrim_title', 'Divine Destinations...')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Subtitle (Gold Italic Accent)</label>
            <input type="text" name="pilgrim_subtitle" value="<?= e(val('pilgrim_subtitle', 'A Journey of Deep Reverence & Faith.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Circuit Description &amp; Temple Details</label>
          <textarea name="pilgrim_desc" rows="3" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;line-height:1.6;"><?= e(val('pilgrim_desc', 'Experience seamlessly managed temple yatras across Sri Murugan Temple Thiruchendur, Sri Ramanathaswamy Rameswaram, Dhanushkodi, Brihadeeswarar Thanjavur, and Meenakshi Amman Madurai. Verified sanitised accommodations, elderly-first ground coordination, and zero hidden costs.')) ?></textarea>
        </div>

        <!-- Tariff & Dates Box -->
        <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:10px;padding:20px;margin-bottom:20px;">
          <h5 style="font-size:0.92rem;font-weight:700;color:#0F172A;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
            <span>💰 Special Yatra Tariff &amp; Booking Details</span>
          </h5>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Featured Price (e.g. ₹6,500)</label>
              <input type="text" name="pilgrim_featured_price" value="<?= e(val('pilgrim_featured_price', '₹6,500')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.92rem;font-weight:700;color:#059669;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Duration / Per Person Label</label>
              <input type="text" name="pilgrim_featured_duration" value="<?= e(val('pilgrim_featured_duration', 'per person • 5 Days / 4 Nights')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.92rem;">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Upcoming Tour Dates</label>
              <input type="text" name="pilgrim_upcoming_dates" value="<?= e(val('pilgrim_upcoming_dates', '23.12.26 to 27.12.26')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.92rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Special Note / Hurry Alert</label>
              <input type="text" name="pilgrim_special_note" value="<?= e(val('pilgrim_special_note', 'Hurry, limited slots on first come, first served basis!')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.92rem;">
            </div>
          </div>

          <div>
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Hotlines Phone Display</label>
            <input type="text" name="pilgrim_hotlines" value="<?= e(val('pilgrim_hotlines', '+91 8075021427, 9446154048')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.92rem;">
          </div>
        </div>

        <!-- Pilgrimage Dedicated Page Hero -->
        <div style="border-top:1px solid #E2E8F0;padding-top:20px;">
          <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:14px;">Pilgrimage Portal Page (/pilgrimage.php) Hero Banner</h5>
          <div style="margin-bottom:16px;">
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Page Hero Heading</label>
            <input type="text" name="pilgrim_hero_title" value="<?= e(val('pilgrim_hero_title', 'Journeys of Faith, Serenity & Devotion')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Page Hero Subtitle</label>
            <textarea name="pilgrim_hero_subtext" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('pilgrim_hero_subtext', 'Thoughtfully planned pilgrimage trails honoring your spiritual quest. Elderly comfort, dedicated private AC transport, verified sanitized stays, and respectful unhurried pacing.')) ?></textarea>
          </div>
        </div>

      </div>

    </div>

    <!-- ===================================================================
         TAB 3: ABOUT US & STORY
         =================================================================== -->
    <div class="tab-content" id="tab-about" style="display:none;">
      
      <div class="admin-card" style="padding:28px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">About Us Narrative &amp; Brand Philosophy</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">Displayed on the dedicated About page (/about.php).</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">About Page</span>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">About Page Hero Title</label>
          <input type="text" name="about_hero_title" value="<?= e(val('about_hero_title', 'The Midhila Voyages Philosophy')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">About Page Hero Subtitle</label>
          <textarea name="about_hero_subtitle" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('about_hero_subtitle', 'Born from a profound respect for India’s timeless landscapes and sacred heritage, we curate journeys defined by grace, stillness, and human warmth.')) ?></textarea>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Featured Quote Headline</label>
          <input type="text" name="about_quote_headline" value="<?= e(val('about_quote_headline', 'We are not merely arranging transit. We are guiding you into moments that linger for a lifetime.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Brand Narrative — Paragraph 1</label>
          <textarea name="about_story_p1" rows="3" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;line-height:1.6;"><?= e(val('about_story_p1', 'MIDHILA VOYAGES began with a fundamental realization: modern travel had become hurried, transactional, and exhausting. Pilgrims were left navigating overwhelming temple crowds with little care for elder comfort, while families found themselves trapped in cookie-cutter packages devoid of authentic connection.')) ?></textarea>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Brand Narrative — Paragraph 2</label>
          <textarea name="about_story_p2" rows="3" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;line-height:1.6;"><?= e(val('about_story_p2', 'We chose a different path. Rooted in the rich cultural soil of Kerala, our agency designs every journey like an editorial narrative: unhurried, transparent, respectful of local traditions, and supported by a 24/7 dedicated private chauffeur network.')) ?></textarea>
        </div>
      </div>

    </div>

    <!-- ===================================================================
         TAB 4: SERVICES PAGE
         =================================================================== -->
    <div class="tab-content" id="tab-services" style="display:none;">
      
      <div class="admin-card" style="padding:28px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">Services &amp; Concierge Page Header</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">Displayed on the dedicated Services page (/services.php).</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Services Page</span>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Services Page Hero Title</label>
          <input type="text" name="services_hero_title" value="<?= e(val('services_hero_title', 'Seamless Travel Curation')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Services Page Subtitle</label>
          <textarea name="services_hero_subtitle" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('services_hero_subtitle', 'From private chauffeured road transit to sacred darshan planning and luxury boutique stays, our concierge manages every detail.')) ?></textarea>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Hero WhatsApp Consultation Button Text</label>
          <input type="text" name="services_cta_text" value="<?= e(val('services_cta_text', 'Consult with a Travel Specialist')) ?>" style="width:100%;max-width:360px;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
        </div>
      </div>

    </div>

    <!-- ===================================================================
         TAB 5: CONTACT & FOOTER
         =================================================================== -->
    <div class="tab-content" id="tab-contact" style="display:none;">
      
      <div class="admin-card" style="padding:28px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;border-bottom:1px solid #F1F5F9;padding-bottom:14px;">
          <div>
            <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">Agency Profile, Hotlines &amp; Footer Info</h4>
            <p style="font-size:0.82rem;color:#64748B;margin:0;">These verified details appear in the global website footer, contact page, and WhatsApp links.</p>
          </div>
          <span style="background:#F1F5F9;color:#475569;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:6px;">Global Footer &amp; Contact</span>
        </div>

        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:18px;margin-bottom:24px;">
          <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:12px;">Contact Page (/contact.php) Hero Heading</h5>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Contact Page Headline</label>
              <input type="text" name="contact_hero_title" value="<?= e(val('contact_hero_title', 'Direct Agency Assistance')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Contact Page Subtext</label>
              <input type="text" name="contact_hero_subtext" value="<?= e(val('contact_hero_subtext', 'Whether reserving a sacred yatra or planning a bespoke holiday, our coordinators are ready to assist.')) ?>" style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.88rem;">
            </div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Official Agency Name *</label>
            <input type="text" name="site_name" value="<?= e(val('site_name', 'MIDHILA VOYAGES')) ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Official WhatsApp (with country code) *</label>
            <input type="text" name="whatsapp_number" value="<?= e(val('whatsapp_number', '+918075021427')) ?>" required placeholder="+918075021427" style="width:100%;padding:10px 14px;border:1.5px solid #25D366;border-radius:8px;font-size:0.92rem;">
            <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Used for all direct WhatsApp chat buttons</span>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Primary Phone</label>
            <input type="text" name="primary_phone" value="<?= e(val('primary_phone', '+91 8075021427')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Secondary Phone</label>
            <input type="text" name="secondary_phone" value="<?= e(val('secondary_phone', '+91 9446154048')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Third Phone / Hotline</label>
            <input type="text" name="hotline_phone" value="<?= e(val('hotline_phone', '+91 8943669027')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Primary Email Address *</label>
            <input type="email" name="primary_email" value="<?= e(val('primary_email', 'contact@midhilavoyages.com')) ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
          <div>
            <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Working Hours</label>
            <input type="text" name="working_hours" value="<?= e(val('working_hours', 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Physical Office Address / Headquarters</label>
          <textarea name="office_address" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('office_address', 'Midhila Voyages, Travel & Pilgrimage Hub, Kerala, India')) ?></textarea>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Footer Description Text</label>
          <textarea name="footer_description" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;"><?= e(val('footer_description', 'A premier travel and pilgrimage management house dedicated to curating bespoke domestic journeys, sacred temple yatras, and luxury escapes with uncompromised integrity.')) ?></textarea>
        </div>

        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Footer Bottom Tagline</label>
          <input type="text" name="footer_tagline" value="<?= e(val('footer_tagline', 'Beyond Destinations. Into Experiences.')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.92rem;">
          <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Appears on right side of bottom copyright bar</span>
        </div>
      </div>

    </div>

    <!-- Sticky Floating Bottom Save Bar -->
    <div class="content-save-bar" style="position:fixed;bottom:0;left:260px;right:0;background:#FFFFFF;border-top:1px solid #E2E8F0;padding:16px 32px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 -4px 16px rgba(15,23,42,0.06);z-index:90;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10B981;"></span>
        <span style="font-size:0.85rem;color:#475569;font-weight:500;">Live updates are automatically reflected on the public website</span>
      </div>

      <div style="display:flex;align-items:center;gap:12px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="padding:11px 32px;font-size:0.95rem;font-weight:700;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(15,23,42,0.15);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
          <span>Save All Changes</span>
        </button>
      </div>
    </div>

    <style>
      @media (max-width: 992px) {
        .content-save-bar { left: 0 !important; padding: 14px 20px !important; }
      }
    </style>

  </form>

</div>

<script>
  // Tab Switching Logic
  (function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const activeTabInput = document.getElementById('activeTabInput');

    function switchTab(targetId) {
      if (!targetId || !document.getElementById(targetId)) {
        targetId = 'tab-home';
      }

      tabBtns.forEach(btn => {
        const isMatch = btn.getAttribute('data-tab') === targetId;
        if (isMatch) {
          btn.style.background = '#0F172A';
          btn.style.color = '#FFFFFF';
        } else {
          btn.style.background = '#FFFFFF';
          btn.style.color = '#475569';
        }
      });

      tabContents.forEach(content => {
        content.style.display = (content.id === targetId) ? 'block' : 'none';
      });

      if (activeTabInput) {
        activeTabInput.value = targetId;
      }
    }

    tabBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-tab');
        switchTab(targetId);
        window.location.hash = targetId;
      });
    });

    // Check URL Hash on Load
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
      switchTab(hash);
    }
  })();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
