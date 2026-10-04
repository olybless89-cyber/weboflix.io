<?php
require_once __DIR__ . '/includes/functions.php';
$__page = 'home';
$user = current_user();

// Fetch categories
$categories = db()->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

// Course query per category
$courseStmt = db()->prepare(
    "SELECT c.*,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id AND m.access_level = 'free') AS free_modules,
        (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
        (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id
     FROM courses c
     WHERE c.category_id = ? AND c.is_published = 1
     ORDER BY c.sort_order ASC, c.id DESC"
);

// Continue watching
$continueWatching = [];
if ($user) {
    $cwStmt = db()->prepare(
        "SELECT DISTINCT c.*,
            (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
            (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id
         FROM lesson_progress lp
         JOIN courses c ON c.id = lp.course_id
         WHERE lp.user_id = ?
         ORDER BY lp.updated_at DESC LIMIT 8"
    );
    $cwStmt->execute([$user['id']]);
    $continueWatching = $cwStmt->fetchAll();
}

// Top 10 courses
$top10 = db()->query(
    "SELECT c.*, cat.name AS category_name,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
        (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
        (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id
     FROM courses c
     JOIN categories cat ON cat.id = c.category_id
     WHERE c.is_published = 1
     ORDER BY c.sort_order ASC, c.id DESC
     LIMIT 10"
)->fetchAll();

// Featured hero billboard course
$featured = db()->query(
    "SELECT c.*, cat.name AS category_name,
        (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
        (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count
     FROM courses c
     JOIN categories cat ON cat.id = c.category_id
     WHERE c.is_published = 1
     ORDER BY
        (c.is_featured = 1 AND EXISTS (SELECT 1 FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id)) DESC,
        c.id DESC
     LIMIT 1"
)->fetch();

// Prepare modal course data map for instant Netflix Quick-Preview
$coursesForModal = [];
$allCoursesQuery = db()->query(
    "SELECT c.*, cat.name AS category_name,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
        (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
        (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id
     FROM courses c
     JOIN categories cat ON cat.id = c.category_id
     WHERE c.is_published = 1"
)->fetchAll();

foreach ($allCoursesQuery as $c) {
    $mStmt = db()->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order ASC");
    $mStmt->execute([$c['id']]);
    $cModules = $mStmt->fetchAll();

    $cLessons = [];
    foreach ($cModules as $mod) {
        $lStmt = db()->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC");
        $lStmt->execute([$mod['id']]);
        foreach ($lStmt->fetchAll() as $les) {
            $cLessons[] = [
                'id' => (int) $les['id'],
                'title' => $les['title'],
                'youtube_id' => $les['youtube_id'],
                'duration' => format_duration((int) $les['duration_seconds']),
                'description' => $les['description'] ?: 'Hands-on lesson walkthrough with Web Oracle.',
                'module_title' => $mod['title'],
                'access_level' => $mod['access_level'],
                'thumbnail_url' => 'https://i.ytimg.com/vi/' . $les['youtube_id'] . '/hqdefault.jpg'
            ];
        }
    }

    $fallbackThumb = 'https://i.ytimg.com/vi/' . ($c['first_youtube_id'] ?: 'EUfyzKqmZbk') . '/maxresdefault.jpg';
    $coursesForModal[$c['id']] = [
        'id' => (int) $c['id'],
        'title' => $c['title'],
        'slug' => $c['slug'],
        'description' => $c['description'],
        'thumbnail_url' => $c['thumbnail_url'] ?: $fallbackThumb,
        'banner_url' => $c['banner_url'] ?: $c['thumbnail_url'] ?: $fallbackThumb,
        'category_name' => $c['category_name'],
        'level' => ucfirst($c['level']),
        'lesson_count' => (int) $c['lesson_count'],
        'first_lesson_id' => $c['first_lesson_id'],
        'first_youtube_id' => $c['first_youtube_id'],
        'lessons' => $cLessons,
    ];
}

$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<!-- Netflix Hero Billboard -->
<header class="hero">
  <?php if ($featured && $featured['first_youtube_id']): ?>
    <div class="hero-video-wrap">
      <iframe
        id="heroVideoFrame"
        class="hero-video"
        src="https://www.youtube-nocookie.com/embed/<?= h($featured['first_youtube_id']) ?>?autoplay=1&mute=1&loop=1&playlist=<?= h($featured['first_youtube_id']) ?>&controls=0&modestbranding=1&rel=0&showinfo=0&iv_load_policy=3&enablejsapi=1"
        title="" frameborder="0" allow="autoplay; encrypted-media" tabindex="-1" aria-hidden="true">
      </iframe>
    </div>
  <?php else: ?>
    <div class="hero-backdrop-fallback" style="background-image: url('<?= h($featured['banner_url'] ?? $featured['thumbnail_url'] ?? 'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg') ?>');"></div>
  <?php endif; ?>

  <!-- Netflix Cinematic Vignette Fades -->
  <div class="hero-vignette-bottom" aria-hidden="true"></div>
  <div class="hero-vignette-left" aria-hidden="true"></div>

  <div class="container hero-inner">
    <?php if ($featured): ?>
      <div class="netflix-badge-pill">
        <span class="n-logo">W</span>
        <span class="series-text">SERIES &bull; WEBOFLIX ORIGINAL</span>
      </div>

      <h1><?= h($featured['title']) ?></h1>

      <div class="hero-meta-row">
        <span class="hero-match-score">98% Match</span>
        <span class="badge-quality">4K ULTRA HD</span>
        <span class="badge-level"><?= ucfirst($featured['level']) ?></span>
        <span class="badge-quality">8 Lessons</span>
      </div>

      <p><?= h(truncate_text(strip_tags((string) $featured['description']), 160)) ?></p>

      <div class="hero-actions">
        <?php if ($featured['first_lesson_id']): ?>
        <a href="<?= base_url('watch.php?lesson=' . $featured['first_lesson_id']) ?>" class="btn-netflix-play" <?= $user ? '' : 'data-auth-gate' ?>>
          <svg width="20" height="20" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg> Play
        </a>
        <?php endif; ?>
        <button type="button" class="btn-netflix-info" onclick="wfOpenQuickModal(<?= (int) $featured['id'] ?>)">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> More Info
        </button>
      </div>
    <?php else: ?>
      <div class="netflix-badge-pill">
        <span class="n-logo">W</span>
        <span class="series-text">MASTERCLASS STREAMING</span>
      </div>
      <h1>Build Real Tech Products.</h1>
      <p>Watch step-by-step masterclasses on AI stores, WordPress platforms, and modern web applications with Web Oracle.</p>
      <div class="hero-actions">
        <a href="#browse" class="btn-netflix-play"><svg width="20" height="20" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg> Start Watching</a>
        <a href="<?= base_url('pricing.php') ?>" class="btn-netflix-info">View Plans</a>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($featured && $featured['first_youtube_id']): ?>
  <div class="hero-right-controls">
    <button class="hero-mute-toggle" id="heroMuteToggle" aria-label="Unmute preview" data-muted="1">
      <svg class="icon-muted" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
      <svg class="icon-unmuted" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 010 7.07"/><path d="M19.07 4.93a10 10 0 010 14.14"/></svg>
    </button>
    <div class="hero-rating-badge">ALL</div>
  </div>
  <?php endif; ?>
</header>

<main class="browse-content" id="browse">

  <!-- Continue Watching Row (if user has active history) -->
  <?php if ($continueWatching): ?>
    <section class="row-section">
      <div class="row-header">
        <h2 class="row-title">Continue Watching for <?= h($user['name']) ?> <span class="title-arrow">&gt;</span></h2>
      </div>
      <div class="row-slider-container">
        <button class="slider-btn prev" data-slider-target="slider-cw" aria-label="Scroll left">&#10094;</button>
        <div class="row-slider" id="slider-cw">
          <?php foreach ($continueWatching as $course):
              $pct = course_progress_percent((int) $user['id'], (int) $course['id']);
              $thumb = $course['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . ($course['first_youtube_id'] ?: 'EUfyzKqmZbk') . '/maxresdefault.jpg';
          ?>
          <div class="netflix-card" onclick="wfOpenQuickModal(<?= (int) $course['id'] ?>)">
            <div class="card-media">
              <img src="<?= h($thumb) ?>" alt="<?= h($course['title']) ?>" loading="lazy">
              <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
              </div>
            </div>
            <div class="card-details">
              <div class="card-action-bar">
                <div class="card-action-left">
                  <?php if ($course['first_lesson_id']): ?>
                  <a href="<?= base_url('watch.php?lesson=' . $course['first_lesson_id']) ?>" class="btn-card-action btn-card-play" onclick="event.stopPropagation();">
                    <svg width="12" height="12" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg>
                  </a>
                  <?php endif; ?>
                  <button type="button" class="btn-card-action" title="Add to My List" onclick="event.stopPropagation(); wfToggleMyList(<?= (int) $course['id'] ?>, this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </button>
                  <button type="button" class="btn-card-action" title="I like this" onclick="event.stopPropagation(); wfLikeCard(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                  </button>
                </div>
                <button type="button" class="btn-card-action" title="More Info" onclick="event.stopPropagation(); wfOpenQuickModal(<?= (int) $course['id'] ?>)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              </div>
              <div class="card-info-title"><?= h($course['title']) ?></div>
              <div class="card-info-meta">
                <span class="match"><?= $pct ?>% complete</span>
                <span class="badge-quality">HD</span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="slider-btn next" data-slider-target="slider-cw" aria-label="Scroll right">&#10095;</button>
      </div>
    </section>
  <?php endif; ?>

  <!-- Netflix Top 10 Row -->
  <?php if ($top10): ?>
    <section class="row-section top10-row" id="top10">
      <div class="row-header">
        <h2 class="row-title">Top 10 Courses in Tech Today <span class="title-arrow">&gt;</span></h2>
        <span class="row-view-all">Updated Daily</span>
      </div>
      <div class="row-slider-container">
        <button class="slider-btn prev" data-slider-target="slider-top10" aria-label="Scroll left">&#10094;</button>
        <div class="row-slider" id="slider-top10">
          <?php foreach ($top10 as $idx => $tCourse):
              $rank = $idx + 1;
              $thumb = $tCourse['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . ($tCourse['first_youtube_id'] ?: 'EUfyzKqmZbk') . '/maxresdefault.jpg';
          ?>
          <div class="netflix-card-top10" onclick="wfOpenQuickModal(<?= (int) $tCourse['id'] ?>)">
            <div class="top10-rank-num"><?= $rank ?></div>
            <div class="top10-poster">
              <img src="<?= h($thumb) ?>" alt="<?= h($tCourse['title']) ?>" loading="lazy">
              <div class="top10-poster-overlay">
                <span class="match">98% Match</span>
                <h4><?= h($tCourse['title']) ?></h4>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="slider-btn next" data-slider-target="slider-top10" aria-label="Scroll right">&#10095;</button>
      </div>
    </section>
  <?php endif; ?>

  <!-- Category Rows -->
  <?php foreach ($categories as $cat):
      $courseStmt->execute([$cat['id']]);
      $courses = $courseStmt->fetchAll();
      if (!$courses) continue;
      $sliderId = 'slider-cat-' . $cat['id'];
  ?>
    <section class="row-section">
      <div class="row-header">
        <a href="<?= base_url('category.php?slug=' . urlencode($cat['slug'])) ?>" class="row-title">
          <?= h($cat['name']) ?> <span class="title-arrow">&gt;</span>
        </a>
        <a href="<?= base_url('category.php?slug=' . urlencode($cat['slug'])) ?>" class="row-view-all">Explore All &rarr;</a>
      </div>
      <div class="row-slider-container">
        <button class="slider-btn prev" data-slider-target="<?= $sliderId ?>" aria-label="Scroll left">&#10094;</button>
        <div class="row-slider" id="<?= $sliderId ?>">
          <?php foreach ($courses as $c):
              $isFree = $c['free_modules'] > 0;
              $thumb = $c['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . ($c['first_youtube_id'] ?: 'EUfyzKqmZbk') . '/maxresdefault.jpg';
          ?>
          <div class="netflix-card" onclick="wfOpenQuickModal(<?= (int) $c['id'] ?>)">
            <div class="card-media">
              <img src="<?= h($thumb) ?>" alt="<?= h($c['title']) ?>" loading="lazy">
              <span class="card-ribbon <?= $isFree ? 'free' : 'premium' ?>"><?= $isFree ? 'Free Lessons' : 'Premium' ?></span>
            </div>
            <div class="card-details">
              <div class="card-action-bar">
                <div class="card-action-left">
                  <?php if ($c['first_lesson_id']): ?>
                  <a href="<?= base_url('watch.php?lesson=' . $c['first_lesson_id']) ?>" class="btn-card-action btn-card-play" onclick="event.stopPropagation();" <?= $user ? '' : 'data-auth-gate' ?>>
                    <svg width="12" height="12" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg>
                  </a>
                  <?php endif; ?>
                  <button type="button" class="btn-card-action" title="Add to My List" onclick="event.stopPropagation(); wfToggleMyList(<?= (int) $c['id'] ?>, this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </button>
                  <button type="button" class="btn-card-action" title="I like this" onclick="event.stopPropagation(); wfLikeCard(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                  </button>
                </div>
                <button type="button" class="btn-card-action" title="More Info" onclick="event.stopPropagation(); wfOpenQuickModal(<?= (int) $c['id'] ?>)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              </div>
              <div class="card-info-title"><?= h($c['title']) ?></div>
              <div class="card-info-meta">
                <span class="match">98% Match</span>
                <span class="badge-quality">HD</span>
                <span><?= $c['lesson_count'] ?> lessons</span>
              </div>
              <div class="card-tags-list">
                <span><?= ucfirst($c['level']) ?></span>
                <span><?= h($cat['name']) ?></span>
                <span>Web Oracle</span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="slider-btn next" data-slider-target="<?= $sliderId ?>" aria-label="Scroll right">&#10095;</button>
      </div>
    </section>
  <?php endforeach; ?>

</main>

<!-- Netflix FAQ Accordion Section -->
<section class="faq-section">
  <div class="container">
    <h2 class="faq-title">Frequently Asked Questions</h2>
    <div class="faq-list">
      <div class="faq-item">
        <button type="button" class="faq-question">
          What is Weboflix?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Weboflix is a Netflix-style masterclass streaming platform for practical tech & web development courses. You can learn how to build AI-powered e-commerce stores, courier & logistics tracking systems, non-profit charity platforms, and modern web applications with step-by-step video lessons taught by industry practitioners.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          How much does Weboflix cost?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Free starter lessons are completely open to everyone. To unlock full premium masterclasses, project source codes, and certificates, plans start at &#8358;<?= number_format(PRICE_MONTHLY) ?> per month or &#8358;<?= number_format(PRICE_YEARLY) ?> per year with instant access.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          Where can I watch?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Watch anywhere, anytime. Sign in with your Weboflix account to watch instantly on the web from your laptop, desktop, tablet, or smartphone. Your progress is saved automatically across all your devices.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          How do I cancel?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Weboflix is flexible. There are no pesky contracts and no commitments. You can easily manage or cancel your subscription anytime with two clicks directly from your account page.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          What can I build on Weboflix?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Real, revenue-generating client projects: complete AI-powered e-commerce stores, logistics package tracking portals, donation portals, payment gateway integrations, and client dashboards.
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section class="cta-netflix-banner">
  <h2>Ready to start building?</h2>
  <p>Enter your email to create your account and unlock your first lesson.</p>
  <form class="cta-form" onsubmit="event.preventDefault(); var em = this.querySelector('input').value; if(em){ var se = document.getElementById('signupEmail'); if(se){ se.value = em; } document.getElementById('signupSubmitBtn')?.click(); }">
    <input type="email" placeholder="Email address" required>
    <button type="submit" class="btn-cta-submit">
      Get Started
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </form>
</section>

<!-- Netflix "More Info" Quick Preview Modal -->
<div class="netflix-modal-backdrop" id="netflixModalBackdrop" onclick="wfCloseQuickModal()"></div>
<div class="netflix-modal-wrapper" id="netflixModalWrapper" onclick="if(event.target === this) wfCloseQuickModal()">
  <div class="netflix-modal" id="netflixModal">
    <button type="button" class="modal-close-btn" onclick="wfCloseQuickModal()" aria-label="Close modal">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-hero" id="modalHeroBg">
      <div class="modal-hero-vignette"></div>
      <div class="modal-hero-content">
        <h2 class="modal-title" id="modalTitle">Course Title</h2>
        <div class="modal-hero-actions">
          <a href="#" id="modalPlayBtn" class="btn-netflix-play" <?= $user ? '' : 'data-auth-gate' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg> Play
          </a>
          <button type="button" class="btn-card-action" id="modalListBtn" title="Add to My List">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
      </div>
    </div>
    <div class="modal-body">
      <div class="modal-columns">
        <div class="modal-col-left">
          <div class="hero-meta-row" style="margin-bottom:8px;">
            <span class="hero-match-score">98% Match</span>
            <span class="badge-quality">4K ULTRA HD</span>
            <span class="badge-level" id="modalLevel">Beginner</span>
            <span class="badge-quality" id="modalLessonCount">8 Lessons</span>
          </div>
          <p class="modal-synopsis" id="modalSynopsis"></p>
        </div>
        <div class="modal-col-right">
          <div><strong>Instructor:</strong> Web Oracle</div>
          <div><strong>Category:</strong> <span id="modalCategory">Web Development</span></div>
          <div><strong>Features:</strong> Hands-on, Real Projects, Source Code</div>
        </div>
      </div>

      <div class="modal-episodes-section">
        <div class="modal-episodes-head">
          <h3>Lessons &amp; Modules</h3>
          <span class="mono" style="color:var(--text-dim);" id="modalEpisodesSubtitle">Module 1</span>
        </div>
        <div id="modalEpisodesList">
          <!-- Populated dynamically via JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  window.__COURSES_DATA = <?= json_encode($coursesForModal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
