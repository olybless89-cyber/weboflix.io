-- ============================================================
-- Weboflix Database Schema (MySQL)
-- ============================================================
-- On cPanel/shared hosting, create the database first via
-- cPanel > MySQL Databases (this also gives it your account's
-- prefix, e.g. username_weboflix). Then select that database in
-- phpMyAdmin and import this file — do NOT run CREATE DATABASE,
-- your hosting user almost certainly doesn't have that privilege.
-- ============================================================

-- ---------- Users ----------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_color VARCHAR(7) DEFAULT '#4C6EF5',
    is_admin TINYINT(1) DEFAULT 0,
    last_active_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_last_active (last_active_at)
) ENGINE=InnoDB;

-- ---------- Subscriptions ----------
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan ENUM('monthly','yearly') NOT NULL,
    status ENUM('active','expired','cancelled') DEFAULT 'active',
    amount_kobo INT NOT NULL,
    flutterwave_ref VARCHAR(100),
    flutterwave_tx_id VARCHAR(100),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Categories (rows) ----------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Courses ----------
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description TEXT,
    thumbnail_url VARCHAR(400),
    banner_url VARCHAR(400),
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    is_published TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Modules ----------
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT,
    access_level ENUM('free','premium') DEFAULT 'premium',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Lessons ----------
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    youtube_id VARCHAR(30) NOT NULL,
    duration_seconds INT DEFAULT 0,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Progress tracking ----------
CREATE TABLE lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    course_id INT NOT NULL,
    last_position_seconds INT DEFAULT 0,
    completed TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_lesson (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Seed: categories ----------
INSERT INTO categories (name, slug, sort_order) VALUES
('Web Development', 'web-development', 1),
('Artificial Intelligence', 'artificial-intelligence', 2),
('Cybersecurity', 'cybersecurity', 3),
('Mobile Development', 'mobile-development', 4),
('Cloud & DevOps', 'cloud-devops', 5);

-- ---------- Seed: admin user (password: admin123 - CHANGE IMMEDIATELY) ----------
-- Hash generated with password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password_hash, is_admin) VALUES
('Admin', 'admin@weboflix.io', '$2y$10$w3Q5nW.7e7EFfZ28V5cvCuA//YqnkyVbTxVjTHkrGyd.0pTLyqhry', 1);

-- ---------- Seed: Real Courses from Web Oracle with actual YouTube videos ----------
-- Course 1 (Featured on Homepage Hero)
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order) VALUES
(2, 'How to Build an E-Commerce Website With AI in 20 Minutes', 'build-ecommerce-website-with-ai',
 'Step-by-step masterclass on building a complete, high-converting online e-commerce store in just 20 minutes using cutting-edge AI tools. Master prompt engineering, instant product catalogs, checkout flows, and automated launch strategies with Web Oracle.',
 'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg',
 'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg',
 'beginner', 1, 1, 1);

SET @c1 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@c1, 'Getting Started with AI Commerce', 'Setting up your AI workspace and generating store foundations.', 'free', 1),
(@c1, 'Store Architecture & Catalog Generation', 'Automating product listings, high-converting copy and images.', 'free', 2),
(@c1, 'Payment Integration & Cart Setup', 'Connecting secure payment gateways and checkout optimizations.', 'premium', 3),
(@c1, 'Live Store Launch & Traffic Strategy', 'Domain setup, SEO indexing, and launching in 20 minutes.', 'premium', 4);

SET @c1_m1 = (SELECT id FROM modules WHERE course_id = @c1 AND sort_order = 1);
SET @c1_m2 = (SELECT id FROM modules WHERE course_id = @c1 AND sort_order = 2);
SET @c1_m3 = (SELECT id FROM modules WHERE course_id = @c1 AND sort_order = 3);
SET @c1_m4 = (SELECT id FROM modules WHERE course_id = @c1 AND sort_order = 4);

INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@c1_m1, 'Welcome & AI Tools Overview', 'EUfyzKqmZbk', 300, 'Introduction to the 20-minute AI e-commerce workflow and tools.', 1),
(@c1_m1, 'Prompt Engineering for Store Design', 'EUfyzKqmZbk', 420, 'How to craft prompts that generate clean, modern store layouts.', 2),
(@c1_m2, 'Generating High-Converting Products & Assets', 'EUfyzKqmZbk', 480, 'Creating product mockups, descriptions, and categories with AI.', 1),
(@c1_m2, 'Store Navigation and Mobile Responsiveness', 'EUfyzKqmZbk', 390, 'Ensuring smooth mobile navigation and fast page load times.', 2),
(@c1_m3, 'Configuring Secure Payment Gateways', 'EUfyzKqmZbk', 540, 'Setting up payment gateways, currency rules, and instant checkout.', 1),
(@c1_m3, 'Shopping Cart & Frictionless Checkout Flow', 'EUfyzKqmZbk', 450, 'Reducing cart abandonment with streamlined checkout.', 2),
(@c1_m4, 'Testing Live Transactions & Security', 'EUfyzKqmZbk', 360, 'Performing test checkouts and order notifications.', 1),
(@c1_m4, 'Go Live: Launching and Generating First Orders', 'EUfyzKqmZbk', 480, 'Putting the store live and applying traffic acceleration tactics.', 2);

-- Course 2: Courier & Logistics Website
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order) VALUES
(1, 'How to Create a Courier or Logistics Website (Free Theme)', 'create-courier-logistics-website',
 'Comprehensive guide on creating a professional cargo, shipment, and courier logistics website using WordPress. Features live shipment tracking numbers, freight rate calculators, dispatcher portals, and custom quote forms.',
 'https://i.ytimg.com/vi/6xbGLvcMRjQ/maxresdefault.jpg',
 'https://i.ytimg.com/vi/6xbGLvcMRjQ/maxresdefault.jpg',
 'intermediate', 1, 0, 2);

