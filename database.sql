-- ============================================================
--  Base de datos del panel escolar (Mi Colegio)
--  Importar en la base de datos: sistema_login
-- ============================================================

CREATE DATABASE IF NOT EXISTS sistema_login
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_login;

-- ------------------------------------------------------------
-- Usuarios (login) — se conserva la tabla existente
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Docentes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS docentes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  apellido VARCHAR(80) NOT NULL,
  documento VARCHAR(30),
  email VARCHAR(150),
  telefono VARCHAR(30),
  especialidad VARCHAR(120),
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Cursos y grados
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  nivel ENUM('Preescolar','Primaria','Bachillerato') NOT NULL,
  grado VARCHAR(20) NOT NULL,
  anio YEAR NOT NULL,
  docente_id INT NULL,
  capacidad INT NOT NULL DEFAULT 30,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cursos_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Estudiantes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS estudiantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  apellido VARCHAR(80) NOT NULL,
  documento VARCHAR(30),
  email VARCHAR(150),
  telefono VARCHAR(30),
  genero ENUM('M','F','Otro') DEFAULT 'Otro',
  fecha_nacimiento DATE,
  curso_id INT NULL,
  acudiente VARCHAR(120),
  telefono_acudiente VARCHAR(30),
  estado ENUM('Activo','Inactivo','Pendiente') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_estudiantes_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Calificaciones
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS calificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  curso_id INT NULL,
  materia VARCHAR(80) NOT NULL,
  periodo VARCHAR(40) NOT NULL,
  nota DECIMAL(4,2) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_calif_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
  CONSTRAINT fk_calif_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Asistencia
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS asistencia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  fecha DATE NOT NULL,
  estado ENUM('Presente','Ausente','Tarde','Excusa') NOT NULL DEFAULT 'Presente',
  observacion VARCHAR(200),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_asist_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Horarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS horarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  curso_id INT NOT NULL,
  materia VARCHAR(80) NOT NULL,
  dia_semana ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado') NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  docente_id INT NULL,
  aula VARCHAR(40),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_horario_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
  CONSTRAINT fk_horario_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Pagos y matrícula
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  concepto VARCHAR(120) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha DATE NOT NULL,
  metodo ENUM('Efectivo','Transferencia','Tarjeta') NOT NULL DEFAULT 'Efectivo',
  estado ENUM('Pagado','Pendiente','Vencido') NOT NULL DEFAULT 'Pendiente',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pago_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Avisos y mensajes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS avisos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  contenido TEXT,
  categoria ENUM('General','Académico','Administrativo','Eventos') NOT NULL DEFAULT 'General',
  autor VARCHAR(120),
  fecha DATE NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DATOS DE EJEMPLO
-- ============================================================

INSERT INTO docentes (id, nombre, apellido, documento, email, telefono, especialidad, estado) VALUES
(1,'María','González','0801-1990-00123','maria.gonzalez@micolegio.edu','9999-1001','Preescolar','Activo'),
(2,'Carlos','Mejía','0801-1985-00456','carlos.mejia@micolegio.edu','9999-1002','Matemáticas','Activo'),
(3,'Ana','Flores','0801-1988-00789','ana.flores@micolegio.edu','9999-1003','Español','Activo'),
(4,'Luis','Ramírez','0801-1982-00321','luis.ramirez@micolegio.edu','9999-1004','Ciencias','Activo'),
(5,'Sofía','Castro','0801-1991-00654','sofia.castro@micolegio.edu','9999-1005','Inglés','Inactivo');

INSERT INTO cursos (id, nombre, nivel, grado, anio, docente_id, capacidad) VALUES
(1,'Preescolar A','Preescolar','Preescolar',2026,1,25),
(2,'1° A','Primaria','1°',2026,2,30),
(3,'5° B','Primaria','5°',2026,3,30),
(4,'6° A','Bachillerato','6°',2026,4,35),
(5,'10° A','Bachillerato','10°',2026,5,35),
(6,'11° A','Bachillerato','11°',2026,5,35);

