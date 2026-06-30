**RootUp**

Materia: Laboratorio de Programación  
Escuela: EEST N.º 1 "Eduardo Ader" — Vicente López  
Curso: 5°3° A-B  
Año: 2026

-------------------------------------------------------------

Integrantes del grupo

- Mikaela Batirola
- Lucas Benítez
- Alma Lazarte
- Alma Olívola

-------------------------------------------------------------

Descripción del proyecto

RootUp es una aplicación web orientada al sector agropecuario y doméstico que permite a los usuarios:

- Consultar una base de datos de plantas con información de riego, luz y épocas de siembra adaptadas al hemisferio sur.
- Guardar sus plantas personales y recibir alertas de riego
- Marcar plantas favoritas para acceso rápido
- Registrar el historial de riego de cada planta

La idea del proyecto surgió inicialmente como una propuesta relacionada con inteligencia artificial y reconocimiento de plantas mediante imágenes. Pero, después de investigar la complejidad de ese sistema y analizar nuestras posibilidades, decidimos transformar la idea en una plataforma más accesible y realista para nuestro nivel actual de programación.

A partir de eso creamos RootUp, una aplicación inspirada en la idea de un “Google de plantas”, donde los usuarios pueden buscar especies, explorar categorías, acceder a información detallada y registrar cuidados básicos como los riegos.

Este proyecto forma parte del Proyecto Integrador Anual, que también incluye SmartPlant: un sistema físico de autorriego automático con sensor de humedad y Arduino.

-------------------------------------------------------------

Estructura de carpetas

RootUp/
├── docs/                 ← Documentación del proyecto
│   ├── wireframes/       ← Bocetos de las pantallas
│   │    ├── boceto (2).jpg
│   │    └── boceto.jpg
│   └── informe.pdf       ← Informe APA v7
│
├── img/                  ← Imágenes del proyecto
├── src/                  ← Código de la app
│   ├── api/  
│   │    ├── conexion.php
│   │    ├── favoritos.php
│   │    ├── login.php
│   │    ├── mis_plantas.php
│   │    ├── registro.php
│   │    └── riegos.php
│   ├── index.html        ← Estructura principal (pantallas y modales)
│   ├── style.css         ← Estilos visuales de toda la app
│   ├── app.js            ← Lógica: navegación, filtros, riego, favoritas
│   └── datos.js          ← Base de datos de plantas
│
├── index.php
├── README.md             ← Este archivo
└── rootup.sql            ← Base de datos de la app

-------------------------------------------------------------

Pantallas de la app

Inicio | Saludo, estadísticas rápidas y plantas destacadas
Buscar| Búsqueda por nombre y por categoría
Favoritas | Lista de plantas marcadas como favoritas
Mis plantas | Plantas propias del usuario
Alertas | Recordatorios de riego con barra de urgencia

-------------------------------------------------------------

Links del proyecto

Prototipo con I.A.: [rootup.base44.app](https://rootup.base44.app)
App actual: (https://rootup.infinityfreeapp.com/src/index.html)
Repositorio GitHub: (https://github.com/almaaluzz05/Proyecto-Agro)

-------------------------------------------------------------

Estado del proyecto

Inicio del primer cuatrimestre — Mayo 2026

[x] Base de datos de plantas
[x] Búsqueda y filtros por categoría
[x] Sistema de favoritas
[x] Seguimiento de plantas propias
[x] Alertas de riego con historial
[ ] Login con cuenta propia (segundo cuatrimestre)
[ ] Conexión con SmartPlant (segundo cuatrimestre)
[ ] Base de datos en la nube (segundo cuatrimestre)
[ ] App publicada (segundo cuatrimestre)

-------------------------------------------------------------
Estado del proyecto

Final del primer cuatrimestre — Julio 2026

[x] Base de datos de plantas
[x] Búsqueda y filtros por categoría
[x] Sistema de favoritas
[x] Seguimiento de plantas propias
[x] Alertas de riego con historial
[x] Login con cuenta propia (segundo cuatrimestre)
[ ] Conexión con SmartPlant (segundo cuatrimestre)
[x] Base de datos en la nube
[x] App publicada (mediante InfinityFree)

-------------------------------------------------------------

Proyecto Integrador Anual — LPR 5° 3° A-B — 2026
