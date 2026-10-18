/**
 * MIDHILA VOYAGES — 3D Tilt Motion & IntersectionObserver Scroll Reveals
 */

document.addEventListener('DOMContentLoaded', () => {
  // Respect user preference for reduced motion
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  // 1. Intersection Observer for Scroll Reveals
  const revealElements = document.querySelectorAll('.reveal-fade-up, .reveal-fade-left, .reveal-fade-right, .reveal-zoom-in, .mask-reveal');
  if ('IntersectionObserver' in window && revealElements.length > 0) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          // Once revealed, unobserve to avoid repeat layout recalculations
          observer.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      rootMargin: '0px 0px -60px 0px',
      threshold: 0.12
    });

    revealElements.forEach((el) => revealObserver.observe(el));
  } else {
    // Fallback: reveal immediately if IntersectionObserver unsupported
    revealElements.forEach((el) => el.classList.add('is-revealed'));
  }

  // 2. 3D Tilt on Desktop Cards (.card-3d)
  const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
  if (!isTouchDevice) {
    const cards = document.querySelectorAll('.card-3d');
    cards.forEach((card) => {
      let isHovered = false;

      card.addEventListener('mouseenter', () => {
        isHovered = true;
        card.style.transition = 'transform 0.1s ease-out, box-shadow 0.25s ease';
      });

      card.addEventListener('mousemove', (e) => {
        if (!isHovered) return;
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        // Gentle tilt limit between -8deg and 8deg
        const rotateX = ((centerY - y) / centerY) * 7;
        const rotateY = ((x - centerX) / centerX) * 7;

        card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-6px)`;
      });

      card.addEventListener('mouseleave', () => {
        isHovered = false;
        card.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease';
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
      });
    });
  }

  // 3. Subtle Parallax for Hero Elements
  const parallaxNodes = document.querySelectorAll('[data-parallax-speed]');
  if (parallaxNodes.length > 0 && !isTouchDevice) {
    window.addEventListener('scroll', () => {
      const scrollY = window.scrollY;
      if (scrollY > window.innerHeight * 1.5) return; // Stop calculating when far down

      parallaxNodes.forEach((node) => {
        const speed = parseFloat(node.getAttribute('data-parallax-speed')) || 0.15;
        const offset = scrollY * speed;
        node.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
      });
    }, { passive: true });
  }
});