INSERT INTO estudiantes (nombre, apellido, documento, email, telefono, genero, fecha_nacimiento, curso_id, acudiente, telefono_acudiente, estado, creado_en) VALUES
('Ana','López','0501-2013-001','ana.lopez@correo.com','8888-0001','F','2013-04-12',4,'Carlos López','9999-2001','Activo','2026-01-08 09:00:00'),
('Diego','Ramírez','0501-2011-002','diego.ramirez@correo.com','8888-0002','M','2011-07-23',5,'Marta Ramírez','9999-2002','Activo','2026-01-15 10:30:00'),
('Sofía','Torres','0501-2015-003','sofia.torres@correo.com','8888-0003','F','2015-02-02',3,'Luis Torres','9999-2003','Pendiente','2026-01-22 11:15:00'),
('Mateo','Herrera','0501-2010-004','mateo.herrera@correo.com','8888-0004','M','2010-09-30',6,'Paula Herrera','9999-2004','Activo','2026-02-03 08:45:00'),
('Valentina','Cruz','0501-2012-005','valentina.cruz@correo.com','8888-0005','F','2012-11-19',4,'Jorge Cruz','9999-2005','Activo','2026-02-10 12:00:00'),
('Samuel','Vargas','0501-2016-006','samuel.vargas@correo.com','8888-0006','M','2016-05-05',2,'Elena Vargas','9999-2006','Activo','2026-02-18 09:30:00'),
('Camila','Reyes','0501-2019-007','camila.reyes@correo.com','8888-0007','F','2019-08-14',1,'Marco Reyes','9999-2007','Activo','2026-02-25 14:00:00'),
('Andrés','Pineda','0501-2011-008','andres.pineda@correo.com','8888-0008','M','2011-01-28',5,'Rosa Pineda','9999-2008','Activo','2026-03-04 10:10:00'),
('Daniela','Suárez','0501-2013-009','daniela.suarez@correo.com','8888-0009','F','2013-06-17',4,'Óscar Suárez','9999-2009','Activo','2026-03-12 11:40:00'),
('Josué','Martínez','0501-2010-010','josue.martinez@correo.com','8888-0010','M','2010-12-01',6,'Karla Martínez','9999-2010','Activo','2026-03-20 08:20:00'),
('Isabella','Romero','0501-2014-011','isabella.romero@correo.com','8888-0011','F','2014-03-09',3,'Pedro Romero','9999-2011','Activo','2026-04-02 13:00:00'),
('Emilio','Navarro','0501-2012-012','emilio.navarro@correo.com','8888-0012','M','2012-10-22',4,'Lucía Navarro','9999-2012','Pendiente','2026-04-09 09:50:00'),
('Fernanda','Ortiz','0501-2016-013','fernanda.ortiz@correo.com','8888-0013','F','2016-07-30',2,'María Ortiz','9999-2013','Activo','2026-04-17 15:20:00'),
('Gabriel','Silva','0501-2011-014','gabriel.silva@correo.com','8888-0014','M','2011-02-11',5,'Raúl Silva','9999-2014','Activo','2026-05-05 10:00:00'),
('Helena','Mendoza','0501-2013-015','helena.mendoza@correo.com','8888-0015','F','2013-09-16',4,'Teresa Mendoza','9999-2015','Activo','2026-05-13 12:30:00'),
('Iván','Cortés','0501-2010-016','ivan.cortes@correo.com','8888-0016','M','2010-04-27',6,'Gloria Cortés','9999-2016','Activo','2026-06-01 08:00:00'),
('Julia','Aguilar','0501-2019-017','julia.aguilar@correo.com','8888-0017','F','2019-12-05',1,'Hugo Aguilar','9999-2017','Activo','2026-07-08 09:15:00'),
('Kevin','Salazar','0501-2015-018','kevin.salazar@correo.com','8888-0018','M','2015-06-21',3,'Norma Salazar','9999-2018','Activo','2026-08-04 11:00:00'),
('Laura','Peña','0501-2012-019','laura.pena@correo.com','8888-0019','F','2012-01-14',4,'Silvia Peña','9999-2019','Activo','2026-08-19 14:45:00'),
('Mario','Guzmán','0501-2011-020','mario.guzman@correo.com','8888-0020','M','2011-08-08',5,'Félix Guzmán','9999-2020','Activo','2026-09-03 10:20:00'),
('Natalia','Fuentes','0501-2016-021','natalia.fuentes@correo.com','8888-0021','F','2016-11-11',2,'Ivonne Fuentes','9999-2021','Activo','2026-09-21 13:10:00'),
('Omar','Delgado','0501-2010-022','omar.delgado@correo.com','8888-0022','M','2010-05-18',6,'Beatriz Delgado','9999-2022','Activo','2026-10-06 09:00:00'),
('Paula','Cáceres','0501-2013-023','paula.caceres@correo.com','8888-0023','F','2013-03-25',4,'René Cáceres','9999-2023','Activo','2026-10-14 11:35:00'),
('Ricardo','Blanco','0501-2019-024','ricardo.blanco@correo.com','8888-0024','M','2019-07-02',1,'Sonia Blanco','9999-2024','Activo','2026-11-11 08:50:00');

