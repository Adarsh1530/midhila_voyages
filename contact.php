<?php
/**
 * MIDHILA VOYAGES — Contact Us
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = 'Contact Us | Speak to Our Travel Specialists | Midhila Voyages';
$pageDescription = 'Get in touch with Midhila Voyages. Reach our travel desk by phone, email, WhatsApp, or visit our office in Kochi, Kerala.';

$agencyPhone = get_setting('primary_phone', '+91 12345 67890');
$agencySecondaryPhone = get_setting('secondary_phone', '+91 12345 67890');
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$agencyEmail = get_setting('primary_email', 'journeys@midhilavoyages.com');
$agencyAddress = get_setting('office_address', 'Midhila Voyages, Travel & Tourism Hub, MG Road, Kochi, Kerala - 682016, India');
$workingHours = get_setting('working_hours', 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)');

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    INSERT INTO `enquiries` 
                    (`reference_no`, `full_name`, `email`, `phone`, `whatsapp`, `travel_category`, `travel_requirements`, `preferred_contact_method`, `status`, `ip_address`, `user_agent`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'whatsapp', 'new', ?, ?)
                ");
                $stmt->execute([
                    $ref,
                    $name,
                    $email ?: null,
                    $phone,
                    $phone,
                    $subject,
                    $message,
                    $ip,
                    $ua
                ]);

                set_flash_message('success', "Thank you, {$name}! Your message has been received with reference #{$ref}. A travel specialist will get back to you shortly.");
                header('Location: ' . BASE_URL . '/contact.php');
                exit;

            } catch (Exception $e) {
                error_log("Contact submission error: " . $e->getMessage());
                $errors[] = 'A system error occurred while recording your message. Please try connecting via WhatsApp directly.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1800&q=80" alt="Contact us" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div class="float-slow" style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-lavender);margin-bottom:12px;font-weight:700;background:rgba(196,181,253,0.12);padding:6px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.3);">✨ Personal Attention</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;text-shadow:0 0 20px rgba(196,181,253,0.25);">Connect with Our Travel Desk</h1>
    <p class="lead-text" style="color:#CBD5E0;">We welcome your travel questions, itinerary inquiries, group tour arrangements, and custom pilgrimage requests.</p>
  </div>
</section>

<!-- Contact Info & Form Section -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:clamp(36px, 6vw, 70px);">
      
      <!-- Left Column: Contact Cards -->
      <div>
        <span class="section-subtitle no-after">Direct Channels</span>
        <h2 class="section-title">We Are Here to Guide You</h2>
        <p class="lead-text" style="margin-bottom:32px;">Reach our travel consultants directly via WhatsApp, telephone, email, or schedule an in-person meeting.</p>

        <div style="display:flex;flex-direction:column;gap:20px;margin-bottom:36px;">
          
          <!-- Office Address -->
          <div class="glass-card" style="display:flex;gap:16px;padding:24px;border-radius:var(--radius-md);">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(196,181,253,0.2);color:var(--color-violet-dark);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <h4 style="font-size:1.05rem;color:var(--color-navy);margin-bottom:4px;">Main Office Address</h4>
              <p style="font-size:0.92rem;color:var(--color-text-body);line-height:1.6;"><?= e($agencyAddress) ?></p>
            </div>
          </div>

          <!-- Phone Numbers -->
          <div class="glass-card" style="display:flex;gap:16px;padding:24px;border-radius:var(--radius-md);">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,168,150,0.12);color:var(--color-teal);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <h4 style="font-size:1.05rem;color:var(--color-navy);margin-bottom:4px;">Telephone Lines</h4>
              <p style="font-size:0.92rem;color:var(--color-text-body);">
                Primary: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencyPhone) ?>" style="color:var(--color-violet-dark);font-weight:600;"><?= e($agencyPhone) ?></a><br>
                Secondary: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencySecondaryPhone) ?>"><?= e($agencySecondaryPhone) ?></a>
              </p>
            </div>
          </div>

          <!-- Email & Hours -->
          <div class="glass-card" style="display:flex;gap:16px;padding:24px;border-radius:var(--radius-md);">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(10,25,47,0.08);color:var(--color-navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <h4 style="font-size:1.05rem;color:var(--color-navy);margin-bottom:4px;">Email &amp; Desk Hours</h4>
              <p style="font-size:0.92rem;color:var(--color-text-body);">
                Email: <a href="mailto:<?= e($agencyEmail) ?>"><?= e($agencyEmail) ?></a><br>
                Hours: <?= e($workingHours) ?>
              </p>
            </div>
          </div>

        </div>

        <!-- WhatsApp Quick Link -->
        <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to enquire with your travel desk.")) ?>" 
           target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg" style="width:100%;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
          <span>Quick WhatsApp Consultation</span>
        </a>

      </div>

      <!-- Right Column: Interactive Form -->
      <div class="glass-card" style="padding:clamp(28px, 5vw, 44px);border-radius:var(--radius-lg);">
        <h3 style="font-size:1.55rem;color:var(--color-navy);margin-bottom:8px;">Send Us a Message</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);margin-bottom:28px;">Fill in your contact details below and our travel team will respond promptly.</p>

        <?php if (!empty($errors)): ?>
          <div style="background:#FEF2F2;border-left:4px solid #E53E3E;padding:14px 18px;border-radius:var(--radius-sm);color:#991B1B;font-size:0.9rem;margin-bottom:24px;">
            <?php foreach ($errors as $err): ?>
              <div>• <?= e($err) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/contact.php">
          <?= csrf_field() ?>

          <div style="margin-bottom:18px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Your Full Name *</label>
            <input type="text" name="name" required placeholder="e.g. Ramesh Menon" 
                   value="<?= e($_POST['name'] ?? '') ?>"
                   style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
          </div>

          <div class="form-grid-2">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Phone / WhatsApp *</label>
              <input type="tel" name="phone" required placeholder="+91 12345 67890" 
                     value="<?= e($_POST['phone'] ?? '') ?>"
                     style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Email Address</label>
              <input type="email" name="email" placeholder="name@example.com" 
                     value="<?= e($_POST['email'] ?? '') ?>"
                     style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;">
            </div>
          </div>

          <div style="margin-bottom:18px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Subject / Topic</label>
            <select name="subject" style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;background:#FFF;outline:none;">
              <option value="General Travel Enquiry">General Travel Enquiry</option>
              <option value="Domestic Holiday Package">Domestic Holiday Package</option>
              <option value="International Tour Package">International Tour Package</option>
              <option value="Pilgrimage & Sacred Yatra">Pilgrimage &amp; Sacred Yatra</option>
              <option value="Houseboat Booking">Houseboat Booking</option>
              <option value="Custom Group Tour">Custom Group Tour</option>
            </select>
          </div>

          <div style="margin-bottom:24px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Your Message / Trip Requirements *</label>
            <textarea name="message" rows="4" required placeholder="Tell us about your preferred destinations, dates, number of travellers, or questions..." 
                      style="width:100%;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.95rem;resize:vertical;outline:none;"><?= e($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <span>Send Message</span>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
          </button>
        </form>

      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
