// ══════════════════════════════════════════════════════
//  app.js — Lógica principal de RootUp
//  Laboratorio de Programación 2026
// ══════════════════════════════════════════════════════

// ─── Variables globales ───────────────────────────────
let categoriaActual = 'todas'; // Filtro activo en búsqueda
let plantaRiegoId   = null;    // ID de planta al registrar riego
let usuarioActual   = null;    // Datos del usuario logueado
let hemisferio      = null;    // 'sur' o 'norte'
let favoritosCache  = [];      // IDs de favoritos cargados desde MySQL
let misPlantasCache = [];      // Plantas propias cargadas desde MySQL

// ─── URL base de los archivos PHP ────────────────────
const API = './api';

// ══════════════════════════════════════════════════════
//  USUARIO (localStorage — solo para mantener sesión)
// ══════════════════════════════════════════════════════

function guardarUsuario(u) { localStorage.setItem('ru-usuario', JSON.stringify(u)); }
function cargarUsuario()   { return JSON.parse(localStorage.getItem('ru-usuario') || 'null'); }

// ══════════════════════════════════════════════════════
//  HEMISFERIO (localStorage)
// ══════════════════════════════════════════════════════

function elegirHemisferio(opcion) {
  hemisferio = opcion;
  localStorage.setItem('ru-hemisferio', opcion);
  cerrarModal('modal-hemisferio');
}

function cargarHemisferio() {
  hemisferio = localStorage.getItem('ru-hemisferio') || 'sur';
}

// ══════════════════════════════════════════════════════
//  FAVORITOS (MySQL)
// ══════════════════════════════════════════════════════

// Trae los favoritos del usuario desde MySQL
async function cargarFavoritas() {
  if (!usuarioActual) return;
  try {
    const r = await fetch(`${API}/favoritos.php?usuario_id=${usuarioActual.id}`);
    const d = await r.json();
    if (d.exito && Array.isArray(d.favoritos)) {
      favoritosCache = d.favoritos;
    } else {
      favoritosCache = [];
    }
  } catch (e) {
    console.error('Error cargando favoritos:', e);
    favoritosCache = [];
  }
}

// Agrega o quita un favorito en MySQL
async function toggleFavorita(id, evento) {
  evento.stopPropagation();
  if (!usuarioActual) return;
  try {
    const r = await fetch(`${API}/favoritos.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ usuario_id: usuarioActual.id, planta_id: id })
    });
    const d = await r.json();
    if (d.exito) {
      // Actualizar la caché
      if (d.accion === 'agregado') favoritosCache.push(id);
      else favoritosCache = favoritosCache.filter(x => x !== id);

      // Actualizar el ícono del corazón en la card
      const btn = document.querySelector(`.card-fav-btn[data-id="${id}"]`);
      if (btn) btn.textContent = favoritosCache.includes(id) ? '❤️' : '🤍';

      // Si estamos en favoritas, refrescar la lista
      if (document.getElementById('pantalla-favoritas').classList.contains('activa')) {
        renderFavoritas();
      }
    }
  } catch (e) {
    console.error('Error actualizando favorito:', e);
  }
}

// Agrega o quita favorito desde el modal de detalle
async function toggleFavoritaDesdeDetalle(id) {
  if (!usuarioActual) return;
  await toggleFavorita(id, { stopPropagation: () => {} });
  const btn = document.getElementById('btn-fav-detalle');
  if (btn) btn.textContent = favoritosCache.includes(id) ? '❤️ En favoritas' : '🤍 Agregar favorita';
}

// ══════════════════════════════════════════════════════
//  MIS PLANTAS (MySQL)
// ══════════════════════════════════════════════════════

// Trae las plantas propias del usuario desde MySQL
async function cargarMisPlantas() {
  if (!usuarioActual) return;
  try {
    const r = await fetch(`${API}/mis_plantas.php?usuario_id=${usuarioActual.id}`);
    const d = await r.json();
    if (d.exito) misPlantasCache = d.plantas;
  } catch (e) {
    console.error('Error cargando mis plantas:', e);
  }
}

// ══════════════════════════════════════════════════════
//  NAVEGACIÓN
// ══════════════════════════════════════════════════════

async function irA(id) {
  // Ocultar todas las pantallas y desmarcar botones
  document.querySelectorAll('.pantalla').forEach(p => p.classList.remove('activa'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));

  // Mostrar la pantalla correcta
  document.getElementById('pantalla-' + id).classList.add('activa');

  // Activar el botón correspondiente si existe
  const btnNav = document.getElementById('nav-' + id);
  if (btnNav) btnNav.classList.add('active');

  // Ocultar navbar en login y registro
  const navbar = document.getElementById('navbar');
  if (id === 'login' || id === 'registro') {
    navbar.classList.add('hidden');
  } else {
    navbar.classList.remove('hidden');
  }

  // Cargar contenido de cada pantalla
  if (id === 'inicio')        await renderInicio();
  if (id === 'buscar')        renderBuscar();
  if (id === 'favoritas')     renderFavoritas();
  if (id === 'mis-plantas')   await renderMisPlantas();
  if (id === 'recordatorios') await renderRecordatorios();

  window.scrollTo(0, 0);
}

// ══════════════════════════════════════════════════════
//  LOGIN
// ══════════════════════════════════════════════════════

async function iniciarSesion() {
  const email    = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  const errorDiv = document.getElementById('login-error');

  if (!email || !password) {
    errorDiv.textContent = 'Completá todos los campos';
    return;
  }

  errorDiv.textContent = 'Iniciando sesión...';

  try {
    const respuesta = await fetch(`${API}/login.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email, password })
    });

    const datos = await respuesta.json();

    if (datos.exito) {
      usuarioActual = datos.usuario;
      guardarUsuario(datos.usuario);
      errorDiv.textContent = '';

      // Cargar datos desde MySQL antes de mostrar la app
      await cargarFavoritas();
      await cargarMisPlantas();

      irA('inicio');
    } else {
      errorDiv.textContent = datos.mensaje;
    }

  } catch (error) {
    errorDiv.textContent = 'Error de conexión con el servidor';
  }
}

