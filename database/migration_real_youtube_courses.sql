-- ============================================================
-- Migration: Add Real YouTube Courses from Web Oracle
-- Run this in phpMyAdmin (SQL tab) against your weboflix database.
-- It populates the 3 real courses with real YouTube videos & thumbnails.
-- ============================================================

-- Remove the old placeholder course if it exists
DELETE FROM courses WHERE slug = 'modern-javascript-fundamentals';

-- Ensure Web Development & Artificial Intelligence categories exist
INSERT IGNORE INTO categories (name, slug, sort_order) VALUES
('Web Development', 'web-development', 1),
('Artificial Intelligence', 'artificial-intelligence', 2),
('WordPress & CMS', 'wordpress-cms', 3),
('Cybersecurity', 'cybersecurity', 4),
('Cloud & DevOps', 'cloud-devops', 5);

-- ------------------------------------------------------------
-- Course 1: AI E-Commerce Mastery (Featured Hero Video)
-- YouTube ID: EUfyzKqmZbk
-- ------------------------------------------------------------
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
VALUES (
    (SELECT id FROM categories WHERE slug = 'artificial-intelligence' LIMIT 1),
    'How to Build an E-Commerce Website With AI in 20 Minutes',
    'build-ecommerce-website-with-ai',
    'Step-by-step masterclass on building a complete, high-converting online e-commerce store in just 20 minutes using cutting-edge AI tools. Master prompt engineering, instant product catalogs, high-converting checkout flows, and automated launch strategies with Web Oracle.',
    'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg',
    'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg',
    'beginner',
    1,
    1,
    1
) ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    thumbnail_url = VALUES(thumbnail_url),
    banner_url = VALUES(banner_url),
    is_featured = 1,
    is_published = 1;

SET @course1_id = (SELECT id FROM courses WHERE slug = 'build-ecommerce-website-with-ai' LIMIT 1);

-- Modules for Course 1
INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@course1_id, 'Getting Started with AI Commerce', 'Setting up your AI workspace and generating store foundations.', 'free', 1),
(@course1_id, 'Store Architecture & Catalog Generation', 'Automating product listings, high-converting copy and images.', 'free', 2),
(@course1_id, 'Payment Integration & Cart Setup', 'Connecting secure payment gateways and checkout optimizations.', 'premium', 3),
(@course1_id, 'Live Store Launch & Traffic Strategy', 'Domain setup, SEO indexing, and launching in 20 minutes.', 'premium', 4);

SET @mod1_1 = (SELECT id FROM modules WHERE course_id = @course1_id AND sort_order = 1 LIMIT 1);
SET @mod1_2 = (SELECT id FROM modules WHERE course_id = @course1_id AND sort_order = 2 LIMIT 1);
SET @mod1_3 = (SELECT id FROM modules WHERE course_id = @course1_id AND sort_order = 3 LIMIT 1);
SET @mod1_4 = (SELECT id FROM modules WHERE course_id = @course1_id AND sort_order = 4 LIMIT 1);

-- Lessons for Course 1
INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@mod1_1, 'Welcome & AI Tools Overview', 'EUfyzKqmZbk', 300, 'Introduction to the 20-minute AI e-commerce workflow and recommended tools.', 1),
(@mod1_1, 'Prompt Engineering for Store Design', 'EUfyzKqmZbk', 420, 'How to craft prompts that generate clean, modern store layouts.', 2),
(@mod1_2, 'Generating High-Converting Products & Assets', 'EUfyzKqmZbk', 480, 'Creating product mockups, descriptions, and categories with AI.', 1),
(@mod1_2, 'Store Navigation and Mobile Responsiveness', 'EUfyzKqmZbk', 390, 'Ensuring smooth mobile navigation and fast page load times.', 2),
(@mod1_3, 'Configuring Secure Payment Gateways', 'EUfyzKqmZbk', 540, 'Setting up payment gateways, currency rules, and instant checkout.', 1),
(@mod1_3, 'Shopping Cart & Frictionless Checkout Flow', 'EUfyzKqmZbk', 450, 'Reducing cart abandonment with streamlined 1-step checkout.', 2),
(@mod1_4, 'Testing Live Transactions & Security', 'EUfyzKqmZbk', 360, 'Performing test checkouts, SSL verification, and order notifications.', 1),
(@mod1_4, 'Go Live: Launching and Generating First Orders', 'EUfyzKqmZbk', 480, 'Putting the store live and applying traffic acceleration tactics.', 2);

