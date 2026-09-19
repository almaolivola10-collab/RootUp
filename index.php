<?php
// index.php — Punto de entrada del sitio
//
// Este archivo va a propósito en la raíz del proyecto (fuera de /src)
// porque así lo requiere el hosting (InfinityFree): cuando alguien
// entra al dominio sin especificar ninguna ruta, el servidor busca
// automáticamente un "index.php" o "index.html" en la raíz.
// Por eso no se puede mover a una subcarpeta. Si estuviera dentro de
// /src, visitar el dominio directamente no mostraría nada.
//
// Su única función es redirigir a la app real.

header("Location: src/index.html");
exit();