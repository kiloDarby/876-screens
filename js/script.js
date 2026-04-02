const navToggle = document.querySelector(".nav-toggle");
const mobileMenu = document.querySelector(".mobile-menu");
const menuOverlay = document.querySelector(".menu-overlay");
const menuClose = document.querySelector(".menu-close");

function openMenu() {
  mobileMenu.classList.add("show");
  menuOverlay.classList.add("show");
  navToggle.setAttribute("aria-expanded", "true");
  document.body.style.overflow = "hidden";
}

function closeMenu() {
  mobileMenu.classList.remove("show");
  menuOverlay.classList.remove("show");
  navToggle.setAttribute("aria-expanded", "false");
  document.body.style.overflow = "";
}

navToggle.addEventListener("click", openMenu);
menuClose.addEventListener("click", closeMenu);
menuOverlay.addEventListener("click", closeMenu);



const carousels = document.querySelectorAll(".carousel");

carousels.forEach((carousel) => {
  const track = carousel.querySelector(".carousel-track");
  const slides = [...carousel.querySelectorAll(".movie-slide")];
  const prevBtn = carousel.querySelector(".carousel-btn--left");
  const nextBtn = carousel.querySelector(".carousel-btn--right");

  let currentIndex = 0;
  let autoPlay;

  function goToSlide(index) {
    currentIndex = index;
    track.style.transform = `translateX(-${currentIndex * 100}%)`;
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    goToSlide(currentIndex);
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    goToSlide(currentIndex);
  }

  function startAutoPlay() {
    stopAutoPlay();
    autoPlay = setInterval(nextSlide, 4000);
  }

  function stopAutoPlay() {
    clearInterval(autoPlay);
  }

  nextBtn.addEventListener("click", () => {
    nextSlide();
    startAutoPlay();
  });

  prevBtn.addEventListener("click", () => {
    prevSlide();
    startAutoPlay();
  });

  carousel.addEventListener("mouseenter", stopAutoPlay);
  carousel.addEventListener("mouseleave", startAutoPlay);

  goToSlide(0);
  startAutoPlay();
});



const modal = document.getElementById("trailerModal");
  const modalBackdrop = modal.querySelector(".trailer-modal__backdrop");
  const closeModalBtn = document.getElementById("closeTrailerModal");
  const trailerFrame = document.getElementById("trailerFrame");
  const modalTitle = document.getElementById("trailerModalTitle");
  const trailerButtons = document.querySelectorAll(".watch-trailer-btn");

  function openTrailerModal(trailerUrl, title = "Movie Trailer") {
    console.log('working')
    trailerFrame.src = `${trailerUrl}?autoplay=1&mute=1&playsinline=1&rel=0`;
    trailerFrame.title = title;
    modalTitle.textContent = title;
    modal.classList.add("active");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeTrailerModal() {
    trailerFrame.src = "";
    modal.classList.remove("active");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  trailerButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault()
      const trailerUrl = button.dataset.trailer;
      const title = button.dataset.title || "Movie Trailer";

      if (trailerUrl) {
        openTrailerModal(trailerUrl, title);
      }
    });
  });

  closeModalBtn.addEventListener("click", closeTrailerModal);
  modalBackdrop.addEventListener("click", closeTrailerModal);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("active")) {
      closeTrailerModal();
    }
  });


//-------------------------------------------------------------------------------
  const bookingModal = document.getElementById("bookingModal");
  const closeBookingModal = document.getElementById("closeBookingModal");

  const bookingPoster = document.getElementById("bookingPoster");
  const bookingRating = document.getElementById("bookingRating");
  const bookingTitle = document.getElementById("bookingModalTitle");
  const bookingMeta = document.getElementById("bookingMeta");
  const bookingDescription = document.getElementById("bookingDescription");
  const bookingCinema = document.getElementById("bookingCinema");
  const bookingDate = document.getElementById("bookingDate");
  const bookingShowtime = document.getElementById("bookingShowtime");
  const bookingFormat = document.getElementById("bookingFormat");

  const adultQty = document.getElementById("adultQty");
  const childQty = document.getElementById("childQty");

  const adultPriceLabel = document.getElementById("adultPriceLabel");
  const childPriceLabel = document.getElementById("childPriceLabel");

  const adultTotalText = document.getElementById("adultTotalText");
  const adultTotalAmount = document.getElementById("adultTotalAmount");
  const childTotalText = document.getElementById("childTotalText");
  const childTotalAmount = document.getElementById("childTotalAmount");
  const grandTotal = document.getElementById("grandTotal");

  let adultPrice = 1500;
  let childPrice = 1000;

  function formatJMD(value) {
    return `JMD $${Number(value).toLocaleString()}`;
  }

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

  function openBookingModal(trigger) {
    adultPrice = Number(trigger.dataset.adultPrice) || 1500;
    childPrice = Number(trigger.dataset.childPrice) || 1000;

    bookingPoster.src = trigger.dataset.poster || bookingPoster.src;
    bookingPoster.alt = `${trigger.dataset.title || "Movie"} Poster`;

    bookingRating.textContent = trigger.dataset.rating || "PG-13";
    bookingTitle.textContent = trigger.dataset.title || "Movie Title";
    bookingMeta.textContent = trigger.dataset.meta || "Genre • Duration";
    bookingDescription.textContent = trigger.dataset.description || "Movie description goes here.";
    bookingCinema.textContent = trigger.dataset.cinema || "Cinema 7";
    bookingDate.textContent = trigger.dataset.date || "Friday, April 10";
    bookingShowtime.textContent = trigger.dataset.showtime || "7:30 PM";
    bookingFormat.textContent = trigger.dataset.format || "2D Digital";

    adultPriceLabel.textContent = `${formatJMD(adultPrice)} each`;
    childPriceLabel.textContent = `${formatJMD(childPrice)} each`;

    adultQty.value = 1;
    childQty.value = 0;
    updateTotals();

    bookingModal.classList.add("active");
    bookingModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    bookingModal.classList.remove("active");
    bookingModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".buy-ticket").forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      openBookingModal(link);
    });
  });

  closeBookingModal.addEventListener("click", closeModal);

  bookingModal.querySelector(".booking-modal__backdrop").addEventListener("click", closeModal);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && bookingModal.classList.contains("active")) {
      closeModal();
    }
  });

  document.querySelectorAll(".qty-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.dataset.target;
      const action = button.dataset.action;
      const input = document.getElementById(targetId);

      let value = Number(input.value) || 0;

      if (action === "increase") value += 1;
      if (action === "decrease") value = Math.max(0, value - 1);

      input.value = value;
      updateTotals();
    });
  });

  [adultQty, childQty].forEach((input) => {
    input.addEventListener("input", () => {
      input.value = Math.max(0, Number(input.value) || 0);
      updateTotals();
    });
  });
