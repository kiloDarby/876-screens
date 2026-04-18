// ============================================================
// 876 Screens - Main JavaScript File
// This file controls different interactive features on the site
// such as the mobile menu, carousels, modals, booking system,
// scheduling, alerts, and forms.
// ============================================================



// ============================================================
// MOBILE NAVIGATION MENU
// This section opens and closes the mobile menu.
// ============================================================
const navToggle = document.querySelector(".nav-toggle");
const mobileMenu = document.querySelector(".mobile-menu");
const menuOverlay = document.querySelector(".menu-overlay");
const menuClose = document.querySelector(".menu-close");

// Open the mobile menu
function openMenu() {
  mobileMenu.classList.add("show");
  menuOverlay.classList.add("show");
  document.body.style.overflow = "hidden"; // prevents scrolling while menu is open
}

// Close the mobile menu
function closeMenu() {
  mobileMenu.classList.remove("show");
  menuOverlay.classList.remove("show");
  document.body.style.overflow = ""; // restore normal scrolling
}

// Event listeners for opening and closing menu
navToggle.addEventListener("click", openMenu);
menuClose.addEventListener("click", closeMenu);
menuOverlay.addEventListener("click", closeMenu);



// ============================================================
// MOVIE CAROUSELS
// This section handles the movie sliders/carousels.
// Each carousel can move left/right and auto-play.
// ============================================================
const carousels = document.querySelectorAll(".carousel");

carousels.forEach((carousel) => {
  const track = carousel.querySelector(".carousel-track");
  const slides = [...carousel.querySelectorAll(".movie-slide")];
  const prevBtn = carousel.querySelector(".carousel-btn--left");
  const nextBtn = carousel.querySelector(".carousel-btn--right");

  let currentIndex = 0;
  let autoPlay;

  // Move to a specific slide
  function goToSlide(index) {
    currentIndex = index;
    track.style.transform = `translateX(-${currentIndex * 100}%)`;
  }

  // Go to next slide
  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    goToSlide(currentIndex);
  }

  // Go to previous slide
  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    goToSlide(currentIndex);
  }

  // Start auto-play
  function startAutoPlay() {
    stopAutoPlay();
    autoPlay = setInterval(nextSlide, 4000);
  }

  // Stop auto-play
  function stopAutoPlay() {
    clearInterval(autoPlay);
  }

  // Manual next button click
  nextBtn.addEventListener("click", () => {
    nextSlide();
    startAutoPlay();
  });

  // Manual previous button click
  prevBtn.addEventListener("click", () => {
    prevSlide();
    startAutoPlay();
  });

  // Pause auto-play when mouse is over carousel
  carousel.addEventListener("mouseenter", stopAutoPlay);

  // Resume auto-play when mouse leaves carousel
  carousel.addEventListener("mouseleave", startAutoPlay);

  // Start on first slide
  goToSlide(0);
  startAutoPlay();
});


// ============================================================
// MOVIE TRAILER MODAL
// This section opens a modal to show the movie trailer.
// ============================================================
function initTrailerModal() {
  const modal = document.getElementById("trailerModal");
  if (!modal) return;

  const modalBackdrop = modal.querySelector(".trailer-modal-backdrop");
  const closeModalBtn = modal.querySelector("#closeTrailerModal");
  const trailerFrame = modal.querySelector("#trailerFrame");
  const modalTitle = modal.querySelector("#trailerModalTitle");
  const trailerButtons = document.querySelectorAll(".watch-trailer-btn");

  // Stop if important modal elements are missing
  if (!closeModalBtn || !trailerFrame || !modalTitle || trailerButtons.length === 0) return;

  // Open trailer modal
  function openTrailerModal(trailerUrl, title = "Movie Trailer") {
    trailerFrame.src = `${trailerUrl}?autoplay=1&mute=1&playsinline=1&rel=0`;
    trailerFrame.title = title;
    modalTitle.textContent = title;
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  // Close trailer modal
  function closeTrailerModal() {
    trailerFrame.src = ""; // remove trailer when closed
    modal.classList.remove("active");
    document.body.style.overflow = "";
  }

  // Open trailer when trailer button is clicked
  trailerButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      const trailerUrl = button.dataset.trailer;
      const title = button.dataset.title || "Movie Trailer";

      if (trailerUrl) {
        openTrailerModal(trailerUrl, title);
      }
    });
  });

  // Close trailer modal with close button or backdrop
  closeModalBtn.addEventListener("click", closeTrailerModal);
  modalBackdrop?.addEventListener("click", closeTrailerModal);

  // Close trailer modal when Escape key is pressed
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("active")) {
      closeTrailerModal();
    }
  });
}

