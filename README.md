# MIDHILA VOYAGES — Production Travel Agency Platform

An original, production-oriented, luxury travel agency website for **MIDHILA VOYAGES**, built with semantic HTML5, modern CSS3 (with 3D perspective transforms, custom properties, and responsive design), vanilla JavaScript, robust PHP backend architecture (PDO, prepared statements, CSRF protection, secure file uploads), and a relational MySQL database.

---

## 1. Architectural Highlights

- **Original Brand Identity:** Deep midnight navy (`#0A192F`), Aegean teal (`#00A896`), refined champagne gold (`#C5A059`), and warm ivory (`#FBF9F5`) with editorial serif typography (`Cinzel`) paired with `Plus Jakarta Sans`. Custom vector emblem combining a stylized nautical compass and sun star.
- **Cinematic Experience & Motion:**
  - 3D interactive tilt cards with cursor-based depth response (automatically disabled on touch devices).
  - Scroll-triggered reveals utilizing efficient `IntersectionObserver`.
  - Floating compass & animated journey route lines.
  - Full keyboard-accessible lightbox gallery (`ArrowLeft`, `ArrowRight`, `Escape`).
  - Seamless mobile slide-out drawer with zero layout thrashing.
  - Strict compliance with `prefers-reduced-motion`.
- **Robust Multi-Page Public Website:**
  1. `index.php` — Flagship homepage with cinematic hero, brand story, featured horizons, package explorer, sacred trails, 5-step process, gallery preview, and verified testimonials.
  2. `about.php` — Brand philosophy, core values, and verified travel standards.
  3. `destinations.php` — Filterable destination catalog (Incredible India Domestic vs Global Wonders).
  4. `destination.php` — Individual destination showcase with travel facts, coordinates, highlights, and associated tour packages.
  5. `packages.php` — Live package discovery with destination, category, duration, and search filters.
  6. `package.php` — Detailed itinerary breakdown with day-by-day tabs, meal/stay tags, inclusions/exclusions, WhatsApp pre-fill modal, and booking CTA.
  7. `pilgrimage.php` — Dedicated portal for sacred Vedic yatras (Varanasi, Ayodhya, Tamil Nadu temple corridors, Kerala shrines) with elder-friendly care ethics.
  8. `services.php` — Full-spectrum travel assistance (custom holidays, resort stays, private AC transit, Alleppey houseboats, flight ticketing, and visa clearances).
  9. `gallery.php` — Interactive photo gallery with category filter tags and lightbox.
  10. `contact.php` — Contact information, interactive contact form with CSRF validation, and office hours.
  11. `enquiry.php` — "Plan My Trip" interactive planner with client and server-side validation, storing submissions directly in MySQL with unique reference generation (`MV-ENQ-2026-XXXX`) and WhatsApp synchronization.
  12. `booking.php` — Tour package checkout with dynamic cost breakdown and deposit selection.
  13. `payment-success.php` — Verified booking confirmation voucher with printable layout (`window.print()`) and WhatsApp coordinator alert.
  14. `payment-failure.php` — Guidance for declined transactions with retry actions and direct coordinator contact.
  15. `faq.php` — Categorized expandable accordion answers.
  16. `privacy.php` — Transparent data privacy provisions.
  17. `terms.php` — Terms and conditions governing travel reservations.
  18. `refund-policy.php` — Comprehensive cancellation and refund policy timeline slabs.
- **Administrative Portal (`/admin/`):**
  - Secure authentication with BCRYPT password verification and brute-force throttling.
  - Real-time KPI dashboard: Total Enquiries, New Enquiries, Pending Follow-ups, Confirmed Bookings, and Captured Revenue.
  - Destinations Management (CRUD, image file upload, category assignment, slug, GPS coordinates, publish/featured toggle).
  - Tour Package Management (CRUD, day-by-day itinerary builder, pricing, inclusions, exclusions, status toggle).
  - Category Management (CRUD with active package counters).
  - Enquiry & Lead Manager (Search, filter by status, view detailed preferences, internal staff follow-up notes thread, WhatsApp direct messaging, CSV export).
  - Booking & Deposit Manager (Review guest details, travel date, payment status, booking status).
  - Payment Audit Trail (Inspect gateway order IDs, payment IDs, amounts, and signature verification timestamps).
  - Photo Gallery Manager (Upload photos, assign categories, and caption).
  - Testimonial Moderation (Add verified customer feedback).
  - FAQ Manager (Add and reorder questions across categories).
  - Site Settings (Configure agency phone numbers, official WhatsApp number, email, address, working hours, Razorpay keys, and test/live modes).
  - Staff Accounts (Manage administrators and role permissions).

---

## 2. Default Administrative Credentials

- **Portal URL:** `http://localhost/MIDHILA%20TRAVELS/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`
- **Role:** `super_admin`

> [!NOTE]
> You can update this password or add additional staff members at any time from the **Staff Accounts** menu in the admin dashboard (`/admin/users.php`).

