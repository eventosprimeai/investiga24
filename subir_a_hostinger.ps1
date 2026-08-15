# ==============================================================================
# SCRIPT DE SUBIDA AUTOMÁTICA POR FTP A HOSTINGER — INVESTIGA24.COM
# ==============================================================================

param(
    [Parameter(Mandatory=$false)]
    [string]$FtpPassword
)

$ftpHost = "45.132.157.29" # o investiga24.com
$ftpUser = "u130691975"
$localPath = "$PSScriptRoot\web"
$remoteBase = "ftp://$ftpHost/public_html"

if (-not $FtpPassword) {
    $FtpPassword = Read-Host -Prompt "Introduce la contraseña de tu cuenta/FTP de Hostinger"
}

Write-Host "`n🚀 Iniciando subida a Hostinger (investiga24.com)..." -ForegroundColor Cyan
Write-Host "Directorio local: $localPath"
Write-Host "Destino remoto: $remoteBase`n"

function Upload-FtpDirectory($localDir, $remoteUrl, $user, $pass) {
    # Crear directorio remoto si no existe
    try {
        $makeDirReq = [System.Net.FtpWebRequest]::Create($remoteUrl)
        $makeDirReq.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $makeDirReq.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $makeDirReq.UseBinary = $true
        $makeDirReq.KeepAlive = $false
        $resp = $makeDirReq.GetResponse()
        $resp.Close()
    } catch {
        # Si ya existe, ignoramos el error
    }

    # Subir archivos
    $files = Get-ChildItem -Path $localDir -File
    foreach ($file in $files) {
        $destUrl = "$remoteUrl/$($file.Name)"
        Write-Host "  -> Subiendo: $($file.Name)..." -ForegroundColor Yellow -NoNewline
        try {
            $webclient = New-Object System.Net.WebClient
            $webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
            $webclient.UploadFile($destUrl, $file.FullName)
            Write-Host " [OK]" -ForegroundColor Green
        } catch {
            Write-Host " [ERROR: $_]" -ForegroundColor Red
        }
    }

    # Subir subdirectorios
    $subDirs = Get-ChildItem -Path $localDir -Directory
    foreach ($dir in $subDirs) {
        Upload-FtpDirectory $dir.FullName "$remoteUrl/$($dir.Name)" $user $pass
    }
}

Upload-FtpDirectory $localPath $remoteBase $ftpUser $FtpPassword

Write-Host "`n✅ ¡Subida completada con éxito! Revisa https://investiga24.com" -ForegroundColor Green
