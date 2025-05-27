# Troubleshooting Guide - Vercel Deployment

## Problemas Comunes y Soluciones

### 1. "Error de conexión con el servidor" en Login

**Causa:** Las rutas de la API no están configuradas correctamente o las variables de entorno no están cargadas.

**Soluciones:**
1. **Verificar variables de entorno en Vercel:**
   - Ve a tu proyecto en Vercel Dashboard
   - Ve a Settings > Environment Variables
   - Asegúrate que estén configuradas:
     ```
     DATABASE_URL=postgres://neondb_owner:npg_hyng4Q2aGNdP@ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require&options=endpoint%3Dep-noisy-bush-a4xycth8
     PGHOST=ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech
     PGUSER=neondb_owner
     PGDATABASE=neondb
     PGPASSWORD=npg_hyng4Q2aGNdP
     PGPORT=5432
     ```

2. **Verificar la base de datos:**
   - Accede a: `https://tu-app.vercel.app/api/verify_db.php`
   - Debe mostrar las tablas creadas
   - Si no hay tablas, ejecuta el script de migración en Neon

3. **Debug de conexión:**
   - Accede a: `https://tu-app.vercel.app/api/debug.php`
   - Verifica que las variables de entorno estén cargadas

### 2. "Página no existe" para Registro/Recuperación

**Causa:** Las rutas no están siendo redirigidas correctamente a la carpeta `/api`.

**Solución:**
- Verifica que el archivo `vercel.json` esté en la raíz del proyecto
- Redeploy en Vercel después de cualquier cambio en `vercel.json`

### 3. Esquema de Base de Datos Faltante

**IMPORTANTE:** Debes ejecutar el script de migración en Neon primero.

**Pasos:**
1. Ve a https://console.neon.tech/
2. Abre tu proyecto > SQL Editor  
3. Ejecuta el contenido completo de `postgresql_migration.sql`
4. Ejecuta también `tokens_recovery_table.sql` para la recuperación de contraseñas

### 4. Errores de CORS

Si ves errores de CORS en la consola del navegador:
- Verifica que el archivo `vercel.json` tenga la configuración de headers
- Los archivos en `/api` ya tienen headers CORS configurados

### 5. Timeouts de Conexión

Si la conexión se demora mucho:
- Verifica que uses la URL pooled de Neon (con `-pooler` en el hostname)
- Las variables de entorno deben usar el endpoint con pooling

## URLs de Verificación (Reemplaza con tu dominio)

- **Debug general:** `https://tu-app.vercel.app/api/debug.php`
- **Verificación DB:** `https://tu-app.vercel.app/api/verify_db.php`
- **Test de login:** `https://tu-app.vercel.app/api/validar_login.php`
- **Test de registro:** `https://tu-app.vercel.app/api/procesar_registro.php`

## Comandos para Redeploy

```bash
# Si tienes Vercel CLI instalado
vercel --prod

# O desde GitHub, haz push a tu rama principal
git add .
git commit -m "Fix API routes"
git push origin main
```

## Checklist de Deployment

- [ ] Ejecutar script de migración en Neon Console
- [ ] Configurar variables de entorno en Vercel
- [ ] Verificar que `vercel.json` esté en la raíz
- [ ] Verificar que la carpeta `/api` tenga los archivos PHP
- [ ] Hacer redeploy después de cambios
- [ ] Probar las URLs de debug
- [ ] Verificar que las tablas existan en la base de datos
- [ ] Probar login con un usuario de prueba

## Usuario de Prueba

Después de ejecutar el script de migración, tendrás este usuario de prueba:

```
Email: admin@comedor.com
Password: admin123
Tipo: admin
```

## Logs de Error

Para ver errores detallados en Vercel:
1. Ve a tu proyecto en Vercel
2. Ve a la pestaña "Functions"
3. Selecciona una función (ej: api/validar_login.php)
4. Ve los logs en tiempo real
