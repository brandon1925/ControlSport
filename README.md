# ControlSport - Sistema de Gestión Deportiva

ControlSport es una plataforma web de control administrativo y seguimiento de rendimiento diseñada específicamente para entrenadores deportivos y administradores escolares. Permite digitalizar y centralizar el registro de alumnos, control de asistencia y métricas físicas, eliminando la dependencia de bitácoras físicas.

## Características Principales

* **Seguridad y Autenticación Avanzada:** * Sistema de login seguro con encriptación de contraseñas mediante Bcrypt (Hashing irreversible).
  * Arquitectura Multi-Tenant (Multiusuario aislado): Cada administrador/entrenador solo tiene acceso y control sobre los registros que él mismo ha creado.
* **Gestión de Usuarios (Roles):** Panel de administración para altas, bajas y modificaciones de personal con soporte para roles (Administrador y Entrenador).
* **Diseño Responsivo:** Interfaz moderna y adaptable a dispositivos móviles (Smartphones y Tablets) con menú lateral colapsable.
* **Gestión Integral:** Módulos de Solicitudes, Alumnos, Grupos, Asistencias y Rendimiento.

## Tecnologías Utilizadas

* **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Phosphor Icons.
* **Backend:** PHP 8+ (Arquitectura MVC simplificada - Vistas y Controladores).
* **Base de Datos:** PostgreSQL.

## Instalación y Configuración Local

Sigue estos pasos para ejecutar el proyecto en tu máquina local:

### 1. Requisitos previos
* Servidor web local (XAMPP, WAMP, Laragon, etc.)
* PHP 8.0 o superior.
* PostgreSQL instalado y pgAdmin (opcional pero recomendado).

### 2. Clonar el repositorio
```bash
git clone [https://github.com/tu-usuario/ControlSport.git](https://github.com/tu-usuario/ControlSport.git)