// ══════════════════════════════════════════════════════
//  REGISTRO
// ══════════════════════════════════════════════════════

async function registrarUsuario() {
  const nombre   = document.getElementById('registro-nombre').value.trim();
  const email    = document.getElementById('registro-email').value.trim();
  const password = document.getElementById('registro-password').value;
  const errorDiv = document.getElementById('registro-error');
  const exitoDiv = document.getElementById('registro-exito');

  if (!nombre || !email || !password) {
    errorDiv.textContent = 'Completá todos los campos';
    exitoDiv.textContent = '';
    return;
  }

  if (password.length < 6) {
    errorDiv.textContent = 'La contraseña debe tener al menos 6 caracteres';
    return;
  }

  errorDiv.textContent = '';
  exitoDiv.textContent = 'Creando cuenta...';

  try {
    const respuesta = await fetch(`${API}/registro.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ nombre, email, password })
    });

    const datos = await respuesta.json();

    if (datos.exito) {
      exitoDiv.textContent = '¡Cuenta creada! Elegí tu hemisferio.';
      usuarioActual = datos.usuario;
      guardarUsuario(datos.usuario);

      // Mostrar selector de hemisferio y luego ir al inicio
      setTimeout(() => {
        document.getElementById('modal-hemisferio').classList.remove('hidden');
        setTimeout(() => irA('inicio'), 800);
      }, 1000);

    } else {
      errorDiv.textContent = datos.mensaje;
      exitoDiv.textContent = '';
    }

  } catch (error) {
    errorDiv.textContent = 'Error de conexión con el servidor';
    exitoDiv.textContent = '';
  }
}

// ══════════════════════════════════════════════════════
//  CERRAR SESIÓN
// ══════════════════════════════════════════════════════

function cerrarSesion() {
  usuarioActual   = null;
  favoritosCache  = [];
  misPlantasCache = [];
  localStorage.removeItem('ru-usuario');
  irA('login');
}

// ══════════════════════════════════════════════════════
//  PANTALLA: INICIO
// ══════════════════════════════════════════════════════

async function renderInicio() {
  // Saludo según hora
  const hora   = new Date().getHours();
  const saludo = hora < 12 ? 'Buenos días' : hora < 20 ? 'Buenas tardes' : 'Buenas noches';
  document.getElementById('saludo-hora').textContent = saludo;

  if (usuarioActual) {
    document.getElementById('saludo-nombre').textContent = `Hola, ${usuarioActual.nombre} 👋`;
  }

  // Estadísticas
  const urgentes = misPlantasCache.filter(p => diasHastaRiego(p) <= 0).length;

  document.getElementById('stats-row').innerHTML = `
    <div class="stat-card">
      <div class="stat-num">${PLANTAS.length}</div>
      <div class="stat-label">Plantas en la guía</div>
    </div>
    <div class="stat-card">
      <div class="stat-num">${Array.isArray(favoritosCache) ? favoritosCache.length : 0}</div>
      <div class="stat-label">Favoritas</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:${urgentes > 0 ? 'var(--rojo)' : 'var(--verde-medio)'}">
        ${urgentes}
      </div>
      <div class="stat-label">Riegos urgentes</div>
    </div>`;

  // Próximos riegos
  const contenedorRiegos = document.getElementById('proximos-riegos');
  if (misPlantasCache.length === 0) {
    contenedorRiegos.innerHTML = `
      <div style="text-align:center; padding:1rem; color:var(--texto-suave); font-size:14px;">
        Agregá plantas propias para ver los recordatorios de riego
      </div>`;
  } else {
    const ordenadas = [...misPlantasCache].sort((a, b) => diasHastaRiego(a) - diasHastaRiego(b));
    contenedorRiegos.innerHTML = ordenadas.slice(0, 4).map(p => {
      const dias       = diasHastaRiego(p);
      const planta     = PLANTAS.find(x => x.id === p.planta_id);
      const claseItem  = dias < 0 ? 'urgente' : dias === 0 ? 'hoy' : '';
      const claseBadge = dias < 0 ? 'badge-rojo' : dias === 0 ? 'badge-amarillo' : 'badge-verde';
      const textoBadge = dias < 0 ? `Venció hace ${Math.abs(dias)}d` : dias === 0 ? '¡Hoy!' : `En ${dias}d`;
      return `
        <div class="riego-item ${claseItem}">
          <div class="riego-emoji">${planta ? planta.emoji : '🌱'}</div>
          <div class="riego-info">
            <div class="riego-nombre">${p.nombre_personalizado || planta?.nombre || 'Planta'}</div>
            <div class="riego-fecha">Cada ${planta?.diasRiego || '?'} días</div>
          </div>
          <span class="riego-badge ${claseBadge}">${textoBadge}</span>
        </div>`;
    }).join('');
  }

  // Plantas destacadas (4 al azar)
  const mezcladas = [...PLANTAS].sort(() => Math.random() - 0.5).slice(0, 4);
  document.getElementById('plantas-destacadas').innerHTML = mezcladas.map(p => cardPlantaHTML(p)).join('');
}

// ══════════════════════════════════════════════════════
//  PANTALLA: BUSCAR
// ══════════════════════════════════════════════════════

function renderBuscar(filtroTexto = '') {
  let resultado = PLANTAS;

  if (categoriaActual !== 'todas') {
    resultado = resultado.filter(p =>
      Array.isArray(p.categoria)
        ? p.categoria.includes(categoriaActual)
        : p.categoria === categoriaActual
    );
  }

  if (filtroTexto) {
    const q = filtroTexto.toLowerCase();
    resultado = resultado.filter(p =>
      p.nombre.toLowerCase().includes(q) ||
      p.cientifico.toLowerCase().includes(q) ||
      p.tags.some(t => t.includes(q))
    );
  }

  const contenedor = document.getElementById('resultados-buscar');
  if (resultado.length === 0) {
    contenedor.innerHTML = `
      <div style="text-align:center; padding:2rem; color:var(--texto-suave); font-size:14px; grid-column:1/-1;">
        No encontramos plantas con esa búsqueda
      </div>`;
  } else {
    contenedor.innerHTML = resultado.map(p => cardPlantaHTML(p)).join('');
  }
}

function buscarPlantas() {
  const val = document.getElementById('input-buscar').value;
  document.getElementById('btn-clear-buscar').style.display = val ? 'block' : 'none';
  renderBuscar(val);
}

function limpiarBusqueda() {
  document.getElementById('input-buscar').value = '';
  document.getElementById('btn-clear-buscar').style.display = 'none';
  renderBuscar();
}

function filtrarCategoria(cat, btn) {
  categoriaActual = cat;
  document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
  btn.classList.add('activo');
  renderBuscar(document.getElementById('input-buscar').value);
}

// ══════════════════════════════════════════════════════
//  PANTALLA: FAVORITAS
// ══════════════════════════════════════════════════════

function renderFavoritas() {
  const favoritas  = PLANTAS.filter(p => favoritosCache.includes(p.id));
  const contenedor = document.getElementById('lista-favoritas');
  const vacio      = document.getElementById('favoritas-vacio');

  if (favoritas.length === 0) {
    contenedor.innerHTML = '';
    vacio.classList.remove('hidden');
  } else {
    vacio.classList.add('hidden');
    contenedor.innerHTML = favoritas.map(p => cardPlantaHTML(p)).join('');
  }
}

// ══════════════════════════════════════════════════════
//  PANTALLA: MIS PLANTAS
// ══════════════════════════════════════════════════════

async function renderMisPlantas() {
  // Recargar desde MySQL
  await cargarMisPlantas();

  const contenedor = document.getElementById('lista-mis-plantas');
  const vacio      = document.getElementById('mis-plantas-vacio');

  if (misPlantasCache.length === 0) {
    contenedor.innerHTML = '';
    vacio.classList.remove('hidden');
    return;
  }

  vacio.classList.add('hidden');
  contenedor.innerHTML = misPlantasCache.map(p => {
    const planta     = PLANTAS.find(x => x.id === p.planta_id);
    if (!planta) return '';
    const dias       = diasHastaRiego(p);
    const claseBadge = dias < 0 ? 'badge-rojo' : dias === 0 ? 'badge-amarillo' : 'badge-verde';
    const textoBadge = dias < 0 ? `Regar hace ${Math.abs(dias)}d` : dias === 0 ? '¡Regar hoy!' : `Regar en ${dias}d`;

    return `
      <div class="mi-planta-card">
        <div class="mi-planta-emoji">${planta.emoji}</div>
        <div class="mi-planta-info">
          <div class="mi-planta-nombre">${p.nombre_personalizado || planta.nombre}</div>
          <div class="mi-planta-especie">${planta.cientifico}</div>
          <div class="mi-planta-estado">
            <span class="riego-badge ${claseBadge}">${textoBadge}</span>
          </div>
          ${p.notas ? `<div style="font-size:12px;color:var(--texto-suave);margin-bottom:8px;font-style:italic">"${p.notas}"</div>` : ''}
          <div class="mi-planta-acciones">
            <button class="btn-sm btn-sm-verde" onclick="abrirModalRiego(${p.id})">💧 Registrar riego</button>
            <button class="btn-sm btn-sm-rojo"  onclick="eliminarMiPlanta(${p.id})">🗑</button>
          </div>
        </div>
      </div>`;
  }).join('');
}

function abrirModalAgregarPlanta() {
  document.getElementById('select-planta').innerHTML =
    PLANTAS.map(p => `<option value="${p.id}">${p.emoji} ${p.nombre}</option>`).join('');
  document.getElementById('input-ultimo-riego').value  = hoy();
  document.getElementById('input-nombre-planta').value = '';
  document.getElementById('input-notas').value         = '';
  document.getElementById('modal-agregar').classList.remove('hidden');
}

async function guardarMiPlanta() {
  const plantaId            = parseInt(document.getElementById('select-planta').value);
  const nombrePersonalizado = document.getElementById('input-nombre-planta').value.trim();
  const ultimoRiego         = document.getElementById('input-ultimo-riego').value;
  const notas               = document.getElementById('input-notas').value.trim();

  if (!ultimoRiego) { alert('Ingresá la fecha del último riego'); return; }
  if (!usuarioActual) return;

  try {
    const r = await fetch(`${API}/mis_plantas.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        accion:             'agregar',
        usuario_id:         usuarioActual.id,
        planta_id:          plantaId,
        nombre_personalizado: nombrePersonalizado,
        ultimo_riego:       ultimoRiego,
        notas
      })
    });
    const d = await r.json();
    if (d.exito) {
      cerrarModal('modal-agregar');
      await renderMisPlantas();
    } else {
      alert('Error al guardar: ' + d.mensaje);
    }
  } catch (e) {
    alert('Error de conexión');
  }
}

