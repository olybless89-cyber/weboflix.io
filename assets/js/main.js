// ============================================================
// Weboflix — Netflix Interactive Experience Engine
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  // ---- 1. Dynamic Navbar on Scroll ----
  const mainNav = document.getElementById('mainNav');
  if (mainNav) {
    function handleScroll() {
      if (window.scrollY > 35) {
        mainNav.classList.add('scrolled');
      } else {
        mainNav.classList.remove('scrolled');
      }
    }
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // ---- 2. Netflix Expanding Search Bar ----
  const navSearchForm = document.getElementById('navSearchForm');
  const searchTriggerBtn = document.getElementById('searchTriggerBtn');
  const navSearchInput = document.getElementById('navSearchInput');
  const searchCloseBtn = document.getElementById('searchCloseBtn');

  if (navSearchForm && searchTriggerBtn && navSearchInput) {
    searchTriggerBtn.addEventListener('click', function (e) {
      if (!navSearchForm.classList.contains('active')) {
        e.preventDefault();
        navSearchForm.classList.add('active');
        navSearchInput.focus();
      } else if (navSearchInput.value.trim() !== '') {
        navSearchForm.submit();
      }
    });

    if (searchCloseBtn) {
      searchCloseBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        navSearchInput.value = '';
        navSearchForm.classList.remove('active');
      });
    }

    document.addEventListener('click', function (e) {
      if (!navSearchForm.contains(e.target) && navSearchInput.value.trim() === '') {
        navSearchForm.classList.remove('active');
      }
    });
  }

  // ---- 3. Notification Dropdown ----
  const notifyBtn = document.getElementById('notifyBtn');
  const notifyDropdown = document.getElementById('notifyDropdown');
  if (notifyBtn && notifyDropdown) {
    notifyBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      notifyDropdown.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
      if (!notifyDropdown.contains(e.target) && e.target !== notifyBtn) {
        notifyDropdown.classList.remove('show');
      }
    });
  }

  // ---- 4. User Profile Dropdown (Click Support) ----
  const userProfileMenu = document.getElementById('userProfileMenu');
  const profileBtn = document.getElementById('profileBtn');
  if (userProfileMenu && profileBtn) {
    profileBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      userProfileMenu.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!userProfileMenu.contains(e.target)) {
        userProfileMenu.classList.remove('open');
      }
    });
  }

  // ---- 5. Mobile Menu Toggle ----
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const mobileMenu = document.getElementById('mobileMenu');
  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener('click', function () {
      const isOpen = mobileMenu.classList.toggle('open');
      mobileMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // ---- 6. Carousel Slider Chevron Navigation ----
  document.querySelectorAll('.slider-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const targetId = btn.dataset.sliderTarget;
      const target = document.getElementById(targetId);
      if (!target) return;
      const scrollAmount = target.clientWidth * 0.75;
      const isPrev = btn.classList.contains('prev');
      target.scrollBy({
        left: isPrev ? -scrollAmount : scrollAmount,
        behavior: 'smooth'
      });
    });
  });

  // ---- 7. Hero Video Mute/Unmute Toggle ----
  const heroMuteToggle = document.getElementById('heroMuteToggle');
  const heroVideoFrame = document.getElementById('heroVideoFrame');
  if (heroMuteToggle && heroVideoFrame) {
    function postFrameMessage(func) {
      heroVideoFrame.contentWindow.postMessage(JSON.stringify({ event: 'command', func: func, args: [] }), '*');
    }

    heroMuteToggle.addEventListener('click', function () {
      const isMuted = heroMuteToggle.dataset.muted === '1';
      postFrameMessage(isMuted ? 'unMute' : 'mute');
      heroMuteToggle.dataset.muted = isMuted ? '0' : '1';
      const iconMuted = heroMuteToggle.querySelector('.icon-muted');
      const iconUnmuted = heroMuteToggle.querySelector('.icon-unmuted');
      if (iconMuted && iconUnmuted) {
        iconMuted.style.display = isMuted ? 'none' : 'block';
        iconUnmuted.style.display = isMuted ? 'block' : 'none';
      }
      heroMuteToggle.setAttribute('aria-label', isMuted ? 'Mute preview' : 'Unmute preview');
    });
  }

  // ---- 8. FAQ Accordion ----
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const parent = btn.closest('.faq-item');
      if (!parent) return;
      const wasOpen = parent.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(function (item) {
        item.classList.remove('open');
      });
      if (!wasOpen) {
        parent.classList.add('open');
      }
    });
  });

  // ---- 9. Auth Modal Setup ----
  const backdrop = document.getElementById('authModalBackdrop');
  const modal = document.getElementById('authModal');
  const closeBtn = document.getElementById('authModalClose');
  const tabSignup = document.getElementById('authTabSignup');
  const tabLogin = document.getElementById('authTabLogin');
  const signupForm = document.getElementById('authSignupForm');
  const loginForm = document.getElementById('authLoginForm');
  const title = document.getElementById('authModalTitle');

  if (modal && backdrop) {
    let pendingRedirect = '';

    window.wfOpenAuthModal = function (redirectTo, tab) {
      pendingRedirect = redirectTo || '';
      const rSignup = document.getElementById('authRedirectSignup');
      const rLogin = document.getElementById('authRedirectLogin');
      if (rSignup) rSignup.value = pendingRedirect;
      if (rLogin) rLogin.value = pendingRedirect;
      switchAuthTab(tab || 'signup');
      backdrop.classList.add('open');
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
      setTimeout(function () {
        const se = document.getElementById('signupEmail');
        if (se) se.focus();
      }, 200);
    };

    function closeAuthModal() {
      backdrop.classList.remove('open');
      modal.classList.remove('open');
      document.body.style.overflow = '';
      resetSignupStep();
    }

    function switchAuthTab(tab) {
      const isSignup = tab === 'signup';
      if (tabSignup) tabSignup.classList.toggle('active', isSignup);
      if (tabLogin) tabLogin.classList.toggle('active', !isSignup);
      if (signupForm) signupForm.hidden = !isSignup;
      if (loginForm) loginForm.hidden = isSignup;
      if (title) title.textContent = isSignup ? 'Create your account to watch' : 'Log in to keep watching';
    }

    function resetSignupStep() {
      if (!signupForm) return;
      signupForm.dataset.step = 'email';
      signupForm.querySelectorAll('.auth-step-more').forEach(function (el) { el.hidden = true; });
      const submitBtn = document.getElementById('signupSubmitBtn');
      if (submitBtn) submitBtn.textContent = 'Continue';
      const sErr = document.getElementById('signupError');
      const lErr = document.getElementById('loginError');
      if (sErr) sErr.textContent = '';
      if (lErr) lErr.textContent = '';
      signupForm.reset();
      if (loginForm) loginForm.reset();
    }

    document.addEventListener('click', function (e) {
      const link = e.target.closest('[data-auth-gate]');
      if (!link) return;
      e.preventDefault();
      wfOpenAuthModal(link.getAttribute('href'), 'signup');
    });

    if (closeBtn) closeBtn.addEventListener('click', closeAuthModal);
    backdrop.addEventListener('click', closeAuthModal);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (modal.classList.contains('open')) closeAuthModal();
        if (typeof wfCloseQuickModal === 'function') wfCloseQuickModal();
      }
    });

    if (tabSignup) tabSignup.addEventListener('click', function () { switchAuthTab('signup'); });
    if (tabLogin) tabLogin.addEventListener('click', function () { switchAuthTab('login'); });

    if (signupForm) {
      signupForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const errorEl = document.getElementById('signupError');
        if (errorEl) errorEl.textContent = '';

        if (signupForm.dataset.step === 'email') {
          const emailInput = document.getElementById('signupEmail');
          const email = emailInput ? emailInput.value.trim() : '';
          if (!email) {
            if (errorEl) errorEl.textContent = 'Enter your email to continue.';
            return;
          }
          signupForm.dataset.step = 'details';
          signupForm.querySelectorAll('.auth-step-more').forEach(function (el) { el.hidden = false; });
          const submitBtn = document.getElementById('signupSubmitBtn');
          if (submitBtn) submitBtn.textContent = 'Create account';
          const nameInput = document.getElementById('signupName');
          if (nameInput) nameInput.focus();
          return;
        }

        const btn = document.getElementById('signupSubmitBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Creating account…'; }
        fetch('ajax_register.php', { method: 'POST', body: new FormData(signupForm) })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.ok) {
              window.location.href = pendingRedirect || 'dashboard.php';
            } else {
              if (errorEl) errorEl.textContent = data.error || 'Something went wrong. Please try again.';
              if (btn) { btn.disabled = false; btn.textContent = 'Create account'; }
            }
          })
          .catch(function () {
            if (errorEl) errorEl.textContent = 'Network error — please try again.';
            if (btn) { btn.disabled = false; btn.textContent = 'Create account'; }
          });
      });
    }

    if (loginForm) {
      loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const errorEl = document.getElementById('loginError');
        if (errorEl) errorEl.textContent = '';
        const btn = loginForm.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.textContent = 'Logging in…'; }
        fetch('ajax_login.php', { method: 'POST', body: new FormData(loginForm) })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.ok) {
              window.location.href = pendingRedirect || 'dashboard.php';
            } else {
              if (errorEl) errorEl.textContent = data.error || 'Something went wrong. Please try again.';
              if (btn) { btn.disabled = false; btn.textContent = 'Log in'; }
            }
          })
          .catch(function () {
            if (errorEl) errorEl.textContent = 'Network error — please try again.';
            if (btn) { btn.disabled = false; btn.textContent = 'Log in'; }
          });
      });
    }
  }

  // ---- 10. Activity Heartbeat ----
  if (document.body.dataset.track === '1') {
    setInterval(function () {
      fetch('heartbeat.php', { method: 'POST' }).catch(function () {});
    }, 120000);
  }
});

