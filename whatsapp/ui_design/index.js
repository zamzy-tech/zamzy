document.addEventListener('DOMContentLoaded', () => {

    // --- 1. DARK / LIGHT THEME TOGGLE ---
    const themeToggleBtn = document.querySelector('.theme-toggle');
    const htmlElement = document.documentElement;

    // Load initial theme from localStorage or system preference
    const savedTheme = localStorage.getItem('orisa-theme');
    if (savedTheme) {
        htmlElement.setAttribute('data-theme', savedTheme);
    } else {
        // Default to dark mode
        htmlElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('orisa-theme', 'dark');
    }

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        htmlElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('orisa-theme', newTheme);
    });

    // --- 2. SEARCH OVERLAY TOGGLE ---
    const searchTrigger = document.querySelector('.search-trigger');
    const searchOverlay = document.querySelector('.search-overlay-container');
    const searchOverlayClose = document.querySelector('.search-overlay-close button');
    const searchInput = document.querySelector('.search-input');
    const popularTags = document.querySelectorAll('.pop-tag');

    const openSearch = () => {
        searchOverlay.classList.add('active');
        setTimeout(() => searchInput.focus(), 150);
    };

    const closeSearch = () => {
        searchOverlay.classList.remove('active');
    };

    searchTrigger.addEventListener('click', openSearch);
    searchOverlayClose.addEventListener('click', closeSearch);

    // Click popular tag to search
    popularTags.forEach(tag => {
        tag.addEventListener('click', () => {
            searchInput.value = tag.textContent;
            searchInput.focus();
        });
    });

    // Handle Escape key to close search
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            closeSearch();
        }
    });

    // --- 3. OFFCANVAS SIDEBAR MENU ---
    const menuTrigger = document.querySelector('.menu-trigger');
    const mobileMenuTrigger = document.getElementById('mobile-menu-trigger');
    const offcanvasSidebar = document.querySelector('.offcanvas-sidebar');
    const offcanvasOverlay = document.querySelector('.offcanvas-overlay');
    const offcanvasClose = document.querySelector('.offcanvas-close');

    const openSidebar = () => {
        offcanvasSidebar.classList.add('active');
        offcanvasOverlay.classList.add('active');
    };

    const closeSidebar = () => {
        offcanvasSidebar.classList.remove('active');
        offcanvasOverlay.classList.remove('active');
    };

    menuTrigger.addEventListener('click', openSidebar);
    if (mobileMenuTrigger) {
        mobileMenuTrigger.addEventListener('click', openSidebar);
    }
    offcanvasClose.addEventListener('click', closeSidebar);
    offcanvasOverlay.addEventListener('click', closeSidebar);

    // --- 4. BACK TO TOP BUTTON ---
    const backToTopBtn = document.querySelector('.back-to-top');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            backToTopBtn.classList.add('active');
        } else {
            backToTopBtn.classList.remove('active');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // --- 5. TESTIMONIALS SLIDER ---
    const slides = document.querySelectorAll('.testimonial-slide');
    const prevBtn = document.querySelector('.slider-nav.prev');
    const nextBtn = document.querySelector('.slider-nav.next');
    let currentSlide = 0;

    const showSlide = (index) => {
        slides.forEach(slide => slide.classList.remove('active'));
        slides[index].classList.add('active');
    };

    if (slides.length > 0) {
        prevBtn.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });

        nextBtn.addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
    }

    // --- 6. SCROLLSPY NAVIGATION ---
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');

    const scrollActive = () => {
        const scrollY = window.pageYOffset;

        sections.forEach(current => {
            const sectionHeight = current.offsetHeight;
            const sectionTop = current.offsetTop - 150;
            const sectionId = current.getAttribute('id');
            const targetLink = document.querySelector(`.nav-menu a[href*=${sectionId}]`);

            if (targetLink) {
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navLinks.forEach(link => link.classList.remove('active'));
                    targetLink.classList.add('active');
                }
            }
        });
    };

    window.addEventListener('scroll', scrollActive);

    // --- 7. CONTACT FORM SUBMISSION ---
    const contactForm = document.getElementById('contact-form');
    const formFeedback = document.getElementById('form-feedback');

    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            formFeedback.textContent = 'Sending message...';
            formFeedback.className = 'form-feedback';
            
            // Mock API submission latency
            setTimeout(() => {
                formFeedback.textContent = 'Thank you! Your message has been sent successfully.';
                formFeedback.className = 'form-feedback success';
                contactForm.reset();
            }, 1000);
        });
    }
});
