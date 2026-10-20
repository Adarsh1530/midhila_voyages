/**
 * MIDHILA VOYAGES — Cinematic Scroll & 3D Interactive Engine
 * Inspired by Brand Motion's "Travelled by Scroll" (VAARĀ)
 */

(function () {
  'use strict';

  // 1. Fast, non-blocking Voyage Loader dismiss
  function initLoader() {
    const loader = document.getElementById('voyageLoader');
    if (!loader) return;
    const hideLoader = () => {
      loader.classList.add('is-hidden');
      setTimeout(() => {
        if (loader.parentNode) loader.parentNode.removeChild(loader);
      }, 550);
    };
    if (document.readyState === 'complete') {
      setTimeout(hideLoader, 350);
    } else {
      window.addEventListener('load', () => setTimeout(hideLoader, 250));
      setTimeout(hideLoader, 800); // Safety fallback
    }
  }

  // 2. Interactive Three.js 3D Travel Sphere on Pure White Canvas
  function initThreeSphere() {
    const container = document.getElementById('travelSceneCanvas');
    if (!container || typeof THREE === 'undefined') return;

    try {
      const width = container.clientWidth || 1000;
      const height = container.clientHeight || 1000;

      const scene = new THREE.Scene();
      const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 2000);
      camera.position.z = 24; // Balanced camera distance for grand free-size globe

      const renderer = new THREE.WebGLRenderer({
        canvas: container,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance'
      });
      renderer.setSize(width, height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

      // Golden Travel Globe Group (Giant Borderless Presence)
      const globeGroup = new THREE.Group();
      scene.add(globeGroup);

      // Inner Wireframe Sphere (Giant Luxury Gold Coordinate Mesh)
      const sphereGeo = new THREE.SphereGeometry(9.6, 40, 32);
      const sphereMat = new THREE.MeshBasicMaterial({
        color: 0xC5A059,
        wireframe: true,
        transparent: true,
        opacity: 0.28
      });
      const sphereMesh = new THREE.Mesh(sphereGeo, sphereMat);
      globeGroup.add(sphereMesh);

      // Orbital Route & Meridian Rings
      const ringMat = new THREE.LineBasicMaterial({
        color: 0xB88E3E,
        transparent: true,
        opacity: 0.45,
        linewidth: 1.5
      });

      const createRing = (radius, rotX, rotY) => {
        const points = [];
        for (let i = 0; i <= 80; i++) {
          const theta = (i / 80) * Math.PI * 2;
          points.push(new THREE.Vector3(Math.cos(theta) * radius, Math.sin(theta) * radius, 0));
        }
        const geo = new THREE.BufferGeometry().setFromPoints(points);
        const line = new THREE.Line(geo, ringMat);
        line.rotation.x = rotX;
        line.rotation.y = rotY;
        return line;
      };

      // Grand Orbital Latitude, Longitude & Equator Rings
      globeGroup.add(createRing(9.9, Math.PI / 4, 0));
      globeGroup.add(createRing(10.2, -Math.PI / 3, Math.PI / 6));
      globeGroup.add(createRing(9.7, Math.PI / 2, 0)); // Equator
      globeGroup.add(createRing(9.7, 0, Math.PI / 2)); // Prime Meridian
      globeGroup.add(createRing(10.5, Math.PI / 6, Math.PI / 3)); // Celestial orbit

      // Coordinate Waypoint Nodes (Glowing Travel Coordinate Pins)
      const waypointGeo = new THREE.SphereGeometry(0.32, 16, 16);
      const waypointMat = new THREE.MeshBasicMaterial({ color: 0xD4AF37 });

      const waypoints = [
        [5.8, 4.0, 5.6],
        [-4.5, 6.8, 4.8],
        [2.7, -6.2, 6.5],
        [-6.3, -3.4, 5.4],
        [1.4, 7.3, 5.8],
        [7.2, -2.5, 5.6]
      ];

      waypoints.forEach(([x, y, z]) => {
        const wp = new THREE.Mesh(waypointGeo, waypointMat);
        wp.position.set(x, y, z);
        globeGroup.add(wp);
      });

      // Mouse Parallax
      let mouseX = 0, mouseY = 0;
      let targetRotX = 0, targetRotY = 0;

      window.addEventListener('mousemove', (e) => {
        const halfW = window.innerWidth / 2;
        const halfH = window.innerHeight / 2;
        mouseX = (e.clientX - halfW) / halfW;
        mouseY = (e.clientY - halfH) / halfH;
      }, { passive: true });

      // Animation Loop
      let animId;
      const animate = () => {
        animId = requestAnimationFrame(animate);

        globeGroup.rotation.y += 0.0025;
        targetRotX = mouseY * 0.25;
        targetRotY = mouseX * 0.35;

        globeGroup.rotation.x += (targetRotX - globeGroup.rotation.x) * 0.05;
        globeGroup.rotation.z += (targetRotY - globeGroup.rotation.z) * 0.05;

        renderer.render(scene, camera);
      };
      animate();

      // Resize Handler
      window.addEventListener('resize', () => {
        const newW = container.clientWidth;
        const newH = container.clientHeight;
        if (!newW || !newH) return;
        camera.aspect = newW / newH;
        camera.updateProjectionMatrix();
        renderer.setSize(newW, newH);
      }, { passive: true });

    } catch (e) {
      console.warn('Three.js canvas initialized in fallback mode', e);
    }
  }

  // 3. Scroll Reveals & GSAP ScrollTrigger Orchestration
  function initScrollAnimations() {
    const header = document.getElementById('siteHeader');
    window.addEventListener('scroll', () => {
      if (header) {
        if (window.scrollY > 40) {
          header.classList.add('is-scrolled');
        } else {
          header.classList.remove('is-scrolled');
        }
      }
    }, { passive: true });

    // GSAP ScrollTrigger if loaded
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
      gsap.registerPlugin(ScrollTrigger);

      // Parallax on Floating Photo Memoirs
      gsap.utils.toArray('.hero-memoir, .floating-memoir').forEach((el, i) => {
        const speed = (i + 1) * 35;
        gsap.to(el, {
          y: -speed,
          ease: 'none',
          scrollTrigger: {
            trigger: el.closest('section') || el,
            start: 'top bottom',
            end: 'bottom top',
            scrub: 1.2
          }
        });
      });

      // Chapter Reveals
      gsap.utils.toArray('.reveal-fade-up').forEach((el) => {
        gsap.fromTo(el, 
          { opacity: 0, y: 32 },
          {
            opacity: 1,
            y: 0,
            duration: 0.85,
            ease: 'power2.out',
            scrollTrigger: {
              trigger: el,
              start: 'top 88%',
              toggleActions: 'play none none none'
            }
          }
        );
      });

    } else {
      // Robust IntersectionObserver Fallback
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });

      document.querySelectorAll('.reveal-fade-up, .reveal-mask-clip').forEach((el) => {
        observer.observe(el);
      });
    }
  }

  // 4. Mobile Navigation Drawer
  function initMobileNav() {
    const toggle = document.getElementById('mobileToggle');
    const drawer = document.getElementById('mobileDrawer');
    const closeBtn = document.querySelector('.mobile-drawer__close');

    if (!toggle || !drawer) return;

    toggle.addEventListener('click', () => {
      drawer.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    });

    const closeDrawer = () => {
      drawer.classList.remove('is-open');
      document.body.style.overflow = '';
    };

    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    drawer.addEventListener('click', (e) => {
      if (e.target.classList.contains('mobile-drawer__link')) closeDrawer();
    });
  }

  // Self Initialization
  document.addEventListener('DOMContentLoaded', () => {
    initLoader();
    initThreeSphere();
    initScrollAnimations();
    initMobileNav();
  });

})();