async function eliminarMiPlanta(id) {
  if (!confirm('¿Eliminar esta planta de tu lista?')) return;
  try {
    const r = await fetch(`${API}/mis_plantas.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ accion: 'eliminar', id })
    });
    const d = await r.json();
    if (d.exito) await renderMisPlantas();
  } catch (e) {
    console.error('Error eliminando planta:', e);
  }
}

// ══════════════════════════════════════════════════════
//  PANTALLA: RECORDATORIOS
// ══════════════════════════════════════════════════════

async function renderRecordatorios() {
  await cargarMisPlantas();

  const contenedor = document.getElementById('lista-recordatorios');
  const vacio      = document.getElementById('recordatorios-vacio');

  if (misPlantasCache.length === 0) {
    contenedor.innerHTML = '';
    vacio.classList.remove('hidden');
    return;
  }

  vacio.classList.add('hidden');
  const ordenadas = [...misPlantasCache].sort((a, b) => diasHastaRiego(a) - diasHastaRiego(b));

  contenedor.innerHTML = ordenadas.map(p => {
    const planta        = PLANTAS.find(x => x.id === p.planta_id);
    if (!planta) return '';
    const dias          = diasHastaRiego(p);
    const ciclo         = planta.diasRiego;
    const porcentaje    = Math.max(0, Math.min(100, ((ciclo - dias) / ciclo) * 100));
    const claseUrgencia = dias < 0 ? 'urgente' : dias <= 2 ? 'pronto' : '';
    const textoDias     = dias < 0
      ? `⚠️ Venció hace ${Math.abs(dias)} día${Math.abs(dias) !== 1 ? 's' : ''}`
      : dias === 0 ? '💧 ¡Regar hoy!'
      : `Faltan ${dias} día${dias !== 1 ? 's' : ''}`;

    return `
      <div class="recordatorio-card">
        <div class="rec-header">
          <div class="rec-emoji">${planta.emoji}</div>
          <div>
            <div class="rec-titulo">${p.nombre_personalizado || planta.nombre}</div>
            <div class="rec-especie">${planta.cientifico}</div>
          </div>
        </div>
        <div class="rec-barra-wrap">
          <div class="rec-barra ${claseUrgencia}" style="width:${porcentaje}%"></div>
        </div>
        <div class="rec-pie">
          <span class="rec-dias">${textoDias}</span>
          <button class="btn-sm btn-sm-verde" onclick="abrirModalRiego(${p.id})">💧 Regar</button>
        </div>
      </div>`;
  }).join('');
}

// ══════════════════════════════════════════════════════
//  MODAL: REGISTRAR RIEGO
// ══════════════════════════════════════════════════════

function abrirModalRiego(miPlantaId) {
  plantaRiegoId   = miPlantaId;
  const p         = misPlantasCache.find(x => x.id === miPlantaId);
  const planta    = PLANTAS.find(x => x.id === p?.planta_id);

  document.getElementById('modal-riego-nombre').textContent =
    p?.nombre_personalizado || planta?.nombre || 'Mi planta';
  document.getElementById('input-fecha-riego').value = hoy();
  document.getElementById('input-nota-riego').value  = '';
  document.getElementById('modal-riego').classList.remove('hidden');
}

async function confirmarRiego() {
  const fecha = document.getElementById('input-fecha-riego').value;
  if (!fecha) { alert('Ingresá la fecha del riego'); return; }
  if (!usuarioActual) return;

  const p = misPlantasCache.find(x => x.id === plantaRiegoId);
  if (!p) return;

  try {
    const r = await fetch(`${API}/riegos.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        usuario_id:  usuarioActual.id,
        planta_id:   p.planta_id,
        fecha_riego: fecha
      })
    });
    const d = await r.json();
    if (d.exito) {
      // Actualizar el ultimo_riego en la caché local
      const idx = misPlantasCache.findIndex(x => x.id === plantaRiegoId);
      if (idx !== -1) misPlantasCache[idx].ultimo_riego = fecha;

      cerrarModal('modal-riego');

      if (document.getElementById('pantalla-mis-plantas').classList.contains('activa'))   await renderMisPlantas();
      if (document.getElementById('pantalla-recordatorios').classList.contains('activa')) await renderRecordatorios();
      if (document.getElementById('pantalla-inicio').classList.contains('activa'))        await renderInicio();
    }
  } catch (e) {
    console.error('Error registrando riego:', e);
  }
}

