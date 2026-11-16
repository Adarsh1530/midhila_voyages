/**
 * MIDHILA VOYAGES — OpenStreetMap & Leaflet Interactive Map Engine
 * Permitted OSM Tile Provider with standard attribution.
 * Zero paid API dependencies.
 */

(function(window) {
  'use strict';

  function createCategoryIcon(category) {
    var bg = '#059669'; // attraction (emerald)
    var iconSvg = '<path d="M12 2L2 22h20L12 2z"/>'; // triangle landmark

    if (category === 'hotel' || category === 'resort') {
      bg = '#2563EB'; // blue
      iconSvg = '<path d="M2 19V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12M2 15h20M6 10h.01M10 10h.01"/>';
    } else if (category === 'restaurant' || category === 'cafe') {
      bg = '#D97706'; // amber
      iconSvg = '<path d="M18 8V4M14 4v4a4 4 0 0 0 8 0V4M6 4v16M10 4v4a2 2 0 0 1-4 0V4"/>';
    }

    var html = '<div style="background:' + bg + ';width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 10px rgba(0,0,0,0.25);border:2px solid #FFFFFF;">'
             + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' + iconSvg + '</svg>'
             + '</div>';

    return L.divIcon({
      html: html,
      className: 'osm-custom-marker',
      iconSize: [34, 34],
      iconAnchor: [17, 34],
      popupAnchor: [0, -32]
    });
  }

  function MidhilaMap(options) {
    this.containerId = options.containerId;
    this.destinationId = options.destinationId;
    this.initialLat = options.latitude || 10.0889;
    this.initialLng = options.longitude || 77.0595;
    this.zoom = options.zoom || 12;
    this.onSelectPlace = options.onSelectPlace || null;
    this.selectedPlaceIds = options.selectedPlaceIds || [];
    this.map = null;
    this.markersLayer = null;
    this.places = [];
    this.activeCategory = 'all';

    this.init();
  }

  MidhilaMap.prototype.init = function() {
    var container = document.getElementById(this.containerId);
    if (!container || typeof L === 'undefined') return;

    // Initialize Leaflet Map
    this.map = L.map(this.containerId, {
      scrollWheelZoom: false
    }).setView([this.initialLat, this.initialLng], this.zoom);

    // Permitted OpenStreetMap Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
    }).addTo(this.map);

    this.markersLayer = L.layerGroup().addTo(this.map);

    if (this.destinationId) {
      this.loadPlaces(this.destinationId);
    }
  };

  MidhilaMap.prototype.loadPlaces = function(destinationId, category) {
    this.destinationId = destinationId;
    if (category) this.activeCategory = category;

    var self = this;
    var url = (window.MIDHILA_BASE_URL || '') + '/api/get-destination-places.php?destination_id=' + destinationId;
    if (this.activeCategory && this.activeCategory !== 'all') {
      url += '&category=' + encodeURIComponent(this.activeCategory);
    }

    fetch(url)
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success && Array.isArray(data.places)) {
          self.places = data.places;
          if (data.destination && data.destination.latitude && data.destination.longitude) {
            self.map.setView([parseFloat(data.destination.latitude), parseFloat(data.destination.longitude)], self.zoom);
          }
          self.renderMarkers();
          if (typeof self.onPlacesLoaded === 'function') {
            self.onPlacesLoaded(self.places);
          }
        }
      })
      .catch(function(err) {
        console.error('Failed to load destination places:', err);
      });
  };

  MidhilaMap.prototype.renderMarkers = function() {
    if (!this.markersLayer) return;
    this.markersLayer.clearLayers();

    var self = this;
    var bounds = [];

    this.places.forEach(function(place) {
      var lat = parseFloat(place.latitude);
      var lng = parseFloat(place.longitude);
      if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

      bounds.push([lat, lng]);
      var icon = createCategoryIcon(place.category);
      var marker = L.marker([lat, lng], { icon: icon });

      var isSelected = self.selectedPlaceIds.indexOf(place.id) !== -1;
      var priceText = (place.indicative_price > 0)
        ? '<div style="font-weight:700;color:#0F172A;font-size:0.85rem;margin-top:4px;">₹' + Number(place.indicative_price).toLocaleString('en-IN') + ' <span style="font-size:0.75rem;font-weight:400;color:#64748B;">' + (place.price_unit || '') + '</span></div>'
        : '<div style="font-size:0.75rem;color:#64748B;margin-top:2px;">Rate verified upon enquiry</div>';

      var selectBtnHtml = '<button type="button" class="osm-popup-select-btn" data-place-id="' + place.id + '" style="margin-top:8px;width:100%;padding:6px 10px;background:' + (isSelected ? '#059669' : '#0F172A') + ';color:#FFFFFF;border:none;border-radius:6px;font-size:0.78rem;font-weight:600;cursor:pointer;">'
        + (isSelected ? '✓ Added to Custom Trip' : '+ Select for Custom Journey')
        + '</button>';

      var popupHtml = '<div style="min-width:200px;font-family:system-ui,sans-serif;">'
        + '<div style="font-size:0.7rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.04em;">' + (place.category || 'Spot').toUpperCase() + '</div>'
        + '<h4 style="font-size:0.95rem;color:#0F172A;margin:2px 0 4px;font-weight:700;">' + place.name + '</h4>'
        + (place.address ? '<div style="font-size:0.75rem;color:#64748B;margin-bottom:4px;">📍 ' + place.address + '</div>' : '')
        + priceText
        + selectBtnHtml
        + '</div>';

      marker.bindPopup(popupHtml);
      marker.on('popupopen', function() {
        var btn = document.querySelector('.osm-popup-select-btn[data-place-id="' + place.id + '"]');
        if (btn) {
          btn.addEventListener('click', function() {
            self.togglePlaceSelection(place, btn);
          });
        }
      });

      self.markersLayer.addLayer(marker);
    });

    if (bounds.length > 0 && this.places.length > 1) {
      this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
  };

  MidhilaMap.prototype.togglePlaceSelection = function(place, btnElement) {
    var idx = this.selectedPlaceIds.indexOf(place.id);
    if (idx === -1) {
      this.selectedPlaceIds.push(place.id);
      if (btnElement) {
        btnElement.style.background = '#059669';
        btnElement.innerText = '✓ Added to Custom Trip';
      }
    } else {
      this.selectedPlaceIds.splice(idx, 1);
      if (btnElement) {
        btnElement.style.background = '#0F172A';
        btnElement.innerText = '+ Select for Custom Journey';
      }
    }

    if (typeof this.onSelectPlace === 'function') {
      this.onSelectPlace(place, this.selectedPlaceIds);
    }
  };

  MidhilaMap.prototype.filterCategory = function(cat) {
    this.activeCategory = cat;
    this.loadPlaces(this.destinationId, cat);
  };

  window.MidhilaMap = MidhilaMap;
})(window);