-- ------------------------------------------------------------
-- Course 2: Courier & Logistics Website with WordPress
-- YouTube ID: 6xbGLvcMRjQ
-- ------------------------------------------------------------
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
VALUES (
    (SELECT id FROM categories WHERE slug = 'web-development' LIMIT 1),
    'How to Create a Courier or Logistics Website (Free Theme)',
    'create-courier-logistics-website',
    'Comprehensive guide on creating a professional cargo, shipment, and courier logistics website using WordPress. Features live shipment tracking numbers, freight rate calculators, dispatcher portals, and custom quote forms.',
    'https://i.ytimg.com/vi/6xbGLvcMRjQ/maxresdefault.jpg',
    'https://i.ytimg.com/vi/6xbGLvcMRjQ/maxresdefault.jpg',
    'intermediate',
    1,
    0,
    2
) ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    thumbnail_url = VALUES(thumbnail_url),
    banner_url = VALUES(banner_url),
    is_published = 1;

SET @course2_id = (SELECT id FROM courses WHERE slug = 'create-courier-logistics-website' LIMIT 1);

-- Modules for Course 2
INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@course2_id, 'Logistics Architecture & Theme Setup', 'Setting up WordPress and the free logistics framework.', 'free', 1),
(@course2_id, 'Real-Time Tracking System', 'Configuring tracking numbers, consignment status and timelines.', 'free', 2),
(@course2_id, 'Freight Rate Calculator & Quotes', 'Automated pricing calculation based on weight, distance and cargo.', 'premium', 3),
(@course2_id, 'Client Portal & Shipment Management', 'Customer shipment history, printable waybills, and notification alerts.', 'premium', 4);

SET @mod2_1 = (SELECT id FROM modules WHERE course_id = @course2_id AND sort_order = 1 LIMIT 1);
SET @mod2_2 = (SELECT id FROM modules WHERE course_id = @course2_id AND sort_order = 2 LIMIT 1);
SET @mod2_3 = (SELECT id FROM modules WHERE course_id = @course2_id AND sort_order = 3 LIMIT 1);
SET @mod2_4 = (SELECT id FROM modules WHERE course_id = @course2_id AND sort_order = 4 LIMIT 1);

-- Lessons for Course 2
INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@mod2_1, 'Logistics Website Blueprint & Theme Setup', '6xbGLvcMRjQ', 360, 'Selecting the right free theme and setting up site structure.', 1),
(@mod2_1, 'Customizing Branding, Fleet & Service Pages', '6xbGLvcMRjQ', 450, 'Building the air freight, sea freight, and land cargo showcase pages.', 2),
(@mod2_2, 'Installing & Configuring the Tracking Engine', '6xbGLvcMRjQ', 540, 'Setting up the tracking shortcode and package lookup forms.', 1),
(@mod2_2, 'Creating Live Consignment Records & Checkpoints', '6xbGLvcMRjQ', 480, 'Adding checkpoints: departed, in transit, customs clearance, delivered.', 2),
(@mod2_3, 'Setting Up the Instant Freight Calculator', '6xbGLvcMRjQ', 510, 'Building custom quote calculators for domestic and international shipments.', 1),
(@mod2_3, 'Automated Email & SMS Shipment Alerts', '6xbGLvcMRjQ', 420, 'Triggering automatic status updates when a package arrives at a hub.', 2),
(@mod2_4, 'Generating Printable Barcode Waybills', '6xbGLvcMRjQ', 460, 'Creating PDF invoices and barcode dispatch manifests.', 1),
(@mod2_4, 'Dispatcher Dashboard & Security Best Practices', '6xbGLvcMRjQ', 520, 'Securing tracking numbers and setting up multi-agent logistics dispatch.', 2);