// ══════════════════════════════════════════════════════
//  MODAL: DETALLE DE PLANTA
// ══════════════════════════════════════════════════════

function abrirDetalle(id) {
  const p = PLANTAS.find(x => x.id === id);
  if (!p) return;

  const fav             = favoritosCache.includes(id);
  const dificultadColor = p.dificultad === 'Fácil' ? 'var(--verde-medio)' : 'var(--amarillo)';
  const meses           = hemisferio === 'norte' && p.mesesSiembraNorte
    ? p.mesesSiembraNorte
    : p.mesesSiembra;

  document.getElementById('modal-planta-contenido').innerHTML = `
    <div class="detalle-hero">
      <div class="detalle-emoji">${p.emoji}</div>
      <div class="detalle-nombre">${p.nombre}</div>
      <div class="detalle-cientifico">${p.cientifico}</div>
    </div>
    <div class="detalle-fichas">
      <div class="ficha">
        <div class="ficha-icono">💧</div>
        <div class="ficha-label">Riego</div>
        <div class="ficha-valor">Cada ${p.diasRiego} días</div>
      </div>
      <div class="ficha">
        <div class="ficha-icono">☀️</div>
        <div class="ficha-label">Luz</div>
        <div class="ficha-valor">${p.luz}</div>
      </div>
      <div class="ficha">
        <div class="ficha-icono">📅</div>
        <div class="ficha-label">Mejor época</div>
        <div class="ficha-valor">${meses}</div>
      </div>
      <div class="ficha">
        <div class="ficha-icono">⭐</div>
        <div class="ficha-label">Dificultad</div>
        <div class="ficha-valor" style="color:${dificultadColor}">${p.dificultad}</div>
      </div>
    </div>
    <div class="detalle-seccion">
      <h4>Descripción</h4>
      <p>${p.descripcion}</p>
    </div>
    <div class="detalle-seccion">
      <h4>Cuidados</h4>
      <p>${p.cuidados}</p>
    </div>
    <div class="detalle-seccion">
      <h4>¿Sabías que...?</h4>
      <p style="font-style:italic; color:var(--verde-medio)">${p.curiosidad}</p>
    </div>
    <div class="detalle-acciones">
      <button class="btn-accion-outline" onclick="toggleFavoritaDesdeDetalle(${p.id})" id="btn-fav-detalle">
        ${fav ? '❤️ En favoritas' : '🤍 Agregar favorita'}
      </button>
      <button class="btn-accion" onclick="agregarDesdeDetalle(${p.id})">
        🪴 Agregar a mis plantas
      </button>
    </div>`;

  document.getElementById('modal-planta').classList.remove('hidden');
}

