<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();
$activeSub = $user ? get_active_subscription((int) $user['id']) : null;

$pageTitle = 'Plans & Pricing';
$__page = 'pricing';
include __DIR__ . '/includes/header.php';
?>

<div class="pricing-netflix-page">
  <div class="pricing-netflix-header">
    <div class="netflix-badge-pill" style="justify-content:center;">
      <span class="n-logo">W</span>
      <span class="series-text">PREMIUM MEMBERSHIP</span>
    </div>
    <h1>Choose the plan that's right for you</h1>
    <p>Unlock every single masterclass, complete project source files, real-world blueprints, and upcoming courses with one simple subscription.</p>
  </div>

  <?php if ($activeSub): ?>
    <div class="alert alert-success" style="max-width:540px;margin:0 auto 40px;text-align:center;">
      <strong>Active Subscription:</strong> You are currently on the <?= h(ucfirst($activeSub['plan'])) ?> plan, valid until <?= date('M j, Y', strtotime($activeSub['expires_at'])) ?>.
    </div>
  <?php endif; ?>

  <div class="pricing-netflix-grid">
    <!-- Free Starter Plan -->
    <div class="pricing-netflix-card">
      <h3>Starter (Free)</h3>
      <p style="color:var(--text-dim);font-size:14px;margin-bottom:20px;">Get a taste of practical web development and AI skills.</p>
      <div class="pricing-amount">&#8358;0<span>/forever</span></div>

      <ul class="pricing-features">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Access all Free Starter modules
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Watch on any laptop, phone, or tablet
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Automated progress saving
        </li>
        <li style="opacity:0.4;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Full masterclass project builds
        </li>
        <li style="opacity:0.4;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Downloadable source code packages
        </li>
      </ul>

      <a class="btn btn-ghost btn-block" href="<?= base_url('register.php') ?>">Create Free Account</a>
    </div>

    <!-- Monthly Plan -->
    <div class="pricing-netflix-card">
      <h3>Monthly Premium</h3>
      <p style="color:var(--text-dim);font-size:14px;margin-bottom:20px;">Full unlimited platform access billed every 30 days.</p>
      <div class="pricing-amount">&#8358;<?= number_format(PRICE_MONTHLY) ?><span>/month</span></div>

      <ul class="pricing-features">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <strong>Unlock EVERY course on Weboflix</strong>
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Full AI e-commerce, Courier &amp; Charity masterclasses
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Ultra HD 4K streaming quality
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          New courses automatically unlocked
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Cancel anytime with no penalties
        </li>
      </ul>

      <a class="btn btn-primary btn-block" href="<?= base_url('subscribe.php?plan=monthly') ?>" style="padding:14px;">
        Choose Monthly
      </a>
    </div>

    <!-- Yearly Plan (Best Value) -->
    <div class="pricing-netflix-card featured">
      <span class="pricing-popular-badge">BEST VALUE &bull; SAVE 25%</span>
      <h3>Yearly VIP Access</h3>
      <p style="color:var(--text-dim);font-size:14px;margin-bottom:20px;">12 full months of uninterrupted learning at our lowest rate.</p>
      <div class="pricing-amount">&#8358;<?= number_format(PRICE_YEARLY) ?><span>/year</span></div>

      <ul class="pricing-features">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <strong>Everything in Monthly included</strong>
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Equivalent to just &#8358;<?= number_format((int) round(PRICE_YEARLY / 12)) ?>/month
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Instant priority access to newly published masterclasses
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Downloadable project codebases &amp; assets
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Priority community &amp; developer support
        </li>
      </ul>

      <a class="btn btn-primary btn-block" href="<?= base_url('subscribe.php?plan=yearly') ?>" style="padding:14px;font-size:16px;">
        Choose Yearly &bull; Instant Access
      </a>
    </div>
  </div>

  <!-- Frequently Asked Questions -->
  <section class="faq-section" style="padding-top:40px;">
    <h2 class="faq-title" style="font-size:32px;">Subscription Questions? We've Got Answers</h2>
    <div class="faq-list">
      <div class="faq-item">
        <button type="button" class="faq-question">
          How does the Flutterwave payment work?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Payments are processed instantly and securely through Flutterwave. You can pay using your debit card (Mastercard, Visa, Verve), bank transfer, or USSD. Once verified, your account is immediately unlocked.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          Can I switch between monthly and yearly?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Yes! You can upgrade from monthly to yearly at any time to take advantage of the 25% savings.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question">
          What happens when my subscription expires?
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="faq-answer">
          Your saved lesson progress and learning history will remain intact forever. You will still have full access to all free modules, and you can reactivate your premium plan whenever you are ready.
        </div>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