initTrailerModal();


// ============================================================
// BOOKING MODAL
// This section handles the ticket booking modal.
// It displays movie details and calculates totals.
// ============================================================
function initBookingModal() {
  const bookingModal = document.getElementById("bookingModal");
  if (!bookingModal) return;

  const closeBookingButtons = document.querySelectorAll(".closeBookingModal");

  const bookingPoster = bookingModal.querySelector("#bookingPoster");
  const bookingRating = bookingModal.querySelector("#bookingRating");
  const bookingTitle = bookingModal.querySelector("#bookingModalTitle");
  const bookingMeta = bookingModal.querySelector("#bookingMeta");
  const bookingDescription = bookingModal.querySelector("#bookingDescription");
  const bookingCinema = bookingModal.querySelector("#bookingCinema");
  const bookingDate = bookingModal.querySelector("#bookingDate");
  const bookingShowtime = bookingModal.querySelector("#bookingShowtime");

  const adultQty = bookingModal.querySelector("#adultQty");
  const childQty = bookingModal.querySelector("#childQty");

  const adultPriceLabel = bookingModal.querySelector("#adultPriceLabel");
  const childPriceLabel = bookingModal.querySelector("#childPriceLabel");

  const adultTotalText = bookingModal.querySelector("#adultTotalText");
  const adultTotalAmount = bookingModal.querySelector("#adultTotalAmount");
  const childTotalText = bookingModal.querySelector("#childTotalText");
  const childTotalAmount = bookingModal.querySelector("#childTotalAmount");
  const grandTotal = bookingModal.querySelector("#grandTotal");

  const bookingBackdrop = bookingModal.querySelector(".booking-modal-backdrop");

  // Default prices
  let adultPrice = 1500;
  let childPrice = 1000;

  // Format currency in Jamaican dollars
  function formatJMD(value) {
    return `JMD $${Number(value).toLocaleString()}`;
  }

  // Update totals whenever ticket quantity changes
  function updateTotals() {
    const adultCount = Number(adultQty.value) || 0;
    const childCount = Number(childQty.value) || 0;

    const adultTotal = adultCount * adultPrice;
    const childTotal = childCount * childPrice;
    const total = adultTotal + childTotal;

    adultTotalText.textContent = `Adult x ${adultCount}`;
    adultTotalAmount.textContent = formatJMD(adultTotal);

    childTotalText.textContent = `Child x ${childCount}`;
    childTotalAmount.textContent = formatJMD(childTotal);

    grandTotal.textContent = formatJMD(total);
  }

  // Open booking modal and fill in movie details
  function openBookingModal(trigger) {
    adultPrice = Number(trigger.dataset.adultPrice) || 1500;
    childPrice = Number(trigger.dataset.childPrice) || 1000;

    bookingPoster.src = '../' + trigger.dataset.poster || bookingPoster.src;
    bookingPoster.alt = `${trigger.dataset.title || "Movie"} Poster`;

    bookingRating.textContent = trigger.dataset.rating || "PG-13";
    bookingTitle.textContent = trigger.dataset.title || "Movie Title";
    bookingMeta.textContent = trigger.dataset.meta || "Genre • Duration";
    bookingDescription.textContent = trigger.dataset.description || "Movie description goes here.";
    bookingCinema.textContent = trigger.dataset.cinema || "Cinema 7";
    bookingDate.textContent = trigger.dataset.date || "Friday, April 10";
    bookingShowtime.textContent = trigger.dataset.showtime || "7:30 PM";

    adultPriceLabel.textContent = `${formatJMD(adultPrice)} each`;
    childPriceLabel.textContent = `${formatJMD(childPrice)} each`;

    // Reset quantities when modal opens
    adultQty.value = 1;
    childQty.value = 0;
    updateTotals();

    bookingModal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  // Close booking modal
  function closeModal() {
    bookingModal.classList.remove("active");
    document.body.style.overflow = "";
  }

  // Open booking modal when user clicks buy ticket button
  document.querySelectorAll(".buy-ticket").forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      openBookingModal(link);
    });
  });

  // Close modal buttons
  closeBookingButtons.forEach((button) => {
    button.addEventListener("click", closeModal);
  });

  // Close with Escape key
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && bookingModal.classList.contains("active")) {
      closeModal();
    }
  });

  // Increase or decrease ticket quantity
  bookingModal.querySelectorAll(".qty-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.dataset.target;
      const action = button.dataset.action;
      const input = bookingModal.querySelector(`#${targetId}`);

      let value = Number(input.value) || 0;

      if (action === "increase") value += 1;
      if (action === "decrease") value = Math.max(0, value - 1);

      input.value = value;
      updateTotals();
    });
  });

  // Update totals if user types directly in the quantity input
  [adultQty, childQty].forEach((input) => {
    input.addEventListener("input", () => {
      input.value = Math.max(0, Number(input.value) || 0);
      updateTotals();
    });
  });
}

