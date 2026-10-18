/**
 * MIDHILA VOYAGES — Trip Enquiry Form & WhatsApp Sync System
 */

document.addEventListener('DOMContentLoaded', () => {
  const enquiryForm = document.getElementById('tripEnquiryForm');
  if (!enquiryForm) return;

  const depDateInput = document.getElementById('departure_date');
  const retDateInput = document.getElementById('return_date');
  const adultsInput = document.getElementById('adults_count');
  const childrenInput = document.getElementById('children_count');
  const whatsappPreviewBtn = document.getElementById('whatsappPreviewBtn');
  const submitBtn = enquiryForm.querySelector('button[type="submit"]');

  // Enforce today as minimum date
  const today = new Date().toISOString().split('T')[0];
  if (depDateInput) {
    depDateInput.min = today;
    depDateInput.addEventListener('change', () => {
      if (retDateInput) {
        retDateInput.min = depDateInput.value;
        if (retDateInput.value && retDateInput.value < depDateInput.value) {
          retDateInput.value = depDateInput.value;
        }
      }
    });
  }

  // Real-time WhatsApp preview message builder
  const buildWhatsAppMessage = () => {
    const fullName = document.getElementById('full_name')?.value.trim() || 'Traveller';
    const destination = document.getElementById('destination_name')?.value.trim() || document.getElementById('destination_id')?.selectedOptions[0]?.text || 'a memorable journey';
    const travelCat = document.getElementById('travel_category')?.value || 'Custom Tour';
    const depDate = depDateInput?.value || 'Flexible';
    const retDate = retDateInput?.value || 'Flexible';
    const adults = adultsInput?.value || '1';
    const children = childrenInput?.value || '0';
    const budget = document.getElementById('budget_range')?.value || 'Standard';

    let msg = `Hello MIDHILA VOYAGES,\n\n`;
    msg += `I am planning a trip and would like to enquire about a travel package.\n`;
    msg += `• Name: ${fullName}\n`;
    msg += `• Preferred Destination: ${destination}\n`;
    msg += `• Category: ${travelCat}\n`;
    msg += `• Travel Dates: ${depDate} to ${retDate}\n`;
    msg += `• Travellers: ${adults} Adult(s), ${children} Child(ren)\n`;
    msg += `• Budget Range: ${budget}\n\n`;
    msg += `Please share itinerary suggestions, inclusions, and a personalized quotation.`;

    return msg;
  };

  // Form submission validation
  enquiryForm.addEventListener('submit', (e) => {
    let isValid = true;
    const errors = [];

    const fullName = document.getElementById('full_name')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();

    if (!fullName || fullName.length < 2) {
      isValid = false;
      errors.push('Please enter your full name.');
    }

    if (!phone || phone.length < 8) {
      isValid = false;
      errors.push('Please provide a valid contact phone or WhatsApp number.');
    }

    if (depDateInput && retDateInput && depDateInput.value && retDateInput.value) {
      if (retDateInput.value < depDateInput.value) {
        isValid = false;
        errors.push('Return date cannot be earlier than departure date.');
      }
    }

    if (!isValid) {
      e.preventDefault();
      alert(errors.join('\n'));
      return;
    }

    // Indicate loading state
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span>Saving Enquiry...</span>';
    }
  });

  // Direct WhatsApp Quick Chat button handler
  if (whatsappPreviewBtn) {
    whatsappPreviewBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const phone = whatsappPreviewBtn.getAttribute('data-phone') || '911234567890';
      const msg = buildWhatsAppMessage();
      const waUrl = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(msg)}`;
      window.open(waUrl, '_blank', 'noopener,noreferrer');
    });
  }
});
