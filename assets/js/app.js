/**
 * Portfolio Website - Main JavaScript
 * Handles animations, interactions, and AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // === PRELOADER ===
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => preloader.classList.add('hidden'), 500);
        });
        // Fallback
        setTimeout(() => preloader.classList.add('hidden'), 3000);
    }

    // === AOS INIT ===
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50,
            disable: window.innerWidth < 768 ? 'phone' : false
        });
    }

    // === THEME TOGGLE ===
    const themeToggle = document.getElementById('theme-toggle');
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
    }

    // === NAVBAR ===
    const navbar = document.getElementById('navbar');
    const toggler = document.getElementById('navbar-toggler');
    const navMenu = document.getElementById('navbar-menu');
    
    // Scroll effect
    window.addEventListener('scroll', () => {
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        }
        // Back to top
        const btt = document.getElementById('back-to-top');
        if (btt) btt.classList.toggle('visible', window.scrollY > 500);
    });
    
    // Mobile toggle
    if (toggler && navMenu) {
        toggler.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            toggler.classList.toggle('active');
        });
        // Close on link click
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => navMenu.classList.remove('active'));
        });
    }

    // === BACK TO TOP ===
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // === TYPING ANIMATION ===
    const typingEl = document.getElementById('typing-text');
    if (typingEl) {
        const texts = typingEl.dataset.texts.split(',');
        let textIndex = 0, charIndex = 0, isDeleting = false;
        
        function type() {
            const current = texts[textIndex];
            if (isDeleting) {
                typingEl.textContent = current.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typingEl.textContent = current.substring(0, charIndex + 1);
                charIndex++;
            }
            
            let speed = isDeleting ? 50 : 100;
            
            if (!isDeleting && charIndex === current.length) {
                speed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                textIndex = (textIndex + 1) % texts.length;
                speed = 500;
            }
            
            setTimeout(type, speed);
        }
        type();
    }

    // === COUNTER ANIMATION ===
    const counters = document.querySelectorAll('.stat-number[data-count]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.count);
                let count = 0;
                const step = Math.ceil(target / 60);
                const timer = setInterval(() => {
                    count += step;
                    if (count >= target) {
                        el.textContent = target + '+';
                        clearInterval(timer);
                    } else {
                        el.textContent = count + '+';
                    }
                }, 30);
                counterObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(c => counterObserver.observe(c));

    // === SKILL PROGRESS BARS ===
    const skillBars = document.querySelectorAll('.skill-progress');
    const skillObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.width = entry.target.dataset.progress + '%';
                skillObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    
    skillBars.forEach(bar => skillObserver.observe(bar));

    // === SKILL TABS ===
    document.querySelectorAll('.skill-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.skill-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const category = tab.dataset.category;
            document.querySelectorAll('.skill-item').forEach(item => {
                item.style.display = (category === 'all' || item.dataset.category === category) ? '' : 'none';
            });
        });
    });

    // === TIMELINE TABS ===
    document.querySelectorAll('.timeline-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.timeline-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const type = tab.dataset.type;
            document.querySelectorAll('.timeline-item').forEach(item => {
                item.style.display = (type === 'all' || item.dataset.type === type) ? '' : 'none';
            });
        });
    });

    // === PORTFOLIO FILTER ===
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            document.querySelectorAll('.project-card[data-category]').forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.5s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // === FAQ ACCORDION ===
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
            if (!isActive) item.classList.add('active');
        });
    });

    // === GALLERY ===
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', () => {
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            const mainImg = document.getElementById('gallery-main-img');
            if (mainImg) mainImg.src = thumb.dataset.src;
        });
    });

    // === COPY LINK ===
    document.querySelectorAll('.copy-link').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.url).then(() => {
                showToast('Link copied to clipboard!', 'success');
            });
        });
    });

    // === CONTACT FORM ===
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('contact-submit');
            const loader = btn.querySelector('.btn-loader');
            
            btn.disabled = true;
            if (loader) loader.style.display = 'inline';
            
            try {
                const formData = new FormData(contactForm);
                const response = await fetch(getBaseUrl() + '/api/contact', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    contactForm.reset();
                } else {
                    showToast(data.message || 'Something went wrong', 'error');
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            }
            
            btn.disabled = false;
            if (loader) loader.style.display = 'none';
        });
    }

    // === COMMENT FORM ===
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            formData.append('blog_id', commentForm.dataset.blogId);
            
            try {
                const response = await fetch(getBaseUrl() + '/api/comments', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    commentForm.reset();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('Failed to post comment.', 'error');
            }
        });
    }

    // === NEWSLETTER FORM ===
    document.querySelectorAll('.newsletter-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = form.querySelector('input[name="email"]').value;
            
            try {
                const response = await fetch(getBaseUrl() + '/api/newsletter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ email })
                });
                const data = await response.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) form.reset();
            } catch (err) {
                showToast('Subscription failed.', 'error');
            }
        });
    });

    // === LOAD MORE (Portfolio) ===
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async () => {
            let page = parseInt(loadMoreBtn.dataset.page) + 1;
            const total = parseInt(loadMoreBtn.dataset.total);
            const category = loadMoreBtn.dataset.category || '';
            
            loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            
            try {
                const response = await fetch(getBaseUrl() + '/api/projects?page=' + page + '&category=' + category, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                if (data.success && data.html) {
                    document.getElementById('projects-grid').insertAdjacentHTML('beforeend', data.html);
                    loadMoreBtn.dataset.page = page;
                    if (page >= total) loadMoreBtn.style.display = 'none';
                }
            } catch (err) { }
            
            loadMoreBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Load More Projects';
        });
    }

    // === TABLE OF CONTENTS (Blog) ===
    const tocNav = document.getElementById('toc-nav');
    if (tocNav) {
        const headings = document.querySelectorAll('.article-content h2, .article-content h3');
        if (headings.length > 0) {
            let html = '<ul>';
            headings.forEach((h, i) => {
                const id = 'heading-' + i;
                h.id = id;
                const level = h.tagName === 'H3' ? 'toc-sub' : '';
                html += `<li class="${level}"><a href="#${id}">${h.textContent}</a></li>`;
            });
            html += '</ul>';
            tocNav.innerHTML = html;
        } else {
            tocNav.closest('.toc-widget').style.display = 'none';
        }
    }

    // === PARTICLES (Simple) ===
    const particles = document.getElementById('particles');
    if (particles) {
        for (let i = 0; i < 30; i++) {
            const dot = document.createElement('div');
            dot.className = 'particle';
            dot.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 1}px;
                height: ${Math.random() * 4 + 1}px;
                background: rgba(0, 102, 255, ${Math.random() * 0.5});
                border-radius: 50%;
                top: ${Math.random() * 100}%;
                left: ${Math.random() * 100}%;
                animation: float ${Math.random() * 10 + 10}s ease-in-out infinite;
                animation-delay: ${Math.random() * 5}s;
            `;
            particles.appendChild(dot);
        }
    }

    // === TOAST NOTIFICATION ===
    window.showToast = function(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    // === HELPER ===
    function getBaseUrl() {
        const meta = document.querySelector('link[rel="canonical"]');
        if (meta) {
            const url = new URL(meta.href);
            return url.origin;
        }
        return window.location.origin;
    }
});

// Float animation for particles
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); }
        25% { transform: translateY(-20px) translateX(10px); }
        50% { transform: translateY(-10px) translateX(-10px); }
        75% { transform: translateY(-30px) translateX(5px); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .toc-nav ul { list-style: none; }
    .toc-nav li { margin-bottom: 8px; }
    .toc-nav a { color: var(--text-secondary); font-size: 0.85rem; }
    .toc-nav a:hover { color: var(--accent); }
    .toc-nav .toc-sub { padding-left: 16px; }
`;
document.head.appendChild(style);