initBookingModal();


// ============================================================
// BOOKING SUCCESS MODAL
// This section shows a success message after booking.
// ============================================================

// Generate a random ticket reference
function generateTicketReference() {
  const randomPart = Math.random().toString(36).slice(2, 8).toUpperCase();
  const timePart = Date.now().toString().slice(-6);

  return `876-${timePart}-${randomPart}`;
}

// Open the success modal and fill in booking details
function openBookingSuccessModal(data) {
  const successModal = document.getElementById("bookingSuccessModal");
  if (!successModal) return;

  const successEmail = document.getElementById("successEmail");
  const successReference = document.getElementById("successReference");
  const successMovieTitle = document.getElementById("successMovieTitle");
  const successDate = document.getElementById("successDate");
  const successCinema = document.getElementById("successCinema");
  const successShowtime = document.getElementById("successShowtime");
  const successTickets = document.getElementById("successTickets");
  const successTotal = document.getElementById("successTotal");

  if (successEmail) successEmail.textContent = data.email || "";
  if (successReference) successReference.textContent = data.reference || "";
  if (successMovieTitle) successMovieTitle.textContent = data.movieTitle || "";
  if (successDate) successDate.textContent = data.date || "";
  if (successCinema) successCinema.textContent = data.cinema || "";
  if (successShowtime) successShowtime.textContent = data.showtime || "";
  if (successTickets) {
    successTickets.textContent = `Adult x ${data.adultQty || 0}, Child x ${data.childQty || 0}`;
  }
  if (successTotal) successTotal.textContent = data.total || "JMD $0";

  successModal.classList.add("active");
  document.body.style.overflow = "hidden";
}

// Close the success modal
function closeBookingSuccessModal() {
  const successModal = document.getElementById("bookingSuccessModal");
  if (!successModal) return;

  successModal.classList.remove("active");
  document.body.style.overflow = "";
}

// Add functionality to close success modal
function initBookingSuccessModal() {
  const successModal = document.getElementById("bookingSuccessModal");
  if (!successModal) return;

  const closeButtons = successModal.querySelectorAll(".closeBookingSuccessModal");
  const backdrop = successModal.querySelector(".booking-success-modal-backdrop");

  closeButtons.forEach((button) => {
    button.addEventListener("click", closeBookingSuccessModal);
  });

  backdrop?.addEventListener("click", closeBookingSuccessModal);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && successModal.classList.contains("active")) {
      closeBookingSuccessModal();
    }
  });
}


// ============================================================
// BOOKING FORM SUBMISSION
// This section validates the booking form before confirming.
// ============================================================
const bookingForm = document.getElementById("bookingForm");