function agregarDesdeDetalle(plantaId) {
  cerrarModal('modal-planta');
  abrirModalAgregarPlanta();
  setTimeout(() => {
    document.getElementById('select-planta').value = plantaId;
  }, 100);
}

// ══════════════════════════════════════════════════════
//  FUNCIONES DE AYUDA
// ══════════════════════════════════════════════════════

// Genera el HTML de una card de planta
function cardPlantaHTML(p) {
  const esFav   = favoritosCache.includes(p.id);
  const tagHTML = p.tags.slice(0, 2).map(t =>
    `<span class="tag tag-verde">${t}</span>`).join('');

  return `
    <div class="card-planta" onclick="abrirDetalle(${p.id})">
      <div class="card-planta-emoji">${p.emoji}</div>
      <div class="card-planta-body">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
          <div style="flex:1; min-width:0;">
            <div class="card-planta-nombre">${p.nombre}</div>
            <div class="card-planta-cientifico">${p.cientifico}</div>
          </div>
          <button class="card-fav-btn" data-id="${p.id}"
            onclick="toggleFavorita(${p.id}, event)">${esFav ? '❤️' : '🤍'}</button>
        </div>
        <div class="card-planta-tags">
          ${tagHTML}
          <span class="tag tag-tierra">💧 ${p.diasRiego}d</span>
        </div>
      </div>
    </div>`;
}

