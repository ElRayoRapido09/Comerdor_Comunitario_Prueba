# Guía de Migración a Neon PostgreSQL y Despliegue en Vercel

## 📋 Resumen
Esta guía te ayudará a migrar tu aplicación PHP "Comedor Comunitario" de MySQL local a Neon PostgreSQL y desplegarla en Vercel.

## 🚀 Pasos de Migración

### 1. Preparar la Base de Datos en Neon

#### 1.1 Ejecutar el Script de Migración
Necesitas ejecutar el script `postgresql_migration.sql` en tu base de datos Neon:

1. Ve a tu dashboard de Neon: https://console.neon.tech
2. Selecciona tu proyecto
3. Ve a la sección "SQL Editor"
4. Copia y pega el contenido del archivo `postgresql_migration.sql`
5. Ejecuta el script

#### 1.2 Verificar las Tablas Creadas
Después de ejecutar el script, verifica que se crearon las siguientes tablas:
- `usuarios`
- `secciones_menu`
- `platillos`
- `menus_dia`
- `menu_platillos`
- `reservaciones`
- `reservacion_platillos`
- `asistencias`
- `gastos`
- `inventario`
- `reportes`

### 2. Probar la Conexión Localmente

#### 2.1 Verificar Extensión PostgreSQL de PHP
Asegúrate de que tienes la extensión `pdo_pgsql` instalada en tu PHP local:

```powershell
php -m | findstr pgsql
```

Si no está instalada, agrégala en tu `php.ini`:
```ini
extension=pdo_pgsql
```

#### 2.2 Ejecutar el Test de Conexión
Ejecuta el archivo de prueba para verificar la conexión:

```powershell
cd "c:\xampp\htdocs\Original\Comedor_Comunitario"
php test_neon_connection.php
```

### 3. Actualizar Archivos Modificados

Los siguientes archivos ya han sido actualizados para PostgreSQL:

#### ✅ Archivos de Conexión Actualizados:
- `config/database.php` - Configuración principal
- `menu/database.php` - Conexión del módulo de menú
- `empleado/pedidos/conexion.php` - Conexión de pedidos
- `empleado/escanear/conexion.php` - Conexión de escaner
- `menu/perfil/conexion.php` - Conexión de perfil
- `inicio/validar_login.php` - Validación de login

#### 📝 Archivos que Requieren Actualización Manual:

Necesitas actualizar los siguientes archivos para usar PostgreSQL en lugar de MySQL:

1. **`empleado/menu_operations.php`** - Cambiar conexión MySQL
2. **`empleado/reporte/api.php`** - Actualizar conexión
3. **`registro/procesar_registro.php`** - Migrar a PostgreSQL
4. **`empleado/registro_empleado/registrar_empleado.php`** - Actualizar
5. **`recuperacion/config.php`** - Cambiar configuración

### 4. Configurar Variables de Entorno para Vercel

#### 4.1 Archivo vercel.json
Ya se ha creado el archivo `vercel.json` con la configuración necesaria.

#### 4.2 Variables de Entorno en Vercel Dashboard
Ve a tu proyecto en Vercel y agrega estas variables de entorno:

```
DATABASE_URL=postgres://neondb_owner:npg_hyng4Q2aGNdP@ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require
PGHOST=ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech
PGUSER=neondb_owner
PGDATABASE=neondb
PGPASSWORD=npg_hyng4Q2aGNdP
PGPORT=5432
```

### 5. Diferencias Principales entre MySQL y PostgreSQL

#### 5.1 Tipos de Datos
- `AUTO_INCREMENT` → `SERIAL`
- `TINYINT(1)` → `BOOLEAN`
- `ENUM` → `VARCHAR` con `CHECK` constraints

#### 5.2 Sintaxis de Consultas
- Comillas para nombres: \`tabla\` → "tabla"
- Funciones de fecha: `NOW()` → `CURRENT_TIMESTAMP`
- LIMIT: `LIMIT 1` sigue igual

#### 5.3 Parámetros de Conexión
- MySQL: `mysql:host=...;dbname=...`
- PostgreSQL: `pgsql:host=...;dbname=...;sslmode=require`

### 6. Despliegue en Vercel

#### 6.1 Preparar el Repositorio
```powershell
cd "c:\xampp\htdocs\Original\Comedor_Comunitario"
git init
git add .
git commit -m "Migración a PostgreSQL y configuración para Vercel"
```

#### 6.2 Conectar con Vercel
1. Ve a https://vercel.com
2. Importa tu repositorio
3. Configura las variables de entorno
4. Despliega

### 7. Verificación Post-Despliegue

#### 7.1 Endpoints Importantes
Después del despliegue, verifica estos endpoints:
- `/test_neon_connection.php` - Test de conexión
- `/inicio/validar_login.php` - Login de usuarios
- `/menu/get_menu_dia.php` - Obtener menú del día

#### 7.2 Usuario de Prueba
El script de migración crea un usuario administrador:
- **Email**: admin@comedor.com
- **Password**: password (cambiar en producción)

## ⚠️ Notas Importantes

### Seguridad
1. **Cambiar credenciales por defecto** después del despliegue
2. **Configurar variables de entorno** en lugar de hardcodear credenciales
3. **Validar todos los inputs** del usuario

### Performance
1. **Usar conexiones pooled** de Neon para mejor rendimiento
2. **Implementar caché** donde sea apropiado
3. **Optimizar consultas** complejas

### Monitoreo
1. **Configurar logs** en Vercel
2. **Monitorear métricas** de Neon
3. **Implementar health checks**

## 🛠️ Solución de Problemas

### Error: "could not connect to server"
- Verificar credenciales de Neon
- Comprobar que SSL está habilitado
- Verificar firewall/red

### Error: "extension pdo_pgsql not found"
- Instalar extensión PostgreSQL para PHP
- Reiniciar servidor web

### Error: "table does not exist"
- Ejecutar script de migración en Neon
- Verificar nombre de base de datos

## 📞 Soporte

Si tienes problemas durante la migración:
1. Revisa los logs de error en PHP
2. Verifica la consola de Neon
3. Comprueba los logs de Vercel
4. Ejecuta el test de conexión

## 🎯 Próximos Pasos

Después de completar la migración:
1. **Poblar datos de prueba** en las tablas
2. **Configurar backup automático** en Neon
3. **Implementar CI/CD** con GitHub Actions
4. **Optimizar SEO** para mejor rendimiento
5. **Configurar dominio personalizado** en Vercel