SET @c2 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@c2, 'Logistics Platform Foundations', 'Setting up WordPress and the free logistics framework.', 'free', 1),
(@c2, 'Real-Time Tracking System', 'Configuring tracking numbers, consignment status and timelines.', 'free', 2),
(@c2, 'Freight Rate Calculator & Quotes', 'Automated pricing calculation based on weight, distance and cargo.', 'premium', 3),
(@c2, 'Client Portal & Shipment Management', 'Customer shipment history, printable waybills, and notification alerts.', 'premium', 4);

SET @c2_m1 = (SELECT id FROM modules WHERE course_id = @c2 AND sort_order = 1);
SET @c2_m2 = (SELECT id FROM modules WHERE course_id = @c2 AND sort_order = 2);
SET @c2_m3 = (SELECT id FROM modules WHERE course_id = @c2 AND sort_order = 3);
SET @c2_m4 = (SELECT id FROM modules WHERE course_id = @c2 AND sort_order = 4);

INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@c2_m1, 'Logistics Website Blueprint & Theme Setup', '6xbGLvcMRjQ', 360, 'Selecting the right free theme and setting up site structure.', 1),
(@c2_m1, 'Customizing Branding, Fleet & Service Pages', '6xbGLvcMRjQ', 450, 'Building the air freight, sea freight, and land cargo showcase pages.', 2),
(@c2_m2, 'Installing & Configuring the Tracking Engine', '6xbGLvcMRjQ', 540, 'Setting up the tracking shortcode and package lookup forms.', 1),
(@c2_m2, 'Creating Live Consignment Records & Checkpoints', '6xbGLvcMRjQ', 480, 'Adding checkpoints: departed, in transit, customs clearance, delivered.', 2),
(@c2_m3, 'Setting Up the Instant Freight Calculator', '6xbGLvcMRjQ', 510, 'Building custom quote calculators for domestic and international shipments.', 1),
(@c2_m3, 'Automated Email & SMS Shipment Alerts', '6xbGLvcMRjQ', 420, 'Triggering automatic status updates when a package arrives at a hub.', 2),
(@c2_m4, 'Generating Printable Barcode Waybills', '6xbGLvcMRjQ', 460, 'Creating PDF invoices and barcode dispatch manifests.', 1),
(@c2_m4, 'Dispatcher Dashboard & Security Best Practices', '6xbGLvcMRjQ', 520, 'Securing tracking numbers and setting up multi-agent logistics dispatch.', 2);

-- Course 3: Charity & Donation Website
INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order) VALUES
(1, 'How I Built a Professional Charity Donation Website With WordPress', 'build-charity-donation-website',
 'Learn how to create a high-trust non-profit and charity donation website using WordPress. Implement visual campaign progress bars, recurring monthly donations, donor recognition walls, and automated tax receipts that inspire trust.',
 'https://i.ytimg.com/vi/mykCOktgKZk/maxresdefault.jpg',
 'https://i.ytimg.com/vi/mykCOktgKZk/maxresdefault.jpg',
 'intermediate', 1, 0, 3);

SET @c3 = LAST_INSERT_ID();

INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES
(@c3, 'Non-Profit Design & Trust Fundamentals', 'Structure, visual hierarchy, and credibility cues for non-profits.', 'free', 1),
(@c3, 'Campaign Creation & Funding Goal Bars', 'Creating causes with targeted funding goals and real-time progress meters.', 'free', 2),
(@c3, 'Multi-Currency Donation Engine', 'Setting up one-time and recurring monthly gifts with instant payment.', 'premium', 3),
(@c3, 'Donor Management, Receipts & Impact Pages', 'Automated receipts, donor walls, and transparent impact reports.', 'premium', 4);

SET @c3_m1 = (SELECT id FROM modules WHERE course_id = @c3 AND sort_order = 1);
SET @c3_m2 = (SELECT id FROM modules WHERE course_id = @c3 AND sort_order = 2);
SET @c3_m3 = (SELECT id FROM modules WHERE course_id = @c3 AND sort_order = 3);
SET @c3_m4 = (SELECT id FROM modules WHERE course_id = @c3 AND sort_order = 4);

INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES
(@c3_m1, 'Charity Website Architecture & Theme Selection', 'mykCOktgKZk', 380, 'Choosing a lightweight, accessible theme for humanitarian causes.', 1),
(@c3_m1, 'Crafting Compelling Cause Stories & Imagery', 'mykCOktgKZk', 460, 'How to present mission statements and beneficiary impact visually.', 2),
(@c3_m2, 'Setting Up Dynamic Funding Goal Bars', 'mykCOktgKZk', 500, 'Configuring campaign target amounts, countdowns, and percentage meters.', 1),
(@c3_m2, 'Pre-set Donation Amounts & Giving Tiers', 'mykCOktgKZk', 420, 'Configuring giving tiers ($25, $50, $100) with impact explanations.', 2),
(@c3_m3, 'Integrating Payment Gateways', 'mykCOktgKZk', 550, 'Configuring multi-currency checkout for domestic and global supporters.', 1),
(@c3_m3, 'Recurring Monthly Giving Programs', 'mykCOktgKZk', 470, 'Building sustainable recurring donor clubs with automatic billing.', 2),
(@c3_m4, 'Automated Donor Thank-You Receipts', 'mykCOktgKZk', 390, 'Instant branded PDF receipts and tax documentation for contributions.', 1),
(@c3_m4, 'Donor Wall & Financial Transparency Dashboard', 'mykCOktgKZk', 440, 'Building donor appreciation walls and fund allocation transparency charts.', 2);
