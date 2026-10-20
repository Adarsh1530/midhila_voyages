<?php
/**
 * MIDHILA VOYAGES — Interactive "Plan My Trip" Enquiry System
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Plan My Trip | Bespoke Custom Journey Planner | Midhila Voyages';
$pageDescription = 'Customize your dream vacation or sacred pilgrimage. Share your preferences with Midhila Voyages and receive a personalized itinerary and quotation.';

$db = getDB();

// Pre-fill parameters if arriving from a package or destination page
$preDest = trim($_GET['destination'] ?? '');
$prePkgId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;
$preCat = trim($_GET['category'] ?? '');

$pkgDetails = null;
if ($prePkgId > 0) {
    $stmt = $db->prepare("SELECT `id`, `title`, `destination_id` FROM `packages` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$prePkgId]);
    $pkgDetails = $stmt->fetch();
}

// Fetch active destinations & categories
$destinations = $db->query("SELECT `id`, `name` FROM `destinations` WHERE `is_published` = 1 ORDER BY `name` ASC")->fetchAll();
$categories = $db->query("SELECT `id`, `name`, `slug` FROM `package_categories` WHERE `is_active` = 1 ORDER BY `display_order` ASC")->fetchAll();

$errors = [];
$submittedRecord = null;
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'Your security session has expired. Please refresh the page and submit again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? $phone);
        $destId = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;
        $destName = trim($_POST['destination_name'] ?? '');
        $pkgId = !empty($_POST['package_id']) ? (int)$_POST['package_id'] : null;
        $travelCategory = trim($_POST['travel_category'] ?? 'Custom Tour');
        $depDate = !empty($_POST['departure_date']) ? $_POST['departure_date'] : null;
        $retDate = !empty($_POST['return_date']) ? $_POST['return_date'] : null;
        $adults = max(1, (int)($_POST['adults_count'] ?? 1));
        $children = max(0, (int)($_POST['children_count'] ?? 0));
        $hotelPref = trim($_POST['hotel_preference'] ?? 'Deluxe 3-Star');
        $budget = trim($_POST['budget_tier'] ?? 'Flexible');
        $vehiclePref = trim($_POST['vehicle_preference'] ?? 'Dedicated AC Sedan');
        $requests = trim($_POST['special_requests'] ?? '');

        if (empty($fullName) || strlen($fullName) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if (empty($phone) || strlen($phone) < 8) {
            $errors[] = 'Please enter a valid primary contact number.';
        }

        if (empty($errors)) {
            try {
                $refNo = generate_reference('MV-REQ');
                $datesFormatted = $depDate ? ($depDate . ($retDate ? ' to ' . $retDate : '')) : 'Flexible Dates';
                
                $compiledNotes = "Category: {$travelCategory}\n";
                $compiledNotes .= "Destination Requested: " . ($destName ?: 'Not specified') . "\n";
                $compiledNotes .= "Dates: {$datesFormatted}\n";
                $compiledNotes .= "Hotel Preference: {$hotelPref}\n";
                $compiledNotes .= "Budget: {$budget}\n";
                $compiledNotes .= "Vehicle: {$vehiclePref}\n";
                $compiledNotes .= "WhatsApp Number: {$whatsapp}\n";
                if (!empty($requests)) {
                    $compiledNotes .= "Special Requests:\n{$requests}";
                }

                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

                $stmt = $db->prepare("
                    INSERT INTO `enquiries` (
                        `reference_no`, `name`, `email`, `phone`, `destination_id`, 
                        `package_id`, `travel_dates`, `adults`, `children`, 
                        `special_requests`, `status`, `ip_address`, `user_agent`, `created_at`
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, 
                        ?, 'new', ?, ?, NOW()
                    )
                ");
                $stmt->execute([
                    $refNo, $fullName, $email, $phone, $destId, 
                    $pkgId, $datesFormatted, $adults, $children, 
                    $compiledNotes, $ip, $ua
                ]);

                // Prefilled WhatsApp message
                $waText = "Hello MIDHILA VOYAGES, I have submitted a bespoke travel request (Ref: {$refNo}).\n\n";
                $waText .= "• Name: {$fullName}\n";
                $waText .= "• Destination: " . ($destName ?: 'Custom Route') . "\n";
                $waText .= "• Dates: {$datesFormatted}\n";
                $waText .= "• Travelers: {$adults} Adults" . ($children > 0 ? ", {$children} Children" : "") . "\n";
                $waText .= "• Accommodation: {$hotelPref}\n";
                $waText .= "\nPlease review and share the customized proposal.";

                $submittedRecord = [
                    'reference' => $refNo,
                    'name' => $fullName,
                    'phone' => $phone,
                    'wa_url' => build_whatsapp_url($agencyWhatsApp, $waText)
                ];
            } catch (Exception $e) {
                $errors[] = 'A database error occurred while saving your request. Please reach our direct WhatsApp hotline.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Planner Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 40px);padding-bottom:40px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Chapter VII &bull; The Horizon</span>
      <span class="chapter-coordinates">Bespoke Concierge Curation</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:14px;">
      Plan Your Custom Journey
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      Share your travel vision with our Kerala headquarters. We craft a bespoke sequence with verified stays, chauffeur routing, and transparent pricing.
    </p>
  </div>
</section>

<!-- Main Form Section -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">

    <?php if ($submittedRecord): ?>
      <!-- Submission Confirmation Card (Pure White Surface) -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:50px 40px;text-align:center;box-shadow:var(--shadow-card);">
        <div style="width:68px;height:68px;border-radius:50%;background:#E6F4EA;border:2px solid #059669;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:#059669;font-size:1.8rem;">
          ✓
        </div>
        <span style="font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">Inquiry Successfully Registered</span>
        <h2 style="font-size:2.2rem;margin:8px 0 16px;">We Have Received Your Journey Vision</h2>
        <p style="font-size:1.02rem;line-height:1.75;color:var(--color-text-secondary);max-width:620px;margin:0 auto 28px;">
          Thank you, <strong><?= e($submittedRecord['name']) ?></strong>. Your request reference number is <strong style="color:var(--color-text-headline);"><?= e($submittedRecord['reference']) ?></strong>. A senior travel curator is reviewing your route pacing.
        </p>

        <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
          <a href="<?= e($submittedRecord['wa_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="padding:14px 28px;font-size:0.95rem;">
            <span>Synchronize with WhatsApp Specialist &rarr;</span>
          </a>
          <a href="<?= BASE_URL ?>/index.php" class="btn-cinematic-outline" style="padding:13px 24px;">
            <span>Return to Home</span>
          </a>
        </div>
      </div>
    <?php else: ?>

      <!-- 3-Step Progress Indicator -->
      <div style="display:flex;align-items:center;justify-content:space-between;max-width:540px;margin:0 auto 40px;position:relative;">
        <div style="text-align:center;position:relative;z-index:2;">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--color-text-main);color:#FFF;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-weight:700;font-size:0.85rem;">1</div>
          <span style="font-size:0.75rem;font-weight:700;color:var(--color-text-main);text-transform:uppercase;">Circuits</span>
        </div>
        <div style="flex:1;height:2px;background:var(--color-border);margin:0 12px;transform:translateY(-12px);"></div>
        <div style="text-align:center;position:relative;z-index:2;">
          <div style="width:36px;height:36px;border-radius:50%;background:#FAFAFA;border:1px solid var(--color-border);color:var(--color-text-secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-weight:700;font-size:0.85rem;">2</div>
          <span style="font-size:0.75rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Rhythm</span>
        </div>
        <div style="flex:1;height:2px;background:var(--color-border);margin:0 12px;transform:translateY(-12px);"></div>
        <div style="text-align:center;position:relative;z-index:2;">
          <div style="width:36px;height:36px;border-radius:50%;background:#FAFAFA;border:1px solid var(--color-border);color:var(--color-text-secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-weight:700;font-size:0.85rem;">3</div>
          <span style="font-size:0.75rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Guest Info</span>
        </div>
      </div>

      <?php if (!empty($errors)): ?>
        <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:16px 20px;border-radius:var(--radius-sm);margin-bottom:28px;font-size:0.92rem;">
          <ul style="list-style:disc;padding-left:18px;">
            <?php foreach ($errors as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- The Custom Planner Form Panel -->
      <div class="editorial-form-panel">
        <form action="<?= BASE_URL ?>/enquiry.php" method="POST">
          <?= csrf_field() ?>

          <?php if ($pkgDetails): ?>
            <div style="background:#FAFAFA;border:1px solid var(--color-border);border-left:4px solid var(--color-gold-deep);padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:24px;font-size:0.88rem;color:var(--color-text-main);">
              <strong>Inquiring specifically for:</strong> <?= e($pkgDetails['title']) ?>
              <input type="hidden" name="package_id" value="<?= (int)$pkgDetails['id'] ?>">
            </div>
          <?php endif; ?>

          <!-- Part 1: Destination & Dates -->
          <h3 style="font-size:1.15rem;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-main);">
            1. Destination &amp; Journey Dates
          </h3>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="editorial-form-group">
              <label class="editorial-label">Selected Destination *</label>
              <select name="destination_name" class="editorial-select" required>
                <option value="">Select Destination</option>
                <?php foreach ($destinations as $d): ?>
                  <option value="<?= e($d['name']) ?>" <?= ($preDest === $d['name'] || ($pkgDetails && $pkgDetails['destination_id'] == $d['id'])) ? 'selected' : '' ?>>
                    <?= e($d['name']) ?>
                  </option>
                <?php endforeach; ?>
                <option value="Pilgrim Tour (Rameswaram - Madurai - Thanjavur)">Pilgrim Tour (Tamil Nadu Circuit ₹6,500)</option>
                <option value="Multi-Region Custom Circuit">Multi-Region Custom Circuit</option>
              </select>
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Travel Style</label>
              <select name="travel_category" class="editorial-select">
                <option value="Sacred Pilgrimage Yatra">Sacred Pilgrimage Yatra</option>
                <option value="Bespoke Family Vacation" selected>Bespoke Family Vacation</option>
                <option value="Honeymoon &amp; Couple">Romantic Escape / Honeymoon</option>
                <option value="Senior Citizen Escorted Tour">Senior Citizen Escorted Tour</option>
                <option value="Group / Corporate Expedition">Group / Extended Family</option>
              </select>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="editorial-form-group">
              <label class="editorial-label">Estimated Departure Date</label>
              <input type="date" name="departure_date" class="editorial-input">
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Return Date (Optional)</label>
              <input type="date" name="return_date" class="editorial-input">
            </div>
          </div>

          <!-- Part 2: Party Size & Accommodation -->
          <h3 style="font-size:1.15rem;margin:28px 0 16px;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-main);">
            2. Party Size &amp; Accommodation Rhythm
          </h3>

          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:20px;">
            <div class="editorial-form-group">
              <label class="editorial-label">Adults (12+ Yrs)</label>
              <input type="number" name="adults_count" value="2" min="1" max="100" class="editorial-input">
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Children (&lt; 12 Yrs)</label>
              <input type="number" name="children_count" value="0" min="0" max="50" class="editorial-input">
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Hotel Category</label>
              <select name="hotel_preference" class="editorial-select">
                <option value="Heritage Palace / 5-Star">Heritage Luxury Palace / 5-Star</option>
                <option value="Boutique 4-Star Resort" selected>Boutique 4-Star Resort</option>
                <option value="Comfort Deluxe 3-Star">Comfort Deluxe 3-Star</option>
                <option value="Clean Ashram / Temple Suite">Clean Ashram / Temple Guest House</option>
              </select>
            </div>
          </div>

          <!-- Part 3: Guest Information -->
          <h3 style="font-size:1.15rem;margin:28px 0 16px;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-main);">
            3. Lead Traveler Details
          </h3>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="editorial-form-group">
              <label class="editorial-label">Full Name *</label>
              <input type="text" name="full_name" class="editorial-input" placeholder="e.g. Adarsh" required>
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Contact Number (Calling / SMS) *</label>
              <input type="tel" name="phone" class="editorial-input" placeholder="+91 80750 21427" required>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="editorial-form-group">
              <label class="editorial-label">WhatsApp Number</label>
              <input type="tel" name="whatsapp" class="editorial-input" placeholder="If different from calling number">
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Email Address</label>
              <input type="email" name="email" class="editorial-input" placeholder="you@domain.com">
            </div>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Special Requests / Preferences</label>
            <textarea name="special_requests" class="editorial-textarea" rows="3" placeholder="Tell us if you need senior wheelchair assistance, special dietary meals, specific darshan timings, or private vehicle type..."></textarea>
          </div>

          <div style="margin-top:28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <button type="submit" class="btn-cinematic-primary" style="padding:15px 38px;">
              <span>Submit Journey Proposal</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>

            <span style="font-size:0.82rem;color:var(--color-text-muted);">
              Need immediate booking? <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I need immediate itinerary assistance.")) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-whatsapp);font-weight:700;">WhatsApp Concierge &rarr;</a>
            </span>
          </div>

        </form>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
