<?php
/**
 * MIDHILA VOYAGES — Tour Package Booking Reservation
 * Direct Personal Reservation & Details Storage (No Payment Gateway Needed)
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Reserve Tour Package | Midhila Voyages';
$pageDescription = 'Reserve your holiday or sacred pilgrimage journey with Midhila Voyages. Submit your personal travel requirements for instant voucher generation.';

$db = getDB();
$errorMessage = '';

// Handle Direct Booking Submission (Zero Payment Gateway - Instant Reservation Storage)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_booking_reservation'])) {
    if (verify_csrf_token()) {
        $packageId       = (int)($_POST['package_id'] ?? 0);
        $customerName    = trim($_POST['customer_name'] ?? '');
        $customerPhone   = trim($_POST['customer_phone'] ?? '');
        $customerEmail   = trim($_POST['customer_email'] ?? '');
        $travelDate      = trim($_POST['travel_date'] ?? '');
        $travellersCount = max(1, (int)($_POST['travellers_count'] ?? 1));
        $specialRequests = trim($_POST['special_requests'] ?? '');

        if (!empty($customerName) && !empty($customerPhone) && !empty($travelDate) && $packageId > 0) {
            try {
                // Fetch Package Details
                $pkgStmt = $db->prepare("SELECT `title`, `starting_price` FROM `packages` WHERE `id` = ? LIMIT 1");
                $pkgStmt->execute([$packageId]);
                $pkg = $pkgStmt->fetch();
                $pkgTitle = $pkg['title'] ?? 'Tour Package';
                $rate = (float)($pkg['starting_price'] ?? 0);
                $totalAmount = $rate * $travellersCount;

                // Generate Unique Booking Reference
                $year = date('Y');
                $refCount = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_reference` LIKE 'MV-BKG-{$year}-%'")->fetchColumn() + 1;
                $bkgRef = sprintf("MV-BKG-%s-%05d", $year, $refCount);

                // Insert into bookings table
                $ins = $db->prepare("
                    INSERT INTO `bookings` (
                        `booking_reference`, `package_id`, `customer_name`, `customer_email`,
                        `customer_phone`, `travel_date`, `travellers_count`, `total_amount`,
                        `currency`, `booking_status`, `payment_status`, `special_requests`,
                        `created_at`, `updated_at`
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        'INR', 'confirmed', 'unpaid', ?,
                        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                    )
                ");
                $ins->execute([
                    $bkgRef, $packageId, $customerName, $customerEmail,
                    $customerPhone, $travelDate, $travellersCount, $totalAmount,
                    $specialRequests
                ]);

                // Sync with customers table
                try {
                    $custCheck = $db->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
                    $custCheck->execute([$customerPhone]);
                    $c = $custCheck->fetch();
                    if ($c) {
                        $db->prepare("UPDATE customers SET total_bookings = total_bookings + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$c['id']]);
                    } else {
                        $db->prepare("INSERT INTO customers (full_name, email, phone, whatsapp, preferred_language, total_bookings, created_at) VALUES (?, ?, ?, ?, 'en', 1, CURRENT_TIMESTAMP)")
                           ->execute([$customerName, $customerEmail, $customerPhone, $customerPhone]);
                    }
                } catch (Exception $e) {}

                header('Location: ' . BASE_URL . '/payment-success.php?ref=' . urlencode($bkgRef));
                exit;

            } catch (Exception $e) {
                $errorMessage = 'Unable to complete your reservation. Please verify your entries and try again.';
            }
        } else {
            $errorMessage = 'Please complete all required fields (Full Name, Phone Number, Travel Date, and Package).';
        }
    } else {
        $errorMessage = 'Security session expired. Please refresh the page and try again.';
    }
}

$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch all packages for dropdown
$packages = $db->query("SELECT `id`, `title`, `starting_price`, `currency`, `duration_days`, `duration_nights` FROM `packages` WHERE `is_published` = 1 ORDER BY `title` ASC")->fetchAll();

$selectedPkg = null;
if ($packageId > 0) {
    $stmt = $db->prepare("
        SELECT p.*, d.name AS destination_name 
        FROM `packages` p 
        JOIN `destinations` d ON p.destination_id = d.id 
        WHERE p.id = ? AND p.is_published = 1 LIMIT 1
    ");
    $stmt->execute([$packageId]);
    $selectedPkg = $stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Booking Hero (Pure White with Subtle 25% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 40px);padding-bottom:40px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:14px;">Confirm Your Booking Reservation</h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      Submit your personal travel requirements and lock in your departure dates. All personal details are stored securely with our concierge desk, and an official digital confirmation voucher is issued immediately.
    </p>
  </div>
</section>

<!-- Reservation Form Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    
    <!-- 2-Step Trust Indicator -->
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:40px;flex-wrap:wrap;">
      <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:var(--radius-pill);font-size:0.8rem;font-weight:700;color:#1E40AF;">
        <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:#1D4ED8;color:#FFF;text-align:center;line-height:20px;font-size:0.75rem;">1</span>
        <span>Enter Personal &amp; Trip Details</span>
      </div>
      <span style="color:var(--color-border);">&bull;&bull;&bull;</span>
      <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:var(--radius-pill);font-size:0.8rem;font-weight:700;color:#166534;">
        <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:#15803D;color:#FFF;text-align:center;line-height:20px;font-size:0.75rem;">2</span>
        <span>Instant Travel Voucher &amp; Desk Coordination</span>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:44px;align-items:start;">
      
      <!-- Left Column: Booking Details Form -->
      <div class="editorial-form-panel">
        <h3 style="font-size:1.45rem;color:var(--color-text-headline);margin-bottom:8px;">Guest Personal &amp; Travel Details</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);margin-bottom:28px;">
          No online payment is processed on our site. Your booking and personal details are registered directly with our operations desk.
        </p>

        <?php if (!empty($errorMessage)): ?>
          <div style="background:#FEE2E2;border-left:4px solid #EF4444;padding:12px 16px;border-radius:6px;color:#991B1B;font-size:0.9rem;margin-bottom:20px;font-weight:600;">
            <?= e($errorMessage) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/booking.php">
          <?= csrf_field() ?>
          <input type="hidden" name="submit_booking_reservation" value="1">

          <!-- Package Selection -->
          <div class="editorial-form-group">
            <label class="editorial-label">Select Package *</label>
            <select name="package_id" id="bookingPackageSelect" class="editorial-select" required>
              <option value="" disabled selected>Select Your Package</option>
              <?php foreach ($packages as $pkg): ?>
                <option value="<?= (int)$pkg['id'] ?>" 
                        data-price="<?= (float)$pkg['starting_price'] ?>"
                        <?= ($selectedPkg && $selectedPkg['id'] == $pkg['id']) ? 'selected' : '' ?>>
                  <?= e($pkg['title']) ?> (<?= (int)$pkg['duration_days'] ?>D/<?= (int)$pkg['duration_nights'] ?>N) - <?= format_price((float)$pkg['starting_price']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Full Name *</label>
              <input type="text" name="customer_name" id="bkgName" class="editorial-input" required placeholder="Enter your full name">
            </div>
            <div class="editorial-form-group">
              <label class="editorial-label">Phone / WhatsApp *</label>
              <input type="tel" name="customer_phone" id="bkgPhone" class="editorial-input" required placeholder="Enter your phone number">
            </div>
          </div>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Email Address *</label>
              <input type="email" name="customer_email" id="bkgEmail" class="editorial-input" required placeholder="Enter your email address">
            </div>
            <div class="editorial-form-group">
              <label class="editorial-label">Travel Departure Date *</label>
              <input type="date" name="travel_date" id="bkgDate" class="editorial-input" required min="<?= date('Y-m-d') ?>" title="Select Your Travel Departure Date">
            </div>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Number of Travellers / Guests *</label>
            <input type="number" name="travellers_count" id="bkgTravellers" min="1" max="50" value="2" class="editorial-input" required placeholder="Enter your number of guests">
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Special Requests or Specific Requirements</label>
            <textarea name="special_requests" id="bkgNotes" class="editorial-textarea" rows="3" placeholder="Enter your special requests"></textarea>
          </div>

          <button type="submit" id="proceedPayBtn" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px 32px;font-size:1.02rem;font-weight:700;">
            <span>Confirm Booking Reservation &rarr;</span>
          </button>
        </form>
      </div>

      <!-- Right Column: Reservation Summary Card -->
      <div>
        <div style="position:sticky;top:100px;background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:30px 24px;box-shadow:var(--shadow-card);">
          <h3 style="font-size:1.25rem;color:var(--color-text-headline);margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">Reservation Summary</h3>
          
          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Package Rate (Per person):</span>
            <strong id="summaryRate" style="color:var(--color-text-main);">₹0</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Travellers:</span>
            <strong id="summaryTravellers" style="color:var(--color-text-main);">2 Person(s)</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:18px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Estimated Tour Value:</span>
            <strong id="summaryTotal" style="color:#059669;font-size:1.25rem;">₹0</strong>
          </div>

          <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:16px;border-radius:var(--radius-sm);margin-bottom:20px;">
            <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.08em;color:#0F172A;font-weight:700;margin-bottom:4px;">
              Zero Online Payment Required
            </div>
            <p style="font-size:0.82rem;color:#475569;margin:0;line-height:1.55;">
              All reservations are confirmed and coordinated directly with our concierge desk. No payment gateway charges or credit card details required.
            </p>
          </div>

          <div style="font-size:0.84rem;color:var(--color-text-muted);line-height:1.6;display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Official digital voucher generated immediately</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Customer personal details saved to operations desk</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Dedicated travel coordinator assigned</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const pkgSelect = document.getElementById('bookingPackageSelect');
  const travellersInput = document.getElementById('bkgTravellers');

  const summaryRate = document.getElementById('summaryRate');
  const summaryTravellers = document.getElementById('summaryTravellers');
  const summaryTotal = document.getElementById('summaryTotal');

  function updatePricing() {
    if (!pkgSelect) return;
    const selectedOpt = pkgSelect.options[pkgSelect.selectedIndex];
    const price = parseFloat(selectedOpt ? selectedOpt.getAttribute('data-price') : 0) || 0;
    const travellers = parseInt(travellersInput.value) || 1;
    const total = price * travellers;

    summaryRate.textContent = '₹' + price.toLocaleString('en-IN');
    summaryTravellers.textContent = travellers + ' Person(s)';
    summaryTotal.textContent = '₹' + total.toLocaleString('en-IN');
  }

  if (pkgSelect) pkgSelect.addEventListener('change', updatePricing);
  if (travellersInput) travellersInput.addEventListener('input', updatePricing);
  updatePricing();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
