/**
 * MIDHILA VOYAGES — Tour Packages Dynamic Filtering & Search
 */

document.addEventListener('DOMContentLoaded', () => {
  const filterTabs = document.querySelectorAll('.package-filter-tab');
  const packageCards = document.querySelectorAll('.package-card-wrapper');
  const searchInput = document.getElementById('packageSearch');
  const emptyState = document.getElementById('packageEmptyState');

  let activeCategory = 'all';
  let searchQuery = '';

  const applyFilters = () => {
    let visibleCount = 0;

    packageCards.forEach((wrapper) => {
      const category = wrapper.getAttribute('data-category') || '';
      const isPilgrimage = wrapper.getAttribute('data-pilgrimage') === '1';
      const title = (wrapper.getAttribute('data-title') || '').toLowerCase();
      const destination = (wrapper.getAttribute('data-destination') || '').toLowerCase();

      // Check category match
      let matchesCategory = false;
      if (activeCategory === 'all') {
        matchesCategory = true;
      } else if (activeCategory === 'pilgrimage') {
        matchesCategory = isPilgrimage;
      } else {
        matchesCategory = (category === activeCategory);
      }

      // Check search match
      let matchesSearch = true;
      if (searchQuery.length > 0) {
        matchesSearch = title.includes(searchQuery) || destination.includes(searchQuery);
      }

      if (matchesCategory && matchesSearch) {
        wrapper.style.display = '';
        visibleCount++;
      } else {
        wrapper.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
  };

  // Tab click events
  filterTabs.forEach((tab) => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      filterTabs.forEach((t) => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      activeCategory = tab.getAttribute('data-category-slug') || 'all';
      applyFilters();
    });
  });

  // Search input events
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim().toLowerCase();
      applyFilters();
    });
  }
});
