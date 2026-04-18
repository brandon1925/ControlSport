CREATE SCHEMA IF NOT EXISTS ControlSport;
SET search_path TO ControlSport, public;

CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'Administrador', 
    estado VARCHAR(20) DEFAULT 'Activo',
    fecha_alta DATE NOT NULL,
    creado_por INT,
    CONSTRAINT fk_creado_por FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario)
);

CREATE TABLE tutor (
    id_tutor SERIAL PRIMARY KEY,
    nombre_tutor VARCHAR(50) NOT NULL,
    apellido_P_t VARCHAR(50) NOT NULL,
    apellido_M_t VARCHAR(50) NOT NULL,
    telefono_tutor VARCHAR(20) NOT NULL
);

CREATE TABLE grupo (
    id_grupo SERIAL PRIMARY KEY,
    id_entrenador INT NOT NULL,
    nombre_grupo VARCHAR(100) NOT NULL,
    limite_alumnos INT NOT NULL,
    cupo_actual INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_entrenador) REFERENCES usuarios(id_usuario)
);

CREATE TABLE alumno (
    id_alumno SERIAL PRIMARY KEY,
    id_entrenador INT NOT NULL,
    id_tutor INT NOT NULL,
    id_grupo INT, 
    curp VARCHAR(18) NOT NULL UNIQUE,
    nombre_alumno VARCHAR(50) NOT NULL,
    apellido_P_a VARCHAR(50) NOT NULL,
    apellido_M_a VARCHAR(50) NOT NULL,
    edad INT NOT NULL,
    peso FLOAT NOT NULL,
    estatura FLOAT NOT NULL,
    institucion_medica VARCHAR(100) NOT NULL,
    num_afiliacion VARCHAR(50) NOT NULL,
    domicilio VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente',
    FOREIGN KEY (id_entrenador) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_tutor) REFERENCES tutor(id_tutor),
    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
);

CREATE TABLE pase_lista (
    id_pase SERIAL PRIMARY KEY,
    id_grupo INT NOT NULL,
    fecha DATE NOT NULL,
    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
);

CREATE TABLE detalle_asistencia (
    id_detalle SERIAL PRIMARY KEY,
    id_pase INT NOT NULL,
    id_alumno INT NOT NULL,
    estado_asistencia VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_pase) REFERENCES pase_lista(id_pase),
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno)
);

CREATE TABLE evaluacion_rendimiento (
    id_evaluacion SERIAL PRIMARY KEY,
    id_alumno INT NOT NULL,
    fecha_evaluacion DATE NOT NULL,
    velocidad INT NOT NULL,
    fuerza INT NOT NULL,
    resistencia INT NOT NULL,
    agilidad INT NOT NULL,
    coordinacion INT NOT NULL,
    flexibilidad INT NOT NULL,
    notas_adicionales VARCHAR(255),
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno)
);

INSERT INTO usuarios (nombre_completo, usuario, contrasena, rol, estado, fecha_alta) 
VALUES ('Administrador BELC', 'admin', '12345', 'Administrador', 'Activo', CURRENT_DATE);

UPDATE usuarios SET creado_por = 1 WHERE id_usuario = 1;

SELECT * FROM ControlSport.usuarios;