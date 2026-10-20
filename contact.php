<?php
/**
 * MIDHILA VOYAGES — Contact Us
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Contact Us | Speak to Our Travel Specialists | Midhila Voyages';
$pageDescription = 'Get in touch with Midhila Voyages. Reach our travel desk by phone, email, WhatsApp, or visit our office in Kerala, India.';

$agencyPhone = get_setting('primary_phone', '+91 8075021427');
$agencySecondaryPhone = get_setting('secondary_phone', '+91 9446154048');
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$agencyEmail = get_setting('primary_email', 'contact@midhilavoyages.com');
$agencyAddress = get_setting('office_address', 'Midhila Voyages, Travel & Pilgrimage Hub, Kerala, India');
$workingHours = get_setting('working_hours', 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)');

$errors = [];
$successMessage = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'Invalid security session. Please reload the page and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? 'General Contact');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Please enter your name.';
        }
        if (empty($phone) || strlen($phone) < 8) {
            $errors[] = 'Please provide a valid phone or WhatsApp number.';
        }
        if (empty($message) || strlen($message) < 5) {
            $errors[] = 'Please write your message or enquiry details.';
        }

        if (empty($errors)) {
            try {
                $db = getDB();
                $ref = generate_reference('MV-MSG');
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

                $stmt = $db->prepare("
                    INSERT INTO `enquiries` (
                        `reference_no`, `name`, `email`, `phone`, `destination_id`, 
                        `package_id`, `travel_dates`, `adults`, `children`, 
                        `special_requests`, `status`, `ip_address`, `user_agent`, `created_at`
                    ) VALUES (
                        ?, ?, ?, ?, NULL, 
                        NULL, NULL, 1, 0, 
                        ?, 'new', ?, ?, NOW()
                    )
                ");
                $fullMsg = "Subject: {$subject}\n\n{$message}";
                $stmt->execute([$ref, $name, $email, $phone, $fullMsg, $ip, $ua]);

                $successMessage = "Thank you, {$name}. Your message has been received (Ref: {$ref}). Our team will reach out to you within 4 hours.";
            } catch (Exception $e) {
                $errors[] = 'A system error occurred. Please reach us directly on WhatsApp or call our hotline.';
            }
        }
    }
}

$waUrl = build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to consult with a travel specialist regarding my upcoming journey.");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Contact Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.22;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Direct Agency Assistance
    </h1>
    <p class="editorial-subtext" style="margin:0 auto 28px;">
      Whether reserving a sacred yatra or planning a bespoke holiday, our coordinators are ready to assist.
    </p>
  </div>
</section>

<!-- Contact Form & Headquarters Details -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    
    <div style="display:grid;grid-template-columns:1fr 420px;gap:48px;align-items:start;">
      
      <!-- Contact Form -->
      <div class="editorial-form-panel">
        <h2 style="font-size:1.6rem;margin-bottom:8px;">Send a Direct Message</h2>
        <p style="font-size:0.92rem;color:var(--color-text-muted);margin-bottom:28px;">Fill in your details below and a travel coordinator will call or message you.</p>

        <?php if (!empty($successMessage)): ?>
          <div style="background:#E6F4EA;border:1px solid #A7F3D0;color:#065F46;padding:16px 20px;border-radius:var(--radius-sm);margin-bottom:24px;font-size:0.92rem;">
            <?= e($successMessage) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:16px 20px;border-radius:var(--radius-sm);margin-bottom:24px;font-size:0.92rem;">
            <ul style="list-style:disc;padding-left:18px;">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/contact.php" method="POST">
          <?= csrf_field() ?>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Your Name *</label>
              <input type="text" name="name" class="editorial-input" placeholder="e.g. Adarsh" required>
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Phone / WhatsApp Number *</label>
              <input type="tel" name="phone" class="editorial-input" placeholder="+91 80750 21427" required>
            </div>
          </div>

          <div class="form-row-2col">
            <div class="editorial-form-group">
              <label class="editorial-label">Email Address</label>
              <input type="email" name="email" class="editorial-input" placeholder="you@domain.com">
            </div>

            <div class="editorial-form-group">
              <label class="editorial-label">Inquiry Subject</label>
              <select name="subject" class="editorial-select">
                <option value="Pilgrim Tour Enquiry">Pilgrim Tour Reservation</option>
                <option value="Custom Family Package">Custom Family Package</option>
                <option value="Honeymoon Itinerary">Romantic Escape Planning</option>
                <option value="Chauffeured Transport Hire">Chauffeured Vehicle Hire</option>
                <option value="General Query">General Question</option>
              </select>
            </div>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Your Message or Requirements *</label>
            <textarea name="message" class="editorial-textarea" rows="4" placeholder="Mention preferred travel dates, destinations, number of passengers..." required></textarea>
          </div>

          <button type="submit" class="btn-cinematic-primary" style="margin-top:12px;padding:14px 36px;">
            <span>Submit Message</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>
        </form>
      </div>

      <!-- Agency Contact Sidebar -->
      <div>
        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:34px 28px;box-shadow:var(--shadow-card);">
          <div class="chapter-badge">
            <span class="chapter-number">Agency Office</span>
            <span class="chapter-coordinates">Kerala Desk</span>
          </div>
          <h3 style="font-size:1.35rem;margin-bottom:20px;">Direct Hotlines</h3>

          <div style="display:flex;flex-direction:column;gap:18px;margin-bottom:28px;">
            <div>
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Primary Contact Numbers</div>
              <div style="font-size:1.05rem;font-weight:700;color:var(--color-text-headline);margin-top:2px;">
                +91 8075021427
              </div>
              <div style="font-size:0.95rem;color:var(--color-text-body);margin-top:2px;">
                +91 9446154048 &bull; +91 8943669027
              </div>
            </div>

            <div>
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Electronic Mail</div>
              <div style="font-size:0.95rem;color:var(--color-text-body);margin-top:2px;"><?= e($agencyEmail) ?></div>
            </div>

            <div>
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Working Hours</div>
              <div style="font-size:0.9rem;color:var(--color-text-body);margin-top:2px;"><?= e($workingHours) ?></div>
            </div>

            <div>
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Location</div>
              <div style="font-size:0.9rem;color:var(--color-text-body);margin-top:2px;"><?= e($agencyAddress) ?></div>
            </div>
          </div>

          <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="width:100%;justify-content:center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
            <span>Instant WhatsApp Chat</span>
          </a>

        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
