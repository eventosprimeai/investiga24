/* ============================================
   INVESTIGA24 — Main JavaScript
   Interactions, animations, navigation
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

  // --- Navbar scroll effect ---
  const nav = document.getElementById('navbar');
  if (nav) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      if (currentScroll > 50) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
      lastScroll = currentScroll;
    }, { passive: true });
  }

  // --- Mobile menu toggle ---
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      navToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
      // Animate hamburger
      const spans = navToggle.querySelectorAll('span');
      if (isOpen) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
      } else {
        spans[0].style.transform = '';
        spans[1].style.opacity = '';
        spans[2].style.transform = '';
      }
    });
    // Close on link click
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        const spans = navToggle.querySelectorAll('span');
        spans[0].style.transform = '';
        spans[1].style.opacity = '';
        spans[2].style.transform = '';
      });
    });
  }

  // --- FAQ Accordion ---
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.parentElement;
      const answer = item.querySelector('.faq-answer');
      const isActive = item.classList.contains('active');

      // Close all
      document.querySelectorAll('.faq-item.active').forEach(activeItem => {
        activeItem.classList.remove('active');
        activeItem.querySelector('.faq-answer').style.maxHeight = null;
      });

      // Open clicked if not already active
      if (!isActive) {
        item.classList.add('active');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });

  // --- Scroll reveal animations ---
  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -60px 0px',
    threshold: 0.1
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        // Stagger animation
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, index * 80);
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.fade-in').forEach(el => {
    observer.observe(el);
  });

  // --- Smooth scroll for anchor links ---
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        const navHeight = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-height')) || 72;
        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;
        window.scrollTo({ top: targetPosition, behavior: 'smooth' });
      }
    });
  });

  // --- Active nav link based on current page ---
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link').forEach(link => {
    const linkPage = link.getAttribute('href');
    if (linkPage === currentPage) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });

  // --- Universal Consultation Form Handler ---
  const urlParams = new URLSearchParams(window.location.search);
  const preselectedService = urlParams.get('tipo') || urlParams.get('servicio');

  document.querySelectorAll('.consult-form, #consultForm, #homeConsultForm').forEach(form => {
    // URL preselection for dropdown
    const selectElem = form.querySelector('select[name="tipo"]');
    if (selectElem && preselectedService) {
      const matchedOption = Array.from(selectElem.options).find(opt => 
        opt.value === preselectedService || opt.value.toLowerCase().includes(preselectedService.toLowerCase())
      );
      if (matchedOption) {
        selectElem.value = matchedOption.value;
      }
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : 'Enviar';
      
      const nombre = (form.querySelector('[name="nombre"]')?.value || '').trim() || 'Anónimo';
      const telefono = (form.querySelector('[name="telefono"]')?.value || '').trim();
      const email = (form.querySelector('[name="email"]')?.value || '').trim();
      const tipo = form.querySelector('[name="tipo"]')?.value || 'general';
      const ubicacion = (form.querySelector('[name="ubicacion"]')?.value || '').trim() || 'No especificada';
      const urgencia = form.querySelector('[name="urgencia"]')?.value || 'consulta';
      const mensaje = (form.querySelector('[name="mensaje"]')?.value || '').trim() || '—';
      
      const preferencia = form.querySelector('[name="preferencia"]:checked')?.value || 
                          form.querySelector('[name="preferencia"]')?.value || 'whatsapp';
      const horario = form.querySelector('[name="horario"]:checked')?.value || 
                      form.querySelector('[name="horario"]')?.value || 'indiferente';
      const origen = form.getAttribute('data-origen') || window.location.pathname.split('/').pop() || 'Web';

      // Validación: teléfono o email requerido
      if (!telefono && !email) {
        alert('Por favor, indica al menos un teléfono (o WhatsApp) o un correo electrónico para poder contactarte.');
        const telInput = form.querySelector('[name="telefono"]');
        if (telInput) telInput.focus();
        return;
      }

      // Validación de privacidad
      const privacyCheck = form.querySelector('[name="privacidad"]');
      if (privacyCheck && !privacyCheck.checked) {
        alert('Por favor, acepta la Política de Privacidad para continuar.');
        privacyCheck.focus();
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Enviando solicitud confidencial...';
      }

      const payload = {
        nombre,
        telefono: telefono || '—',
        email: email || '—',
        tipo,
        ubicacion,
        preferencia,
        horario,
        urgencia,
        mensaje,
        origen
      };

      try {
        const res = await fetch('api/send-email.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (json.success) {
          if (submitBtn) {
            submitBtn.style.background = 'var(--color-serenity)';
            submitBtn.style.borderColor = 'var(--color-serenity)';
            submitBtn.style.color = '#ffffff';
            submitBtn.innerHTML = 'Solicitud enviada con éxito — Te contactaremos con total discreción';
          }
          form.reset();
        } else {
          throw new Error(json.error || 'Error en el envío');
        }
      } catch (err) {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
        alert('Hubo una incidencia técnica al procesar tu solicitud. Por favor, escríbenos directamente a investiga24siempre@gmail.com o contáctanos por WhatsApp al 099-212-4169 (+593 99 212 4169).');
      }
    });
  });

  // --- Floating WhatsApp Widget ---
  const initWhatsAppWidget = () => {
    if (document.getElementById('waFloatContainer')) return;

    const waContainer = document.createElement('div');
    waContainer.id = 'waFloatContainer';
    waContainer.className = 'wa-float-container';
    waContainer.innerHTML = `
      <!-- Popup Card -->
      <div class="wa-popup-card" id="waPopupCard" aria-hidden="true">
        <div class="wa-popup-header">
          <div class="wa-header-icon">
            <svg viewBox="0 0 32 32" width="28" height="28" fill="currentColor">
              <path d="M16.01 2.002C8.28 2.002 2 8.282 2 16.013c0 2.68.75 5.18 2.05 7.32L2 30l6.87-1.99c2.06 1.18 4.44 1.86 7.14 1.86 7.73 0 14.01-6.28 14.01-14.01s-6.28-13.858-14.01-13.858zm8.21 19.86c-.34.97-1.7 1.79-2.77 2.02-.73.15-1.68.27-4.88-1.06-4.1-1.69-6.73-5.86-6.93-6.13-.2-.27-1.67-2.22-1.67-4.24 0-2.02 1.05-3.01 1.43-3.42.38-.41.83-.51 1.11-.51.27 0 .55 0 .79.01.25.02.59-.1.92.71.34.83 1.17 2.85 1.27 3.06.1.2.17.45.03.72-.14.27-.21.44-.42.68-.21.24-.44.53-.63.71-.21.21-.43.44-.19.86.25.41 1.09 1.8 2.34 2.91 1.61 1.44 2.97 1.89 3.39 2.1.42.21.67.17.92-.1.25-.28 1.07-1.25 1.36-1.68.29-.43.58-.36.97-.21.39.14 2.47 1.16 2.89 1.37.42.21.7.31.8.49.1.17.1.99-.24 1.96z"/>
            </svg>
          </div>
          <div class="wa-header-text">
            <h4 class="wa-header-title">Inicia una conversación</h4>
            <p class="wa-header-sub">Hola, selecciona al agente para iniciar una conversación en WhatsApp</p>
          </div>
        </div>
        <div class="wa-popup-body">
          <div class="wa-reply-time">El equipo responde normalmente en unos minutos.</div>
          <a href="https://wa.me/593992124169?text=Hola,%20quisiera%20hacer%20una%20consulta%20confidencial." target="_blank" rel="noopener" class="wa-agent-card">
            <div class="wa-agent-avatar">
              <svg viewBox="0 0 44 44" width="44" height="44" fill="none">
                <circle cx="22" cy="22" r="22" fill="#2563eb"/>
                <path d="M22 13a5 5 0 100 10 5 5 0 000-10zm0 12.5c-4.2 0-7.8 2.2-7.8 5v1.5h15.6v-1.5c0-2.8-3.6-5-7.8-5z" fill="#ffffff"/>
                <circle cx="31" cy="31" r="5" fill="#22c55e" stroke="#ffffff" stroke-width="1.8"/>
              </svg>
            </div>
            <div class="wa-agent-info">
              <div class="wa-agent-name">INVESTIGA24</div>
              <div class="wa-agent-role">Atención al Cliente</div>
            </div>
            <div class="wa-agent-btn">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="#25D366">
                <path d="M12.01 2c-5.5 0-9.98 4.48-9.98 9.99 0 1.91.53 3.69 1.46 5.21L2 22l4.94-1.42c1.47.84 3.17 1.33 5.07 1.33 5.51 0 9.99-4.48 9.99-9.99 0-5.51-4.48-9.92-9.99-9.92zm5.85 14.15c-.24.69-1.21 1.27-1.97 1.43-.52.11-1.2.19-3.47-.75-2.92-1.2-4.79-4.17-4.94-4.36-.14-.19-1.19-1.58-1.19-3.02s.75-2.14 1.02-2.43c.27-.29.59-.36.79-.36.2 0 .39 0 .56.01.18.01.42-.07.66.5.24.59.83 2.03.9 2.18.07.15.12.32.02.51-.1.2-.15.31-.3.48-.15.17-.31.38-.45.5-.15.15-.31.31-.13.61.18.29.78 1.28 1.67 2.07 1.15 1.02 2.11 1.34 2.41 1.49.3.15.48.12.65-.07.18-.2.76-.89.97-1.2.2-.31.41-.26.69-.15.28.1 1.76.83 2.06.98.3.15.5.22.57.35.07.12.07.7-.17 1.39z"/>
              </svg>
            </div>
          </a>
        </div>
      </div>

      <!-- Toggle Button & Tooltip -->
      <div class="wa-float-btn-wrapper">
        <div class="wa-float-tooltip" id="waFloatTooltip">Consulta Directa</div>
        <button class="wa-float-btn" id="waFloatBtn" aria-label="Abrir chat de WhatsApp">
          <span class="wa-icon-open">
            <svg viewBox="0 0 32 32" width="34" height="34" fill="#ffffff">
              <path d="M16.01 2.002C8.28 2.002 2 8.282 2 16.013c0 2.68.75 5.18 2.05 7.32L2 30l6.87-1.99c2.06 1.18 4.44 1.86 7.14 1.86 7.73 0 14.01-6.28 14.01-14.01s-6.28-13.858-14.01-13.858zm8.21 19.86c-.34.97-1.7 1.79-2.77 2.02-.73.15-1.68.27-4.88-1.06-4.1-1.69-6.73-5.86-6.93-6.13-.2-.27-1.67-2.22-1.67-4.24 0-2.02 1.05-3.01 1.43-3.42.38-.41.83-.51 1.11-.51.27 0 .55 0 .79.01.25.02.59-.1.92.71.34.83 1.17 2.85 1.27 3.06.1.2.17.45.03.72-.14.27-.21.44-.42.68-.21.24-.44.53-.63.71-.21.21-.43.44-.19.86.25.41 1.09 1.8 2.34 2.91 1.61 1.44 2.97 1.89 3.39 2.1.42.21.67.17.92-.1.25-.28 1.07-1.25 1.36-1.68.29-.43.58-.36.97-.21.39.14 2.47 1.16 2.89 1.37.42.21.7.31.8.49.1.17.1.99-.24 1.96z"/>
            </svg>
          </span>
          <span class="wa-icon-close">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </span>
        </button>
      </div>
    `;

    document.body.appendChild(waContainer);

    const btn = document.getElementById('waFloatBtn');
    const popup = document.getElementById('waPopupCard');
    const tooltip = document.getElementById('waFloatTooltip');

    const togglePopup = (e) => {
      e.stopPropagation();
      const isActive = waContainer.classList.toggle('active');
      popup.setAttribute('aria-hidden', !isActive);
      if (tooltip) {
        tooltip.style.opacity = isActive ? '0' : '1';
        tooltip.style.visibility = isActive ? 'hidden' : 'visible';
      }
    };

    btn.addEventListener('click', togglePopup);
    if (tooltip) {
      tooltip.addEventListener('click', togglePopup);
    }

    document.addEventListener('click', (e) => {
      if (!waContainer.contains(e.target) && waContainer.classList.contains('active')) {
        waContainer.classList.remove('active');
        popup.setAttribute('aria-hidden', 'true');
        if (tooltip) {
          tooltip.style.opacity = '1';
          tooltip.style.visibility = 'visible';
        }
      }
    });
  };

  initWhatsAppWidget();

});
