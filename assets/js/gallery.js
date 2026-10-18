/**
 * MIDHILA VOYAGES — Interactive Travel Gallery & Lightbox
 */

document.addEventListener('DOMContentLoaded', () => {
  const galleryItems = Array.from(document.querySelectorAll('.gallery-card'));
  const filterButtons = document.querySelectorAll('.gallery-filter-btn');
  const lightbox = document.getElementById('galleryLightbox');
  if (!lightbox) return;

  const lightboxImg = lightbox.querySelector('.lightbox__image');
  const lightboxTitle = lightbox.querySelector('.lightbox__title');
  const lightboxLocation = lightbox.querySelector('.lightbox__location');
  const closeBtn = lightbox.querySelector('.lightbox__close');
  const prevBtn = lightbox.querySelector('.lightbox__prev');
  const nextBtn = lightbox.querySelector('.lightbox__next');

  let currentIndex = 0;
  let activeVisibleItems = [...galleryItems];

  // Open Lightbox
  const openLightbox = (index) => {
    currentIndex = index;
    const item = activeVisibleItems[currentIndex];
    if (!item) return;

    const fullSrc = item.getAttribute('data-full') || item.querySelector('img')?.src;
    const title = item.getAttribute('data-title') || '';
    const location = item.getAttribute('data-location') || '';

    lightboxImg.src = fullSrc;
    lightboxImg.alt = title;
    lightboxTitle.textContent = title;
    lightboxLocation.textContent = location;

    lightbox.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  };

  const closeLightbox = () => {
    lightbox.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  const showNext = () => {
    currentIndex = (currentIndex + 1) % activeVisibleItems.length;
    openLightbox(currentIndex);
  };

  const showPrev = () => {
    currentIndex = (currentIndex - 1 + activeVisibleItems.length) % activeVisibleItems.length;
    openLightbox(currentIndex);
  };

  // Attach click listeners to gallery cards
  const bindItemClicks = () => {
    activeVisibleItems.forEach((item, index) => {
      item.onclick = (e) => {
        e.preventDefault();
        openLightbox(index);
      };
    });
  };
  bindItemClicks();

  if (closeBtn) closeBtn.onclick = closeLightbox;
  if (nextBtn) nextBtn.onclick = showNext;
  if (prevBtn) prevBtn.onclick = showPrev;

  lightbox.onclick = (e) => {
    if (e.target === lightbox) closeLightbox();
  };

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') showNext();
    if (e.key === 'ArrowLeft') showPrev();
  });

  // Filter items by category
  filterButtons.forEach((btn) => {
    btn.onclick = () => {
      filterButtons.forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      const filterCat = btn.getAttribute('data-filter') || 'all';
      activeVisibleItems = [];

      galleryItems.forEach((card) => {
        const itemCat = card.getAttribute('data-category') || '';
        if (filterCat === 'all' || itemCat === filterCat) {
          card.style.display = '';
          activeVisibleItems.push(card);
        } else {
          card.style.display = 'none';
        }
      });

      bindItemClicks();
    };
  });
});
