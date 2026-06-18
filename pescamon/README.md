# PescaDex

App de pesca recreativa social y gamificada. Los usuarios suben capturas de peces (foto + datos), completan una "PescaDex" personal de especies y pueden ver en un mapa interactivo dónde otros han pescado cada especie.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 + PHP 8.4 |
| Frontend | Vue 3 (Composition API) + Inertia.js v3 |
| Estilos | Tailwind CSS v4 |
| Auth | Laravel Fortify + Passkeys |
| Estado cliente | Pinia |
| Mapa | Leaflet.js + OpenStreetMap (sin API key) |
| Imágenes | intervention/image v4 (thumbnails) |
| EXIF | `exif_read_data` nativo PHP (via `ExifService`) |
| Colas | Laravel Queues (driver `database`) |
| Testing | Pest v4 |

## Requisitos previos

- PHP 8.3+ con extensiones: `pdo_sqlite`, `exif`, `gd` o `imagick`
- Composer 2.x
- Node.js 20+ y npm

Para instalar la extensión SQLite en Ubuntu/Debian:

```bash
sudo apt-get install php8.4-sqlite3 php8.4-gd
```

## Instalación

```bash
# 1. Clonar / entrar al proyecto
cd pescamon

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Crear el archivo de entorno
cp .env.example .env
php artisan key:generate

# 5. Crear la base de datos SQLite
touch database/database.sqlite

# 6. Ejecutar las migraciones
php artisan migrate

# 7. Crear el enlace simbólico de storage
php artisan storage:link

# 8. Compilar assets
npm run build
```

## Seeders

Carga el catálogo de especies (20 especies) y el set inicial de logros:

```bash
php artisan db:seed
```

O individualmente:

```bash
php artisan db:seed --class=SpeciesSeeder
php artisan db:seed --class=AchievementSeeder
```

## Levantar en local

El comando `dev` del `composer.json` levanta todos los procesos en paralelo (servidor, cola, logs, Vite):

```bash
composer dev
```

O manualmente en terminales separadas:

```bash
# Servidor Laravel
php artisan serve

# Worker de colas (imprescindible para procesar imágenes)
php artisan queue:listen --tries=3

# Vite (hot reload)
npm run dev

# Logs (opcional, requiere Laravel Pail)
php artisan pail
```

## Worker de colas

Las imágenes subidas se procesan de forma **asíncrona** mediante el job `ProcessCatchImage`. Para que funcione en local necesitas al menos un worker corriendo:

```bash
php artisan queue:work --tries=3 --sleep=3
```

En producción usa Supervisor para mantener el worker activo:

```ini
[program:pescadex-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
```

## Testing

```bash
# Ejecutar todos los tests con Pest
php artisan test

# Solo tests unitarios
php artisan test --testsuite=Unit
```

## Estructura de módulos clave

```
app/
├── Http/Controllers/
│   ├── DashboardController.php   # GET /dashboard – mapa principal
│   ├── CatchController.php       # CRUD capturas
│   ├── SpeciesController.php     # Ficha de especie + mapa
│   ├── ProfileController.php     # Perfil propio y público + PescaDex
│   └── LeaderboardController.php # Rankings
│
├── Models/
│   ├── User.php                  # +level, xp, location_privacy, unit_preference
│   ├── FishCatch.php             # Tabla: catches (Catch es keyword PHP)
│   ├── Species.php               # Catálogo maestro
│   ├── Achievement.php           # Logros disponibles
│   └── ...
│
├── Services/
│   ├── ExifService.php           # Extrae GPS y fecha de metadatos EXIF
│   ├── LevelingService.php       # Calcula XP y sube de nivel
│   └── AchievementService.php    # Verifica y desbloquea logros
│
├── Jobs/
│   └── ProcessCatchImage.php     # Cola: thumbnail + EXIF + evento CatchProcessed
│
└── Listeners/
    └── AwardCatchRewards.php     # Recompensas XP + achievements tras verificación

resources/js/
├── pages/
│   ├── dashboard/Map.vue         # Mapa Leaflet con capturas verificadas
│   ├── profile/Show.vue          # Perfil + PescaDex grid + logros
│   ├── catches/Create.vue        # Formulario de nueva captura
│   ├── catches/Show.vue          # Detalle de captura + comentarios
│   ├── species/Show.vue          # Ficha de especie + mapa de avistamientos
│   └── leaderboard/Index.vue     # Rankings (XP, capturas, especies, peso)
│
├── components/
│   ├── XpBar.vue                 # Barra de progreso de nivel
│   ├── PokedexGrid.vue           # Grid de especies capturadas/bloqueadas
│   ├── AchievementBadge.vue      # Tarjeta de logro desbloqueado
│   ├── SpeciesCard.vue           # Tarjeta de especie en catálogo
│   └── MapPin.vue                # Marcador del mapa con color por rareza
│
└── stores/
    └── notifications.ts          # Store Pinia para notificaciones in-app
```

## Privacidad de ubicación

Configurada por usuario en `location_privacy`:

| Valor | Comportamiento en el mapa público |
|-------|-----------------------------------|
| `exact` | Coordenadas exactas |
| `approximate` | Redondeo a 2 decimales + offset aleatorio ~500m |
| `hidden` | No aparece en ningún mapa público |

## Lógica de XP

| Evento | XP |
|--------|----|
| Primera captura de una especie (nueva en PescaDex) | +50 |
| Nuevo récord personal de peso en esa especie | +20 |
| Captura repetida sin mejora | +5 |

Fórmula de niveles: `XP requerido para nivel N = N² × 100`

## Moderación

Las capturas entran en estado `pending` y **no aparecen** en el mapa público ni en estadísticas hasta ser marcadas como `verified`. Para aprobar/rechazar manualmente desde Tinker:

```bash
php artisan tinker
>>> \App\Models\FishCatch::find(1)->update(['status' => 'verified']);
```

## Licencia

MIT
