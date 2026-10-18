/**
 * Universal Image Fallback Handler:
 * Intercepts any broken image load errors and gracefully substitutes the branded placeholder.
 */
window.addEventListener(
  'error',
  function (e) {
    if (e.target && e.target.tagName === 'IMG' && !e.target.dataset.fallbackApplied) {
      e.target.dataset.fallbackApplied = 'true';
      var assetsUrl = window.MIDHILA_ASSETS_URL || '/MIDHILA%20TRAVELS/assets';
      e.target.src = assetsUrl + '/images/placeholder.svg';
    }
  },
  true
);

document.addEventListener('DOMContentLoaded', () => {
  // 1. Sticky Header with dynamic blur and shadow on scroll
  const header = document.querySelector('.site-header');
  if (header) {
    const handleScroll = () => {
      if (window.scrollY > 40) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // 2. Mobile Drawer Navigation
  const navToggle = document.querySelector('.mobile-nav-toggle');
  const drawer = document.querySelector('.mobile-drawer');
  const overlay = document.querySelector('.mobile-drawer__overlay');
  const drawerCloseBtn = document.querySelector('.mobile-drawer__close');

  const openDrawer = () => {
    if (!drawer) return;
    drawer.classList.add('is-open');
    if (overlay) overlay.classList.add('is-visible');
    if (navToggle) navToggle.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  };

  const closeDrawer = () => {
    if (!drawer) return;
    drawer.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-visible');
    if (navToggle) navToggle.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  if (navToggle) {
    navToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      if (drawer && drawer.classList.contains('is-open')) {
        closeDrawer();
      } else {
        openDrawer();
      }
    });
  }

  if (overlay) overlay.addEventListener('click', closeDrawer);
  if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeDrawer);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
      closeDrawer();
    }
  });

  // 3. Auto-dismiss Flash Toasts after 6 seconds
  const toasts = document.querySelectorAll('.flash-toast');
  toasts.forEach((toast) => {
    setTimeout(() => {
      toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(50px)';
      setTimeout(() => toast.remove(), 400);
    }, 6000);
  });
});
