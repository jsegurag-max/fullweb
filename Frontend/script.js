// ============================================
// PARTICLES
// ============================================
function createParticles() {
    const container = document.getElementById('particles');
    const count = window.innerWidth < 768 ? 30 : 60;
    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        particle.style.left = Math.random() * 100 + '%';
        particle.style.width = particle.style.height = Math.random() * 4 + 2 + 'px';
        particle.style.animationDuration = Math.random() * 15 + 10 + 's';
        particle.style.animationDelay = Math.random() * 10 + 's';
        container.appendChild(particle);
    }
}

// ============================================
// NAVBAR
// ============================================
const navbar = document.getElementById('navbar');
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');
const navLinks = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
});

navToggle.addEventListener('click', () => {
    navToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
});

navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navToggle.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Active nav link
function updateActiveNav() {
    const sections = document.querySelectorAll('section, header');
    const scrollPos = window.scrollY + 150;
    sections.forEach(section => {
        const top = section.offsetTop;
        const height = section.offsetHeight;
        const id = section.getAttribute('id');
        if (scrollPos >= top && scrollPos < top + height) {
            navLinks.forEach(link => {
                link.classList.toggle('active', link.getAttribute('href') === '#' + id);
            });
        }
    });
}

// ============================================
// TYPING EFFECT
// ============================================
function typeWriter() {
    const texts = [
        'Desarrollador Web',
        'Creativo & Innovador',
        'Amante de la Tecnología',
        'Problem Solver'
    ];
    const el = document.getElementById('typing');
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function type() {
        const current = texts[textIndex];
        if (isDeleting) {
            el.textContent = current.substring(0, charIndex - 1);
            charIndex--;
        } else {
            el.textContent = current.substring(0, charIndex + 1);
            charIndex++;
        }

        let speed = isDeleting ? 40 : 80;

        if (!isDeleting && charIndex === current.length) {
            speed = 2000;
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
            speed = 400;
        }

        setTimeout(type, speed);
    }
    type();
}

// ============================================
// SCROLL REVEAL
// ============================================
function setupReveal() {
    const elements = document.querySelectorAll(
        '.about-grid, .skill-category, .project-card, .contact-grid, .stat'
    );
    elements.forEach(el => el.classList.add('reveal'));

    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
    );

    elements.forEach(el => observer.observe(el));
}

// ============================================
// SKILL BARS
// ============================================
function animateSkills() {
    const bars = document.querySelectorAll('.skill-progress');
    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    bar.style.width = bar.dataset.width + '%';
                    observer.unobserve(bar);
                }
            });
        },
        { threshold: 0.5 }
    );

    bars.forEach(bar => observer.observe(bar));
}

// ============================================
// COUNTER
// ============================================
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.target);
                    let current = 0;
                    const increment = target / 40;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            el.textContent = target;
                            clearInterval(timer);
                        } else {
                            el.textContent = Math.ceil(current);
                        }
                    }, 40);
                    observer.unobserve(el);
                }
            });
        },
        { threshold: 0.5 }
    );

    counters.forEach(counter => observer.observe(counter));
}

// ============================================
// PROJECT FILTER
// ============================================
function setupFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.project-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;

            cards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.classList.remove('hidden');
                    card.style.animation = 'fadeInUp 0.5s forwards';
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
}

// ============================================
// FORM
// ============================================
function setupForm() {
    const form = document.getElementById('contact-form');
    const status = document.getElementById('form-status');

    const SERVICE_ID = 'service_xtg66bj';
    const TEMPLATE_ID = 'template_4ym1gar';
    const PUBLIC_KEY = 'HKGh412FLdfodLNe1';

    emailjs.init({
        publicKey: PUBLIC_KEY,
    });

    form.addEventListener('submit', e => {
        e.preventDefault();
        status.textContent = '';
        status.className = 'form-status';

        const btn = form.querySelector('button');
        btn.innerHTML = '<span>Enviando...</span><i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        const params = {
            from_name: document.getElementById('name').value.trim(),
            reply_to: document.getElementById('email').value.trim(),
            subject: document.getElementById('subject').value.trim(),
            message: document.getElementById('message').value.trim(),
        };

        emailjs.send(SERVICE_ID, TEMPLATE_ID, params)
            .then(() => {
                status.textContent = 'Mensaje enviado correctamente!';
                status.classList.add('success');
                form.reset();
            })
            .catch(() => {
                status.textContent = 'Error al enviar. Intenta de nuevo.';
                status.classList.add('error');
            })
            .finally(() => {
                btn.innerHTML = '<span>Enviar Mensaje</span><i class="fas fa-paper-plane"></i>';
                btn.disabled = false;
            });
    });
}

// ============================================
// BACK TO TOP
// ============================================
function setupBackToTop() {
    const btn = document.getElementById('back-to-top');
    window.addEventListener('scroll', () => {
        btn.classList.toggle('visible', window.scrollY > 500);
    });
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// ============================================
// THEME TOGGLE
// ============================================
function setupThemeToggle() {
    const toggle = document.getElementById('theme-toggle');
    const icon = toggle.querySelector('i');
    const saved = localStorage.getItem('theme');

    if (saved === 'light') {
        document.body.classList.add('light');
        icon.className = 'fas fa-sun';
    }

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('light');
        const isLight = document.body.classList.contains('light');
        icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    });
}

// ============================================
// LOADER
// ============================================
function setupLoader() {
    const loader = document.getElementById('loader');
    const progress = document.getElementById('loader-progress');
    let p = 0;

    const interval = setInterval(() => {
        p += Math.random() * 25 + 10;
        if (p >= 100) {
            p = 100;
            clearInterval(interval);
            progress.style.width = p + '%';
            setTimeout(() => loader.classList.add('hidden'), 300);
        } else {
            progress.style.width = p + '%';
        }
    }, 350);
}

// ============================================
// CV DOWNLOAD
// ============================================
function setupCvDownload() {
    const btn = document.getElementById('cv-download');
    btn.addEventListener('click', e => {
        e.preventDefault();
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-download"></i> Descargar CV';
        }, 1200);
    });
}

// ============================================
// SMOOTH SCROLL
// ============================================
function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// ============================================
// AUTH NAV - solo refleja la sesión creada en login.js
// ============================================
function setupAuth() {
    const openBtn = document.getElementById('login-open');
    const userChip = document.getElementById('user-chip');
    const userName = document.getElementById('user-name');
    const userAvatar = document.getElementById('user-avatar');
    const logoutBtn = document.getElementById('logout-btn');
    if (!openBtn || !userChip) return;

    const SESSION_KEY = 'portfolio_session';
    const getSession = () => {
        try {
            return JSON.parse(localStorage.getItem(SESSION_KEY) || sessionStorage.getItem(SESSION_KEY) || 'null');
        } catch { return null; }
    };

    function renderSession() {
        const s = getSession();
        if (s) {
            openBtn.classList.add('hidden');
            userChip.classList.remove('hidden');
            userName.textContent = s.name;
            userAvatar.title = s.name;
        } else {
            openBtn.classList.remove('hidden');
            userChip.classList.add('hidden');
        }
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem(SESSION_KEY);
            sessionStorage.removeItem(SESSION_KEY);
            renderSession();
        });
    }

    renderSession();
}

// ============================================
// INIT
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    setupLoader();
    createParticles();
    typeWriter();
    setupReveal();
    animateSkills();
    animateCounters();
    setupFilter();
    setupForm();
    setupBackToTop();
    setupThemeToggle();
    setupCvDownload();
    setupSmoothScroll();
    setupAuth();
    window.addEventListener('scroll', updateActiveNav);
});
