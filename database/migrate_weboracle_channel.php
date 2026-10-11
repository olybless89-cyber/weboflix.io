<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = db();
    
    // Ensure categories exist
    $cats = [
        ['Artificial Intelligence', 'artificial-intelligence', 1],
        ['Web Development', 'web-development', 2],
        ['WordPress & CMS', 'wordpress-cms', 3],
        ['Cybersecurity', 'cybersecurity', 4],
        ['Cloud & DevOps', 'cloud-devops', 5]
    ];
    $catStmt = $db->prepare("INSERT IGNORE INTO categories (name, slug, sort_order) VALUES (?, ?, ?)");
    foreach ($cats as $cat) {
        $catStmt->execute($cat);
    }
    
    // Helper to get category ID
    $getCat = function($slug) use ($db) {
        $st = $db->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
        $st->execute([$slug]);
        return (int) $st->fetchColumn();
    };
    
    $aiCat = $getCat('artificial-intelligence');
    $cyberCat = $getCat('cybersecurity');
    $cloudCat = $getCat('cloud-devops');
    
    // ------------------------------------------------------------------------
    // Course 3: AI Agents & Workflow Engineering Mastery
    // ------------------------------------------------------------------------
    $c3Stmt = $db->prepare(
        "INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 3)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), thumbnail_url = VALUES(thumbnail_url), banner_url = VALUES(banner_url)"
    );
    $c3Stmt->execute([
        $aiCat,
        'AI Agents & Workflow Engineering Mastery',
        'ai-agents-workflow-engineering',
        'Hands-on masterclass on building autonomous AI agents and automated workflows without writing code. Master no-code agent platforms, departmental AI deployment, deep research agents, and enterprise AI orchestration with Web Oracle.',
        'https://i.ytimg.com/vi/0j1hjGf98c0/maxresdefault.jpg',
        'https://i.ytimg.com/vi/0j1hjGf98c0/maxresdefault.jpg',
        'intermediate'
    ]);
    $c3Id = (int) $db->query("SELECT id FROM courses WHERE slug = 'ai-agents-workflow-engineering' LIMIT 1")->fetchColumn();
    
    // Re-create modules for Course 3
    $db->prepare("DELETE FROM modules WHERE course_id = ?")->execute([$c3Id]);
    
    $m3_1 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m3_1->execute([$c3Id, 'No-Code AI Agents & Autonomous Systems', 'Architecting agents and setting up autonomous decision engines.', 'free', 1]);
    $mod3_1_id = (int) $db->lastInsertId();
    
    $m3_2 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m3_2->execute([$c3Id, 'Research & Content Generation Agents', 'Automating deep web investigations and creative media workflows.', 'free', 2]);
    $mod3_2_id = (int) $db->lastInsertId();
    
    $m3_3 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m3_3->execute([$c3Id, 'Enterprise Frontier Deployments', 'Real-world physical integrations, frontier labs, and governance.', 'premium', 3]);
    $mod3_3_id = (int) $db->lastInsertId();
    
    $lesStmt = $db->prepare("INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    $lesStmt->execute([$mod3_1_id, 'I Built AI Agents With 6 No-Code Tools (Most People Pick the Wrong One)', '0j1hjGf98c0', 607, 'Comprehensive comparison of the top 6 no-code AI agent builders.', 1]);
    $lesStmt->execute([$mod3_1_id, 'This Company Just Gave AI Its Own Department (17 Employees, Zero Humans)', 'UNMSFwA04aQ', 602, 'How autonomous AI agents are running entire company departments without humans.', 2]);
    $lesStmt->execute([$mod3_2_id, '6 AI Research Tools That Find What Google Cant', '7I9W0cqYPQY', 440, 'Specialized deep-research engines for rapid intelligence gathering.', 1]);
    $lesStmt->execute([$mod3_2_id, '6 AI Tools Every Content Creator Needs (But Nobody Talks About)', 'bnvp5qANQsE', 353, 'Secret AI automation toolchain for creators and digital operators.', 2]);
    $lesStmt->execute([$mod3_3_id, 'Anthropic Secretly Built a Biology Lab: Claude in the Real World', 'oFzs2b1VzQE', 793, 'Frontier models bridging software into biotechnology and physical robotics.', 1]);
    $lesStmt->execute([$mod3_3_id, 'The White House Ordered Anthropic to Kill Its Own AI', 'j0DO1Rc0el4', 624, 'The 19-day national security standoff and model containment protocols.', 2]);

    // ------------------------------------------------------------------------
    // Course 4: The Complete 2026 AI Roadmap: From Zero to Pro
    // ------------------------------------------------------------------------
    $c4Stmt = $db->prepare(
        "INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 4)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), thumbnail_url = VALUES(thumbnail_url), banner_url = VALUES(banner_url)"
    );
    $c4Stmt->execute([
        $aiCat,
        'The Complete 2026 AI Roadmap: From Zero to Pro',
        'ai-roadmap-zero-to-pro',
        'The ultimate curriculum to master artificial intelligence from scratch in 2026. Discover prompt mastery, AI-driven personal finance, career future-proofing strategies, and insights into next-generation frontier intelligence.',
        'https://i.ytimg.com/vi/-KDhfQ58bPk/maxresdefault.jpg',
        'https://i.ytimg.com/vi/-KDhfQ58bPk/maxresdefault.jpg',
        'beginner'
    ]);
    $c4Id = (int) $db->query("SELECT id FROM courses WHERE slug = 'ai-roadmap-zero-to-pro' LIMIT 1")->fetchColumn();
    $db->prepare("DELETE FROM modules WHERE course_id = ?")->execute([$c4Id]);
    
    $m4_1 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m4_1->execute([$c4Id, 'AI Foundations & Personal Mastery', 'Core mental models, 2026 learning blueprint, and financial AI.', 'free', 1]);
    $mod4_1_id = (int) $db->lastInsertId();
    
    $m4_2 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m4_2->execute([$c4Id, 'Career Defense & Evaluation Limits', 'Protecting your career against automation and understanding where LLMs fail.', 'free', 2]);
    $mod4_2_id = (int) $db->lastInsertId();
    
    $m4_3 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m4_3->execute([$c4Id, 'Frontier Model Economics', 'Premium tier economics ($500/mo ChatGPT) and the race for Gemini 4.', 'premium', 3]);
    $mod4_3_id = (int) $db->lastInsertId();
    
    $lesStmt->execute([$mod4_1_id, 'How to Learn AI From Zero (A Complete 2026 Roadmap)', '-KDhfQ58bPk', 555, 'Step-by-step learning progression from basic prompts to advanced workflows.', 1]);
    $lesStmt->execute([$mod4_1_id, '5 AI Tools That Manage Your Money Better Than You Can', '_eg1YKmEZfc', 349, 'Automating financial tracking, portfolio balancing, and wealth operations.', 2]);
    $lesStmt->execute([$mod4_2_id, 'Your Job Isnt Safe. Heres What to Do About AI', 'ctr29flOv_0', 505, 'Practical actions tech workers and creatives must take today.', 1]);
    $lesStmt->execute([$mod4_2_id, 'AI Chatbots Fail Most Money Questions (The Test That Should Scare You)', 'UkGDjwKT5wU', 654, 'Critical benchmark testing LLM reasoning and algorithmic hallucinations.', 2]);
    $lesStmt->execute([$mod4_3_id, 'OpenAI Is About to Charge $500 a Month for ChatGPT (Here\'s What You Get)', 'y43lncgpI_M', 690, 'Breaking down OpenAI high-tier pricing and enterprise compute power.', 1]);
    $lesStmt->execute([$mod4_3_id, 'Google\'s New AI Boss Just Killed the AGI Dream (Gemini 4 Ships Early)', 'W1ncQTTnhPs', 520, 'Internal roadmap shifts at Google DeepMind and early Gemini 4 access.', 2]);

    // ------------------------------------------------------------------------
    // Course 5: AI Security, Privacy & Deepfake Defense
    // ------------------------------------------------------------------------
    $c5Stmt = $db->prepare(
        "INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 5)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), thumbnail_url = VALUES(thumbnail_url), banner_url = VALUES(banner_url)"
    );
    $c5Stmt->execute([
        $cyberCat,
        'AI Security, Privacy & Deepfake Defense',
        'ai-security-privacy-deepfake-defense',
        'Essential cybersecurity masterclass for the AI era. Learn forensic deepfake detection, privacy isolation against aggressive scraping, AI model breakout prevention, and state-level kill switch frameworks.',
        'https://i.ytimg.com/vi/TTl9Ue6h0q4/maxresdefault.jpg',
        'https://i.ytimg.com/vi/TTl9Ue6h0q4/maxresdefault.jpg',
        'advanced'
    ]);
    $c5Id = (int) $db->query("SELECT id FROM courses WHERE slug = 'ai-security-privacy-deepfake-defense' LIMIT 1")->fetchColumn();
    $db->prepare("DELETE FROM modules WHERE course_id = ?")->execute([$c5Id]);
    
    $m5_1 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m5_1->execute([$c5Id, 'Synthetic Media & Forensics', 'Identifying audio/video generative manipulation before damage occurs.', 'free', 1]);
    $mod5_1_id = (int) $db->lastInsertId();
    
    $m5_2 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m5_2->execute([$c5Id, 'Data Tracking & Identity Protection', 'Isolating browsing footprints and stopping 1-year data persistence.', 'premium', 2]);
    $mod5_2_id = (int) $db->lastInsertId();
    
    $m5_3 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m5_3->execute([$c5Id, 'Model Breakouts & Legislative Kill Switches', 'Analysis of model escapes, regulatory mandates, and coordination laws.', 'premium', 3]);
    $mod5_3_id = (int) $db->lastInsertId();
    
    $lesStmt->execute([$mod5_1_id, 'How to Spot a Deepfake (Before It Ruins You)', 'TTl9Ue6h0q4', 392, 'Visual forensics, audio glitches, and identity safeguards.', 1]);
    $lesStmt->execute([$mod5_1_id, 'How to Protect Your Privacy from AI Before Its Too Late', 'nUcLRHJIPlY', 445, 'Hardening browser sessions and opt-out strategies for training sets.', 2]);
    $lesStmt->execute([$mod5_2_id, 'OpenAI Is Secretly Linking Your Web Browsing to Your Account', 'gkDgk5IFpC4', 609, 'Technical breakdown of cross-site tracking IDs and long-term retention.', 1]);
    $lesStmt->execute([$mod5_2_id, 'Anthropic Admits Its AI Escaped and Hacked 3 Real Companies', 'N9UFEpkGNEE', 767, 'Autonomous breakout vulnerability case study and containment failures.', 2]);
    $lesStmt->execute([$mod5_3_id, 'California Just Ordered a Kill Switch for AI', '_HxDZcxzTnI', 711, 'Legislative compliance, emergency model shutdowns, and federal reactions.', 1]);
    $lesStmt->execute([$mod5_3_id, 'OpenAI, Google and Anthropic Are Secretly Coordinating', 'XtD4yxzmD04', 718, 'Antitrust questions and safety consortium alignments.', 2]);

    // ------------------------------------------------------------------------
    // Course 6: Inside Big Tech AI: Hardware Wars, Economics & AGI
    // ------------------------------------------------------------------------
    $c6Stmt = $db->prepare(
        "INSERT INTO courses (category_id, title, slug, description, thumbnail_url, banner_url, level, is_published, is_featured, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 6)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), thumbnail_url = VALUES(thumbnail_url), banner_url = VALUES(banner_url)"
    );
    $c6Stmt->execute([
        $cloudCat,
        'Inside Big Tech AI: Hardware Wars, Economics & AGI',
        'inside-big-tech-ai-economics',
        'Deep investigative analysis into semiconductor monopolies, trillion-dollar cloud deals, model capacity limits, and the geopolitical battle for artificial general intelligence.',
        'https://i.ytimg.com/vi/lAp6n4p_fcA/maxresdefault.jpg',
        'https://i.ytimg.com/vi/lAp6n4p_fcA/maxresdefault.jpg',
        'intermediate'
    ]);
    $c6Id = (int) $db->query("SELECT id FROM courses WHERE slug = 'inside-big-tech-ai-economics' LIMIT 1")->fetchColumn();
    $db->prepare("DELETE FROM modules WHERE course_id = ?")->execute([$c6Id]);
    
    $m6_1 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m6_1->execute([$c6Id, 'Silicon Wars & GPU Dominance', 'Nvidia pricing strategies, supply squeezes, and acquisition plays.', 'free', 1]);
    $mod6_1_id = (int) $db->lastInsertId();
    
    $m6_2 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m6_2->execute([$c6Id, 'Frontier Lab Clashes & Compute Crises', 'Microsoft vs. Anthropic, capacity exhaustion, and newspaper deals.', 'premium', 2]);
    $mod6_2_id = (int) $db->lastInsertId();
    
    $m6_3 = $db->prepare("INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $m6_3->execute([$c6Id, 'Geopolitics, Wall Street & The Slowdown Debate', 'Superpower negotiations, emergency royal summits, and corporate earnings.', 'premium', 3]);
    $mod6_3_id = (int) $db->lastInsertId();
    
    $lesStmt->execute([$mod6_1_id, 'Nvidia Just Made Your Dream GPU Disappear (And Prices Went Insane)', 'lAp6n4p_fcA', 647, 'Analyzing consumer GPU cuts to prioritize enterprise H100/B200 racks.', 1]);
    $lesStmt->execute([$mod6_1_id, 'Nvidia Just Bought Hugging Face for 13 Billion', 's-kfm74YARk', 724, 'Why controlling the open-source model hub cements semiconductor dominance.', 2]);
    
    $lesStmt->execute([$mod6_2_id, 'Microsoft Just Attacked Anthropic for How It Trains Claude', 'QL1pSks6FiI', 606, 'The intellectual property rift dividing cloud sponsors and AI research labs.', 1]);
    $lesStmt->execute([$mod6_2_id, 'OpenAI Just Stopped Selling Its Best AI (Demand Broke Everything)', 'zfJldZeeU-s', 792, 'Token exhaustion, inference constraints, and waitlist throttles.', 2]);
    $lesStmt->execute([$mod6_2_id, 'OpenAI Just Signed Its First Deals With India\'s Newspaper Giants', 'y0_GmAndrOA', 647, 'Data licensing agreements reshaping the economics of online media.', 3]);
    
    $lesStmt->execute([$mod6_3_id, 'Trump and Xi Just Met and AI Was the Elephant in the Room', 'yQl4vuvMAwI', 653, 'Geopolitical export restrictions and national defense model controls.', 1]);
    $lesStmt->execute([$mod6_3_id, 'King Charles Called an Emergency AI Summit', 'XQoXrEn4zLo', 766, 'Diplomatic summits on existential alignment and catastrophic risks.', 2]);
    $lesStmt->execute([$mod6_3_id, 'Tech CEOs Suddenly Love Blaming AI for Layoffs (Follow the Money)', '3Ok0XHudKtw', 700, 'Investigating whether AI is replacing engineers or acting as a PR cover.', 3]);
    $lesStmt->execute([$mod6_3_id, 'AI Leaders Beg for Rules — Washington Just Said No', 'WRgea7IrDaE', 802, 'The breakdown of federal legislative efforts and regulatory capture.', 4]);
    $lesStmt->execute([$mod6_3_id, 'Every AI Boss Just Asked to Slow Down (Watch What Happened to Their Stocks)', 'p1h_rDycHZI', 861, 'Incentive misalignment between market valuations and existential safety.', 5]);
    $lesStmt->execute([$mod6_3_id, 'Ex-Anthropic Researcher Quits: The AI Industry Is Gambling With Our Lives', 'Uw1ajFnd5vQ', 717, 'Whistleblower revelations regarding safety protocol cutbacks.', 6]);
    $lesStmt->execute([$mod6_3_id, 'The Entire AI Industry Just Turned Doomsday Prep Into a Product', 'WwkHLHBeRjY', 620, 'How disaster scenarios became a monetization vehicle for tech startups.', 7]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully imported all 30 real videos from @weboracle1095 into courses and modules!',
        'courses_created' => [
            'AI Agents & Workflow Engineering Mastery' => 6,
            'The Complete 2026 AI Roadmap: From Zero to Pro' => 6,
            'AI Security, Privacy & Deepfake Defense' => 6,
            'Inside Big Tech AI: Hardware Wars, Economics & AGI' => 12
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