bookingForm?.addEventListener("submit", (event) => {
  event.preventDefault();

  const fullName = document.getElementById("fullName")?.value.trim() || "";
  const email = document.getElementById("email")?.value.trim() || "";
  const phone = document.getElementById("phone")?.value.trim() || "";

  const adultCount = Number(document.getElementById("adultQty")?.value || 0);
  const childCount = Number(document.getElementById("childQty")?.value || 0);

  const movieTitle =
    document.getElementById("bookingModalTitle")?.textContent.trim() || "Movie";

  const selectedDate =
    document.getElementById("bookingDate")?.selectedOptions[0]?.textContent.trim() || "";

  const selectedCinema =
    document.getElementById("bookingCinema")?.selectedOptions[0]?.textContent.trim() || "";

  const selectedShowtime =
    document.getElementById("bookingShowtime")?.selectedOptions[0]?.textContent.trim() || "";

  const total =
    document.getElementById("grandTotal")?.textContent.trim() || "JMD $0";

  // Basic validation checks
  if (!fullName) {
    alert("Full name is missing.");
    return;
  }

  if (!email) {
    alert("Email address is missing.");
    return;
  }

  if (!phone) {
    alert("Phone number is missing.");
    return;
  }

  if (adultCount + childCount <= 0) {
    alert("Please select at least one ticket.");
    return;
  }

  if (!selectedDate || !selectedCinema || !selectedShowtime) {
    alert("Please select a date, cinema, and showtime.");
    return;
  }

  // Ask user to confirm booking
  const confirmed = window.confirm(
    `Proceed with booking for ${movieTitle}?\n\nTotal: ${total}`
  );

  if (!confirmed) {
    return;
  }

  const bookingModal = document.getElementById("bookingModal");

  if (bookingModal) {
    bookingModal.classList.remove("active");
  }

  // Prepare success data
  const successData = {
    reference: generateTicketReference(),
    movieTitle,
    date: selectedDate,
    cinema: selectedCinema,
    showtime: selectedShowtime,
    adultQty: adultCount,
    childQty: childCount,
    total,
    email
  };

  openBookingSuccessModal(successData);
});

initBookingSuccessModal();


// ============================================================
// BUY TICKET - SHOWTIME LOADING
// This section fetches available showtimes from the server
// and fills the booking dropdowns dynamically.
// ============================================================
const bookingDate = document.getElementById("bookingDate");
const bookingCinema = document.getElementById("bookingCinema");
const bookingShowtime = document.getElementById("bookingShowtime");

let allShowtimes = [];

/* =========================
   HELPER FUNCTIONS
========================= */

// Format date nicely for display
function formatDate(dateStr) {
  if (!dateStr) {
    return "";
  }

  const date = new Date(dateStr + "T00:00:00");

  return date.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
  });
}

// Format time nicely for display
function formatTime(timeStr) {
  const [h, m] = timeStr.split(":");
  const d = new Date();
  d.setHours(h, m);
  return d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" });
}

// Format money for display
function formatMoney(val) {
  return `JMD $${Number(val).toLocaleString()}`;
}

/* =========================
   FETCH SHOWTIMES
========================= */

// Get showtimes from the backend using movie ID
async function fetchShowtimes(movieId) {
  const res = await fetch(`${BASE_URL}/public/get_movie_showtimes.php?movie_id=${movieId}`);
  const data = await res.json();
  return data.success ? data.showtimes : [];
}

/* =========================
   BUILD DATE DROPDOWN
========================= */

// Fill booking date dropdown
function populateDates() {
  bookingDate.innerHTML = "";

  const dates = [...new Set(allShowtimes.map(s => s.show_date))];

  dates.forEach(date => {
    const opt = document.createElement("option");
    opt.value = date;
    opt.textContent = formatDate(date);
    bookingDate.appendChild(opt);
  });
}

/* =========================
   BUILD CINEMA DROPDOWN
========================= */

// Fill cinema dropdown based on selected date
function populateCinemas(selectedDate) {
  bookingCinema.innerHTML = "";

  const filtered = allShowtimes.filter(s => s.show_date === selectedDate);

  const seen = new Set();
  const cinemas = [];

  filtered.forEach(s => {
    if (!seen.has(s.cinema_id)) {
      seen.add(s.cinema_id);
      cinemas.push(s);
    }
  });

  cinemas.forEach(c => {
    const opt = document.createElement("option");
    opt.value = c.cinema_id;
    opt.textContent = c.cinema_name;
    bookingCinema.appendChild(opt);
  });
}

/* =========================
   BUILD SHOWTIME DROPDOWN
========================= */

// Fill showtime dropdown based on selected date and cinema
function populateShowtimes(date, cinemaId) {
  bookingShowtime.innerHTML = "";

  const filtered = allShowtimes.filter(
    s => s.show_date === date && String(s.cinema_id) === String(cinemaId)
  );

  filtered.forEach(s => {
    const opt = document.createElement("option");
    opt.value = s.showtime_id;
    opt.textContent = formatTime(s.start_time);
    opt.dataset.adult = s.adult_price;
    opt.dataset.child = s.child_price;
    bookingShowtime.appendChild(opt);
  });

  if (filtered.length) updatePrices(filtered[0]);
}