INSERT INTO calificaciones (estudiante_id, curso_id, materia, periodo, nota) VALUES
(1,4,'Matemáticas','II Trimestre',85.50),
(1,4,'Español','II Trimestre',78.00),
(2,5,'Ciencias','II Trimestre',91.25),
(2,5,'Inglés','II Trimestre',88.00),
(4,6,'Matemáticas','II Trimestre',74.50),
(5,4,'Español','II Trimestre',82.75),
(8,5,'Ciencias','II Trimestre',69.00),
(9,4,'Inglés','II Trimestre',95.00),
(11,3,'Matemáticas','II Trimestre',80.00),
(14,5,'Español','II Trimestre',77.25);

INSERT INTO asistencia (estudiante_id, fecha, estado, observacion) VALUES
(1,CURDATE(),'Presente',NULL),
(2,CURDATE(),'Presente',NULL),
(3,CURDATE(),'Tarde','Llegó 10 min tarde'),
(4,CURDATE(),'Presente',NULL),
(5,CURDATE(),'Presente',NULL),
(6,CURDATE(),'Ausente','Sin aviso'),
(7,CURDATE(),'Presente',NULL),
(8,CURDATE(),'Presente',NULL),
(9,CURDATE(),'Excusa','Cita médica'),
(10,CURDATE(),'Presente',NULL),
(11,CURDATE(),'Presente',NULL),
(12,CURDATE(),'Presente',NULL);

INSERT INTO horarios (curso_id, materia, dia_semana, hora_inicio, hora_fin, docente_id, aula) VALUES
(4,'Matemáticas','Lunes','07:00:00','08:30:00',2,'A-201'),
(4,'Español','Lunes','08:30:00','10:00:00',3,'A-201'),
(5,'Ciencias','Martes','07:00:00','08:30:00',4,'B-105'),
(5,'Inglés','Martes','08:30:00','10:00:00',5,'B-105'),
(6,'Matemáticas','Miércoles','07:00:00','08:30:00',2,'B-301'),
(3,'Español','Jueves','10:00:00','11:30:00',3,'P-102'),
(2,'Matemáticas','Viernes','07:00:00','08:30:00',2,'P-101'),
(1,'Juegos','Viernes','08:30:00','09:30:00',1,'Pre-1');

INSERT INTO pagos (estudiante_id, concepto, monto, fecha, metodo, estado) VALUES
(1,'Matrícula 2026',1200.00,'2026-01-08','Transferencia','Pagado'),
(2,'Matrícula 2026',1200.00,'2026-01-15','Efectivo','Pagado'),
(3,'Matrícula 2026',1200.00,'2026-01-22','Tarjeta','Pendiente'),
(4,'Matrícula 2026',1200.00,'2026-02-03','Transferencia','Pagado'),
(5,'Pensión Marzo',300.00,'2026-03-05','Efectivo','Pagado'),
(6,'Pensión Marzo',300.00,'2026-03-06','Efectivo','Vencido');

INSERT INTO avisos (titulo, contenido, categoria, autor, fecha) VALUES
('Reunión de padres','Reunión general de padres de familia el viernes a las 4:00 p.m. en el auditorio.','General','Dirección General','2026-10-14'),
('Cierre de notas II trimestre','Los docentes deben entregar las notas del II trimestre antes del 30 de junio.','Académico','Coordinación Académica','2026-10-13'),
('Pago de matrícula','Últimos 5 días para realizar el pago de la matrícula del próximo ciclo.','Administrativo','Administración','2026-10-12'),
('Feria de ciencias','Inscripciones abiertas para la feria de ciencias por nivel.','Eventos','Coordinación Académica','2026-10-10');
