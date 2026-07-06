/* ============================================
   Mente em Equilíbrio - Script Principal
   (versão limpa – sem subpáginas, sem temas, sem erros)
   ============================================ */

   (function() {
    // ---------- LOADING SCREEN ----------
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        window.addEventListener('load', () => {
            setTimeout(() => loadingScreen.classList.add('hidden'), 600);
        });
    }

    // ---------- CUSTOM CURSOR ----------
    const cursor = document.getElementById('customCursor');
    const cursorDot = document.getElementById('customCursorDot');
    if (cursor && cursorDot && window.innerWidth > 768) {
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            cursorDot.style.left = e.clientX + 'px';
            cursorDot.style.top = e.clientY + 'px';
        });
        const hoverTargets = document.querySelectorAll(
            'a, button, .btn, .card, .solution-card, .filter-pill, .stat-item, .dashboard-card, .navbar-toggle, .back-to-top'
        );
        hoverTargets.forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.classList.add('hovering');
                cursorDot.classList.add('hovering');
            });
            el.addEventListener('mouseleave', () => {
                cursor.classList.remove('hovering');
                cursorDot.classList.remove('hovering');
            });
        });
    }

    // ---------- FLOATING PARTICLES ----------
    const particlesContainer = document.getElementById('floatingParticles');
    if (particlesContainer) {
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.classList.add('floating-particle');
            const size = Math.random() * 3 + 1.5;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 20 + 10) + 's';
            particle.style.animationDelay = (Math.random() * 15) + 's';
            particle.style.opacity = Math.random() * 0.4 + 0.1;
            particlesContainer.appendChild(particle);
        }
    }

    // ---------- NAVBAR ----------
    const navbar = document.getElementById('navbar');
    const navbarLinks = document.getElementById('navbarLinks');
    const navbarToggle = document.getElementById('navbarToggle');
    const navOverlay = document.getElementById('navOverlay');
    const navLinks = document.querySelectorAll('[data-nav]');

    function closeNav() {
        if (navbarLinks) navbarLinks.classList.remove('open');
        if (navOverlay) navOverlay.classList.remove('open');
    }

    if (navbarToggle) {
        navbarToggle.addEventListener('click', () => {
            if (!navbarLinks) return;
            const isOpen = navbarLinks.classList.contains('open');
            isOpen ? closeNav() : (navbarLinks.classList.add('open'), navOverlay && navOverlay.classList.add('open'));
        });
    }
    if (navOverlay) {
        navOverlay.addEventListener('click', closeNav);
    }
    navLinks.forEach(link => {
        link.addEventListener('click', closeNav);
    });

    // ---------- SCROLL SPY ----------
    const sections = document.querySelectorAll('section[id]');
    if (navbar) {
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            navbar.classList.toggle('scrolled', scrollY > 50);
            let current = '';
            sections.forEach(section => {
                if (scrollY >= section.offsetTop - 150) current = section.getAttribute('id');
            });
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) link.classList.add('active');
            });
        });
    }

    // ---------- BACK TO TOP ----------
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 500);
        });
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // ---------- REVEAL ANIMATIONS ----------
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealElements.forEach(el => revealObserver.observe(el));

    // ---------- COUNTER ANIMATION ----------
    const counterElements = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseFloat(el.getAttribute('data-target'));
                const decimals = parseInt(el.getAttribute('data-decimals')) || 0;
                const duration = 2000;
                const startTime = performance.now();

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 4);
                    const current = target * eased;
                    el.textContent = decimals > 0 ? current.toFixed(decimals) : Math.round(current).toLocaleString('pt-BR');
                    if (progress < 1) requestAnimationFrame(updateCounter);
                    else el.textContent = decimals > 0 ? target.toFixed(decimals) : Math.round(target).toLocaleString('pt-BR');
                }
                requestAnimationFrame(updateCounter);
                counterObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    counterElements.forEach(el => counterObserver.observe(el));

    // ---------- CHARTS (Chart.js) ----------
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = '#b8b8d0';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,15,45,0.9)';
        Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.15)';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.titleFont = { weight: '600', size: 13 };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };

        const blue = '#4a6cf7';
        const purple = '#7c3aed';
        const cyan = '#06b6d4';
        const teal = '#14b8a6';
        const rose = '#f43f5e';
        const gridColor = 'rgba(255,255,255,0.06)';
        const textColor = '#b8b8d0';

        // Linha
        const ctxLinha = document.getElementById('chartLinha');
        if (ctxLinha) {
            new Chart(ctxLinha, {
                type: 'line',
                data: {
                    labels: ['2012', '2015', '2019'],
                    datasets: [{
                        label: 'Total das Capitais',
                        data: [3.4, 3.8, 3.2],
                        borderColor: blue,
                        backgroundColor: 'rgba(74,108,247,0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: blue,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 7,
                        pointHoverRadius: 11,
                        tension: 0.45,
                        fill: true,
                    }, {
                        label: 'Curitiba',
                        data: [3.1, 3.7, 2.6],
                        borderColor: cyan,
                        backgroundColor: 'rgba(6,182,212,0.08)',
                        borderWidth: 3,
                        pointBackgroundColor: cyan,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 7,
                        pointHoverRadius: 11,
                        tension: 0.45,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, padding: 24, color: textColor, font: { size: 12 } } } },
                    scales: {
                        y: { beginAtZero: false, min: 2, max: 5, ticks: { callback: v => v + '%', stepSize: 0.5, color: textColor }, grid: { color: gridColor } },
                        x: { ticks: { color: textColor }, grid: { display: false } },
                    },
                },
            });
        }

        // Barras
        const ctxBarras = document.getElementById('chartBarras');
        if (ctxBarras) {
            new Chart(ctxBarras, {
                type: 'bar',
                data: {
                    labels: ['Curitiba', 'São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Fortaleza', 'Salvador'],
                    datasets: [{
                        label: '% sem amigos próximos (2019)',
                        data: [2.6, 2.6, 2.1, 3.1, 3.8, 5.7],
                        backgroundColor: [
                            'rgba(6,182,212,0.8)', 'rgba(74,108,247,0.5)', 'rgba(74,108,247,0.5)',
                            'rgba(74,108,247,0.5)', 'rgba(74,108,247,0.5)', 'rgba(74,108,247,0.5)'
                        ],
                        borderColor: [cyan, blue, blue, blue, blue, blue],
                        borderWidth: 2,
                        borderRadius: 10,
                        borderSkipped: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: false, min: 1, max: 7, ticks: { callback: v => v + '%', color: textColor }, grid: { color: gridColor } },
                        x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
                    },
                },
            });
        }

        // Rosca
        const ctxRosca = document.getElementById('chartRosca');
        if (ctxRosca) {
            new Chart(ctxRosca, {
                type: 'doughnut',
                data: {
                    labels: ['Homens (4,0%)', 'Mulheres (2,7%)'],
                    datasets: [{
                        data: [4.0, 2.7],
                        backgroundColor: ['rgba(74,108,247,0.75)', 'rgba(124,58,237,0.75)'],
                        borderColor: [blue, purple],
                        borderWidth: 2,
                        hoverBorderWidth: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, padding: 20, color: textColor, font: { size: 12 } } } },
                },
            });
        }

        // Radar
        const ctxRadar = document.getElementById('chartRadar');
        if (ctxRadar) {
            new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Solidão', 'Insatisfação', 'Ansiedade', 'Baixa Autoestima', 'Isolamento', 'Dificuldade de Concentração'],
                    datasets: [{
                        label: 'Com amigos próximos',
                        data: [15, 20, 30, 18, 12, 22],
                        borderColor: blue,
                        backgroundColor: 'rgba(74,108,247,0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: blue,
                    }, {
                        label: 'Sem amigos próximos',
                        data: [85, 70, 65, 72, 80, 55],
                        borderColor: rose,
                        backgroundColor: 'rgba(244,63,94,0.08)',
                        borderWidth: 2,
                        pointBackgroundColor: rose,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, padding: 20, color: textColor, font: { size: 10 } } } },
                    scales: { r: { min: 0, max: 100, ticks: { display: false, stepSize: 20 }, grid: { color: 'rgba(255,255,255,0.08)' }, pointLabels: { color: textColor, font: { size: 10 } } } },
                },
            });
        }

        // Área
        const ctxArea = document.getElementById('chartArea');
        if (ctxArea) {
            new Chart(ctxArea, {
                type: 'line',
                data: {
                    labels: ['2012', '2015', '2019'],
                    datasets: [{
                        label: 'Escolas Públicas',
                        data: [4.0, 4.5, 3.7],
                        borderColor: purple,
                        backgroundColor: 'rgba(124,58,237,0.15)',
                        borderWidth: 3,
                        pointBackgroundColor: purple,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        tension: 0.4,
                        fill: true,
                    }, {
                        label: 'Escolas Privadas',
                        data: [1.7, 2.7, 1.9],
                        borderColor: teal,
                        backgroundColor: 'rgba(20,184,166,0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: teal,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        tension: 0.4,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, padding: 24, color: textColor, font: { size: 12 } } } },
                    scales: {
                        y: { beginAtZero: false, min: 1, max: 6, ticks: { callback: v => v + '%', color: textColor }, grid: { color: gridColor } },
                        x: { ticks: { color: textColor }, grid: { display: false } },
                    },
                },
            });
        }
    }

    // ---------- FILTER PILLS ----------
    const filterPills = document.querySelectorAll('#filterPills .filter-pill');
    const dashboardCards = document.querySelectorAll('#dashboardGrid .dashboard-card');
    if (filterPills.length > 0) {
        filterPills.forEach(pill => {
            pill.addEventListener('click', () => {
                filterPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                const filter = pill.getAttribute('data-filter');
                dashboardCards.forEach(card => {
                    const cardType = card.getAttribute('data-chart-type');
                    if (filter === 'all' || cardType === filter) {
                        card.style.display = '';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        requestAnimationFrame(() => {
                            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        });
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => card.style.display = 'none', 400);
                    }
                });
            });
        });
    }

    // ---------- PARALLAX ----------
    const heroVisual = document.querySelector('.hero-svg-container');
    if (heroVisual && window.innerWidth > 768) {
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            const heroSection = document.querySelector('.hero-section');
            if (heroSection && scrollY < heroSection.offsetHeight) {
                heroVisual.style.transform = `translateY(${scrollY * 0.25}px)`;
            }
        });
    }

    console.log('🚀 Mente em Equilíbrio — Dados reais IBGE/PeNSE carregados.');
    console.log('📊 Total Capitais: 3,4% (2012) → 3,8% (2015) → 3,2% (2019)');
    console.log('🎯 Curitiba 2019: 2,6% — abaixo da média nacional.');
})();
