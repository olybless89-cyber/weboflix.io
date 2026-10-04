<footer class="footer">
  <div class="container footer-inner">
    <span>&copy; <?= date('Y') ?> Weboflix. All rights reserved.</span>
    <span>Powered by <a href="https://digitalweboracleict.com" target="_blank" rel="noopener">DWO</a></span>
  </div>
</footer>

<div class="auth-modal-backdrop" id="authModalBackdrop"></div>
<div class="auth-modal" id="authModal" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
  <button class="auth-modal-close" id="authModalClose" aria-label="Close">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
  <div class="auth-modal-handle"></div>

  <div class="auth-modal-tabs">
    <button type="button" class="auth-tab active" data-tab="signup" id="authTabSignup">Sign up</button>
    <button type="button" class="auth-tab" data-tab="login" id="authTabLogin">Log in</button>
  </div>

  <h2 class="auth-modal-title" id="authModalTitle">Create your account to watch</h2>

  <form id="authSignupForm" class="auth-modal-form" data-step="email" novalidate>
    <input type="hidden" name="redirect" id="authRedirectSignup" value="">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" id="signupEmail" required placeholder="you@example.com" autocomplete="email">
    </div>
    <div class="field auth-step-more" hidden>
      <label>Full name</label>
      <input type="text" name="name" id="signupName" placeholder="Your name" autocomplete="name">
    </div>
    <div class="field auth-step-more" hidden>
      <label>Password</label>
      <input type="password" name="password" id="signupPassword" minlength="6" placeholder="At least 6 characters" autocomplete="new-password">
    </div>
    <div class="auth-modal-error" id="signupError"></div>
    <button type="submit" class="btn btn-primary btn-block" id="signupSubmitBtn">Continue</button>
  </form>

  <form id="authLoginForm" class="auth-modal-form" hidden novalidate>
    <input type="hidden" name="redirect" id="authRedirectLogin" value="">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="field"><label>Email</label><input type="email" name="email" required autocomplete="email"></div>
    <div class="field"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div>
    <div class="auth-modal-error" id="loginError"></div>
    <button type="submit" class="btn btn-primary btn-block">Log in</button>
  </form>
</div>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
