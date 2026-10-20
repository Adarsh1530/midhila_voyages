<?php
/**
 * MIDHILA VOYAGES — Tour Package Booking & Deposit Checkout
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Book Tour Package | Secure Reservation | Midhila Voyages';
$pageDescription = 'Reserve your holiday or pilgrimage package securely with Midhila Voyages. Pay initial deposit or full amount with instant booking confirmation.';

$db = getDB();
$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch all packages for dropdown if not selected
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

$paymentMode = get_setting('payment_mode', 'test');
$razorpayKey = get_setting('razorpay_key_id', 'rzp_test_midhila_demo');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Booking Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 40px);padding-bottom:40px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:14px;">Confirm Your Booking</h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      Lock in your preferred departure dates with an initial booking deposit or complete payment. Official digital vouchers generated instantly.
    </p>
  </div>
</section>

<!-- Checkout Section (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    
    <!-- 3-Step Progress Indicator -->
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:40px;flex-wrap:wrap;">
      <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-pill);font-size:0.8rem;font-weight:700;color:var(--color-text-main);">
        <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:var(--color-text-main);color:#FFF;text-align:center;line-height:20px;font-size:0.75rem;">1</span>
        <span>Package &amp; Dates</span>
      </div>
      <span style="color:var(--color-border);">&bull;&bull;&bull;</span>
      <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-pill);font-size:0.8rem;font-weight:700;color:var(--color-text-main);">
        <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:var(--color-gold-deep);color:#FFF;text-align:center;line-height:20px;font-size:0.75rem;">2</span>
        <span>Guest Details</span>
      </div>
      <span style="color:var(--color-border);">&bull;&bull;&bull;</span>
      <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-pill);font-size:0.8rem;font-weight:600;color:var(--color-text-muted);">
        <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:#E2E8F0;color:#334155;text-align:center;line-height:20px;font-size:0.75rem;">3</span>
        <span>Secure Payment</span>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 400px;gap:48px;align-items:start;">
      
      <!-- Left Column: Booking Form -->
      <div class="editorial-form-panel">
        <h3 style="font-size:1.45rem;color:var(--color-text-headline);margin-bottom:8px;">Traveller &amp; Trip Details</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);margin-bottom:28px;">All vouchers and confirmation updates will be sent to the email and phone entered below.</p>

        <form id="bookingCheckoutForm">
          <?= csrf_field() ?>

          <!-- Package Selection -->
          <div class="editorial-form-group">
            <label class="editorial-label">Select Package *</label>
            <select name="package_id" id="bookingPackageSelect" class="editorial-select" required>
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
              <input type="text" name="customer_name" id="bkgName" class="editorial-input" required placeholder="e.g. Priya Nair">
            </div>
            <div class="editorial-form-group">
              <label class="editorial-label">Phone / WhatsApp *</label>
              <input type="tel" name="customer_phone" id="bkgPhone" class="editorial-input" required placeholder="+91 80750 21427">
            </div>
          </div>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Email Address *</label>
              <input type="email" name="customer_email" id="bkgEmail" class="editorial-input" required placeholder="priya@domain.com">
            </div>
            <div class="editorial-form-group">
              <label class="editorial-label">Travel Departure Date *</label>
              <input type="date" name="travel_date" id="bkgDate" class="editorial-input" required>
            </div>
          </div>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Number of Travellers *</label>
              <input type="number" name="travellers_count" id="bkgTravellers" min="1" max="50" value="2" class="editorial-input" required>
            </div>
            <div class="editorial-form-group">
              <label class="editorial-label">Payment Selection</label>
              <select name="payment_type" id="bkgPaymentType" class="editorial-select">
                <option value="deposit">Initial Tour Deposit (₹5,000 / person)</option>
                <option value="full">Pay Full Package Cost</option>
              </select>
            </div>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Special Requests or Flight Timings</label>
            <textarea name="special_requests" id="bkgNotes" class="editorial-textarea" rows="3" placeholder="Airport pickup timing, vegetarian meal preference, room preferences..."></textarea>
          </div>

          <div id="bookingErrorAlert" style="display:none;background:#FEE2E2;border-left:4px solid #E53E3E;padding:12px 16px;border-radius:var(--radius-sm);color:#991B1B;font-size:0.9rem;margin-bottom:20px;"></div>

          <button type="submit" id="proceedPayBtn" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px 32px;">
            <span>Proceed to Secure Payment</span>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>
        </form>
      </div>

      <!-- Right Column: Cost Breakdown & Payment Summary -->
      <div>
        <div style="position:sticky;top:100px;background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:34px 28px;box-shadow:var(--shadow-card);">
          <h3 style="font-size:1.25rem;color:var(--color-text-headline);margin-bottom:18px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">Payment Summary</h3>
          
          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Package Rate (Per person):</span>
            <strong id="summaryRate" style="color:var(--color-text-main);">₹0</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Travellers:</span>
            <strong id="summaryTravellers" style="color:var(--color-text-main);">2 Person(s)</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Total Tour Cost:</span>
            <strong id="summaryTotal" style="color:var(--color-text-main);font-size:1.15rem;">₹0</strong>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);padding:18px;border-radius:var(--radius-sm);margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div>
                <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Amount Payable Now</span>
                <div style="font-size:1.85rem;font-weight:800;color:var(--color-gold-deep);margin-top:2px;" id="summaryPayable">₹10,000</div>
              </div>
              <span style="font-size:0.72rem;background:rgba(197,160,89,0.12);color:var(--color-gold-deep);padding:4px 10px;border-radius:var(--radius-pill);font-weight:700;">Deposit</span>
            </div>
            <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px;margin-bottom:0;">
              Remaining balance payable 15 days prior to travel commencement.
            </p>
          </div>

          <div style="font-size:0.84rem;color:var(--color-text-muted);line-height:1.6;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Official digital voucher generated immediately</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Bank-grade 256-bit encrypted gateway</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span>
              <span>Instant WhatsApp booking confirmation</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Payment Test Modal (Pure White Surface) -->
<div id="paymentSimulationModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(10px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#FFFFFF;border-radius:var(--radius-lg);padding:40px 32px;max-width:440px;width:100%;text-align:center;box-shadow:var(--shadow-hover);border:1px solid var(--color-border);position:relative;">
    <div style="width:54px;height:54px;border-radius:50%;background:rgba(197,160,89,0.12);color:var(--color-gold-deep);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.4rem;">
      💳
    </div>
    <h3 style="font-size:1.35rem;margin-bottom:8px;">Payment Authorization</h3>
    <p style="font-size:0.86rem;color:var(--color-text-muted);margin-bottom:20px;">
      Simulating checkout gateway for Booking Ref: <strong id="modalBkgRef" style="color:var(--color-text-headline);"></strong>
    </p>
    <div style="font-size:2.2rem;font-weight:800;color:var(--color-text-headline);margin-bottom:24px;" id="modalPayAmount">₹0</div>

    <div style="display:flex;flex-direction:column;gap:10px;">
      <button type="button" id="simulateSuccessBtn" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
        <span>Simulate Successful Payment</span>
      </button>
      <button type="button" id="simulateFailureBtn" class="btn-cinematic-outline" style="width:100%;justify-content:center;border-color:#EF4444;color:#EF4444;">
        <span>Simulate Payment Failure</span>
      </button>
      <button type="button" id="closeModalBtn" style="font-size:0.85rem;color:var(--color-text-muted);padding:8px;background:none;border:none;cursor:pointer;margin-top:4px;">
        Cancel and return
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const pkgSelect = document.getElementById('bookingPackageSelect');
  const travellersInput = document.getElementById('bkgTravellers');
  const payTypeSelect = document.getElementById('bkgPaymentType');

  const summaryRate = document.getElementById('summaryRate');
  const summaryTravellers = document.getElementById('summaryTravellers');
  const summaryTotal = document.getElementById('summaryTotal');
  const summaryPayable = document.getElementById('summaryPayable');

  const DEPOSIT_PER_PERSON = 5000;

  function updatePricing() {
    const selectedOpt = pkgSelect.options[pkgSelect.selectedIndex];
    const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
    const travellers = parseInt(travellersInput.value) || 1;
    const isDeposit = (payTypeSelect.value === 'deposit');

    const total = price * travellers;
    const payable = isDeposit ? Math.min(total, travellers * DEPOSIT_PER_PERSON) : total;

    summaryRate.textContent = '₹' + price.toLocaleString('en-IN');
    summaryTravellers.textContent = travellers + ' Person(s)';
    summaryTotal.textContent = '₹' + total.toLocaleString('en-IN');
    summaryPayable.textContent = '₹' + payable.toLocaleString('en-IN');
  }

  pkgSelect.addEventListener('change', updatePricing);
  travellersInput.addEventListener('input', updatePricing);
  payTypeSelect.addEventListener('change', updatePricing);
  updatePricing();

  // Booking Form Submission & Gateway Checkout
  const form = document.getElementById('bookingCheckoutForm');
  const btn = document.getElementById('proceedPayBtn');
  const errBox = document.getElementById('bookingErrorAlert');
  const testModal = document.getElementById('paymentSimulationModal');
  let activeOrderData = null;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<span>Initiating Payment Order...</span>';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch('<?= BASE_URL ?>/api/create-payment-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const order = await res.json();

      if (!order.success) {
        throw new Error(order.error || 'Failed to initiate order.');
      }

      activeOrderData = order;

      document.getElementById('modalPayAmount').textContent = '₹' + order.amount.toLocaleString('en-IN');
      document.getElementById('modalBkgRef').textContent = order.booking_reference;
      testModal.style.display = 'flex';

    } catch (err) {
      errBox.textContent = err.message;
      errBox.style.display = 'block';
      window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<span>Proceed to Secure Payment</span>';
    }
  });

  // Test simulation buttons
  document.getElementById('simulateSuccessBtn').addEventListener('click', async () => {
    if (!activeOrderData) return;
    try {
      const res = await fetch('<?= BASE_URL ?>/api/verify-payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          booking_reference: activeOrderData.booking_reference,
          razorpay_order_id: activeOrderData.order_id,
          razorpay_payment_id: 'pay_test_' + Math.random().toString(36).substring(2, 10),
          is_test_mock: 1
        })
      });
      const verify = await res.json();
      if (verify.success && verify.redirect_url) {
        window.location.href = verify.redirect_url;
      } else {
        alert(verify.error || 'Payment verification failed.');
      }
    } catch (err) {
      alert('Error verifying test payment: ' + err.message);
    }
  });

  document.getElementById('simulateFailureBtn').addEventListener('click', () => {
    if (!activeOrderData) return;
    window.location.href = '<?= BASE_URL ?>/payment-failure.php?ref=' + encodeURIComponent(activeOrderData.booking_reference);
  });

  document.getElementById('closeModalBtn').addEventListener('click', () => {
    testModal.style.display = 'none';
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