---

## 3. Local XAMPP Setup Instructions

### Step 1: Place Files in XAMPP `htdocs`
Ensure the project is located at:
```
C:\xampp\htdocs\MIDHILA TRAVELS\
```

### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Start the **Apache** module.
3. Start the **MySQL** module.

### Step 3: Database Import (MySQL / phpMyAdmin)
**Method A: Using phpMyAdmin**
1. Open your browser and navigate to: `http://localhost/phpmyadmin/`
2. Click on the **Databases** tab, type `midhila_voyages` as the database name, choose `utf8mb4_unicode_ci` collation, and click **Create**.
3. Select the newly created `midhila_voyages` database from the left sidebar.
4. Click on the **Import** tab in the top navigation.
5. Click **Choose File**, select `database/midhila_voyages.sql` from the project directory, and click **Import** at the bottom.

**Method B: Using Command Line (Fastest)**
Open PowerShell or Command Prompt and run:
```powershell
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root < database\midhila_voyages.sql"
```

### Step 4: Access the Website
- **Public Homepage:** `http://localhost/MIDHILA%20TRAVELS/`
- **Admin Portal:** `http://localhost/MIDHILA%20TRAVELS/admin/`

---

## 4. Configuration & Settings

Application settings are centrally managed in `includes/config.php` and the database table `site_settings`:

| Setting | Default Value | Managed In |
|---|---|---|
| Database Host | `127.0.0.1` | `includes/config.php` |
| Database Name | `midhila_voyages` | `includes/config.php` |
| Database User | `root` | `includes/config.php` |
| Database Password | `""` (Empty in default XAMPP) | `includes/config.php` |
| Official WhatsApp | `+911234567890` | Admin -> Site Settings |
| Primary Phone | `+91 12345 67890` | Admin -> Site Settings |
| Primary Email | `journeys@midhilavoyages.com` | Admin -> Site Settings |
| Payment Gateway Mode | `test` (Sandbox simulator enabled) | Admin -> Site Settings |

---

## 5. Security & Best Practices Implemented

- **Prepared Statements (PDO):** All database operations utilize parameterized queries with strict PDO exception modes to completely eradicate SQL injection vulnerabilities.
- **CSRF Defense:** Cryptographic tokens generated via `random_bytes()` protecting all state-changing public forms, booking checkouts, and admin operations.
- **Secure File Uploads:** Uploaded images in `uploads/` are inspected using PHP `finfo` for MIME type verification, restricted to `jpg`, `png`, and `webp`, limited to 5MB, assigned non-deterministic random filenames, and protected by an `uploads/.htaccess` file disallowing script execution.
- **Authentication & Sessions:** Password storage uses standard BCRYPT `password_hash()` and `password_verify()`. Login sessions regenerate IDs upon authentication to block session fixation attacks, with `HttpOnly` and `SameSite=Lax` cookie flags.
- **XSS Mitigation:** All dynamic output rendered via contextual `htmlspecialchars()` escaping using the `e()` helper.
- **Idempotent Payment Processing:** Payment callbacks inspect existing transaction states before updating bookings, ensuring webhook repetitions or multiple browser refreshes never duplicate credits.

---

## 6. Production Deployment Checklist

1. **HTTPS Enforcement:** Enable SSL certificate on your hosting server and ensure `session.cookie_secure` activates automatically in `includes/config.php`.
2. **Database Credentials:** Update `DB_USER` and `DB_PASS` in `includes/config.php` (or set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT` for cloud/Vercel hosting).
3. **Razorpay Live Credentials:** When ready to accept live payments, enter your verified Razorpay Live Key ID and Secret in Admin -> Site Settings, and toggle Gateway Mode to `live`.
4. **Error Display:** Confirm `display_errors` is set to `0` in `includes/config.php` (already configured by default) so technical stack traces are never exposed to visitors.
5. **Change Admin Password:** Immediately change the default admin password from the admin dashboard.

---

## 7. Deploying to Vercel

1. **Repository Setup:** Push the code to GitHub (`https://github.com/Adarsh1530/midhila_voyages.git`).
2. **Database Setup:** 
   - Import `database/schema.sql` into a cloud MySQL provider (such as Aiven, PlanetScale, Supabase, TiDB Cloud, Railway, or Clever Cloud).
3. **Vercel Project Setup:**
   - Connect the repository on [Vercel](https://vercel.com/new).
   - In **Project Settings -> Environment Variables**, add:
     - `DB_HOST` = `<your_cloud_mysql_host>`
     - `DB_PORT` = `3306` (or provider's custom port)
     - `DB_NAME` = `<your_cloud_database_name>`
     - `DB_USER` = `<your_cloud_mysql_user>`
     - `DB_PASS` = `<your_cloud_mysql_password>`
4. **Deployment:**
   - Vercel detects `vercel.json` and runs PHP using the community runtime `vercel-php`.
   - Your site will be live at `https://your-project.vercel.app`!

