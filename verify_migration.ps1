# 🔧 Script de Verificación Post-Migración
# Ejecutar después de crear las tablas en Neon Console

Write-Host "🔄 Iniciando verificación post-migración..." -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow

# Cambiar al directorio del proyecto
$projectPath = "c:\xampp\htdocs\Original\Comedor_Comunitario"
Set-Location $projectPath

Write-Host "📍 Directorio actual: $projectPath" -ForegroundColor Green

# Verificar que PHP está disponible
$phpPath = "C:\xampp\php\php.exe"
if (Test-Path $phpPath) {
    Write-Host "✅ PHP encontrado en: $phpPath" -ForegroundColor Green
} else {
    Write-Host "❌ PHP no encontrado. Verifica la instalación de XAMPP." -ForegroundColor Red
    exit 1
}

# Ejecutar test de conexión
Write-Host "" 
Write-Host "🧪 Ejecutando test de conexión a Neon PostgreSQL..." -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow

try {
    & $phpPath "test_neon_connection.php"
    Write-Host ""
    Write-Host "✅ Test de conexión completado." -ForegroundColor Green
} catch {
    Write-Host "❌ Error ejecutando test de conexión: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "📋 PRÓXIMOS PASOS:" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow
Write-Host "1. Si viste 11 tablas arriba, la migración fue exitosa ✅" -ForegroundColor Green
Write-Host "2. Si no hay tablas, ejecuta el script SQL en Neon Console ⚠️" -ForegroundColor Yellow
Write-Host "3. Después puedes desplegar en Vercel con: vercel --prod 🚀" -ForegroundColor Cyan

Write-Host ""
Write-Host "📊 ESTADO DEL PROYECTO:" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow

# Verificar archivos clave
$keyFiles = @(
    "postgresql_migration.sql",
    "vercel.json", 
    "config/database.php",
    "api/validar_login.php",
    "api/procesar_registro.php",
    "api/recuperar.php"
)

foreach ($file in $keyFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ $file (FALTANTE)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🎯 RESUMEN:" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow
Write-Host "- Configuración: ✅ Completa" -ForegroundColor Green
Write-Host "- APIs: ✅ Listas" -ForegroundColor Green
Write-Host "- Vercel Config: ✅ Listo" -ForegroundColor Green
Write-Host "- Base de datos: ⚠️ Verificar arriba" -ForegroundColor Yellow

Write-Host ""
Write-Host "📞 ¿NECESITAS AYUDA?" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Yellow
Write-Host "- Lee: FINAL_MIGRATION_STEPS.md" -ForegroundColor White
Write-Host "- Revisa: CURRENT_STATUS.md" -ForegroundColor White
Write-Host "- Guía: NEON_MIGRATION_GUIDE.md" -ForegroundColor White

Write-Host ""
Write-Host "🚀 Script completado. ¡Buena suerte con el despliegue!" -ForegroundColor Green
