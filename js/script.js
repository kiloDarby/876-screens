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
    button.addEventListener("click", () => {
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