// Calcula los días hasta el próximo riego
// Usa ultimo_riego (campo de MySQL) en vez de ultimoRiego (localStorage)
function diasHastaRiego(miPlanta) {
  const planta = PLANTAS.find(x => x.id === miPlanta.planta_id);
  if (!planta || !miPlanta.ultimo_riego) return 0;

  const ultimoRiego = new Date(miPlanta.ultimo_riego + 'T00:00:00');
  const proximo     = new Date(ultimoRiego);
  proximo.setDate(proximo.getDate() + planta.diasRiego);

  const ahora = new Date();
  ahora.setHours(0, 0, 0, 0);

  return Math.round((proximo - ahora) / (1000 * 60 * 60 * 24));
}

// Devuelve la fecha de hoy en formato YYYY-MM-DD
function hoy() {
  return new Date().toISOString().split('T')[0];
}

// Cierra un modal
function cerrarModal(id) {
  document.getElementById(id).classList.add('hidden');
}

// ══════════════════════════════════════════════════════
//  INICIO DE LA APP
// ══════════════════════════════════════════════════════

cargarHemisferio();
usuarioActual = cargarUsuario();

if (usuarioActual) {
  // Ya estaba logueado: cargar datos y entrar
  Promise.all([cargarFavoritas(), cargarMisPlantas()]).then(() => irA('inicio'));
} else {
  irA('login');
}