-- ------------------------------------------------------------
-- Course 3: Professional Charity & Donation Website
-- YouTube ID: mykCOktgKZk
-- ------------------------------------------------------------
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
VALUES (
    (SELECT id FROM categories WHERE slug = 'web-development' LIMIT 1),
    'How I Built a Professional Charity Donation Website With WordPress',
    'build-charity-donation-website',
    'Learn how to create a high-trust non-profit and charity donation website using WordPress. Implement visual campaign progress bars, recurring monthly donations, donor recognition walls, and automated tax receipts that inspire trust.',
    'https://i.ytimg.com/vi/mykCOktgKZk/maxresdefault.jpg',
    'https://i.ytimg.com/vi/mykCOktgKZk/maxresdefault.jpg',
    'intermediate',
    1,
    0,
    3
) ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    thumbnail_url = VALUES(thumbnail_url),
    banner_url = VALUES(banner_url),
    is_published = 1;

SET @course3_id = (SELECT id FROM courses WHERE slug = 'build-charity-donation-website' LIMIT 1);

-- Modules for Course 3
INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@course3_id, 'Non-Profit Design & Trust Fundamentals', 'Structure, visual hierarchy, and credibility cues for non-profits.', 'free', 1),
(@course3_id, 'Campaign Creation & Funding Goal Bars', 'Creating causes with targeted funding goals and real-time progress meters.', 'free', 2),
(@course3_id, 'Multi-Currency Donation Engine', 'Setting up one-time and recurring monthly gifts with instant payment.', 'premium', 3),
(@course3_id, 'Donor Management, Receipts & Impact Pages', 'Automated 501(c)(3) receipts, donor walls, and transparent impact reports.', 'premium', 4);

SET @mod3_1 = (SELECT id FROM modules WHERE course_id = @course3_id AND sort_order = 1 LIMIT 1);
SET @mod3_2 = (SELECT id FROM modules WHERE course_id = @course3_id AND sort_order = 2 LIMIT 1);
SET @mod3_3 = (SELECT id FROM modules WHERE course_id = @course3_id AND sort_order = 3 LIMIT 1);
SET @mod3_4 = (SELECT id FROM modules WHERE course_id = @course3_id AND sort_order = 4 LIMIT 1);

-- Lessons for Course 3
INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@mod3_1, 'Charity Website Architecture & Theme Selection', 'mykCOktgKZk', 380, 'Choosing a lightweight, accessible theme for humanitarian causes.', 1),
(@mod3_1, 'Crafting Compelling Cause Stories & Imagery', 'mykCOktgKZk', 460, 'How to present mission statements and beneficiary impact visually.', 2),
(@mod3_2, 'Setting Up Dynamic Funding Goal Bars', 'mykCOktgKZk', 500, 'Configuring campaign target amounts, countdowns, and percentage meters.', 1),
(@mod3_2, 'Pre-set Donation Amounts & Giving Tiers', 'mykCOktgKZk', 420, 'Configuring giving tiers ($25, $50, $100) with impact explanations.', 2),
(@mod3_3, 'Integrating Flutterwave, Stripe & PayPal Donations', 'mykCOktgKZk', 550, 'Configuring multi-currency checkout for domestic and global supporters.', 1),
(@mod3_3, 'Recurring Monthly Giving Programs', 'mykCOktgKZk', 470, 'Building sustainable recurring donor clubs with automatic billing.', 2),
(@mod3_4, 'Automated Donor Thank-You Receipts & Letters', 'mykCOktgKZk', 390, 'Instant branded PDF receipts and tax documentation for contributions.', 1),
(@mod3_4, 'Donor Wall & Financial Transparency Dashboard', 'mykCOktgKZk', 440, 'Building donor appreciation walls and fund allocation transparency charts.', 2);
