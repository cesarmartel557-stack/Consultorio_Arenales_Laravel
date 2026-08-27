# 🚀 Guía de Despliegue en DonWeb (Ferozo / cPanel)

Esta guía detalla los pasos para desplegar **Consultorio Integral Arenales** en un hosting compartido de DonWeb (panel Ferozo / cPanel) de forma segura y optimizada.

---

## 📦 1. Paquetes Listos para Subir

En la raíz del proyecto tenés disponibles **2 archivos ZIP listos para subir**:

1. **`deploy.zip` (~23.6 MB):**
   - Contiene todo el backend y la lógica de Laravel (`app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`).
   - Se descomprime en la carpeta privada `/laravel_app/`.

2. **`public_html.zip` (~4.3 MB):**
   - Contiene todos los archivos públicos (`assets/`, `build/`, `css/`, `js/`, `favicon.ico`, `robots.txt`, `.htaccess`).
   - **Ya incluye `index.php` preconfigurado** apuntando a `../laravel_app/`, por lo que **no tenés que editar código a mano**.
   - Se descomprime directamente dentro de `public_html/` (o la carpeta pública de tu subdominio).

---

## 📂 2. Estructura de Carpetas en Ferozo

Basado en tu Administrador de Archivos:

```text
/ (directorio raíz de tu cuenta en Ferozo)
│
├── laravel_app/                       <--- Descomprimir aquí deploy.zip
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   ├── composer.json
│   ├── composer.lock
│   └── .env                           <--- Crear aquí con los datos de producción
│
└── public_html/
    └── turnos/                        <--- Descomprimir aquí public_html.zip
        ├── assets/
        ├── build/
        ├── css/
        ├── js/
        ├── favicon.ico
        ├── robots.txt
        ├── .htaccess
        └── index.php                  <--- Ya incluido y configurado
```

---

## ⚙️ 3. Pasos de Instalación en el Servidor

### Paso 1: Subir el Backend (`deploy.zip`)
1. Ingresá a la carpeta `/laravel_app/` (que ya tenés creada en la raíz).
2. Subí `deploy.zip` dentro de `/laravel_app/` y hacé clic en **Extraer / Descomprimir**.

### Paso 2: Subir el Frontend (`public_html.zip`)
1. En el Administrador de Archivos, ingresá a la carpeta **`/public_html/turnos/`** (la que tenés abierta en tu captura).
2. Subí **`public_html.zip`** dentro de `/public_html/turnos/` y hacé clic en **Extraer / Descomprimir**.
3. Listo: los assets, estilos, imágenes y el archivo `index.php` ya quedan en su lugar exacto funcionando automáticamente.

### Paso 3: Configurar el archivo `.env`
En `laravel_app/`, creá un archivo llamado `.env` con la siguiente configuración base adaptada a DonWeb:

```ini
APP_NAME="Consultorio Integral Arenales"
APP_ENV=production
APP_KEY=base64:COPIA_AQUI_LA_APP_KEY
APP_DEBUG=false
APP_TIMEZONE="America/Argentina/Buenos_Aires"
APP_URL=http://turnos.consultoriointegralarenales.com.ar

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_AR

# Base de datos MySQL en Ferozo
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_en_ferozo
DB_USERNAME=usuario_base_de_datos
DB_PASSWORD=password_base_de_datos

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

# Envío de correos SMTP Ferozo
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=c0750160.ferozo.com
MAIL_PORT=465
MAIL_USERNAME=no-responder@consultoriointegralarenales.com.ar
MAIL_PASSWORD="WAp1B*9CuaQiyyK@"
MAIL_FROM_ADDRESS="no-responder@consultoriointegralarenales.com.ar"
MAIL_FROM_NAME="Consultorio Integral Arenales"
```

---

## 🗄️ 4. Base de Datos y Creación de Tablas

Si tenés acceso a terminal SSH o mediante el Administrador de Tareas de Ferozo:

