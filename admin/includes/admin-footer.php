    </main>
  </div>

  <!-- Admin Interactive Confirmation Modal -->
  <div id="adminConfirmModal" class="admin-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="admin-modal-card">
      <div id="adminConfirmIcon" class="admin-modal-icon admin-modal-icon--warning">
        <svg id="adminConfirmIconDelete" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          <line x1="10" y1="11" x2="10" y2="17"></line>
          <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
        <svg id="adminConfirmIconSave" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline>
          <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
      </div>

      <h3 id="adminConfirmTitle" class="admin-modal-title">Confirm Action</h3>
      <p id="adminConfirmMessage" class="admin-modal-desc">Are you sure you want to proceed with this action?</p>

      <div class="admin-modal-actions">
        <button type="button" id="adminConfirmCancelBtn" class="admin-btn admin-btn-outline">Cancel</button>
        <button type="button" id="adminConfirmProceedBtn" class="admin-btn admin-btn-danger">Yes, Proceed</button>
      </div>
    </div>
  </div>

  <!-- Admin Action Confirmed / Success Popup Modal -->
  <div id="adminSuccessModal" class="admin-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="admin-modal-card">
      <div class="admin-modal-icon admin-modal-icon--success">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>

      <h3 id="adminSuccessTitle" class="admin-modal-title" style="color:#6EE7B7;">Action Confirmed!</h3>
      <p id="adminSuccessMessage" class="admin-modal-desc" style="color:#E2E8F0;font-weight:500;">Your changes have been successfully saved.</p>

      <div class="admin-modal-actions" style="justify-content:center;">
        <button type="button" id="adminSuccessOkBtn" class="admin-btn admin-btn-primary" style="min-width:140px;">OK, Continue</button>
      </div>

      <div class="admin-modal-timer-bar">
        <div id="adminSuccessProgress" class="admin-modal-timer-progress"></div>
      </div>
    </div>
  </div>

  <style>
    /* Admin Frosted Glass Modals */
    .admin-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(6, 11, 24, 0.78);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      z-index: 99999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.25s ease-out;
    }
    .admin-modal-overlay.is-active {
      display: flex;
      opacity: 1;
    }
    .admin-modal-card {
      background: rgba(14, 23, 46, 0.88);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      border-radius: 20px;
      max-width: 440px;
      width: 100%;
      padding: 32px 28px 26px;
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.2), 0 30px 60px -12px rgba(0, 0, 0, 0.6);
      border: 1px solid rgba(196, 181, 253, 0.3);
      text-align: center;
      position: relative;
      overflow: hidden;
      transform: scale(0.92);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      color: #FFF;
    }
    .admin-modal-overlay.is-active .admin-modal-card {
      transform: scale(1);
    }
    .admin-modal-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
    }
    .admin-modal-icon--warning {
      background: rgba(239, 68, 68, 0.2);
      color: #FCA5A5;
      border: 2px solid rgba(239, 68, 68, 0.45);
      box-shadow: 0 0 16px rgba(239, 68, 68, 0.3);
    }
    .admin-modal-icon--info {
      background: rgba(59, 130, 246, 0.2);
      color: #93C5FD;
      border: 2px solid rgba(59, 130, 246, 0.45);
      box-shadow: 0 0 16px rgba(59, 130, 246, 0.3);
    }
    .admin-modal-icon--success {
      background: rgba(16, 185, 129, 0.2);
      color: #6EE7B7;
      border: 2px solid rgba(16, 185, 129, 0.45);
      box-shadow: 0 0 20px rgba(16, 185, 129, 0.35);
    }
    .admin-modal-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #FFF;
      margin-bottom: 8px;
      text-shadow: 0 0 12px rgba(196, 181, 253, 0.2);
    }
    .admin-modal-desc {
      font-size: 0.92rem;
      color: #CBD5E1;
      line-height: 1.55;
      margin-bottom: 24px;
    }
    .admin-modal-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }
    .admin-modal-timer-bar {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: rgba(255, 255, 255, 0.1);
      overflow: hidden;
    }
    .admin-modal-timer-progress {
      height: 100%;
      width: 100%;
      background: linear-gradient(90deg, #10B981, #02C39A);
      transform-origin: left center;
      transform: scaleX(1);
    }
  </style>

  <script>
  (function() {
    // Admin Confirmation & Confirmed Popup System
    const confirmModal = document.getElementById('adminConfirmModal');
    const confirmTitle = document.getElementById('adminConfirmTitle');
    const confirmMessage = document.getElementById('adminConfirmMessage');
    const confirmProceedBtn = document.getElementById('adminConfirmProceedBtn');
    const confirmCancelBtn = document.getElementById('adminConfirmCancelBtn');
    const iconDelete = document.getElementById('adminConfirmIconDelete');
    const iconSave = document.getElementById('adminConfirmIconSave');
    const iconWrapper = document.getElementById('adminConfirmIcon');

    const successModal = document.getElementById('adminSuccessModal');
    const successTitle = document.getElementById('adminSuccessTitle');
    const successMessage = document.getElementById('adminSuccessMessage');
    const successOkBtn = document.getElementById('adminSuccessOkBtn');
    const successProgress = document.getElementById('adminSuccessProgress');

    let onConfirmCallback = null;
    let autoCloseTimer = null;
    let progressAnim = null;

    function openConfirmModal(config) {
      confirmTitle.textContent = config.title || 'Confirm Action';
      confirmMessage.textContent = config.message || 'Are you sure you want to proceed?';
      confirmProceedBtn.textContent = config.btnText || 'Confirm';

      if (config.type === 'delete') {
        iconWrapper.className = 'admin-modal-icon admin-modal-icon--warning';
        iconDelete.style.display = 'block';
        iconSave.style.display = 'none';
        confirmProceedBtn.className = 'admin-btn admin-btn-danger';
      } else {
        iconWrapper.className = 'admin-modal-icon admin-modal-icon--info';
        iconDelete.style.display = 'none';
        iconSave.style.display = 'block';
        confirmProceedBtn.className = 'admin-btn admin-btn-primary';
      }

      onConfirmCallback = config.onConfirm;
      confirmModal.style.display = 'flex';
      setTimeout(() => confirmModal.classList.add('is-active'), 10);
      confirmProceedBtn.focus();
    }

    function closeConfirmModal() {
      confirmModal.classList.remove('is-active');
      setTimeout(() => {
        confirmModal.style.display = 'none';
        onConfirmCallback = null;
      }, 200);
    }

    window.showAdminConfirmedModal = function(title, message) {
      if (typeof title === 'string' && !message) {
        message = title;
        title = 'Action Confirmed!';
      }
      successTitle.textContent = title || 'Action Confirmed!';
      successMessage.textContent = message || 'Your changes have been successfully saved.';
      
      successModal.style.display = 'flex';
      setTimeout(() => successModal.classList.add('is-active'), 10);
      successOkBtn.focus();

      // Reset progress animation
      if (progressAnim) cancelAnimationFrame(progressAnim);
      if (autoCloseTimer) clearTimeout(autoCloseTimer);

      const duration = 3500;
      const startTime = performance.now();

      function updateBar(now) {
        const elapsed = now - startTime;
        const remaining = Math.max(0, 1 - (elapsed / duration));
        if (successProgress) {
          successProgress.style.transform = 'scaleX(' + remaining + ')';
        }
        if (elapsed < duration) {
          progressAnim = requestAnimationFrame(updateBar);
        }
      }
      progressAnim = requestAnimationFrame(updateBar);

      autoCloseTimer = setTimeout(() => {
        closeSuccessModal();
      }, duration);
    };

    function closeSuccessModal() {
      if (autoCloseTimer) clearTimeout(autoCloseTimer);
      if (progressAnim) cancelAnimationFrame(progressAnim);
      successModal.classList.remove('is-active');
      setTimeout(() => {
        successModal.style.display = 'none';
      }, 200);
    }

    confirmCancelBtn.addEventListener('click', closeConfirmModal);
    confirmProceedBtn.addEventListener('click', function() {
      const cb = onConfirmCallback;
      closeConfirmModal();
      if (typeof cb === 'function') {
        cb();
      }
    });

    successOkBtn.addEventListener('click', closeSuccessModal);

    // Keyboard support (Escape closes modals)
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        if (confirmModal.classList.contains('is-active')) closeConfirmModal();
        if (successModal.classList.contains('is-active')) closeSuccessModal();
      }
    });

    // 1. Defensively strip any inline confirm attributes and convert to data-confirm-delete
    document.querySelectorAll('a[onclick*="confirm"], button[onclick*="confirm"]').forEach(function(el) {
      const oc = el.getAttribute('onclick') || '';
      const match = oc.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
      if (match && match[1]) {
        el.setAttribute('data-confirm-delete', match[1]);
      }
      el.removeAttribute('onclick');
    });

    // 2. Intercept All Delete Links and Buttons across the Entire Admin Site
    document.addEventListener('click', function(e) {
      const deleteTrigger = e.target.closest('a[data-confirm-delete], a[href*="delete_id="], .admin-btn-danger, button[name*="delete"]');
      if (!deleteTrigger) return;

      // Ignore if trigger is inside modal itself or disabled
      if (deleteTrigger.closest('.admin-modal-overlay')) return;
      if (deleteTrigger.classList.contains('no-confirm')) return;

      e.preventDefault();
      e.stopPropagation();

      const href = deleteTrigger.getAttribute('href');
      const customMsg = deleteTrigger.getAttribute('data-confirm-delete');
      const itemTitle = deleteTrigger.getAttribute('data-title') || deleteTrigger.getAttribute('aria-label') || '';
      
      let message = customMsg;
      if (!message) {
        if (itemTitle) {
          message = "Are you sure you want to delete '" + itemTitle + "'? This action cannot be undone.";
        } else {
          message = "Are you sure you want to delete this record? This action cannot be undone.";
        }
      }

      openConfirmModal({
        type: 'delete',
        title: 'Confirm Deletion',
        message: message,
        btnText: 'Yes, Delete',
        onConfirm: function() {
          if (href && href !== '#' && !href.startsWith('javascript:')) {
            window.location.href = href;
          } else if (deleteTrigger.tagName === 'BUTTON' || deleteTrigger.type === 'submit') {
            const form = deleteTrigger.closest('form');
            if (form) {
              form._isConfirmed = true;
              deleteTrigger.click();
            }
          }
        }
      });
    }, true);

    // 3. Intercept All Save / Update Form Submissions across the Entire Admin Site
    document.addEventListener('submit', function(e) {
      const form = e.target;
      if (!form || form.tagName !== 'FORM') return;
      if (form.classList.contains('no-confirm')) return;
      if (form._isConfirmed) return; // Already confirmed, let submission proceed

      // Check form method (ignore pure GET searches/filters unless explicitly marked)
      const method = (form.getAttribute('method') || 'GET').toUpperCase();
      if (method !== 'POST') return;

      // Validate HTML5 inputs before confirming
      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        form.reportValidity();
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      const submitter = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
      let actionLabel = 'these changes';
      if (submitter) {
        const btnText = submitter.textContent.trim().replace(/\s+/g, ' ');
        if (btnText && btnText.length < 40) {
          actionLabel = btnText.toLowerCase();
        }
      }

      let saveTitle = 'Confirm Save';
      let saveMessage = 'Are you sure you want to proceed and save ' + actionLabel + '?';
      if (actionLabel.includes('upload')) {
        saveTitle = 'Confirm Upload';
        saveMessage = 'Are you sure you want to upload this media file?';
      } else if (actionLabel.includes('update')) {
        saveTitle = 'Confirm Update';
        saveMessage = 'Are you sure you want to update ' + actionLabel + '?';
      }

      openConfirmModal({
        type: 'save',
        title: saveTitle,
        message: saveMessage,
        btnText: 'Yes, Save Changes',
        onConfirm: function() {
          form._isConfirmed = true;
          // Ensure submit button name/value is sent with the form submission
          if (submitter && submitter.name && !form.querySelector('input[type="hidden"][name="' + submitter.name + '"]')) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = submitter.name;
            hidden.value = submitter.value || '1';
            form.appendChild(hidden);
          }
          if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter);
          } else {
            form.submit();
          }
        }
      });
    });

    // 4. On Page Load, check for any PHP success flash messages and show Confirmed Popup Modal!
    function checkFlashMessages() {
      const successFlash = document.querySelector('.flash-toast--success .flash-toast__body');
      if (successFlash) {
        const msg = successFlash.textContent.trim();
        if (msg) {
          window.showAdminConfirmedModal('Action Confirmed!', msg);
        }
      }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', checkFlashMessages);
    } else {
      checkFlashMessages();
    }

  })();
  </script>
</body>
</html>
