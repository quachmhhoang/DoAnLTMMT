// Animation utilities and effects

class AnimationUtils {
    // Stagger animation for lists
    static staggerFadeIn(elements, delay = 100) {
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * delay);
        });
    }

    // Loading animation
    static showLoading(element, message = 'Đang tải...') {
        const loadingHTML = `
            <div class="loading-overlay">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">${message}</span>
                    </div>
                    <div class="loading-text">${message}</div>
                </div>
            </div>
        `;
        
        element.style.position = 'relative';
        element.insertAdjacentHTML('beforeend', loadingHTML);
    }

    static hideLoading(element) {
        const overlay = element.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Fade transition between content
    static fadeTransition(oldElement, newElement, duration = 300) {
        return new Promise((resolve) => {
            // Fade out old element
            oldElement.style.transition = `opacity ${duration}ms ease`;
            oldElement.style.opacity = '0';
            
            setTimeout(() => {
                // Replace content
                oldElement.innerHTML = newElement.innerHTML;
                
                // Fade in new content
                oldElement.style.opacity = '1';
                
                setTimeout(resolve, duration);
            }, duration);
        });
    }

    // Slide in from direction
    static slideIn(element, direction = 'left', distance = '100px') {
        const transforms = {
            left: `translateX(-${distance})`,
            right: `translateX(${distance})`,
            top: `translateY(-${distance})`,
            bottom: `translateY(${distance})`
        };

        element.style.transform = transforms[direction];
        element.style.opacity = '0';
        element.style.transition = 'all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        
        // Trigger animation
        requestAnimationFrame(() => {
            element.style.transform = 'translate(0)';
            element.style.opacity = '1';
        });
    }

    // Bounce effect
    static bounce(element) {
        element.style.animation = 'bounce 0.6s ease';
        
        setTimeout(() => {
            element.style.animation = '';
        }, 600);
    }

    // Pulse effect
    static pulse(element, color = 'rgba(0, 123, 255, 0.3)') {
        const originalBoxShadow = element.style.boxShadow;
        
        element.style.transition = 'box-shadow 0.3s ease';
        element.style.boxShadow = `0 0 0 10px ${color}`;
        
        setTimeout(() => {
            element.style.boxShadow = originalBoxShadow;
        }, 300);
    }

    // Shake effect for errors
    static shake(element) {
        element.style.animation = 'shake 0.5s ease';
        
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }

    // Count up animation for numbers
    static countUp(element, start = 0, end, duration = 2000) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }
            
            element.textContent = Math.floor(current).toLocaleString('vi-VN');
        }, 16);
    }

    // Typewriter effect
    static typeWriter(element, text, speed = 100) {
        element.textContent = '';
        let i = 0;
        
        const timer = setInterval(() => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(timer);
            }
        }, speed);
    }

    // Parallax scroll effect
    static parallax(element, speed = 0.5) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const scrollTop = window.pageYOffset;
                    const rate = scrollTop * -speed;
                    element.style.transform = `translate3d(0, ${rate}px, 0)`;
                }
            });
        });
        
        observer.observe(element);
        
        // Add scroll listener
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset;
            const rate = scrollTop * -speed;
            element.style.transform = `translate3d(0, ${rate}px, 0)`;
        });
    }
}

// Intersection Observer for scroll animations
class ScrollAnimations {
    constructor() {
        this.observers = new Map();
        this.init();
    }

    init() {
        // Create intersection observer
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px 0px -50px 0px'
        });

        // Observe elements
        this.observeElements();
    }

    observeElements() {
        // Elements to animate on scroll
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            this.observer.observe(element);
        });

        // Product cards
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            this.observer.observe(card);
        });

        // Statistics cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            this.observer.observe(card);
        });
    }

    animateElement(element) {
        const animationType = element.dataset.animation || 'fadeInUp';
        
        switch (animationType) {
            case 'fadeInUp':
                element.classList.add('animate-fade-in-up');
                break;
            case 'fadeInLeft':
                element.classList.add('animate-fade-in-left');
                break;
            case 'fadeInRight':
                element.classList.add('animate-fade-in-right');
                break;
            case 'scaleIn':
                element.classList.add('animate-scale-in');
                break;
            case 'slideInUp':
                element.classList.add('animate-slide-in-up');
                break;
            default:
                element.classList.add('animate-fade-in');
        }

        // Count up for numbers
        if (element.classList.contains('stat-number')) {
            const value = parseInt(element.textContent.replace(/[,\s]/g, ''));
            AnimationUtils.countUp(element, 0, value);
        }

        // Remove from observer
        this.observer.unobserve(element);
    }
}

// Image lazy loading and effects
class ImageEffects {
    static lazyLoad() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    static addHoverEffects() {
        const productImages = document.querySelectorAll('.product-image');
        
        productImages.forEach(img => {
            img.addEventListener('mouseenter', () => {
                img.style.transition = 'transform 0.3s ease';
                img.style.transform = 'scale(1.05)';
            });

            img.addEventListener('mouseleave', () => {
                img.style.transform = 'scale(1)';
            });
        });
    }

    static addZoomEffect() {
        const zoomableImages = document.querySelectorAll('.zoomable');
        
        zoomableImages.forEach(img => {
            img.addEventListener('click', () => {
                this.createImageModal(img.src);
            });
        });
    }

    static createImageModal(src) {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="image-modal-backdrop">
                <div class="image-modal-content">
                    <img src="${src}" alt="Enlarged image">
                    <button class="image-modal-close">&times;</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.classList.contains('image-modal-close')) {
                modal.remove();
            }
        });
    }
}

// Form enhancements
class FormEnhancements {
    static init() {
        this.addFloatingLabels();
        this.addValidationEffects();
        this.addPasswordToggle();
        this.addAutoComplete();
    }

    static addFloatingLabels() {
        const inputs = document.querySelectorAll('.form-floating input, .form-floating textarea');
        
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });

            // Check if input has value on load
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        });
    }

    static addValidationEffects() {
        const forms = document.querySelectorAll('.enhanced-form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });

                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid')) {
                        this.validateField(input);
                    }
                });
            });
        });
    }

    static validateField(input) {
        const isValid = input.checkValidity();
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            AnimationUtils.shake(input);
        }
    }

    static addPasswordToggle() {
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(input => {
            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-toggle';
            toggle.innerHTML = '<i class="fas fa-eye"></i>';
            
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(toggle);
            
            toggle.addEventListener('click', () => {
                if (input.type === 'password') {
                    input.type = 'text';
                    toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    toggle.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
    }

    static addAutoComplete() {
        // Phone number formatting
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = value.match(/.{1,3}/g).join('-');
                    if (value.length > 13) value = value.substr(0, 13);
                }
                e.target.value = value;
            });
        });
    }
}

// Initialize all enhancements when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize scroll animations
    window.scrollAnimations = new ScrollAnimations();
    
    // Initialize image effects
    ImageEffects.lazyLoad();
    ImageEffects.addHoverEffects();
    ImageEffects.addZoomEffect();
    
    // Initialize form enhancements
    FormEnhancements.init();
    
    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Add loading states to all buttons with loading class
    const loadingButtons = document.querySelectorAll('.btn-loading');
    loadingButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            AnimationUtils.showLoading(this);
        });
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AnimationUtils, ScrollAnimations, ImageEffects, FormEnhancements };
}
