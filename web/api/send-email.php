<?php
/**
 * INVESTIGA24 — Email Handler via Resend API
 * Endpoint: /api/send-email.php
 *
 * Configuración:
 *   1. Configura tu dominio en resend.com/domains → verifica investiga24.com
 *   2. Genera una API Key en resend.com/api-keys
 *   3. Reemplaza RESEND_API_KEY abajo con tu clave real
 *   4. Cambia NOTIFY_EMAIL al correo donde quieres recibir las consultas
 */

// ─── CONFIGURACIÓN ────────────────────────────────────────────────────────────
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (!defined('RESEND_API_KEY')) {
    define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: 're_YOUR_API_KEY_HERE');
}
if (!defined('FROM_EMAIL')) {
    define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'consulta@investiga24.com');
}
if (!defined('NOTIFY_EMAIL')) {
    define('NOTIFY_EMAIL', getenv('NOTIFY_EMAIL') ?: 'consulta@investiga24.com');
}
if (!defined('BRAND_NAME')) {
    define('BRAND_NAME', 'INVESTIGA24');
}
// ──────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://investiga24.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Leer body JSON
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Sanitizar inputs
$nombre   = htmlspecialchars(trim($data['nombre']   ?? 'Anónimo'), ENT_QUOTES, 'UTF-8');
$telefono = htmlspecialchars(trim($data['telefono'] ?? '—'),       ENT_QUOTES, 'UTF-8');
$email    = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$tipo     = htmlspecialchars(trim($data['tipo']    ?? '—'),         ENT_QUOTES, 'UTF-8');
$mensaje  = htmlspecialchars(trim($data['mensaje'] ?? '—'),         ENT_QUOTES, 'UTF-8');

$tipos_map = [
    'personal'    => 'Situación personal o familiar',
    'empresarial' => 'Investigación empresarial',
    'judicial'    => 'Informes judiciales o periciales',
    'localizacion'=> 'Localización de personas o bienes',
    'otro'        => 'Otra consulta',
    'no-se'       => 'No sabe aún',
];
$tipo_label = $tipos_map[$tipo] ?? $tipo;
$fecha      = date('d/m/Y H:i', time() - 5 * 3600); // UTC-5 (Ecuador)
$clean_phone = preg_replace('/[^0-9+]/', '', $telefono);
$wa_phone    = preg_replace('/[^0-9]/', '', $telefono);
$wa_link     = !empty($wa_phone) ? "https://wa.me/{$wa_phone}" : "https://wa.me/593963809259";

// ── Email de Notificación para INVESTIGA24 (Llega a NOTIFY_EMAIL) ───────────
$html_notif = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>INVESTIGA24 - Notificación de Consulta</title>
</head>
<body style="margin:0;padding:0;background-color:#0b0f19;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0f19;padding:36px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background-color:#111726;border:1px solid #1f2a3d;border-radius:10px;overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td style="padding:24px 32px;border-bottom:1px solid #1f2a3d;background-color:#161f33;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <span style="font-size:20px;font-weight:800;letter-spacing:0.5px;color:#f8fafc;">INVESTIGA<span style="color:#2d7dd2;">24</span></span>
                  </td>
                  <td align="right">
                    <span style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:1px;text-transform:uppercase;background-color:#0b0f19;border:1px solid #2d3b55;padding:6px 12px;border-radius:4px;">NUEVA CONSULTA</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Título y Fecha -->
          <tr>
            <td style="padding:28px 32px 16px 32px;">
              <h1 style="margin:0 0 6px 0;font-size:17px;font-weight:700;color:#ffffff;line-height:1.4;">Solicitud de Consulta Confidencial</h1>
              <p style="margin:0;font-size:13px;color:#94a3b8;">Registro web generado el $fecha (Hora local)</p>
            </td>
          </tr>

          <!-- Tabla de Datos -->
          <tr>
            <td style="padding:8px 32px 24px 32px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #233047;border-radius:6px;background-color:#0d121f;border-collapse:collapse;">
                <tr>
                  <td width="35%" style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Solicitante</td>
                  <td width="65%" style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#f8fafc;font-size:14px;font-weight:600;">$nombre</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Tipo de Servicio</td>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#38bdf8;font-size:14px;font-weight:600;">$tipo_label</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Teléfono</td>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#f8fafc;font-size:14px;">
                    <a href="tel:$clean_phone" style="color:#60a5fa;text-decoration:none;font-weight:600;">$telefono</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Email</td>
                  <td style="padding:14px 18px;border-bottom:1px solid #1c273a;color:#f8fafc;font-size:14px;">
                    <a href="mailto:$email" style="color:#60a5fa;text-decoration:none;">$email</a>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" style="padding:18px;background-color:#101624;">
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Mensaje / Descripción del caso:</div>
                    <div style="font-size:14px;line-height:1.7;color:#e2e8f0;white-space:pre-wrap;background-color:#090d16;padding:16px;border-radius:4px;border-left:3px solid #2d7dd2;">$mensaje</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Botones de Acción Directa -->
          <tr>
            <td style="padding:0 32px 28px 32px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="50%" style="padding-right:6px;">
                    <a href="$wa_link" target="_blank" style="display:block;background-color:#2563eb;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;padding:12px;border-radius:6px;text-align:center;">Contactar por WhatsApp</a>
                  </td>
                  <td width="50%" style="padding-left:6px;">
                    <a href="mailto:$email" style="display:block;background-color:#1e293b;color:#cbd5e1;border:1px solid #334155;text-decoration:none;font-size:13px;font-weight:600;padding:12px;border-radius:6px;text-align:center;">Responder por Email</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer Confidencialidad -->
          <tr>
            <td style="padding:18px 32px;border-top:1px solid #1f2a3d;background-color:#0d111c;text-align:center;">
              <p style="margin:0;font-size:11px;color:#64748b;line-height:1.5;">
                INVESTIGA24 &middot; Sistema de Gestión de Consultas &middot; Información estrictamente confidencial
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