// ============================================================
// Netflix "More Info" Quick Preview Modal Functionality
// ============================================================
window.wfOpenQuickModal = function (courseId) {
  const dataMap = window.__COURSES_DATA || {};
  const course = dataMap[courseId];
  if (!course) return;

  const backdrop = document.getElementById('netflixModalBackdrop');
  const wrapper = document.getElementById('netflixModalWrapper');
  const heroBg = document.getElementById('modalHeroBg');
  const title = document.getElementById('modalTitle');
  const synopsis = document.getElementById('modalSynopsis');
  const level = document.getElementById('modalLevel');
  const cat = document.getElementById('modalCategory');
  const count = document.getElementById('modalLessonCount');
  const playBtn = document.getElementById('modalPlayBtn');
  const listBtn = document.getElementById('modalListBtn');
  const episodesList = document.getElementById('modalEpisodesList');

  if (!backdrop || !wrapper) return;

  // Set Content
  if (title) title.textContent = course.title;
  if (synopsis) synopsis.textContent = course.description;
  if (level) level.textContent = course.level;
  if (cat) cat.textContent = course.category_name;
  if (count) count.textContent = course.lesson_count + ' Lessons';
  if (heroBg) {
    heroBg.style.backgroundImage = 'url("' + (course.banner_url || course.thumbnail_url) + '")';
  }

  if (playBtn) {
    if (course.first_lesson_id) {
      playBtn.href = 'watch.php?lesson=' + course.first_lesson_id;
      playBtn.style.display = 'inline-flex';
    } else {
      playBtn.style.display = 'none';
    }
  }

  if (listBtn) {
    listBtn.onclick = function () {
      wfToggleMyList(courseId, listBtn);
    };
  }

  // Populate Episodes
  if (episodesList) {
    episodesList.innerHTML = '';
    const lessons = course.lessons || [];
    if (lessons.length === 0) {
      episodesList.innerHTML = '<div style="color:var(--text-dim);padding:16px 0;">No lessons published yet.</div>';
    } else {
      lessons.forEach(function (les, i) {
        const row = document.createElement('a');
        row.className = 'modal-episode-row';
        row.href = 'watch.php?lesson=' + les.id;
        row.innerHTML = `
          <span class="modal-episode-idx">${i + 1}</span>
          <img src="${les.thumbnail_url}" alt="" class="modal-episode-thumb">
          <div class="modal-episode-info">
            <div class="modal-episode-title">${les.title}</div>
            <div class="modal-episode-desc">${les.description}</div>
          </div>
          <span class="modal-episode-duration">${les.duration}</span>
        `;
        episodesList.appendChild(row);
      });
    }
  }

  backdrop.classList.add('open');
  wrapper.classList.add('open');
  document.body.style.overflow = 'hidden';
};

