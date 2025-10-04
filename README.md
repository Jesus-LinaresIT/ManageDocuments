# Sistema de Gestión Documental para Proyectos de Proyección Social (FICA/UTEC)

Sistema web desarrollado en Laravel 11 para la gestión documental de proyectos de proyección social con RBAC (Role-Based Access Control) y versionado de documentos.

## Características Implementadas (40% MVP)

### ✅ Autenticación y Autorización
- Sistema de autenticación con Laravel Breeze
- RBAC con spatie/laravel-permission
- 5 roles: Administrador, Docente, Revisor Académico, Revisor Proyección Social, Observador
- Gates y middleware para control de acceso

### ✅ Gestión de Proyectos
- Creación y asignación de proyectos
- Asignación de docente y revisores
- Vista de estado de documentos por proyecto

### ✅ Gestión de Documentos
- 5 tipos de documentos con secuencia obligatoria
- Bloqueo secuencial: documento N solo se puede subir si N-1 está aprobado
- Versionado completo con historial
- Validación de tipos MIME (PDF, DOCX) y tamaño (20MB max)
- Almacenamiento en storage/app/public/projects/

### ✅ Auditoría
- Log de acciones: project.create, document.upload
- Registro de metadatos en AuditLog

### ✅ Administración
- CRUD completo de usuarios
- Asignación de roles
- Vista de administración restringida a administradores

## Requisitos del Sistema

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8.0+
- Laravel 11

## Instalación

### 1. Clonar y configurar el proyecto

```bash
# El proyecto ya está creado en fica-ps/
cd fica-ps

# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install

# Configurar variables de entorno
cp .env.example .env
php artisan key:generate
```

### 2. Configurar base de datos

Editar `.env` con los datos de tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fica_ps
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 3. Ejecutar migraciones y seeders

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (crea roles, permisos, tipos de documentos y usuarios demo)
php artisan db:seed

# Crear enlace simbólico para storage
php artisan storage:link
```

### 4. Compilar assets

```bash
# Compilar assets con Vite
npm run build
```

### 5. Iniciar servidor

```bash
php artisan serve
```

El sistema estará disponible en `http://localhost:8000`

## Usuarios Demo

El seeder crea los siguientes usuarios con contraseña `password`:

| Email | Rol | Descripción |
|-------|-----|-------------|
| admin@fica.edu.sv | Administrador | Acceso completo al sistema |
| juan.perez@fica.edu.sv | Docente | Puede crear proyectos y subir documentos |
| maria.gonzalez@fica.edu.sv | Revisor Académico | Revisa documentos en etapa 1 |
| carlos.rodriguez@fica.edu.sv | Revisor Proyección Social | Revisa documentos en etapa 2 |

## Estructura del Proyecto

```
app/
├── Models/
│   ├── Project.php              # Modelo de proyectos
│   ├── DocumentType.php         # Tipos de documentos
│   ├── ProjectDocument.php      # Relación proyecto-documento
│   ├── DocumentVersion.php      # Versiones de documentos
│   ├── Review.php               # Revisiones (stub)
│   └── AuditLog.php             # Log de auditoría
├── Http/Controllers/
│   ├── ProjectController.php    # Gestión de proyectos
│   ├── DocumentController.php   # Gestión de documentos
│   ├── ReviewController.php     # Revisión (stub)
│   └── Admin/UserController.php # Administración de usuarios
└── Providers/
    └── AuthServiceProvider.php  # Gates y políticas

database/
├── migrations/                  # Migraciones de base de datos
└── seeders/
    ├── RbacSeeder.php          # Roles y permisos
    ├── DocumentTypesSeeder.php  # Tipos de documentos
    └── DemoUsersSeeder.php     # Usuarios demo

resources/views/
├── projects/                   # Vistas de proyectos
├── admin/users/               # Vistas de administración
└── dashboard.blade.php        # Panel principal
```

## Funcionalidades por Rol

### Administrador
- ✅ Gestión completa de usuarios
- ✅ Vista de todos los proyectos
- ✅ Todos los permisos del sistema

### Docente
- ✅ Crear proyectos
- ✅ Subir documentos a sus proyectos
- ✅ Ver estado de documentos
- ✅ Historial de versiones

### Revisor Académico
- ✅ Ver proyectos asignados
- 🔄 Revisar documentos (stub - 60% restante)

### Revisor Proyección Social
- ✅ Ver proyectos asignados
- 🔄 Revisar documentos (stub - 60% restante)

### Observador
- ✅ Vista de solo lectura
- 🔄 Reportes (stub - 60% restante)

## Reglas de Negocio Implementadas

### Bloqueo Secuencial
- Documento 1: Sin restricciones
- Documento 2: Requiere que Documento 1 esté aprobado
- Documento 3: Requiere que Documento 2 esté aprobado
- Y así sucesivamente...

### Versionado
- Cada nueva carga crea una nueva versión
- Historial completo de versiones
- Descarga de versiones anteriores
- Metadatos: nombre original, tamaño, fecha, versión

### Validaciones
- Tipos MIME: PDF, DOCX
- Tamaño máximo: 20MB
- Secuencia obligatoria
- Permisos por rol

## Próximas Funcionalidades (60% restante)

- [ ] Sistema de revisión completo
- [ ] Notificaciones en tiempo real
- [ ] Reportes y KPIs
- [ ] Centro de notificaciones
- [ ] 2FA/SSO
- [ ] Preview de documentos
- [ ] Aprobaciones/denegaciones avanzadas

## Comandos Útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recompilar assets
npm run dev

# Ver rutas
php artisan route:list

# Tinker para debugging
php artisan tinker
```

## Contribución

Este es un proyecto académico para FICA/UTEC. Para contribuir:

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## Licencia

Proyecto académico - FICA/UTEC 2024

---

**Nota**: Este es el 40% inicial del sistema. Las funcionalidades de revisión, notificaciones y reportes se implementarán en el 60% restante.