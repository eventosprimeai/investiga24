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
$reply_to   = $email ?: FROM_EMAIL;

// ── Email para el equipo INVESTIGA24 ─────────────────────────────────────────
$html_notif = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Nueva consulta</title></head>
<body style="font-family:'Helvetica Neue',Arial,sans-serif;background:#0a0e1a;color:#f9fafb;padding:32px;">
  <div style="max-width:600px;margin:0 auto;background:#111827;border-radius:16px;padding:40px;border:1px solid rgba(255,255,255,0.08);">
    <div style="text-align:center;margin-bottom:32px;">
      <span style="font-size:24px;font-weight:800;color:#f9fafb;">INVESTIGA<span style="color:#d4a853;">24</span></span>
      <p style="color:#d4a853;font-size:12px;letter-spacing:3px;text-transform:uppercase;margin:8px 0 0;">Nueva consulta confidencial</p>
    </div>
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><td style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Nombre</span><br>
        <strong style="color:#f9fafb;font-size:16px;">$nombre</strong>
      </td></tr>
      <tr><td style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Tipo de consulta</span><br>
        <strong style="color:#d4a853;font-size:16px;">$tipo_label</strong>
      </td></tr>
      <tr><td style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Teléfono</span><br>
        <a href="tel:$telefono" style="color:#3b82f6;font-size:16px;text-decoration:none;">$telefono</a>
      </td></tr>
      <tr><td style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Email</span><br>
        <a href="mailto:$email" style="color:#3b82f6;font-size:16px;text-decoration:none;">$email</a>
      </td></tr>
      <tr><td style="padding:16px 0 0;">
        <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Mensaje</span><br>
        <p style="color:#f9fafb;font-size:15px;line-height:1.7;margin:8px 0 0;background:rgba(255,255,255,0.04);padding:16px;border-radius:8px;border-left:3px solid #d4a853;">$mensaje</p>
      </td></tr>
    </table>
    <div style="margin-top:32px;padding:16px;background:rgba(212,168,83,0.08);border-radius:8px;text-align:center;">
      <p style="color:#9ca3af;font-size:12px;margin:0;">Recibida el $fecha &nbsp;·&nbsp; Responder en menos de 24h</p>
    </div>
  </div>
</body></html>
HTML;

// ── Email de confirmación para el usuario ────────────────────────────────────
$html_confirm = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Hemos recibido tu consulta</title></head>
<body style="font-family:'Helvetica Neue',Arial,sans-serif;background:#0a0e1a;color:#f9fafb;padding:32px;">
  <div style="max-width:600px;margin:0 auto;background:#111827;border-radius:16px;padding:40px;border:1px solid rgba(255,255,255,0.08);">
    <div style="text-align:center;margin-bottom:40px;">
      <span style="font-size:28px;font-weight:800;color:#f9fafb;">INVESTIGA<span style="color:#d4a853;">24</span></span>
    </div>
    <h1 style="color:#f9fafb;font-size:22px;font-weight:600;margin:0 0 16px;">Hemos recibido tu consulta</h1>
    <p style="color:#9ca3af;line-height:1.8;font-size:15px;">Hola $nombre,</p>
    <p style="color:#9ca3af;line-height:1.8;font-size:15px;">Gracias por contactarnos. Hemos recibido tu consulta y te responderemos en menos de 24 horas.</p>
    <p style="color:#9ca3af;line-height:1.8;font-size:15px;">Recuerda que todo lo que nos has compartido está protegido por nuestro deber de confidencialidad profesional.</p>
    <div style="margin:32px 0;padding:24px;background:rgba(255,255,255,0.04);border-radius:12px;border-left:3px solid #d4a853;">
      <p style="color:#d4a853;font-size:13px;text-transform:uppercase;letter-spacing:2px;margin:0 0 8px;">Mientras tanto</p>
      <p style="color:#f9fafb;font-size:15px;margin:0;line-height:1.7;">Si tu situación requiere atención más urgente, puedes llamarnos al <a href="tel:+593963809259" style="color:#d4a853;text-decoration:none;">+593 96 380 9259</a> o escribirnos por <a href="https://wa.me/593963809259" style="color:#d4a853;text-decoration:none;">WhatsApp</a>.</p>
    </div>
    <p style="color:#6b7280;font-size:13px;line-height:1.7;">La discreción comienza desde el primer momento. Nuestro trabajo termina cuando vuelves a sentir que puedes decidir con tranquilidad.</p>
    <p style="color:#6b7280;font-size:13px;margin-top:32px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.06);">
      &copy; INVESTIGA24 &nbsp;·&nbsp; <a href="https://investiga24.com" style="color:#d4a853;text-decoration:none;">investiga24.com</a>
    </p>
  </div>
</body></html>
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

// 1. Notificación al equipo
$ok1 = send_resend(
    NOTIFY_EMAIL,
    "📋 Nueva consulta: $tipo_label — $nombre",
    $html_notif,
    $reply_to
);

// 2. Confirmación al usuario (solo si dio email válido)
$ok2 = true;
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $ok2 = send_resend(
        $email,
        'Hemos recibido tu consulta — INVESTIGA24',
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
