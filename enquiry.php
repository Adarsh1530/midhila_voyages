<?php
/**
 * MIDHILA VOYAGES — Interactive "Plan My Trip" Enquiry System
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
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $budget = trim($_POST['budget_range'] ?? 'Standard');
        $accommodation = trim($_POST['accommodation_preference'] ?? 'Premium 4-Star');
        $requirements = trim($_POST['travel_requirements'] ?? '');
        $contactMethod = in_array($_POST['preferred_contact_method'] ?? '', ['whatsapp', 'phone', 'email']) ? $_POST['preferred_contact_method'] : 'whatsapp';

        // Server-side validation
        if (empty($fullName) || strlen($fullName) < 2) {
            $errors[] = 'Please provide your full name.';
        }
        if (empty($phone) || strlen($phone) < 8) {
            $errors[] = 'Please provide a valid contact telephone or WhatsApp number.';
        }
        if ($depDate && $retDate && ($retDate < $depDate)) {
            $errors[] = 'Return date cannot be earlier than departure date.';
        }

        // If destination ID provided, lookup name
        if ($destId && empty($destName)) {
            foreach ($destinations as $d) {
                if ($d['id'] == $destId) {
                    $destName = $d['name'];
                    break;
                }
            }
        }

        if (empty($errors)) {
            try {
                $refNo = generate_reference('MV-ENQ');
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

                $insertStmt = $db->prepare("
                    INSERT INTO `enquiries` (
                        `reference_no`, `full_name`, `email`, `phone`, `whatsapp`,
                        `destination_id`, `destination_name`, `package_id`, `package_title`,
                        `travel_category`, `departure_date`, `return_date`,
                        `adults_count`, `children_count`, `budget_range`,
                        `accommodation_preference`, `travel_requirements`,
                        `preferred_contact_method`, `status`, `ip_address`, `user_agent`
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?,
                        ?, ?, ?,
                        ?, ?,
                        ?, 'new', ?, ?
                    )
                ");

                $pkgTitle = $pkgDetails['title'] ?? null;

                $insertStmt->execute([
                    $refNo, $fullName, $email ?: null, $phone, $whatsapp ?: $phone,
                    $destId, $destName, $pkgId, $pkgTitle,
                    $travelCategory, $depDate, $retDate,
                    $adults, $children, $budget,
                    $accommodation, $requirements,
                    $contactMethod, $ip, $ua
                ]);

                // Construct WhatsApp prefilled message
                $waMessage = "Hello MIDHILA VOYAGES,\n\n";
                $waMessage .= "I have submitted an enquiry on your website (Ref: #{$refNo}).\n";
                $waMessage .= "• Name: {$fullName}\n";
                $waMessage .= "• Preferred Destination: " . ($destName ?: 'Not Decided') . "\n";
                if ($pkgTitle) {
                    $waMessage .= "• Package: {$pkgTitle}\n";
                }
                $waMessage .= "• Travel Style: {$travelCategory}\n";
                $waMessage .= "• Dates: " . ($depDate ?: 'Flexible') . " to " . ($retDate ?: 'Flexible') . "\n";
                $waMessage .= "• Travellers: {$adults} Adult(s), {$children} Child(ren)\n";
                $waMessage .= "• Stay Preference: {$accommodation}\n";
                $waMessage .= "• Budget: {$budget}\n\n";
                $waMessage .= "Please share your proposal and let me know when we can discuss.";

                $submittedRecord = [
                    'reference' => $refNo,
                    'full_name' => $fullName,
                    'destination' => $destName ?: 'General Enquiry',
                    'whatsapp_url' => build_whatsapp_url($agencyWhatsApp, $waMessage)
                ];

            } catch (Exception $e) {
                error_log("Failed to save enquiry: " . $e->getMessage());
                $errors[] = 'A database error occurred while registering your trip preferences. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80" alt="Travel horizon" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Personalized Trip Planning</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;">Plan Your Journey</h1>
    <p class="lead-text" style="color:#CBD5E0;">Share your ideal dates, companion counts, and preferences. Our travel specialists will craft your personalized proposal.</p>
  </div>
</section>

<!-- Main Planner Section -->
<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container container-narrow">

    <?php if ($submittedRecord): ?>
      <!-- SUCCESS CONFIRMATION STATE -->
      <div style="background:#FFF;padding:clamp(32px, 6vw, 56px);border-radius:var(--radius-lg);box-shadow:var(--shadow-xl);border:2px solid var(--color-gold);text-align:center;animation:slideInRight 0.4s ease;">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(0,168,150,0.15);color:var(--color-teal);font-size:2.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
          ✓
        </div>
        <span class="section-subtitle">Enquiry Confirmed</span>
        <h2 style="font-size:clamp(1.8rem, 3.5vw, 2.4rem);color:var(--color-navy);margin-bottom:12px;">
          Thank You, <?= e($submittedRecord['full_name']) ?>!
        </h2>
        <p class="lead-text" style="margin-bottom:24px;">
          Your trip requirements have been safely registered under reference:
        </p>
        
        <div style="display:inline-block;background:var(--color-navy-card);color:var(--color-gold-bright);padding:10px 24px;border-radius:var(--radius-pill);font-family:var(--font-serif);font-size:1.3rem;font-weight:700;letter-spacing:0.1em;margin-bottom:30px;">
          <?= e($submittedRecord['reference']) ?>
        </div>

        <div style="background:var(--color-ivory-warm);border-radius:var(--radius-md);padding:24px;max-width:580px;margin:0 auto 36px;text-align:left;font-size:0.95rem;color:var(--color-text-body);border:1px solid rgba(0,0,0,0.06);">
          <h4 style="color:var(--color-navy);margin-bottom:8px;font-size:1.05rem;">Next Step: Connect on WhatsApp</h4>
          <p style="margin-bottom:14px;line-height:1.6;">
            We have prepared a pre-filled WhatsApp message summarizing your submitted travel preferences. Click the button below to open your chat with our specialist.
          </p>
          <p style="font-size:0.85rem;color:var(--color-text-muted);margin:0;">
            <strong>Important:</strong> Please ensure you press <em>Send</em> inside WhatsApp once the chat window opens.
          </p>
        </div>

        <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
          <a href="<?= e($submittedRecord['whatsapp_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
            <span>Continue on WhatsApp &amp; Send</span>
          </a>
          <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-gold btn-lg">Back to Homepage</a>
        </div>
      </div>

    <?php else: ?>

      <!-- ENQUIRY FORM -->
      <div style="background:#FFF;padding:clamp(28px, 5vw, 48px);border-radius:var(--radius-lg);box-shadow:var(--shadow-md);border:1px solid var(--color-border);">
        
        <?php if (!empty($errors)): ?>
          <div style="background:#FEF2F2;border-left:4px solid #E53E3E;padding:14px 18px;border-radius:var(--radius-sm);color:#991B1B;font-size:0.9rem;margin-bottom:28px;">
            <?php foreach ($errors as $err): ?>
              <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/enquiry.php" id="tripEnquiryForm">
          <?= csrf_field() ?>

          <?php if ($pkgDetails): ?>
            <input type="hidden" name="package_id" value="<?= (int)$pkgDetails['id'] ?>">
            <div style="background:var(--color-ivory-warm);padding:14px 20px;border-radius:var(--radius-md);border:1px solid var(--color-border-gold);margin-bottom:28px;display:flex;align-items:center;gap:12px;">
              <span style="font-size:1.4rem;color:var(--color-gold);">✦</span>
              <div>
                <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Enquiring for package</span>
                <h4 style="font-size:1.05rem;color:var(--color-navy);"><?= e($pkgDetails['title']) ?></h4>
              </div>
            </div>
          <?php endif; ?>

          <!-- Step 1: Traveller Information -->
          <div style="margin-bottom:32px;">
            <h3 style="font-size:1.25rem;color:var(--color-navy);margin-bottom:18px;border-bottom:1px solid var(--color-border);padding-bottom:10px;">
              1. Your Contact Details
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:18px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Full Name *</label>
                <input type="text" name="full_name" id="full_name" required placeholder="e.g. Ananya Sharma" 
                       value="<?= e($_POST['full_name'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Phone Number *</label>
                <input type="tel" name="phone" id="phone" required placeholder="+91 12345 67890" 
                       value="<?= e($_POST['phone'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">WhatsApp Number (If different)</label>
                <input type="tel" name="whatsapp" placeholder="WhatsApp contact number" 
                       value="<?= e($_POST['whatsapp'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" 
                       value="<?= e($_POST['email'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
            </div>
          </div>

          <!-- Step 2: Trip Horizon & Preferences -->
          <div style="margin-bottom:32px;">
            <h3 style="font-size:1.25rem;color:var(--color-navy);margin-bottom:18px;border-bottom:1px solid var(--color-border);padding-bottom:10px;">
              2. Destination &amp; Journey Dates
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:18px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Preferred Destination</label>
                <select name="destination_id" id="destination_id" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
                  <option value="">-- Select Destination or Other --</option>
                  <?php foreach ($destinations as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= ($preDest === $d['name'] || (isset($_POST['destination_id']) && $_POST['destination_id'] == $d['id'])) ? 'selected' : '' ?>>
                      <?= e($d['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Travel Category</label>
                <select name="travel_category" id="travel_category" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['name']) ?>" <?= ($preCat === $c['slug'] || $preCat === $c['name']) ? 'selected' : '' ?>>
                      <?= e($c['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Preferred Departure Date</label>
                <input type="date" name="departure_date" id="departure_date" 
                       value="<?= e($_POST['departure_date'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Preferred Return Date</label>
                <input type="date" name="return_date" id="return_date" 
                       value="<?= e($_POST['return_date'] ?? '') ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
            </div>
          </div>

          <!-- Step 3: Travellers & Accommodation -->
          <div style="margin-bottom:32px;">
            <h3 style="font-size:1.25rem;color:var(--color-navy);margin-bottom:18px;border-bottom:1px solid var(--color-border);padding-bottom:10px;">
              3. Travellers &amp; Comfort Preferences
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:20px;margin-bottom:18px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Adults (12+ yrs) *</label>
                <input type="number" name="adults_count" id="adults_count" min="1" max="60" required 
                       value="<?= (int)($_POST['adults_count'] ?? 2) ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Children (Under 12 yrs)</label>
                <input type="number" name="children_count" id="children_count" min="0" max="30" 
                       value="<?= (int)($_POST['children_count'] ?? 0) ?>"
                       style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Estimated Budget</label>
                <select name="budget_range" id="budget_range" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
                  <option value="Standard Comfort (₹15,000 – ₹25,000 / person)">Standard Comfort (₹15,000 – ₹25,000 / person)</option>
                  <option value="Premium 4-Star (₹25,000 – ₹45,000 / person)">Premium 4-Star (₹25,000 – ₹45,000 / person)</option>
                  <option value="Luxury / 5-Star (₹45,000+ / person)">Luxury / 5-Star (₹45,000+ / person)</option>
                  <option value="Bespoke High-End Retreat">Bespoke High-End Retreat</option>
                </select>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Preferred Stay Category</label>
                <select name="accommodation_preference" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
                  <option value="Premium 4-Star Resorts & Hotels">Premium 4-Star Resorts &amp; Hotels</option>
                  <option value="5-Star Luxury Resorts / Overwater Villa">5-Star Luxury Resorts / Overwater Villa</option>
                  <option value="Heritage Homestay / Boutique Stay">Heritage Homestay / Boutique Stay</option>
                  <option value="Near Temple Precincts (Senior Friendly)">Near Temple Precincts (Senior Friendly)</option>
                  <option value="Standard 3-Star Comfortable">Standard 3-Star Comfortable</option>
                </select>
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Preferred Contact Channel</label>
                <select name="preferred_contact_method" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
                  <option value="whatsapp">WhatsApp (Recommended)</option>
                  <option value="phone">Phone Call</option>
                  <option value="email">Email</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 4: Special Requirements -->
          <div style="margin-bottom:32px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Special Requirements, Dietary Needs or Sightseeing Wishlist</label>
            <textarea name="travel_requirements" rows="4" placeholder="Let us know if you need senior citizen ground floor rooms, wheelchair assistance, pure vegetarian meals, candlelight dinner, or specific temple darshan wishes..." 
                      style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;resize:vertical;outline:none;"><?= e($_POST['travel_requirements'] ?? '') ?></textarea>
          </div>

          <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <button type="submit" class="btn btn-primary btn-lg" style="flex:1;min-width:240px;">
              <span>Submit Enquiry &amp; Generate Itinerary</span>
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
            <button type="button" id="whatsappPreviewBtn" data-phone="<?= e($agencyWhatsApp) ?>" class="btn btn-teal btn-lg">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
              <span>Instant WhatsApp Sync</span>
            </button>
          </div>

        </form>

      </div>

    <?php endif; ?>

  </div>
</section>

<script src="<?= ASSETS_URL ?>/js/enquiry.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
