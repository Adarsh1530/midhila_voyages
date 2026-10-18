<?php
/**
 * MIDHILA VOYAGES — Travel Services
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Travel Services | Custom Holidays, Transit & Stays | Midhila Voyages';
$pageDescription = 'Comprehensive travel services by Midhila Voyages: bespoke itinerary design, luxury stays, private vehicle rentals, Alleppey houseboat cruises, and visa assistance.';

$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Services Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.22;">
    <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1800&q=80" alt="World travel" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div class="float-slow" style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-lavender);margin-bottom:12px;font-weight:700;background:rgba(196,181,253,0.12);padding:6px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.3);">✨ Full-Spectrum Travel Assistance</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;text-shadow:0 0 20px rgba(196,181,253,0.25);">Our Travel Services</h1>
    <p class="lead-text" style="color:#CBD5E0;">From individual flight logistics and handpicked resort stays to complete end-to-end holiday management.</p>
  </div>
</section>

<!-- Services Grid -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:32px;">
      
      <!-- Service 1: Bespoke Itineraries -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(196,181,253,0.2);color:var(--color-violet-dark);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Bespoke Holiday Planning</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Personalized day-by-day itineraries tailored to your unique preferences, budget, and travel tempo. Whether solo, couple, or multi-generational family.
        </p>
        <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-outline-gold btn-sm">Plan Custom Trip</a>
      </div>

      <!-- Service 2: Luxury Stays & Resorts -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(0,168,150,0.12);color:var(--color-teal);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Curated Stays &amp; Resorts</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Direct reservations at vetted 4-star and 5-star properties, heritage plantation bungalows, private pool villas in Bali, and overwater villas in Maldives.
        </p>
        <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like assistance with luxury resort booking.")) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gold btn-sm">Enquire Stays</a>
      </div>

      <!-- Service 3: Kerala Houseboat Cruises -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(10,25,47,0.08);color:var(--color-navy);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Private Houseboat Charters</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Exclusive, air-conditioned kettuvallams in Alleppey and Kumarakom staffed by an experienced captain, private chef, and cabin crew serving freshly cooked delicacies.
        </p>
        <a href="<?= BASE_URL ?>/package.php?slug=kerala-serenade-munnar-alleppey" class="btn btn-outline-gold btn-sm">View Houseboat Package</a>
      </div>

      <!-- Service 4: Private Chauffeur Transit -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(196,181,253,0.2);color:var(--color-violet-dark);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Private AC Chauffeur Transit</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Impeccably clean, air-conditioned sedans, SUVs, and luxury coaches with professional, courteous drivers familiar with local routes and terrain.
        </p>
        <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-gold btn-sm">Request Transport</a>
      </div>

      <!-- Service 5: Flight & Train Ticketing -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(0,168,150,0.12);color:var(--color-teal);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Ticketing &amp; Transit Coordination</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Assistance with connecting domestic flights, international airline tickets, and confirmed train berths coordinated seamlessly with your itinerary.
        </p>
        <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I need assistance with flight/train ticketing.")) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gold btn-sm">Ticket Assistance</a>
      </div>

      <!-- Service 6: Visa & Entry Guidance -->
      <div class="glass-card card-3d glass-specular" style="padding:36px;border-radius:var(--radius-lg);">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(10,25,47,0.08);color:var(--color-navy);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:12px;">Visa Guidance &amp; Clearances</h3>
        <p style="font-size:0.92rem;color:var(--color-text-muted);line-height:1.7;margin-bottom:18px;">
          Expert guidance on tourist visas for Dubai, Thailand, Singapore, and Indonesia, as well as official Lakshadweep entry permits and documentation.
        </p>
        <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-gold btn-sm">Enquire About Visas</a>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
