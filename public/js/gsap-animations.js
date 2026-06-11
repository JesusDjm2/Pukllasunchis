/**
 * GSAP Animations — Pukllasunchis public views
 * Subtle scroll-reveal, stagger, and micro-interaction animations.
 * Requires: gsap.min.js + ScrollTrigger.min.js (loaded in layouts/home.blade.php)
 */
(function () {
    'use strict';

    if (typeof gsap === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    /* -------------------------------------------------------
       Utility helpers
    ------------------------------------------------------- */

    /** Animate elements into view from below */
    function revealUp(targets, triggerEl, options) {
        var els = gsap.utils.toArray(targets);
        if (!els.length) return;
        gsap.from(els, Object.assign({
            opacity: 0,
            y: 32,
            duration: 0.65,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: triggerEl || els[0],
                start: 'top 80%',
                once: true
            }
        }, options));
    }

    /** Stagger animate a group of elements from below */
    function staggerUp(targets, triggerEl, stagger) {
        var els = gsap.utils.toArray(targets);
        if (!els.length) return;
        gsap.from(els, {
            opacity: 0,
            y: 40,
            duration: 0.6,
            stagger: stagger || 0.12,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: triggerEl || els[0],
                start: 'top 78%',
                once: true
            }
        });
    }

    /* -------------------------------------------------------
       1. Page-load entrance — solo el hero/slider, NUNCA el header
       (el header tiene position sticky + slicknav mobile; animarlo
       con transforms rompe el layout del menú)
    ------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {

        gsap.from('.slider_area, .hero-area', { opacity: 0, duration: 0.7, ease: 'power2.out' });

        /* -------------------------------------------------------
           2. Sticky navbar: add shadow on scroll
        ------------------------------------------------------- */
        var stickyHeader = document.getElementById('sticky-header');
        if (stickyHeader) {
            ScrollTrigger.create({
                start: 90,
                onEnter: function () { stickyHeader.classList.add('nav-scrolled'); },
                onLeaveBack: function () { stickyHeader.classList.remove('nav-scrolled'); }
            });
        }

        /* -------------------------------------------------------
           3. Scroll-to-top button: smooth show / hide
        ------------------------------------------------------- */
        var scrollBtn = document.getElementById('scrollToTopBtn');
        if (scrollBtn) {
            gsap.set(scrollBtn, { opacity: 0, scale: 0.75 });
            ScrollTrigger.create({
                start: 420,
                onEnter: function () {
                    gsap.to(scrollBtn, { opacity: 1, scale: 1, duration: 0.35, ease: 'back.out(1.7)' });
                },
                onLeaveBack: function () {
                    gsap.to(scrollBtn, { opacity: 0, scale: 0.75, duration: 0.2 });
                }
            });
        }

        /* -------------------------------------------------------
           4. WhatsApp button: soft floating pulse
        ------------------------------------------------------- */
        var wasa = document.querySelector('.wasa');
        if (wasa) {
            gsap.to(wasa, {
                y: -4,
                duration: 1.8,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            });
        }

        /* -------------------------------------------------------
           5. "Quiénes somos" section
        ------------------------------------------------------- */
        var welcomeArea = document.querySelector('.welcome_docmed_area');
        if (welcomeArea) {
            gsap.from('.welcome_docmed_info', {
                opacity: 0, x: -38, duration: 0.75,
                scrollTrigger: { trigger: welcomeArea, start: 'top 76%', once: true }
            });
            var iframeEl = welcomeArea.querySelector('iframe');
            if (iframeEl) {
                gsap.from(iframeEl, {
                    opacity: 0, x: 38, duration: 0.75, delay: 0.12,
                    scrollTrigger: { trigger: welcomeArea, start: 'top 76%', once: true }
                });
            }
        }

        /* -------------------------------------------------------
           6. FID / PPD dual banner — slide in from each side
        ------------------------------------------------------- */
        var mensajes = document.querySelector('.mensajes');
        if (mensajes) {
            var cols = mensajes.querySelectorAll('.col-12');
            if (cols.length >= 2) {
                gsap.from(cols[0].querySelector('.w-100'), {
                    opacity: 0, x: -46, duration: 0.78,
                    scrollTrigger: { trigger: mensajes, start: 'top 76%', once: true }
                });
                gsap.from(cols[1].querySelector('.w-100'), {
                    opacity: 0, x: 46, duration: 0.78, delay: 0.12,
                    scrollTrigger: { trigger: mensajes, start: 'top 76%', once: true }
                });
            }
        }

        /* -------------------------------------------------------
           7. Section titles (.section_title) across all views
        ------------------------------------------------------- */
        gsap.utils.toArray('.section_title').forEach(function (el) {
            gsap.from(el, {
                opacity: 0, y: 28, duration: 0.65,
                scrollTrigger: { trigger: el, start: 'top 82%', once: true }
            });
        });

        /* -------------------------------------------------------
           8. Programs / departments grid — stagger cards
        ------------------------------------------------------- */
        var deptArea = document.querySelector('.our_department_area');
        if (deptArea) {
            staggerUp('.single_department', deptArea, 0.11);
        }

        /* -------------------------------------------------------
           9. Tabs section
        ------------------------------------------------------- */
        var tabArea = document.querySelector('.business_expert_area');
        if (tabArea) {
            revealUp(tabArea, tabArea, { y: 28, duration: 0.7 });
        }

        /* -------------------------------------------------------
           10. Parallax section — cards stagger in
        ------------------------------------------------------- */
        var parallaxEl = document.querySelector('.parallax');
        if (parallaxEl) {
            gsap.from(parallaxEl.querySelector('h2'), {
                opacity: 0, y: 22, duration: 0.6,
                scrollTrigger: { trigger: parallaxEl, start: 'top 78%', once: true }
            });
            gsap.from(parallaxEl.querySelector('p'), {
                opacity: 0, y: 18, duration: 0.6, delay: 0.12,
                scrollTrigger: { trigger: parallaxEl, start: 'top 78%', once: true }
            });
            staggerUp('.parallax .card', parallaxEl, 0.1);
        }

        /* -------------------------------------------------------
           11. Bolsa de trabajo section
        ------------------------------------------------------- */
        var bolsaSection = document.querySelector('.bolsa-registro-section');
        if (bolsaSection) {
            gsap.from('.bolsa-registro-intro', {
                opacity: 0, x: -36, duration: 0.72,
                scrollTrigger: { trigger: bolsaSection, start: 'top 78%', once: true }
            });
            gsap.from('.bolsa-registro-card', {
                opacity: 0, x: 36, duration: 0.72, delay: 0.14,
                scrollTrigger: { trigger: bolsaSection, start: 'top 78%', once: true }
            });
        }

        /* -------------------------------------------------------
           12. Generic interior-page content blocks
           (breadcrumbs, sidebar, content sections)
        ------------------------------------------------------- */
        revealUp('.breadcrumb_area', '.breadcrumb_area');
        staggerUp('.single-feature, .service_single_content, .about_list li', null, 0.1);
        revealUp('.pegajoso', '.pegajoso', { x: 24, y: 0, duration: 0.6 });

        /* -------------------------------------------------------
           13. Nosotros — misión section & organigrama
        ------------------------------------------------------- */
        var misionEl = document.querySelector('.mision');
        if (misionEl) {
            revealUp(misionEl, misionEl, { y: 26, duration: 0.7 });
        }
        var fondoLogo = document.querySelector('.fondoLogo');
        if (fondoLogo) {
            var flTitle = fondoLogo.querySelector('h2');
            var flImg   = fondoLogo.querySelector('img');
            if (flTitle) {
                gsap.from(flTitle, {
                    opacity: 0, x: -38, duration: 0.72,
                    scrollTrigger: { trigger: fondoLogo, start: 'top 78%', once: true }
                });
            }
            if (flImg) {
                gsap.from(flImg, {
                    opacity: 0, x: 38, duration: 0.72, delay: 0.14,
                    scrollTrigger: { trigger: fondoLogo, start: 'top 78%', once: true }
                });
            }
        }

        /* -------------------------------------------------------
           Titulación — ficha step cards
        ------------------------------------------------------- */
        var fichasEl = document.querySelector('.fichas');
        if (fichasEl) {
            staggerUp('.fichas .col-lg-4, .fichas .col-6', fichasEl, 0.11);
        }

        /* -------------------------------------------------------
           Novedades — comunicados card grid
        ------------------------------------------------------- */
        gsap.utils.toArray('.novedadesCard .card').forEach(function (card, i) {
            gsap.from(card, {
                opacity: 0, y: 32, duration: 0.55,
                delay: (i % 2) * 0.1,
                scrollTrigger: { trigger: card, start: 'top 85%', once: true }
            });
        });

        /* -------------------------------------------------------
           13. Footer widgets stagger
        ------------------------------------------------------- */
        var footerTop = document.querySelector('.footer_top');
        if (footerTop) {
            staggerUp('.footer_widget', footerTop, 0.15);
        }

        /* -------------------------------------------------------
           14. Nosotros — value cards (Misión / Visión / Propósito)
        ------------------------------------------------------- */
        staggerUp('.value-card, .nosotros-card', null, 0.13);

    });

})();
