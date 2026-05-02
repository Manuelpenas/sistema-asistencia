# Sistema de Asistencia a Cursos

Sistema web desarrollado en PHP + MySQL para gestión de asistencia, inscripciones y consulta de notas.

## Características

- **Dashboard gerencial** con estadísticas y gráficos
- **Módulo de Inscripción** - Busca colaborador por DNI/nombre y inscribe en cursos
- **Módulo de Asistencia** - Marca asistencia comparando inscritos vs asistentes
- **Consulta** - Busca por DNI o nombre para ver historial de cursos
- **Configuración protegida** (pass: `D3yf0rE1RL`)
  - Subir perfiles (DNI|nombres|cc|mp)
  - Subir cursos
  - Subir asistencias (Excel/CSV)
  - Personalizar logo y favicon
  - Cambiar contraseña

## Instalación en cPanel

1. Subir archivos vía FTP o File Manager a `public_html`
2. Crear base de datos MySQL en cPanel → MySQL Databases
3. Visitar `tu-dominio.com/install.php`
4. Completar datos de conexión
5. ¡Listo!

## Estructura de archivos

```
├── index.php           # Dashboard
├── inscripcion.php     # Inscripción de colaboradores
├── asistencia.php      # Registro de asistencia
├── consulta.php        # Consulta por DNI/nombre
├── registros.php       # Ver todos los registros
├── config.php          # Configuración (protegido)
├── login.php           # Acceso a configuración
├── functions.php       # Funciones y conexión DB
├── header.php/footer.php
├── assets/             # Logo, favicon
└── template.csv        # Plantilla de ejemplo
```

## Tecnologías

- PHP 8+
- MySQL / SQLite (para pruebas locales)
- Diseño verde minimalista (paleta #1b5e20)
