<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario API · Panel de pruebas</title>
    <meta name="description" content="Panel para probar la API REST de productos hecha en Laravel: listar, crear, editar y eliminar.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: hsl(230 25% 7%);
            --panel: hsl(230 22% 11% / .75);
            --line: hsl(230 20% 22%);
            --text: hsl(230 30% 94%);
            --muted: hsl(230 12% 62%);
            --accent: hsl(162 80% 52%);
            --accent-2: hsl(262 85% 68%);
            --danger: hsl(352 85% 62%);
            --warn: hsl(38 95% 60%);
            --radius: 14px;
        }
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: 'Outfit', system-ui, sans-serif;
            background:
                radial-gradient(900px 500px at 10% -10%, hsl(262 85% 40% / .35), transparent 60%),
                radial-gradient(800px 500px at 110% 10%, hsl(162 80% 35% / .25), transparent 60%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        .wrap { max-width: 1180px; margin: 0 auto; padding: 32px 20px 60px; }
        header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; }
        h1 { font-size: clamp(26px, 4vw, 36px); font-weight: 700; letter-spacing: -.02em; }
        h1 span { background: linear-gradient(90deg, var(--accent), var(--accent-2)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .sub { color: var(--muted); margin-top: 4px; }
        .status { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid var(--line); border-radius: 999px; background: var(--panel); font-size: 14px; }
        .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--warn); box-shadow: 0 0 10px currentColor; }
        .dot.ok { background: var(--accent); color: var(--accent); }
        .dot.err { background: var(--danger); color: var(--danger); }
        .grid { display: grid; grid-template-columns: 360px 1fr; gap: 20px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 20px; backdrop-filter: blur(14px); }
        .card h2 { font-size: 17px; font-weight: 600; margin-bottom: 14px; }
        label { display: block; font-size: 13px; color: var(--muted); margin: 10px 0 5px; }
        input, textarea, select {
            width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line);
            background: hsl(230 25% 6% / .7); color: var(--text); font: inherit; font-size: 14px; transition: border-color .2s, box-shadow .2s;
        }
        input:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px hsl(162 80% 52% / .15); }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; cursor: pointer;
            padding: 10px 16px; border-radius: 10px; font: inherit; font-weight: 600; font-size: 14px;
            transition: transform .15s, filter .2s, background .2s;
        }
        .btn:hover { transform: translateY(-1px); filter: brightness(1.1); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), hsl(180 75% 45%)); color: hsl(230 30% 8%); width: 100%; margin-top: 16px; }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--line); }
        .btn-sm { padding: 6px 10px; font-size: 13px; }
        .btn-danger { background: hsl(352 85% 62% / .15); color: var(--danger); }
        .toolbar { display: flex; gap: 10px; margin-bottom: 14px; }
        .toolbar input { flex: 1; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: var(--muted); font-weight: 500; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; padding: 8px 10px; border-bottom: 1px solid var(--line); }
        td { padding: 11px 10px; border-bottom: 1px solid hsl(230 20% 16%); vertical-align: middle; }
        tr { transition: background .2s; }
        tbody tr:hover { background: hsl(230 25% 15% / .6); }
        tbody tr.nuevo { animation: flash 1.2s ease; }
        @keyframes flash { from { background: hsl(162 80% 52% / .25); } to { background: transparent; } }
        .sku { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--accent-2); }
        .tag { font-size: 12px; padding: 3px 8px; border-radius: 999px; background: hsl(262 85% 68% / .14); color: hsl(262 90% 80%); }
        .low { color: var(--warn); font-weight: 600; }
        .acciones { display: flex; gap: 6px; justify-content: flex-end; }
        .empty { text-align: center; color: var(--muted); padding: 30px; }
        .console { margin-top: 20px; }
        .console pre {
            font-family: 'JetBrains Mono', monospace; font-size: 12.5px; line-height: 1.55; background: hsl(230 30% 5%);
            border: 1px solid var(--line); border-radius: 10px; padding: 14px; max-height: 260px; overflow: auto; white-space: pre-wrap; color: hsl(230 20% 80%);
        }
        .method { font-weight: 600; }
        .code-ok { color: var(--accent); } .code-err { color: var(--danger); }
        .errors { color: var(--danger); font-size: 13px; margin-top: 10px; }
        .endpoints { display: grid; gap: 6px; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--muted); margin-top: 14px; }
        .endpoints b { color: var(--text); display: inline-block; width: 58px; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Inventario <span>API REST</span></h1>
            <p class="sub">Laravel {{ app()->version() }} · Panel para probar el CRUD de <code>/api/productos</code></p>
        </div>
        <div class="status" id="estado"><span class="dot" id="estado-dot"></span><span id="estado-texto">Conectando…</span></div>
    </header>

    <main class="grid">
        <section class="card" aria-labelledby="form-titulo">
            <h2 id="form-titulo">Nuevo producto</h2>
            <form id="form-producto" novalidate>
                <input type="hidden" id="producto-id">
                <div class="row">
                    <div><label for="sku">SKU</label><input id="sku" placeholder="TEC-010"></div>
                    <div><label for="categoria">Categoría</label><input id="categoria" placeholder="Periféricos"></div>
                </div>
                <label for="nombre">Nombre</label><input id="nombre" placeholder="Teclado inalámbrico">
                <label for="descripcion">Descripción</label><textarea id="descripcion" rows="2" placeholder="Opcional"></textarea>
                <div class="row">
                    <div><label for="precio">Precio</label><input id="precio" type="number" step="0.01" min="0" placeholder="99.90"></div>
                    <div><label for="stock">Stock</label><input id="stock" type="number" min="0" placeholder="10"></div>
                </div>
                <div id="errores" class="errors" role="alert"></div>
                <button class="btn btn-primary" id="btn-guardar" type="submit">Crear producto</button>
                <button class="btn btn-ghost" id="btn-cancelar" type="button" style="width:100%;margin-top:8px;display:none">Cancelar edición</button>
            </form>
            <div class="endpoints" aria-label="Endpoints disponibles">
                <div><b>GET</b>/api/productos</div>
                <div><b>POST</b>/api/productos</div>
                <div><b>GET</b>/api/productos/{id}</div>
                <div><b>PUT</b>/api/productos/{id}</div>
                <div><b>DELETE</b>/api/productos/{id}</div>
            </div>
        </section>

        <section>
            <div class="card">
                <div class="toolbar">
                    <input id="buscar" type="search" placeholder="Buscar por nombre, SKU o descripción…" aria-label="Buscar">
                    <button class="btn btn-ghost" id="btn-recargar" type="button">↻ Recargar</button>
                </div>
                <table>
                    <thead><tr><th>SKU</th><th>Producto</th><th>Precio</th><th>Stock</th><th></th></tr></thead>
                    <tbody id="tabla"><tr><td colspan="5" class="empty">Cargando…</td></tr></tbody>
                </table>
            </div>
            <div class="card console">
                <h2>Respuesta de la API</h2>
                <pre id="consola">Aquí verás el método, la URL, el código HTTP y el JSON de cada petición.</pre>
            </div>
        </section>
    </main>
</div>

<script>
const API = '/api/productos';
const $ = (id) => document.getElementById(id);
const campos = ['sku', 'nombre', 'descripcion', 'categoria', 'precio', 'stock'];
let ultimoCreado = null;

async function llamar(metodo, url, cuerpo) {
    const res = await fetch(url, {
        method: metodo,
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: cuerpo ? JSON.stringify(cuerpo) : undefined,
    });
    const datos = res.status === 204 ? null : await res.json().catch(() => null);
    const clase = res.ok ? 'code-ok' : 'code-err';
    $('consola').innerHTML = `<span class="method">${metodo}</span> ${url}  →  <span class="${clase}">${res.status} ${res.statusText}</span>\n\n` +
        escapar(JSON.stringify(datos, null, 2));
    return { res, datos };
}

function escapar(t) {
    return String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

async function cargar() {
    const q = $('buscar').value.trim();
    const { res, datos } = await llamar('GET', q ? `${API}?buscar=${encodeURIComponent(q)}&por_pagina=50` : `${API}?por_pagina=50`);
    if (!res.ok) return;
    const filas = datos.data;
    $('tabla').innerHTML = filas.length ? filas.map((p) => `
        <tr data-id="${p.id}" class="${p.id === ultimoCreado ? 'nuevo' : ''}">
            <td class="sku">${escapar(p.sku)}</td>
            <td><div>${escapar(p.nombre)}</div><span class="tag">${escapar(p.categoria)}</span></td>
            <td>$${p.precio.toFixed(2)}</td>
            <td class="${p.bajo_stock ? 'low' : ''}">${p.stock}${p.bajo_stock ? ' ⚠' : ''}</td>
            <td class="acciones">
                <button class="btn btn-ghost btn-sm" id="editar-${p.id}" onclick="editar(${p.id})">Editar</button>
                <button class="btn btn-danger btn-sm" id="eliminar-${p.id}" onclick="eliminar(${p.id})">Eliminar</button>
            </td>
        </tr>`).join('') : '<tr><td colspan="5" class="empty">No hay productos.</td></tr>';
    ultimoCreado = null;
}

async function editar(id) {
    const { res, datos } = await llamar('GET', `${API}/${id}`);
    if (!res.ok) return;
    campos.forEach((c) => $(c).value = datos.data[c] ?? '');
    $('producto-id').value = id;
    $('form-titulo').textContent = `Editar producto #${id}`;
    $('btn-guardar').textContent = 'Guardar cambios';
    $('btn-cancelar').style.display = 'block';
}

function limpiar() {
    $('form-producto').reset();
    $('producto-id').value = '';
    $('errores').textContent = '';
    $('form-titulo').textContent = 'Nuevo producto';
    $('btn-guardar').textContent = 'Crear producto';
    $('btn-cancelar').style.display = 'none';
}

async function eliminar(id) {
    if (!confirm(`¿Eliminar el producto #${id}?`)) return;
    const { res } = await llamar('DELETE', `${API}/${id}`);
    if (res.ok) cargar();
}

$('form-producto').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('producto-id').value;
    const cuerpo = {};
    campos.forEach((c) => { const v = $(c).value.trim(); cuerpo[c] = v === '' ? null : v; });
    const { res, datos } = await llamar(id ? 'PUT' : 'POST', id ? `${API}/${id}` : API, cuerpo);
    if (res.status === 422) {
        $('errores').innerHTML = Object.values(datos.errors).flat().map(escapar).join('<br>');
        return;
    }
    if (res.ok) {
        if (!id) ultimoCreado = datos.data.id;
        limpiar();
        cargar();
    }
});

$('btn-cancelar').addEventListener('click', limpiar);
$('btn-recargar').addEventListener('click', cargar);
let t;
$('buscar').addEventListener('input', () => { clearTimeout(t); t = setTimeout(cargar, 300); });

fetch('/api/estado', { headers: { Accept: 'application/json' } })
    .then((r) => r.json())
    .then((d) => {
        const ok = d.base_de_datos !== 'sin conexión';
        $('estado-dot').className = 'dot ' + (ok ? 'ok' : 'err');
        $('estado-texto').textContent = ok ? `API en línea · BD: ${d.base_de_datos}` : 'API en línea · BD sin conexión';
    })
    .catch(() => { $('estado-dot').className = 'dot err'; $('estado-texto').textContent = 'API sin respuesta'; });

cargar();
</script>
</body>
</html>
