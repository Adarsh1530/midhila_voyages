/**
 * MIDHILA VOYAGES — 3D Tilt, Dynamic Specular Light & Motion Engine
 * - Short voyage globe loader dismissal (< 1.1s)
 * - Smooth 3D perspective transforms with mouse-tracking specular highlights
 * - GSAP ScrollTrigger timeline reveals with IntersectionObserver fallback
 * - Parallax and smooth micro-interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // Respect user preference for reduced motion
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 0. Short Animated Globe Voyage Loader Dismissal (< 1.1s)
  const loader = document.getElementById('voyageLoader') || document.querySelector('.voyage-loader');
  if (loader) {
    if (prefersReducedMotion) {
      loader.classList.add('is-hidden');
    } else {
      setTimeout(() => {
        loader.classList.add('is-hidden');
        setTimeout(() => {
          if (loader.parentNode) loader.remove();
        }, 600);
      }, 950);
    }
  }

  if (prefersReducedMotion) return;

  // 1. Ambient Background Mesh Auto-Injector (if not explicitly in HTML)
  if (!document.querySelector('.ambient-mesh')) {
    const mesh = document.createElement('div');
    mesh.className = 'ambient-mesh';
    mesh.setAttribute('aria-hidden', 'true');
    mesh.innerHTML = `
      <div class="ambient-orb ambient-orb--1"></div>
      <div class="ambient-orb ambient-orb--2"></div>
      <div class="ambient-orb ambient-orb--3"></div>
      <div class="ambient-orb ambient-orb--4"></div>
    `;
    document.body.prepend(mesh);
  }

  // 2. GSAP ScrollTrigger Integration with Graceful Vanilla Fallback
  if (typeof window.gsap !== 'undefined' && typeof window.ScrollTrigger !== 'undefined') {
    window.gsap.registerPlugin(window.ScrollTrigger);

    // Hero timeline reveal
    const heroTl = window.gsap.timeline({ defaults: { ease: 'power3.out' } });
    if (document.querySelector('.hero-headline-anim')) {
      heroTl.fromTo('.hero-headline-anim', 
        { opacity: 0, y: 40 }, 
        { opacity: 1, y: 0, duration: 1.1, delay: 0.2 }
      );
    }

    if (document.querySelector('.hero-floating-card')) {
      heroTl.fromTo('.hero-floating-card', 
        { opacity: 0, scale: 0.85, y: 30 }, 
        { opacity: 1, scale: 1, y: 0, stagger: 0.18, duration: 1 }, 
        '-=0.7'
      );
    }

    // ScrollTrigger for route line SVG stroke
    const routeSvg = document.querySelector('.hero-route-line-svg');
    if (routeSvg) {
      window.gsap.fromTo('.hero-route-line',
        { strokeDashoffset: 800 },
        {
          strokeDashoffset: 0,
          duration: 3,
          ease: 'power2.inOut',
          scrollTrigger: {
            trigger: routeSvg,
            start: 'top 80%',
            toggleActions: 'play none none none'
          }
        }
      );
    }
  }

  // 3. Intersection Observer for Scroll Reveals
  const revealElements = document.querySelectorAll('.reveal-fade-up, .reveal-fade-left, .reveal-fade-right, .reveal-zoom-in, .mask-reveal');
  if ('IntersectionObserver' in window && revealElements.length > 0) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          observer.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      rootMargin: '0px 0px -50px 0px',
      threshold: 0.1
    });

    revealElements.forEach((el) => revealObserver.observe(el));
  } else {
    revealElements.forEach((el) => el.classList.add('is-revealed'));
  }

  // 4. Interactive 3D Tilt Engine with Real-Time Specular Glare Tracking
  const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
  if (!isTouchDevice) {
    const tiltTargets = document.querySelectorAll('.card-3d, .package-card, .dest-card, .glass-specular, .service-card, .editorial-card');
    
    tiltTargets.forEach((card) => {
      let isHovered = false;
      let animationFrameId = null;

      card.addEventListener('mouseenter', () => {
        isHovered = true;
        card.style.transition = 'transform 0.12s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease';
      });

      card.addEventListener('mousemove', (e) => {
        if (!isHovered) return;
        
        if (animationFrameId) {
          cancelAnimationFrame(animationFrameId);
        }

        animationFrameId = requestAnimationFrame(() => {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;

          // Update CSS custom properties for specular glare reflection
          card.style.setProperty('--mouse-x', `${x}px`);
          card.style.setProperty('--mouse-y', `${y}px`);

          const centerX = rect.width / 2;
          const centerY = rect.height / 2;

          // Gentle tilt: clamp to +/- 7.5 deg
          const rotateX = ((centerY - y) / centerY) * 7.5;
          const rotateY = ((x - centerX) / centerX) * 7.5;

          card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-8px)`;
        });
      });

      card.addEventListener('mouseleave', () => {
        isHovered = false;
        if (animationFrameId) {
          cancelAnimationFrame(animationFrameId);
        }
        card.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease';
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
      });
    });
  }

  // 5. Parallax Depth on Selected Visual Elements
  const parallaxNodes = document.querySelectorAll('[data-parallax-speed]');
  if (parallaxNodes.length > 0 && !isTouchDevice) {
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          const scrollY = window.scrollY;
          if (scrollY < window.innerHeight * 2.5) {
            parallaxNodes.forEach((node) => {
              const speed = parseFloat(node.getAttribute('data-parallax-speed')) || 0.12;
              const offset = scrollY * speed;
              node.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
            });
          }
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
  }

  // 6. Interactive Magnetic Buttons (.btn-magnetic)
  if (!isTouchDevice) {
    const magneticBtns = document.querySelectorAll('.btn-primary, .btn-plan, .btn-vibrant-teal, .btn-vibrant-coral, .btn-vibrant-sunset, .btn-vibrant-gold');
    magneticBtns.forEach((btn) => {
      btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
      });
      btn.addEventListener('mouseleave', () => {
        btn.style.transform = '';
      });
    });
  }
});
