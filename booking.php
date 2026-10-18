<?php
/**
 * MIDHILA VOYAGES — Tour Package Booking & Deposit Checkout
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

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:60px;color:#FFF;overflow:hidden;">
  <div class="container" style="text-align:center;max-width:760px;position:relative;z-index:2;">
    <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Secure Reservation</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.2rem, 4vw, 3.4rem);margin-bottom:12px;">Confirm Your Booking</h1>
    <p class="lead-text" style="color:#CBD5E0;">Reserve your preferred dates with an initial booking deposit or complete payment.</p>
  </div>
</section>

<!-- Checkout Section -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    <div class="detail-layout-grid">
      
      <!-- Left Column: Booking Form -->
      <div class="glass-card" style="padding:clamp(28px, 5vw, 44px);border-radius:var(--radius-lg);">
        <h3 style="font-size:1.45rem;color:var(--color-navy);margin-bottom:8px;">Traveller &amp; Trip Details</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);margin-bottom:28px;">All vouchers and confirmation updates will be sent to the email and phone entered below.</p>

        <form id="bookingCheckoutForm">
          <?= csrf_field() ?>

          <!-- Package Selection -->
          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Select Package *</label>
            <select name="package_id" id="bookingPackageSelect" required style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
              <?php foreach ($packages as $pkg): ?>
                <option value="<?= (int)$pkg['id'] ?>" 
                        data-price="<?= (float)$pkg['starting_price'] ?>"
                        <?= ($selectedPkg && $selectedPkg['id'] == $pkg['id']) ? 'selected' : '' ?>>
                  <?= e($pkg['title']) ?> (<?= (int)$pkg['duration_days'] ?>D/<?= (int)$pkg['duration_nights'] ?>N) - <?= format_price((float)$pkg['starting_price']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-grid-2">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Full Name *</label>
              <input type="text" name="customer_name" id="bkgName" required placeholder="e.g. Priya Nair" style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Phone / WhatsApp *</label>
              <input type="tel" name="customer_phone" id="bkgPhone" required placeholder="+91 12345 67890" style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
            </div>
          </div>

          <div class="form-grid-2">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Email Address *</label>
              <input type="email" name="customer_email" id="bkgEmail" required placeholder="priya@example.com" style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Travel Departure Date *</label>
              <input type="date" name="travel_date" id="bkgDate" required style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
            </div>
          </div>

          <div class="form-grid-2">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Number of Travellers *</label>
              <input type="number" name="travellers_count" id="bkgTravellers" min="1" max="50" value="2" required style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Payment Selection</label>
              <select name="payment_type" id="bkgPaymentType" style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;">
                <option value="deposit">Initial Tour Deposit (₹5,000 / person)</option>
                <option value="full">Pay Full Package Cost</option>
              </select>
            </div>
          </div>

          <div style="margin-bottom:28px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Special Requests or Flight Timings</label>
            <textarea name="special_requests" id="bkgNotes" rows="3" placeholder="Airport pickup timing, vegetarian meal preference, room preferences..." style="width:100%;padding:12px 16px;border:1.5px solid rgba(196,181,253,0.4);border-radius:var(--radius-md);font-size:0.95rem;background:rgba(255,255,255,0.85);outline:none;resize:vertical;"></textarea>
          </div>

          <div id="bookingErrorAlert" style="display:none;background:rgba(239,68,68,0.15);border-left:4px solid #E53E3E;padding:12px 16px;border-radius:var(--radius-md);color:#991B1B;font-size:0.9rem;margin-bottom:20px;"></div>

          <button type="submit" id="proceedPayBtn" class="btn btn-primary btn-lg" style="width:100%;">
            <span>Proceed to Secure Payment</span>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>
        </form>
      </div>

      <!-- Right Column: Cost Breakdown & Trust -->
      <div>
        <div class="glass-card" style="position:sticky;top:100px;padding:30px;border-radius:var(--radius-lg);">
          <h3 style="font-size:1.25rem;color:var(--color-navy);margin-bottom:18px;border-bottom:1px solid rgba(196,181,253,0.25);padding-bottom:12px;">Payment Summary</h3>
          
          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Package Rate (Per person):</span>
            <strong id="summaryRate" style="color:var(--color-navy);">₹0</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Travellers:</span>
            <strong id="summaryTravellers" style="color:var(--color-navy);">2 Person(s)</strong>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:0.92rem;">
            <span style="color:var(--color-text-muted);">Total Tour Cost:</span>
            <strong id="summaryTotal" style="color:var(--color-navy);font-size:1.1rem;">₹0</strong>
          </div>

          <div style="background:rgba(237,233,254,0.6);backdrop-filter:blur(10px);padding:16px;border-radius:var(--radius-md);margin-bottom:24px;border:1px solid rgba(196,181,253,0.4);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div>
                <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Amount Payable Now</span>
                <div style="font-family:var(--font-serif);font-size:1.75rem;font-weight:700;color:var(--color-violet-dark);margin-top:2px;" id="summaryPayable">₹10,000</div>
              </div>
              <span style="font-size:0.72rem;background:rgba(139,92,246,0.15);color:var(--color-violet);padding:4px 10px;border-radius:var(--radius-pill);font-weight:700;">Deposit</span>
            </div>
            <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px;margin-bottom:0;">
              Remaining balance payable 15 days prior to travel commencement.
            </p>
          </div>

          <div style="font-size:0.82rem;color:var(--color-text-muted);line-height:1.6;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="color:var(--color-teal);">✓</span>
              <span>Official digital voucher generated immediately</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="color:var(--color-teal);">✓</span>
              <span>100% refundable according to policy timelines</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="color:var(--color-teal);">✓</span>
              <span>Encrypted payment processing (256-Bit SSL)</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Pre-Payment Interactive Confirmation Modal -->
<div id="paymentConfirmModal" style="position:fixed;inset:0;background:rgba(6,11,24,0.85);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);z-index:2150;display:none;align-items:center;justify-content:center;padding:20px;">
  <div class="glass-card" style="max-width:520px;width:100%;padding:32px;border-radius:var(--radius-lg);box-shadow:var(--shadow-xl);max-height:92vh;overflow-y:auto;background:rgba(255,255,255,0.92);">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;border-bottom:1px solid var(--color-border);padding-bottom:16px;">
      <div style="width:48px;height:48px;border-radius:50%;background:rgba(0,168,150,0.12);color:var(--color-teal);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">
        🛡️
      </div>
      <div>
        <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-gold-dark);font-weight:700;">Final Review</span>
        <h3 style="font-size:1.3rem;color:var(--color-navy);margin:0;line-height:1.2;">Confirm Your Booking &amp; Payment</h3>
      </div>
    </div>

    <div style="background:var(--color-ivory-warm);border-radius:var(--radius-md);padding:18px 20px;margin-bottom:20px;border:1px solid var(--color-border-gold);">
      <div style="font-size:0.82rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:4px;">Selected Tour Package</div>
      <div id="confPkgTitle" style="font-size:1.05rem;font-weight:700;color:var(--color-navy);margin-bottom:14px;">-</div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:0.88rem;border-top:1px dashed var(--color-border);padding-top:12px;">
        <div>
          <span style="color:var(--color-text-muted);display:block;font-size:0.78rem;">Lead Traveller:</span>
          <strong id="confGuest" style="color:var(--color-navy);">-</strong>
        </div>
        <div>
          <span style="color:var(--color-text-muted);display:block;font-size:0.78rem;">Contact Number:</span>
          <strong id="confPhone" style="color:var(--color-navy);">-</strong>
        </div>
        <div>
          <span style="color:var(--color-text-muted);display:block;font-size:0.78rem;">Travel Date:</span>
          <strong id="confDate" style="color:var(--color-navy);">-</strong>
        </div>
        <div>
          <span style="color:var(--color-text-muted);display:block;font-size:0.78rem;">Travellers:</span>
          <strong id="confTravellers" style="color:var(--color-navy);">-</strong>
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#F8FAFC;border-radius:var(--radius-sm);margin-bottom:12px;font-size:0.92rem;">
      <span style="color:var(--color-text-muted);">Total Tour Price:</span>
      <strong id="confTotalPrice" style="color:var(--color-navy);font-size:1.05rem;">-</strong>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:rgba(0,168,150,0.1);border-radius:var(--radius-md);margin-bottom:20px;border:1px solid rgba(0,168,150,0.3);">
      <div>
        <span style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-teal-dark);font-weight:700;display:block;">Amount Payable Now</span>
        <span id="confPayTypeBadge" style="font-size:0.75rem;color:var(--color-text-muted);">-</span>
      </div>
      <div id="confPayableNow" style="font-family:var(--font-serif);font-size:1.6rem;font-weight:700;color:var(--color-teal);">-</div>
    </div>

    <p style="font-size:0.8rem;color:var(--color-text-muted);line-height:1.5;margin-bottom:24px;">
      🔒 <strong>Secure 256-Bit SSL Payment:</strong> By confirming, your payment order will be initiated. An official tax invoice and booking confirmation voucher will be generated immediately upon successful transaction.
    </p>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <button type="button" id="confEditBtn" class="btn btn-outline-gold" style="padding:10px 18px;font-size:0.9rem;">
        &larr; Review Details
      </button>
      <button type="button" id="confProceedPayBtn" class="btn btn-teal" style="padding:10px 22px;font-size:0.9rem;">
        <span>Confirm &amp; Pay Now</span> &rarr;
      </button>
    </div>
  </div>
</div>

<!-- Test Mode Simulation Modal (for seamless local XAMPP testing) -->
<div id="testPaymentModal" style="position:fixed;inset:0;background:rgba(6,16,30,0.85);backdrop-filter:blur(8px);z-index:2200;display:none;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#FFF;border-radius:var(--radius-lg);max-width:480px;width:100%;padding:32px;box-shadow:var(--shadow-xl);text-align:center;">
    <div style="width:56px;height:56px;border-radius:50%;background:rgba(197,160,89,0.2);color:var(--color-gold-dark);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.6rem;">
      💳
    </div>
    <span class="section-subtitle">Gateway Sandbox</span>
    <h3 style="font-size:1.4rem;color:var(--color-navy);margin-bottom:12px;">Complete Test Payment</h3>
    <p style="font-size:0.92rem;color:var(--color-text-body);margin-bottom:20px;">
      Simulating Razorpay test mode payment of <strong id="modalPayAmount" style="color:var(--color-teal);"></strong> for booking reference <code id="modalBkgRef" style="background:#F1F5F9;padding:2px 8px;border-radius:4px;"></code>.
    </p>

    <div style="display:flex;flex-direction:column;gap:12px;">
      <button type="button" id="simulateSuccessBtn" class="btn btn-teal" style="width:100%;">
        <span>Simulate Successful Payment</span>
      </button>
      <button type="button" id="simulateFailureBtn" class="btn btn-outline-gold" style="width:100%;color:#E53E3E;border-color:#E53E3E;">
        <span>Simulate Failed Payment</span>
      </button>
      <button type="button" id="closeModalBtn" style="color:var(--color-text-muted);font-size:0.85rem;margin-top:8px;">Cancel &amp; Close</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('bookingPackageSelect');
  const travellers = document.getElementById('bkgTravellers');
  const paymentType = document.getElementById('bkgPaymentType');
  const summaryRate = document.getElementById('summaryRate');
  const summaryTravellers = document.getElementById('summaryTravellers');
  const summaryTotal = document.getElementById('summaryTotal');
  const summaryPayable = document.getElementById('summaryPayable');
  const dateInput = document.getElementById('bkgDate');

  if (dateInput) {
    dateInput.min = new Date().toISOString().split('T')[0];
  }

  let currentCalculations = { rate: 0, count: 1, total: 0, payable: 0, type: 'deposit' };

  const updateCalculations = () => {
    const selectedOption = select.options[select.selectedIndex];
    const rate = parseFloat(selectedOption?.getAttribute('data-price')) || 15000;
    const count = parseInt(travellers.value) || 1;
    const type = paymentType.value;

    const total = rate * count;
    let payable = (type === 'deposit') ? Math.min(5000 * count, total) : total;

    currentCalculations = { rate, count, total, payable, type };

    summaryRate.textContent = '₹' + rate.toLocaleString('en-IN');
    summaryTravellers.textContent = count + ' Person(s)';
    summaryTotal.textContent = '₹' + total.toLocaleString('en-IN');
    summaryPayable.textContent = '₹' + payable.toLocaleString('en-IN');
  };

  select.addEventListener('change', updateCalculations);
  travellers.addEventListener('input', updateCalculations);
  paymentType.addEventListener('change', updateCalculations);
  updateCalculations();

  // Form submission & Pre-Payment Confirmation handling
  const form = document.getElementById('bookingCheckoutForm');
  const errBox = document.getElementById('bookingErrorAlert');
  const btn = document.getElementById('proceedPayBtn');
  const confirmModal = document.getElementById('paymentConfirmModal');
  const confEditBtn = document.getElementById('confEditBtn');
  const confProceedPayBtn = document.getElementById('confProceedPayBtn');
  const testModal = document.getElementById('testPaymentModal');

  let activeOrderData = null;

  // Step 1: When user clicks "Proceed to Secure Payment", validate and show Confirmation Modal
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    errBox.style.display = 'none';

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // Populate confirmation modal with latest trip & payment details
    const selectedPkgText = select.options[select.selectedIndex]?.text || 'Selected Package';
    const guestName = document.getElementById('bkgName').value.trim();
    const guestPhone = document.getElementById('bkgPhone').value.trim();
    const travelDateVal = dateInput.value;
    let formattedDate = travelDateVal;
    if (travelDateVal) {
      try {
        const d = new Date(travelDateVal + 'T00:00:00');
        formattedDate = d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
      } catch (err) {}
    }

    document.getElementById('confPkgTitle').textContent = selectedPkgText;
    document.getElementById('confGuest').textContent = guestName;
    document.getElementById('confPhone').textContent = guestPhone;
    document.getElementById('confDate').textContent = formattedDate;
    document.getElementById('confTravellers').textContent = currentCalculations.count + ' Person(s)';
    document.getElementById('confTotalPrice').textContent = '₹' + currentCalculations.total.toLocaleString('en-IN');
    document.getElementById('confPayableNow').textContent = '₹' + currentCalculations.payable.toLocaleString('en-IN');
    document.getElementById('confPayTypeBadge').textContent = currentCalculations.type === 'deposit' ? 'Initial Booking Deposit' : 'Full Tour Cost';

    // Show Confirmation Modal
    confirmModal.style.display = 'flex';
  });

  // Step 2: "Review Details" button in confirmation modal closes modal
  confEditBtn.addEventListener('click', () => {
    confirmModal.style.display = 'none';
  });

  // Step 3: "Confirm & Pay Now" button proceeds with order creation & gateway
  confProceedPayBtn.addEventListener('click', async () => {
    confirmModal.style.display = 'none';
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

      // Open Test Simulation Modal or Gateway
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
