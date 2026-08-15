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
        alert('Hubo una incidencia técnica al procesar tu solicitud. Por favor, escríbenos directamente a consulta@investiga24.com o contáctanos por WhatsApp al +593 96 380 9259.');
      }
    });
  });

});
