// Banner Auto Slider
let slideIndex = 1;
let slideInterval;

// Initialize slider when page loads
document.addEventListener('DOMContentLoaded', function() {
    showSlides(slideIndex);
    startAutoSlide();
});

// Next/Previous controls
function plusSlides(n) {
    clearInterval(slideInterval);
    showSlides(slideIndex += n);
    startAutoSlide();
}

// Dot indicator controls
function currentSlide(n) {
    clearInterval(slideInterval);
    showSlides(slideIndex = n);
    startAutoSlide();
}

// Show slides function
function showSlides(n) {
    let slides = document.querySelectorAll('.banner-slide');
    let dots = document.querySelectorAll('.dot');
    
    if (!slides.length) return;
    
    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }
    
    // Hide all slides
    slides.forEach(slide => {
        slide.classList.remove('active');
    });
    
    // Remove active class from all dots
    dots.forEach(dot => {
        dot.classList.remove('active');
    });
    
    // Show current slide and highlight current dot
    if (slides[slideIndex - 1]) {
        slides[slideIndex - 1].classList.add('active');
    }
    if (dots[slideIndex - 1]) {
        dots[slideIndex - 1].classList.add('active');
    }
}

// Auto slide function
function startAutoSlide() {
    slideInterval = setInterval(() => {
        slideIndex++;
        showSlides(slideIndex);
    }, 5000); // Change slide every 5 seconds
}

// Pause auto slide on hover
const bannerSlider = document.querySelector('.banner-slider');
if (bannerSlider) {
    bannerSlider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    bannerSlider.addEventListener('mouseleave', () => {
        startAutoSlide();
    });
}

// Touch/swipe support for mobile
let startX = 0;
let startY = 0;
let endX = 0;
let endY = 0;

if (bannerSlider) {
    bannerSlider.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    bannerSlider.addEventListener('touchend', e => {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        handleSwipe();
    });
}

function handleSwipe() {
    const diffX = startX - endX;
    const diffY = startY - endY;
    
    // Check if horizontal swipe is more significant than vertical
    if (Math.abs(diffX) > Math.abs(diffY)) {
        if (Math.abs(diffX) > 50) { // Minimum swipe distance
            if (diffX > 0) {
                // Swipe left - next slide
                plusSlides(1);
            } else {
                // Swipe right - previous slide
                plusSlides(-1);
            }
        }
    }
}

// Keyboard navigation
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') {
        plusSlides(-1);
    } else if (e.key === 'ArrowRight') {
        plusSlides(1);
    }
});

// Page visibility API to pause slider when tab is not visible
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(slideInterval);
    } else {
        startAutoSlide();
    }
});