// ── Email de Confirmación para el Cliente ────────────────────────────────────
$html_confirm = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Hemos recibido tu consulta - INVESTIGA24</title>
</head>
<body style="margin:0;padding:0;background-color:#0b0f19;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0f19;padding:36px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;background-color:#111726;border:1px solid #1f2a3d;border-radius:10px;overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td style="padding:24px 32px;border-bottom:1px solid #1f2a3d;background-color:#161f33;">
              <span style="font-size:20px;font-weight:800;letter-spacing:0.5px;color:#f8fafc;">INVESTIGA<span style="color:#2d7dd2;">24</span></span>
            </td>
          </tr>

          <!-- Cuerpo del mensaje -->
          <tr>
            <td style="padding:32px 32px 16px 32px;">
              <h1 style="margin:0 0 16px 0;font-size:18px;font-weight:700;color:#ffffff;line-height:1.4;">Hemos recibido tu consulta</h1>
              <p style="margin:0 0 14px 0;font-size:14px;line-height:1.8;color:#cbd5e1;">Estimado/a $nombre,</p>
              <p style="margin:0 0 14px 0;font-size:14px;line-height:1.8;color:#94a3b8;">
                Confirmamos la correcta recepción de tu mensaje. Un detective asignado a nuestro equipo analizará la información aportada y se pondrá en contacto contigo en un plazo máximo de 24 horas.
              </p>
              <p style="margin:0 0 20px 0;font-size:14px;line-height:1.8;color:#94a3b8;">
                Te recordamos que toda comunicación y datos compartidos están rigurosamente amparados por el deber de secreto profesional y la más estricta confidencialidad.
              </p>
            </td>
          </tr>

          <!-- Atención Directa -->
          <tr>
            <td style="padding:0 32px 28px 32px;">
              <div style="background-color:#0d121f;border:1px solid #233047;border-left:3px solid #2d7dd2;border-radius:6px;padding:16px;">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Canal de atención directa</div>
                <p style="margin:0;font-size:13px;line-height:1.6;color:#cbd5e1;">
                  Si tu situación requiere orientación inmediata, puedes comunicarte directamente al teléfono <a href="tel:+593963809259" style="color:#60a5fa;text-decoration:none;font-weight:600;">+593 96 380 9259</a> o escribirnos vía <a href="https://wa.me/593963809259" style="color:#60a5fa;text-decoration:none;font-weight:600;">WhatsApp</a>.
                </p>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:18px 32px;border-top:1px solid #1f2a3d;background-color:#0d111c;text-align:center;">
              <p style="margin:0 0 4px 0;font-size:11px;color:#64748b;">
                INVESTIGA24 &middot; Despacho de Investigación Privada y Seguridad
              </p>
              <p style="margin:0;font-size:11px;color:#475569;">
                <a href="https://investiga24.com" style="color:#64748b;text-decoration:none;">investiga24.com</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

// ── Enviar ambos emails vía Resend ───────────────────────────────────────────
function send_resend(string $to, string $subject, string $html, string $reply_to): bool {
    $payload = json_encode([
        'from'     => BRAND_NAME . ' <' . FROM_EMAIL . '>',
        'to'       => [$to],
        'reply_to' => $reply_to,
        'subject'  => $subject,
        'html'     => $html,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code === 200 || $code === 201;
}

// 1. Notificación al equipo (Asunto sobrio sin emojis)
$ok1 = send_resend(
    NOTIFY_EMAIL,
    "INVESTIGA24 | Nueva Consulta: $tipo_label - $nombre",
    $html_notif,
    $reply_to
);

// 2. Confirmación al usuario (solo si dio email válido)
$ok2 = true;
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $ok2 = send_resend(
        $email,
        'INVESTIGA24 | Hemos recibido tu consulta confidencial',
        $html_confirm,
        FROM_EMAIL
    );
}

if ($ok1) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al enviar. Por favor contacta por WhatsApp.']);
}