```bash
cd /home/tu_usuario/laravel_app

# 1. Ejecutar migraciones
php artisan migrate --force

# 2. Generar datos iniciales (especialidades, obras sociales, médicos, portada)
php artisan db:seed --class=ConsultorioSeeder --force

# 3. Aplicar credenciales seguras de producción
php artisan app:setup-production-users --force

# 4. Crear enlace simbólico de almacenamiento
php artisan storage:link

# 5. Optimizar cachés para producción
php artisan optimize
php artisan filament:optimize
```

*(Si no tenés acceso a SSH, podés importar el archivo `.sql` inicial desde phpMyAdmin y ejecutar los comandos mediante un script temporal o tarea Cron).*

---

## 🔐 5. Credenciales Iniciales de Producción

### Acceso al Panel de Control:
- **URL del Panel:** `http://turnos.consultoriointegralarenales.com.ar/gestion`

| Rol | Nombre | Correo Electrónico | Contraseña Segura |
| :--- | :--- | :--- | :--- |
| **Admin** | Administración | `admin@consultoriointegralarenales.com.ar` | `Adm#Arenales!2026$Sec*9k` |
| **Médico** | Dr. Gabriel Toledo | `gabriel-toledo@consultoriointegralarenales.com.ar` | `Tol#DrGabi!92785$Px*7m` |
| **Médica** | Dra. Mariana Albrizio | `mariana-albrizio@consultoriointegralarenales.com.ar` | `Alb#DraMari!Fert$8q*2v` |
| **Médico** | Dr. Humberto Giambastiani | `humberto-giambastiani@consultoriointegralarenales.com.ar` | `Gia#DrHumb!43029$Wk*4z` |
| **Médico** | Dr. Mariano Martinotti | `mariano-martinotti@consultoriointegralarenales.com.ar` | `Mar#DrMari!11179$Bn*6x` |
| **Médica** | Dra. Silvina Vulcano | `silvina-vulcano@consultoriointegralarenales.com.ar` | `Vul#DraSilv!8321$Jy*3p` |
| **Médica** | Dra. Claudia Krasnapolsky | `claudia-krasnapolsky@consultoriointegralarenales.com.ar` | `Kra#DraClau!1054$Lt*8d` |
| **Médica** | Dra. Natalia Capeluto | `natalia-capeluto@consultoriointegralarenales.com.ar` | `Cap#DraNati!Pedi$5m*1s` |
| **Médica** | Dra. Laura Bidegain | `laura-bidegain@consultoriointegralarenales.com.ar` | `Bid#DraLaur!Masto$9r*5k` |

> [!TIP]
> Los médicos pueden acceder directamente a `http://turnos.consultoriointegralarenales.com.ar/gestion` con su correo y contraseña para gestionar sus días de atención, excepciones y ver los turnos agendados.

---

## ⏰ 6. Configuración de Tareas Programadas (Cron Job)

En el panel de Ferozo / cPanel, ingresá a **Tareas Programadas (Cron Jobs)** y agregá la siguiente tarea para que se ejecute cada minuto:

- **Frecuencia:** `* * * * *` (Cada minuto)
- **Comando:**
  ```bash
  /usr/local/bin/php /home/tu_usuario/laravel_app/artisan schedule:run >> /dev/null 2>&1
  ```
  *(Reemplazá `/usr/local/bin/php` por la ruta del binario PHP 8.2 en tu servidor Ferozo y `tu_usuario` por tu usuario del hosting).*

Esto garantiza el envío automático diario de los recordatorios de turnos a las 09:00 hs.

---

## 🔒 7. Permisos de Archivos y Carpetas

Asegurate de que las siguientes carpetas tengan permisos de escritura (`chmod 775` o `755` según el servidor):
- `laravel_app/storage/`
- `laravel_app/storage/app/`
- `laravel_app/storage/framework/`
- `laravel_app/storage/logs/`
- `laravel_app/bootstrap/cache/`
