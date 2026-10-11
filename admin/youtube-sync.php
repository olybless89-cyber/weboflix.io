<?php
$pageTitle = 'YouTube Auto-Sync';
$__adminPage = 'youtube-sync';
require_once __DIR__ . '/includes/header.php';

$message = null;
$messageType = 'success';
$syncedVideos = [];

// Helper to fetch YouTube content with cURL
function fetch_yt_url(string $url): ?string {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode === 200 && $response) ? $response : null;
}

// Helper to parse videos from YouTube HTML
function parse_yt_videos(string $html): array {
    $videos = [];
    $marker = 'var ytInitialData = ';
    $idx = strpos($html, $marker);
    if ($idx !== false) {
        $start = $idx + strlen($marker);
        $end = strpos($html, ';</script>', $start);
        $jsonStr = substr($html, $start, $end !== false ? ($end - $start) : null);
        $data = json_decode($jsonStr, true);
        
        if (isset($data['contents']['twoColumnBrowseResultsRenderer']['tabs'])) {
            $tabs = $data['contents']['twoColumnBrowseResultsRenderer']['tabs'];
            foreach ($tabs as $tab) {
                $tabRenderer = $tab['tabRenderer'] ?? null;
                if (!$tabRenderer) continue;
                $richGrid = $tabRenderer['content']['richGridRenderer'] ?? null;
                if ($richGrid && isset($richGrid['contents'])) {
                    foreach ($richGrid['contents'] as $item) {
                        $vm = $item['richItemRenderer']['content']['lockupViewModel'] ?? null;
                        if (!$vm) continue;
                        $vidId = $vm['contentId'] ?? null;
                        if (!$vidId) continue;
                        $label = $vm['rendererContext']['accessibilityContext']['label'] ?? '';
                        $title = $vm['metadata']['lockupMetadataViewModel']['title']['content'] ?? '';
                        
                        $durationSeconds = 300;
                        if ($label) {
                            if (!$title) {
                                $durMatch = preg_match('/^(.*?)(?:(\d+\s+hours?,\s+)?(\d+\s+minutes?)?(,?\s+)?(\d+\s+seconds?)?)$/i', $label, $m);
                                $title = !empty($m[1]) ? trim($m[1]) : $label;
                            }
                            $hours = preg_match('/(\d+)\s+hour/i', $label, $hm) ? (int)$hm[1] : 0;
                            $mins = preg_match('/(\d+)\s+minute/i', $label, $mm) ? (int)$mm[1] : 0;
                            $secs = preg_match('/(\d+)\s+second/i', $label, $sm) ? (int)$sm[1] : 0;
                            if ($hours > 0 || $mins > 0 || $secs > 0) {
                                $durationSeconds = $hours * 3600 + $mins * 60 + $secs;
                            }
                        }
                        if (!$title) $title = $label ?: "Lesson {$vidId}";
                        
                        $videos[] = [
                            'youtube_id' => $vidId,
                            'title' => $title,
                            'duration_seconds' => $durationSeconds,
                            'thumbnail_url' => "https://i.ytimg.com/vi/{$vidId}/maxresdefault.jpg"
                        ];
                    }
                }
            }
        }
    }
    
    // Fallback: match direct watch links in HTML if ytInitialData was different
    if (empty($videos)) {
        if (preg_match_all('~/watch\?v=([a-zA-Z0-9_-]{11})~', $html, $matches)) {
            $uniqueIds = array_unique($matches[1]);
            foreach (array_slice($uniqueIds, 0, 30) as $vidId) {
                $videos[] = [
                    'youtube_id' => $vidId,
                    'title' => "Masterclass Lesson: {$vidId}",
                    'duration_seconds' => 600,
                    'thumbnail_url' => "https://i.ytimg.com/vi/{$vidId}/maxresdefault.jpg"
                ];
            }
        }
    }
    
    return $videos;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'sync_weboracle_preset') {
        // Run migration for all 30 real videos from Web Oracle
        try {
            require_once __DIR__ . '/../database/migrate_weboracle_channel.php';
            exit;
        } catch (Exception $e) {
            $message = 'Error syncing videos: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'sync_url') {
        $inputUrl = trim($_POST['youtube_url'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $courseMode = $_POST['course_mode'] ?? 'new'; // 'new' or course_id
        $accessLevel = $_POST['access_level'] ?? 'preview'; // 'free', 'preview', 'premium'
        
        // Single video URL or Channel URL
        if (preg_match('~(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{11})~', $inputUrl, $singleMatch)) {
            $singleId = $singleMatch[1];
            // Fetch oEmbed title
            $oembedJson = fetch_yt_url("https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$singleId}&format=json");
            $title = "Lesson {$singleId}";
            if ($oembedJson) {
                $oembed = json_decode($oembedJson, true);
                if (!empty($oembed['title'])) $title = $oembed['title'];
            }
            $syncedVideos = [[
                'youtube_id' => $singleId,
                'title' => $title,
                'duration_seconds' => 600,
                'thumbnail_url' => "https://i.ytimg.com/vi/{$singleId}/maxresdefault.jpg"
            ]];
        } else {
            // Channel URL: normalize
            $channelUrl = $inputUrl;
            if (!str_contains($channelUrl, '/videos')) {
                $channelUrl = rtrim($channelUrl, '/') . '/videos';
            }
            if (!str_starts_with($channelUrl, 'http')) {
                $channelUrl = 'https://www.youtube.com/@' . ltrim($channelUrl, '@') . '/videos';
            }
            
            $html = fetch_yt_url($channelUrl);
            if ($html) {
                $syncedVideos = parse_yt_videos($html);
            }
        }
        
        if (empty($syncedVideos)) {
            $message = 'Could not find videos at the provided URL. Please verify the channel handle or video URL.';
            $messageType = 'error';
        } else {
            // Save to database
            try {
                $db = db();
                $targetCourseId = 0;
                
                if ($courseMode === 'new') {
                    $firstVid = $syncedVideos[0];
                    $courseTitle = trim($_POST['custom_course_title'] ?? '') ?: ('Masterclass: ' . $firstVid['title']);
                    $courseSlug = slugify($courseTitle) . '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                    
                    $cStmt = $db->prepare(
                        "INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
                         VALUES (?, ?, ?, ?, ?, ?, 'beginner', 1, 0, 10)"
                    );
                    $cStmt->execute([
                        $categoryId ?: 1,
                        $courseTitle,
                        $courseSlug,
                        "Hands-on masterclass series pulled from YouTube by Web Oracle. Explore practical industry lessons and real projects.",
                        $firstVid['thumbnail_url'],
                        $firstVid['thumbnail_url']
                    ]);
                    $targetCourseId = (int) $db->lastInsertId();
                    
                    // Create Module 1
                    $mStmt = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, 'Module 1: Core Lessons', 'Core hands-on video modules.', ?, 1)");
                    $mStmt->execute([$targetCourseId, $accessLevel === 'free' ? 'free' : 'free']);
                    $firstModId = (int) $db->lastInsertId();
                    
                    // Create Module 2 for premium if preview mode
                    $secondModId = $firstModId;
                    if ($accessLevel === 'preview' && count($syncedVideos) > 2) {
                        $m2Stmt = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, 'Module 2: Advanced Walkthroughs', 'Advanced modules.', 'premium', 2)");
                        $m2Stmt->execute([$targetCourseId]);
                        $secondModId = (int) $db->lastInsertId();
                    } elseif ($accessLevel === 'premium') {
                        $db->prepare("UPDATE modules SET access_level = 'premium' WHERE id = ?")->execute([$firstModId]);
                    }
                } else {
                    $targetCourseId = (int) $courseMode;
                    // Find or create a module in this course
                    $modStmt = $db->prepare("SELECT id FROM modules WHERE course_id = ? ORDER BY sort_order DESC LIMIT 1");
                    $modStmt->execute([$targetCourseId]);
                    $modId = $modStmt->fetchColumn();
                    if (!$modId) {
                        $db->prepare("INSERT INTO modules (course_id, title, access_level, sort_order) VALUES (?, 'Module 1: Lessons', 'free', 1)")->execute([$targetCourseId]);
                        $firstModId = (int) $db->lastInsertId();
                    } else {
                        $firstModId = (int) $modId;
                    }
                    $secondModId = $firstModId;
                }
                
                // Insert lessons
                $lesStmt = $db->prepare("INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $order = 1;
                foreach ($syncedVideos as $idx => $v) {
                    $targetMod = ($accessLevel === 'preview' && $idx >= 2) ? $secondModId : $firstModId;
                    $lesStmt->execute([
                        $targetMod,
                        $v['title'],
                        $v['youtube_id'],
                        $v['duration_seconds'],
                        "Lesson covering practical implementation techniques and walkthroughs with Web Oracle.",
                        $order++
                    ]);
                }
                
                $message = "Successfully pulled and imported " . count($syncedVideos) . " video lessons into Course #{$targetCourseId}!";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$categories = db()->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
$courses = db()->query("SELECT id, title FROM courses ORDER BY id DESC")->fetchAll();
$stats = [
    'courses' => (int) db()->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'modules' => (int) db()->query("SELECT COUNT(*) FROM modules")->fetchColumn(),
    'lessons' => (int) db()->query("SELECT COUNT(*) FROM lessons")->fetchColumn(),
];
?>

<div class="admin-topbar">
  <div>
    <h1>⚡ YouTube Auto-Sync Engine</h1>
    <div style="font-size:13px; color:var(--text-dim); margin-top:4px;">
      Automatically pull videos from your YouTube channel (<b>@weboracle1095</b>) into courses and lessons.
    </div>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/courses.php') ?>">&larr; Back to Courses</a>
</div>

<?php if ($message): ?>
  <div style="margin-bottom:20px; padding:12px 16px; border-radius:6px; font-size:14px; <?= $messageType === 'success' ? 'background:rgba(0,200,83,0.15); color:#00E676; border:1px solid #00E676;' : 'background:rgba(255,82,82,0.15); color:#ff5252; border:1px solid #ff5252;' ?>">
    <?= h($message) ?>
  </div>
<?php endif; ?>

<!-- Quick Stats -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:28px;">
  <div style="background:var(--card-bg); border:1px solid var(--border); padding:16px; border-radius:8px;">
    <div style="font-size:11px; text-transform:uppercase; color:var(--text-dim); font-weight:700;">Active Courses</div>
    <div style="font-size:28px; font-weight:800; color:#fff; font-family:var(--font-display);"><?= $stats['courses'] ?></div>
  </div>
  <div style="background:var(--card-bg); border:1px solid var(--border); padding:16px; border-radius:8px;">
    <div style="font-size:11px; text-transform:uppercase; color:var(--text-dim); font-weight:700;">Total Modules</div>
    <div style="font-size:28px; font-weight:800; color:#fff; font-family:var(--font-display);"><?= $stats['modules'] ?></div>
  </div>
  <div style="background:var(--card-bg); border:1px solid var(--border); padding:16px; border-radius:8px;">
    <div style="font-size:11px; text-transform:uppercase; color:var(--text-dim); font-weight:700;">Live Lessons</div>
    <div style="font-size:28px; font-weight:800; color:var(--accent); font-family:var(--font-display);"><?= $stats['lessons'] ?></div>
  </div>
</div>

<!-- 1-Click Sync All Web Oracle Channel Videos -->
<div style="background:rgba(20,20,20,0.85); border:1px solid rgba(80, 115, 246, 0.35); border-radius:10px; padding:24px; margin-bottom:32px; box-shadow:0 4px 20px rgba(0,0,0,0.5);">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
      <h2 style="font-size:18px; font-weight:800; color:#fff; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#00E676;"></span>
        Instant 1-Click Sync: All 30 Videos from @weboracle1095
      </h2>
      <p style="font-size:13px; color:var(--text-dim); margin:0;">
        Pulls all 30 authentic videos from your channel and automatically organizes them into 4 dedicated AI, Security & Tech Masterclasses with free preview lessons and premium modules.
      </p>
    </div>
    <form method="post" action="<?= base_url('database/migrate_weboracle_channel.php') ?>" target="_blank">
      <button type="submit" class="btn btn-primary" style="padding:10px 22px; font-size:14px; font-weight:700; background:linear-gradient(135deg, var(--accent) 0%, #00E676 100%); color:#000;">
        ⚡ Sync All 30 Videos Now
      </button>
    </form>
  </div>
</div>

<!-- Custom URL / Playlist / Video Importer -->
<div style="background:var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:24px; max-width:720px;">
  <h2 style="font-size:17px; font-weight:700; color:#fff; margin-bottom:14px;">Import Any YouTube Channel, Playlist, or Video</h2>
  
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="sync_url">
    
    <div class="field">
      <label>YouTube Channel, Playlist, or Video Link</label>
      <input type="text" name="youtube_url" value="https://www.youtube.com/@weboracle1095" required placeholder="https://www.youtube.com/@weboracle1095 or https://youtu.be/xxxx">
      <div class="field-hint" style="margin-top:6px; font-size:12px; color:var(--text-dim);">
        Supports channel handles (<code>@weboracle1095</code>), playlists, and direct video links.
      </div>
    </div>
    
    <div class="field" style="margin-top:16px;">
      <label>Import Destination</label>
      <select name="course_mode" id="courseModeSelect" onchange="toggleCourseTitleInput(this.value)">
        <option value="new">+ Create a New Masterclass Course</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['id'] ?>">Add lessons to existing: <?= h($c['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field" id="customTitleField" style="margin-top:16px;">
      <label>New Course Title (Optional)</label>
      <input type="text" name="custom_course_title" placeholder="Leave blank to use video/playlist title">
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
      <div class="field">
        <label>Category</label>
        <select name="category_id">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Access Tier Strategy</label>
        <select name="access_level">
          <option value="preview" selected>Free Preview (First lessons free, rest Premium)</option>
          <option value="premium">100% Premium (Subscribers only)</option>
          <option value="free">100% Free (Open to all visitors)</option>
        </select>
      </div>
    </div>

    <div style="margin-top:24px;">
      <button type="submit" class="btn btn-primary" style="padding:10px 24px;">Pull & Import Videos</button>
    </div>
  </form>
</div>

<script>
function toggleCourseTitleInput(val) {
  var field = document.getElementById('customTitleField');
  if (val === 'new') {
    field.style.display = 'block';
  } else {
    field.style.display = 'none';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
