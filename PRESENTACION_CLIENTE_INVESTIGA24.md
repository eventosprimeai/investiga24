# INVESTIGA24 — Dossier de Entrega y Presentación de la Plataforma Web

**Documento:** Dossier Técnico, Funcional y Legal de Entrega  
**Plataforma:** [investiga24.com](https://investiga24.com)  
**Repositorio GitHub:** [eventosprimeai/investiga24](https://github.com/eventosprimeai/investiga24)  
**Versión:** 1.0 — Producción  
**Fecha:** Agosto 2026  

---

## 1. Resumen Ejecutivo y Enfoque Estratégico

**INVESTIGA24** es una plataforma web de alta gama concebida para posicionar un despacho de **investigación privada, seguridad corporativa y obtención de pruebas con validez judicial**.

### Pilares del Proyecto
1. **Autoridad y Sobriedad:** Diseño visual obsidian dark-glassmorphism que transmite máxima discreción, rigor forense y serenidad institucional.
2. **Empatía y Conversión Discreta:** Orientada al cliente en momentos de alta vulnerabilidad o sospecha (fraude, infidelidad, custodia, bajas laborales), priorizando la reducción de ansiedad y la facilidad de contacto seguro.
3. **Blindaje Jurídico Integral:** Redacción y arquitectura legal adaptadas a la Ley de Seguridad Privada (Ley 5/2014) y al Reglamento General de Protección de Datos (RGPD / LOPDGDD).
4. **Infraestructura de Notificaciones Automatizada:** Integración directa con **Resend API** para el despacho de alertas de consultas a `publicidad553@gmail.com` y acuses de recibo institucionales desde `consulta@investiga24.com`.

---

## 2. Arquitectura de Navegación y Páginas Desarrolladas

La plataforma está compuesta por **9 páginas independientes** y un motor API backend optimizado en PHP:

```
web/
├── index.html               ← Portada principal con captación directa
├── servicios.html           ← Catálogo de áreas con preselección en formularios
├── como-trabajamos.html     ← Metodología de investigación, fases y entregables
├── sobre-nosotros.html      ← Rigor deontológico, habilitación y principios
├── conocimiento.html        ← Centro de divulgación, guías y preguntas frecuentes
├── consulta.html            ← Portal de intake confidencial y evaluación de casos
│
├── aviso-legal.html         ← Blindaje regulatorio, habilitación TIP y competencias
├── privacidad.html          ← Política de privacidad RGPD, interés legítimo y custodia
├── cookies.html             ← Política técnica de cookies (sin tracking invasivo)
│
├── api/
│   ├── send-email.php       ← Controlador backend de envío vía Resend API
│   ├── config.php           ← Credenciales privadas y correo de destino (Ignorado en Git)
│   └── config.example.php   ← Plantilla pública para el repositorio
│
├── css/
│   └── style.css            ← Sistema de diseño completo (Dark Theme, Glassmorphism, Forms)
├── js/
│   └── main.js              ← Control de formularios, interactividad y preselecciones
└── img/
    ├── logo-investiga24.png
    ├── hero-bg.png
    └── contacto-investiga24.jpg ← Gráfica oficial con número de contacto
```

---

## 3. Sistema de Captación y Calificación de Consultas

Para que el despacho reciba información cualificada y suficiente para valorar la viabilidad del caso antes de establecer contacto, se diseñó un protocolo de toma de datos estructurado:

### Campos Solicitados en los Formularios

| Campo | Justificación Profesional | Opciones / Formato |
| :--- | :--- | :--- |
| **Nombre o Alias** | Permite al consultante mantener anonimato inicial si siente temor o desconfianza. | Texto libre (*"Ej. Laura / Anónimo"*). |
| **Teléfono / WhatsApp** | Canal crítico e inmediato para contactar con discreción. | Formato internacional (`+593 96 380 9259`). |
| **Correo Electrónico** | Envío del acuse de recibo y canal alternativo de respuesta. | Email sanitizado. |
| **Ciudad / Ubicación** | Vital para valorar la logística de campo y jurisdicción. | Texto libre (*"Ej. Guayaquil, Quito, Madrid..."*). |
| **Área de Actuación** | Clasificación exacta de la tipología del servicio. | Desplegable estructurado: <br>• Investigación personal y familiar<br>• Custodia de menores / Régimen de visitas<br>• Patrimonio y divorcios<br>• Fraude empresarial y riesgos internos<br>• Bajas laborales simuladas<br>• Competencia desleal y fuga de información<br>• Informes periciales judiciales<br>• Localización de personas o bienes |
| **Nivel de Urgencia** | Priorización de la respuesta operativa del equipo. | • *Alta (Urgente 24-48h)*<br>• *Media (Próximos días)*<br>• *Informativa (Valoración previa / dudas)* |
| **Canal de Contacto Preferido** | Respeto a la vía más segura para el usuario. | *WhatsApp (Recomendado) / Llamada telefónica / Email / Indiferente*. |
| **Franja Horaria Segura** | Evita poner en compromiso al cliente en horarios no deseados. | *Mañana (09:00 - 14:00) / Tarde (14:00 - 19:00) / Noche (19:00 - 23:00) / Cualquier hora*. |
| **Descripción de los Hechos** | Exposición preliminar de la situación. | Cuadro de texto amplio con advertencia de discreción. |
| **Consentimiento RGPD** | Exigencia legal de tratamiento confidencial. | Checkbox obligatorio con enlace a la Política de Privacidad. |

### Ubicaciones de los Formularios
1. **Formulario en Portada (`index.html#contacto`):** Sección dual con acceso telefónico/WhatsApp inmediato en el panel izquierdo y formulario integrado en el panel derecho.
2. **Formulario Dedicado (`consulta.html`):** Vista especializada para usuarios que requieren orientación exhaustiva.
3. **Preselección Inteligente en Servicios (`servicios.html`):** Al hacer clic en *"Consulta confidencial sobre tu caso"* en cualquier servicio, la web abre `consulta.html?tipo=[servicio]` seleccionando automáticamente dicha categoría.

---

## 4. Notificaciones y Flujo de Correo Electrónico (Resend API)

El sistema opera con dos flujos de comunicación automatizados con diseño **Memorándum Corporativo** (100% libre de emojis o informalidades):

### A. Correo de Notificación Interna (Llega a `publicidad553@gmail.com`)
* **Asunto:** `INVESTIGA24 | Nueva Consulta: [Área de Actuación] - [Nombre/Alias]`
* **Estructura:**
  * Cabecera institucional con insignia `NUEVA CONSULTA`.
  * Metadatos: Origen (*Home Page* o *Página de Consulta*) y fecha/hora exacta.
  * Tabla ejecutiva con todos los campos sanitizados.
  * Bloque destacado con el detalle del caso.
  * **Botones de Acción Inmediata:**
    * *Contactar por WhatsApp* (abre chat directo con el número del consultante).
    * *Responder por Email*.
  * Cláusula legal de secreto profesional.

### B. Correo de Confirmación para el Cliente (Desde `consulta@investiga24.com`)
* **Asunto:** `INVESTIGA24 | Hemos recibido tu consulta confidencial`
* **Contenido:**
  * Mensaje formal y tranquilizador confirmando la recepción.
  * Compromiso de análisis y respuesta en menos de 24 horas.
  * Recordatorio del amparo bajo secreto profesional.
  * Canales de atención urgente (+593 96 380 9259 y WhatsApp).

---

## 5. Datos Oficiales de Contacto y Marca

* **Línea Telefónica Directa:** `+593 96 380 9259`
* **WhatsApp Oficial:** `https://wa.me/593963809259` (Visual: `096 380 9259`)
* **Correo de Envío / Dominio:** `consulta@investiga24.com` (Dominio verificado en Resend con SPF, DKIM, MX y DMARC).
* **Correo Receptor de Consultas:** `publicidad553@gmail.com`
* **Pieza Gráfica Oficial:** [web/img/contacto-investiga24.jpg](file:///c:/Users/hp/OneDrive/Documentos/Eventos%20Prime/00%20-%20Contratistas/Investiga24/web/img/contacto-investiga24.jpg)

---

## 6. Blindaje Jurídico y Cumplimiento Normativo

La plataforma cuenta con 3 páginas legales redactadas bajo estándares estrictos de derecho procesal y seguridad privada:

1. **Aviso Legal ([aviso-legal.html](file:///c:/Users/hp/OneDrive/Documentos/Eventos%20Prime/00%20-%20Contratistas/Investiga24/web/aviso-legal.html)):**
   * Habilitación profesional conforme a la Ley 5/2014 de Seguridad Privada.
   * Exigencia inexcusable de **interés legítimo acreditado** para aceptar cualquier investigación.
   * Prohibición absoluta de investigación sobre vida íntima desarrollada en domicilios o espacios reservados (Art. 48.3).
   * Deber de reserva y secreto profesional (Art. 50).
2. **Política de Privacidad ([privacidad.html](file:///c:/Users/hp/OneDrive/Documentos/Eventos%20Prime/00%20-%20Contratistas/Investiga24/web/privacidad.html)):**
   * Tratamiento diferenciado para: Usuarios web, Clientes contratantes y **Sujetos Investigados**.
   * Base jurídica para investigados: Interés legítimo (Art. 6.1.f RGPD) y habilitación legal expresa (Ley 5/2014).
   * **Exención de notificación previa al investigado** amparada en el Art. 14.5.b del RGPD (la información previa frustraría la finalidad de la investigación judicial/privada).
   * Custodia de datos y Libro-Registro Oficial durante 3 a 5 años según exigencias del Ministerio del Interior / Autoridades competentes.
3. **Política de Cookies ([cookies.html](file:///c:/Users/hp/OneDrive/Documentos/Eventos%20Prime/00%20-%20Contratistas/Investiga24/web/cookies.html)):**
   * Uso exclusivo de cookies técnicas y de sesión esenciales. Sin rastreadores intrusivos de terceros.

---

## 7. Instrucciones para Despliegue en Servidor (Hostinger)

El proyecto está listo para ser publicado en el hosting oficial mediante los siguientes pasos:

1. **Subir archivos a Hostinger:**
   * Ingresar a **Hostinger $\rightarrow$ Administrador de Archivos (File Manager)**.
   * Navegar a la carpeta pública del dominio: `public_html/`.
   * Subir la totalidad del contenido que se encuentra dentro de la carpeta local `web/`.
2. **Verificar el archivo de credenciales (`web/api/config.php`):**
   * Comprobar que en `public_html/api/config.php` consten los siguientes valores:
     ```php
     <?php
     define('RESEND_API_KEY', 're_TU_API_KEY_DE_RESEND');
     define('FROM_EMAIL',     'consulta@investiga24.com');
     define('NOTIFY_EMAIL',   'publicidad553@gmail.com');
     define('BRAND_NAME',     'INVESTIGA24');
     ```
3. **Prueba de Funcionamiento:**
   * Ingresar a `https://investiga24.com`.
   * Rellenar el formulario de prueba en portada o en `/consulta.html`.
   * Verificar la llegada inmediata de la ficha técnica a `publicidad553@gmail.com`.

---

**INVESTIGA24** &middot; *Rigor, Discreción y Validez Judicial.*