window.wfCloseQuickModal = function () {
  const backdrop = document.getElementById('netflixModalBackdrop');
  const wrapper = document.getElementById('netflixModalWrapper');
  if (backdrop) backdrop.classList.remove('open');
  if (wrapper) wrapper.classList.remove('open');
  document.body.style.overflow = '';
};

// ============================================================
// Interactive User Micro-Actions (Like & My List)
// ============================================================
window.wfToggleMyList = function (courseId, btn) {
  if (!btn) return;
  const isAdded = btn.dataset.added === '1';
  btn.dataset.added = isAdded ? '0' : '1';
  btn.style.borderColor = isAdded ? 'rgba(255,255,255,0.5)' : 'var(--netflix-green)';
  btn.style.color = isAdded ? '#fff' : 'var(--netflix-green)';
  btn.innerHTML = isAdded
    ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'
    : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
};

window.wfLikeCard = function (btn) {
  if (!btn) return;
  const liked = btn.dataset.liked === '1';
  btn.dataset.liked = liked ? '0' : '1';
  btn.style.color = liked ? '#fff' : 'var(--accent)';
  btn.style.borderColor = liked ? 'rgba(255,255,255,0.5)' : 'var(--accent)';
};

window.wfMarkComplete = function (lessonId, courseId) {
  fetch('progress.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'lesson_id=' + encodeURIComponent(lessonId) + '&course_id=' + encodeURIComponent(courseId) + '&action=complete'
  }).then(function (r) { return r.json(); })
    .then(function () {
      const badge = document.getElementById('completeBtn');
      if (badge) {
        badge.textContent = '✓ Completed';
        badge.classList.remove('btn-primary');
        badge.classList.add('btn-ghost');
        badge.disabled = true;
      }
    });
};