/* =========================
   PRICE + TOTALS
========================= */

// Update price labels and hidden showtime ID
function updatePrices(showtime) {
  document.getElementById("bookingShowtimeId").value = showtime.showtime_id;

  document.getElementById("adultPriceLabel").textContent =
    `${formatMoney(showtime.adult_price)} each`;

  document.getElementById("childPriceLabel").textContent =
    `${formatMoney(showtime.child_price)} each`;

  updateTotals(showtime.adult_price, showtime.child_price);
}

// Recalculate total from current quantities
function updateTotals(adultPrice, childPrice) {
  const a = Number(document.getElementById("adultQty").value || 0);
  const c = Number(document.getElementById("childQty").value || 0);

  const total = a * adultPrice + c * childPrice;

  document.getElementById("grandTotal").textContent = formatMoney(total);
}

/* =========================
   EVENT FLOW
========================= */

// When booking date changes, refresh cinema and showtimes
bookingDate?.addEventListener("change", () => {
  populateCinemas(bookingDate.value);
  populateShowtimes(bookingDate.value, bookingCinema.value);
});

// When cinema changes, refresh showtimes
bookingCinema?.addEventListener("change", () => {
  populateShowtimes(bookingDate.value, bookingCinema.value);
});

// When showtime changes, update prices
bookingShowtime?.addEventListener("change", () => {
  const selected = allShowtimes.find(
    s => String(s.showtime_id) === bookingShowtime.value
  );

  if (selected) updatePrices(selected);
});

/* =========================
   BUY BUTTON CLICK
========================= */

// When buy ticket button is clicked
document?.querySelectorAll(".buy-ticket").forEach(btn => {
  btn.addEventListener("click", async (e) => {
    e.preventDefault();

    // Redirect to login if user is not logged in
    if (btn.dataset.isLoggedIn !== "1") {
      window.location.href = "./login.php";
      return;
    }

    const movieId = btn.dataset.movieId;

    // Fill movie details in modal
    document.getElementById("bookingPoster").src = '../' + btn.dataset.poster;
    document.getElementById("bookingModalTitle").textContent = btn.dataset.title;
    document.getElementById("bookingRating").textContent = btn.dataset.rating;
    document.getElementById("bookingMeta").textContent = btn.dataset.meta;
    document.getElementById("bookingDescription").textContent = btn.dataset.description;

    // Reset dropdowns before fetching
    bookingDate.innerHTML = `<option>Loading...</option>`;
    bookingCinema.innerHTML = "";
    bookingShowtime.innerHTML = "";

    document.getElementById("bookingModal").classList.add("is-open");

    // Fetch available showtimes
    allShowtimes = await fetchShowtimes(movieId);

    if (!allShowtimes.length) {
      bookingDate.innerHTML = `<option>No dates available</option>`;
      return;
    }

    // Build dropdowns using first available showtime info
    populateDates();

    const firstDate = bookingDate.value;
    populateCinemas(firstDate);

    const firstCinema = bookingCinema.value;
    populateShowtimes(firstDate, firstCinema);
  });
});


// ============================================================
// REMOVE FLASH MESSAGES
// This section automatically removes alert messages after a few seconds.
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    document.querySelectorAll('.global-alert').forEach((el) => {
      el.classList.add('is-hiding');

      setTimeout(() => {
        el.remove();
      }, 300);
    });
  }, 4000);
});


