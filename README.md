# Weboflix — Setup & Deployment Guide

A Netflix-style streaming platform for practical tech & web development courses.

---

## 🚀 Instant Auto-Deployment on Railway (No Stress)

Deploying Weboflix on Railway is 100% automated:

1. **Push your code to GitHub** (either the root folder or `weboflix/` subfolder).
2. Go to **[Railway.com](https://railway.com)**, click **+ New Project** &rarr; **Deploy from GitHub repo**.
3. Select your repository.
4. In your Railway project canvas, click **+ New** &rarr; **Database** &rarr; **Add MySQL**.
5. In your Web Service settings on Railway:
   - Click **Settings** &rarr; **Networking** &rarr; **Generate Domain** (gives you a live `https://...up.railway.app` URL).
6. **That's it!**
   - Weboflix automatically connects to the Railway MySQL database using the injected environment variables (`MYSQL_URL` / `MYSQLHOST`).
   - On first launch, the built-in auto-init engine automatically creates all tables and seeds the admin account, categories, and real YouTube masterclasses without running any SQL manually!

---

## Option 2: cPanel / Shared Hosting Upload

Upload everything to your cPanel `public_html` (or a subfolder) via File
Manager or FTP, then extract.

## 2. Database

Shared hosting database users are never allowed to create databases
directly — you must create it through cPanel first, then import into
that empty database.

1. In cPanel, open **MySQL® Databases**.
2. Under "Create New Database," enter a name (e.g. `weboflix`) and
   click Create. cPanel will prefix it automatically, e.g.
   `yourcpaneluser_weboflix` — note the full prefixed name.
3. Under "Add New User," create a database user with a strong
   password — note the full prefixed username too, e.g.
   `yourcpaneluser_wfadmin`.
4. Under "Add User to Database," attach that user to the database you
   just created, and check **All Privileges**.
5. Open **phpMyAdmin** (also in cPanel), click on your new database in
   the left sidebar so it's selected/highlighted, then go to the
   **Import** tab, choose `database/schema.sql`, and click Go.
   - If you instead see `Access denied ... to database 'weboflix'`,
     it means the database wasn't selected first, or you're using the
     unprefixed name — make sure phpMyAdmin shows your database
     highlighted before importing, and never edit the file to add a
     `CREATE DATABASE` line back in.

## 3. Configure

Open `config/config.php` and fill in:

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — use the **full prefixed
  names** from step 2 (e.g. `DB_NAME` should be
  `yourcpaneluser_weboflix`, not just `weboflix`). `DB_HOST` is almost
  always `localhost` on cPanel.
- `SITE_URL` — your live domain, no trailing slash
- `FLW_PUBLIC_KEY`, `FLW_SECRET_KEY` — from your Flutterwave dashboard
  under Settings → API Keys (use **live** keys when you're ready to
  accept real payments, **test** keys while trying things out)
- `PRICE_MONTHLY`, `PRICE_YEARLY` — in Naira

The `config/` folder is blocked from direct browser access via
`.htaccess`, but never commit this file to a public git repo.

## 4. Log in as admin

Visit `yourdomain.com/admin/login.php`, log in with the seeded admin
account, and immediately change the password (there's no self-service
"change password" screen yet — update it directly in phpMyAdmin using
`password_hash()` output, or ask your developer to add one).

## 5. Add your real content

From the admin panel:

1. **Categories** — add or edit the tracks shown as homepage rows
2. **Courses** — add a course under a category, mark it published
3. Click **Modules** on a course → add modules, marking each **Free**
   or **Premium**
4. Click **Lessons** on a module → paste a YouTube URL (unlisted videos
   work fine) or a raw video ID, set the duration

Free modules are visible and playable by anyone. Premium modules are
locked behind a lock icon until the visitor has an active subscription.

## 6. Test a payment

Use Flutterwave's test card numbers (in their docs) with your test API
keys before switching to live keys. The flow is:
`pricing.php` → Flutterwave checkout → `flutterwave-callback.php`
(server-side verification, never trusts the redirect alone) →
subscription activated for 30 days (monthly) or 365 days (yearly).

## 7. Already have this site live? Run these migrations

If you deployed an earlier version of Weboflix, don't re-import
`schema.sql` — it will try to recreate tables you already have data
in. Instead, open phpMyAdmin, select your database, go to the **SQL**
tab, and run each of these once (safe on a live site with existing data):

- `database/migration_activity_tracking.sql` — needed for the Metrics page
- `database/migration_featured_course.sql` — needed for the "Feature on homepage" toggle
- `database/migration_real_youtube_courses.sql` — populates the real YouTube masterclasses (AI E-Commerce, Logistics tracking, Charity donation)

## Notes

- Videos are embedded via `youtube-nocookie.com` — there's no way to
  fully block someone from finding an "unlisted" link if they really
  try, but this keeps casual sharing/download friction reasonably high.
- Progress is tracked per lesson and rolled up into a per-course
  percentage on the dashboard and course page.
- The premium check happens server-side on every watch page load and
  on the `progress.php` endpoint — locking isn't just a frontend visual.
- **Metrics** (admin sidebar) shows who's actually active — right now,
  today, and this week — based on real page activity, plus signups and
  your most-engaged courses. The watch page also pings every 2 minutes
  so someone mid-video for a while doesn't drop out of "active now."
- The site (and admin panel) has a proper mobile menu now — a
  hamburger opens the nav/search on the public site, and a slide-in
  drawer replaces the sidebar in admin.
- The homepage hero is now a real playing video, Netflix-style: it
  automatically features your most recently added published course
  and autoplays (muted) its first lesson in the background, with
  Play / More Info buttons. There's nothing to configure — add a
  course with at least one lesson and it becomes the featured one.
- **Watching now requires an account, even for free lessons.**
  Clicking Play opens a seamless signup/login popup instead of
  navigating away — enter an email, then name and password reveal
  in the same popup, no page reload. This is enforced on the server
  too (`watch.php` requires login), so it can't be bypassed by
  disabling JavaScript or linking straight to a lesson URL.
- **The hero video is fully replaceable.** Go to Courses in the admin
  panel and click "Feature on homepage" on whichever course you want
  playing in the hero — only one can be featured at a time. If none
  is marked, the most recently added course is used automatically.