// ============================================================
// MOVIE SCHEDULING
// This section helps admin/supervisor add and manage showtimes.
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  const cinemaSelect = document.getElementById('scheduleCinema');
  const dateInput = document.getElementById('scheduleDate');
  const addShowtimeBtn = document.getElementById('addShowtimeBtn');
  const deleteAllBtn = document.getElementById('deleteAllShowtimesBtn');
  const showtimesList = document.getElementById('showtimesList');
  const hiddenInputsContainer = document.getElementById('showtimesHiddenInputs');
  const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');
  const scheduleErrorBox = document.getElementById('scheduleErrorBox');
  const isFeaturedCheckbox = document.getElementById('isFeatured');
  const featuredNote = document.getElementById('featuredNote');

  // Start with any showtimes already loaded from backend
  let scheduledShowtimes = Array.isArray(window.initialShowtimes)
    ? [...window.initialShowtimes]
    : [];

  // Show scheduling error message
  function showScheduleError(message) {
    if (!scheduleErrorBox) {
      return;
    }

    scheduleErrorBox.textContent = message;
    scheduleErrorBox.hidden = false;
  }

  // Clear scheduling error message
  function clearScheduleError() {
    if (!scheduleErrorBox) {
      return;
    }

    scheduleErrorBox.textContent = '';
    scheduleErrorBox.hidden = true;
  }

  // Format date for display in the list
  function formatDateForDisplay(dateString) {
    if (!dateString) {
      return '';
    }

    const date = new Date(dateString + 'T00:00:00');

    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  }

  // Escape text before inserting into HTML for safety
  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Uncheck all selected time slots
  function clearSelectedTimeSlots() {
    timeSlotCheckboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });
  }

  // Only allow movie to be featured if at least one showtime exists
  function syncFeaturedCheckboxState() {
    if (!isFeaturedCheckbox) {
      return;
    }

    const hasShowtimes = scheduledShowtimes.length > 0;

    isFeaturedCheckbox.disabled = !hasShowtimes;

    if (!hasShowtimes) {
      isFeaturedCheckbox.checked = false;
    }

    if (featuredNote) {
      if (hasShowtimes) {
        featuredNote.classList.add('is-hidden');
      } else {
        featuredNote.classList.remove('is-hidden');
      }
    }
  }

  // Check if this showtime already exists
  function showtimeExists(newShowtime) {
    return scheduledShowtimes.some((showtime) => {
      return (
        showtime.cinemaValue === newShowtime.cinemaValue &&
        showtime.date === newShowtime.date &&
        showtime.timeValue === newShowtime.timeValue
      );
    });
  }

  // Group showtimes by cinema and date for display
  function groupShowtimes(showtimes) {
    const grouped = {};

    showtimes.forEach((showtime, index) => {
      const cinemaKey = showtime.cinemaValue;

      if (!grouped[cinemaKey]) {
        grouped[cinemaKey] = {
          cinemaValue: showtime.cinemaValue,
          cinemaText: showtime.cinemaText,
          dates: {}
        };
      }

      if (!grouped[cinemaKey].dates[showtime.date]) {
        grouped[cinemaKey].dates[showtime.date] = [];
      }

      grouped[cinemaKey].dates[showtime.date].push({
        ...showtime,
        index
      });
    });

    return grouped;
  }

  // Create hidden inputs so scheduled showtimes can be submitted in form
  function renderHiddenInputs() {
    hiddenInputsContainer.innerHTML = '';

    scheduledShowtimes.forEach((showtime, index) => {
      hiddenInputsContainer.insertAdjacentHTML('beforeend', `
        <input type="hidden" name="showtimes[${index}][cinema_id]" value="${escapeHtml(showtime.cinemaValue)}">
        <input type="hidden" name="showtimes[${index}][show_date]" value="${escapeHtml(showtime.date)}">
        <input type="hidden" name="showtimes[${index}][show_time]" value="${escapeHtml(showtime.timeValue)}">
      `);
    });
  }

  // Render all scheduled showtimes in grouped format
  function renderShowtimes() {
    if (!showtimesList) {
      return;
    }

    showtimesList.innerHTML = '';
    renderHiddenInputs();
    syncFeaturedCheckboxState();

    // Show empty state if no showtimes exist
    if (scheduledShowtimes.length === 0) {
      deleteAllBtn.hidden = true;

      showtimesList.innerHTML = `
        <div class="showtimes-empty-card" id="showtimesEmptyState">
          <p>No showtimes added yet.</p>
        </div>
      `;
      return;
    }

    deleteAllBtn.hidden = false;

    const groupedShowtimes = groupShowtimes(scheduledShowtimes);

    Object.values(groupedShowtimes).forEach((cinemaGroup) => {
      const group = document.createElement('fieldset');
      group.className = 'showtime-group';

      const datesHtml = Object.entries(cinemaGroup.dates)
        .sort(([dateA], [dateB]) => dateA.localeCompare(dateB))
        .map(([date, times]) => {
          const timeItems = times
            .sort((a, b) => a.timeValue.localeCompare(b.timeValue))
            .map((time) => `
              <div class="showtime-time-item">
                <span class="showtime-time-text">${escapeHtml(time.timeLabel)}</span>
                <button
                  type="button"
                  class="showtime-remove-btn"
                  data-index="${time.index}"
                >
                  Remove
                </button>
              </div>
            `)
            .join('');

          return `
            <div class="showtime-date-group">
              <div class="showtime-date-label">${escapeHtml(formatDateForDisplay(date))}</div>
              <div class="showtime-time-list">
                ${timeItems}
              </div>
            </div>
          `;
        })
        .join('');

      group.innerHTML = `
        <legend class="showtime-group-title">${escapeHtml(cinemaGroup.cinemaText)}</legend>
        ${datesHtml}
      `;

      showtimesList.appendChild(group);
    });
  }

  // Add selected showtimes
  if (addShowtimeBtn) {
    addShowtimeBtn.addEventListener('click', () => {
      clearScheduleError();

      const cinemaValue = cinemaSelect.value;
      const cinemaText = cinemaSelect.options[cinemaSelect.selectedIndex]?.text || '';
      const selectedDate = dateInput.value;

      const selectedTimeSlots = Array.from(timeSlotCheckboxes)
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => ({
          timeValue: checkbox.value,
          timeLabel: checkbox.dataset.label
        }));

      if (!cinemaValue) {
        showScheduleError('Please select a cinema.');
        return;
      }

      if (!selectedDate) {
        showScheduleError('Please select a date.');
        return;
      }

      // Prevent past dates
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const selected = new Date(selectedDate + 'T00:00:00');

      if (selected < today) {
        showScheduleError('You cannot schedule showtimes in the past.');
        return;
      }

      if (selectedTimeSlots.length === 0) {
        showScheduleError('Please select at least one time slot.');
        return;
      }

      let addedCount = 0;

      selectedTimeSlots.forEach((slot) => {
        const newShowtime = {
          cinemaValue,
          cinemaText,
          date: selectedDate,
          timeValue: slot.timeValue,
          timeLabel: slot.timeLabel
        };

        if (!showtimeExists(newShowtime)) {
          scheduledShowtimes.push(newShowtime);
          addedCount++;
        }
      });

      if (addedCount === 0) {
        showScheduleError('Those showtimes were already added.');
        return;
      }

      renderShowtimes();
      clearSelectedTimeSlots();
      clearScheduleError();
    });
  }

  // Delete all scheduled showtimes
  if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', () => {
      if (scheduledShowtimes.length === 0) {
        return;
      }

      if (!confirm('Delete all scheduled showtimes? This is a permanent action and cannot be undone.')) {
        return;
      }

      scheduledShowtimes = [];
      renderShowtimes();
      clearScheduleError();
    });
  }

  // Remove a single showtime
  if (showtimesList) {
    showtimesList.addEventListener('click', (event) => {
      const removeButton = event.target.closest('.showtime-remove-btn');

      if (!removeButton) {
        return;
      }

      const index = Number(removeButton.dataset.index);

      if (Number.isNaN(index)) {
        return;
      }

      if (!confirm('Remove this showtime?  This is a permanent action and cannot be undone.')) {
        return;
      }

      scheduledShowtimes.splice(index, 1);
      renderShowtimes();
      clearScheduleError();
    });
  }

  // Initial render
  renderShowtimes();
});


// ============================================================
// CONFIRM USER DELETE
// This section confirms before deleting users in admin panel.
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".manage-users-form");

  if (!form) return;

  form.addEventListener("submit", (e) => {
    // Get all delete checkboxes inside the form
    const deleteCheckboxes = form.querySelectorAll('input[name$="[delete]"]');

    // Check if at least one delete checkbox is selected
    const hasDelete = Array.from(deleteCheckboxes).some(cb => cb.checked);

    if (hasDelete) {
      const confirmed = window.confirm(
        "You are about to delete one or more users.\n\n" +
        "This is a permanent action and cannot be undone.\n\n" +
        "Do you want to continue?"
      );

      if (!confirmed) {
        e.preventDefault(); // stop form submission if user cancels
      }
    }
  });
});


// ============================================================
// CONTACT FORM
// This section shows a success message and clears the form
// after user submits the contact form.
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  const contactForm = document.querySelector('.contact-form');

  if (!contactForm) return;

  contactForm.addEventListener('submit', (e) => {
    e.preventDefault(); // stop page reload

    alert('Your message has been sent successfully!');

    contactForm.reset(); // clear all form fields
  });
});