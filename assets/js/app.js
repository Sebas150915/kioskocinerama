// Estado global de la aplicación
const estado = {
    idCine: 1, // Default cinema ID
    peliculaSeleccionada: null,
    funcionSeleccionada: null,
    butacasSeleccionadas: [],
    tarifas: {},
    confiteria: {},
    facturacion: {},
    totales: {
        entradas: 0,
        confiteria: 0
    }
};

// ===== FUNCIONES DE NAVEGACIÓN =====
function goToScreen(screenName) {
    document.querySelectorAll('.screen').forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById(`screen-${screenName}`).classList.add('active');

    // Inicializar pantalla según corresponda
    switch (screenName) {
        case 'peliculas':
            cargarPeliculas();
            break;
        case 'horarios':
            cargarHorarios();
            break;
        case 'butacas':
            generarMapaButacas();
            break;
        case 'tarifas':
            cargarTarifas();
            break;
        case 'confiteria':
            cargarConfiteria();
            break;
        case 'pago':
            mostrarResumenPago();
            break;
    }
}

// ===== PANTALLA DE PELÍCULAS =====
function cargarPeliculas() {
    const grid = document.getElementById('peliculas-grid');
    grid.innerHTML = '<div style="text-align:center; color:white; padding:50px;"><i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Cargando películas...</div>';

    fetch(`assets/ajax/peliculas.php?idcine=${estado.idCine}`)
        .then(response => response.json())
        .then(data => {
            grid.innerHTML = '';

            if (data.error) {
                grid.innerHTML = `<div style="text-align:center; color:red; padding:50px;">${data.error}</div>`;
                return;
            }

            if (data.length === 0) {
                grid.innerHTML = '<div style="text-align:center; color:white; padding:50px;">No hay películas disponibles hoy</div>';
                return;
            }

            data.forEach(pelicula => {
                const card = document.createElement('div');
                card.className = 'pelicula-card';
                card.onclick = () => seleccionarPelicula(pelicula);

                const imagePath = `http://localhost/cineramaonline/panel/assets/img/peliculas/${pelicula.img}`;
                card.innerHTML = `
                    <img src="${imagePath}" alt="${pelicula.nombre}" class="pelicula-poster" onerror="this.onerror=null;this.src='assets/img/no-poster.jpg';">
                    <div class="pelicula-info">
                        <h3>${pelicula.nombre}</h3>
                        <div class="genero">${pelicula.genero || 'General'}</div>
                        <div class="duracion">${pelicula.duracion ? pelicula.duracion + ' min' : ''}</div>
                    </div>
                `;
                grid.appendChild(card);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            grid.innerHTML = '<div style="text-align:center; color:red; padding:50px;">Error al cargar películas</div>';
        });
}

function seleccionarPelicula(pelicula) {
    estado.peliculaSeleccionada = pelicula;
    goToScreen('horarios');
}

// ===== PANTALLA DE HORARIOS =====
function cargarHorarios() {
    const titulo = document.getElementById('pelicula-title');
    const container = document.getElementById('horarios-container');

    titulo.textContent = estado.peliculaSeleccionada.nombre;
    container.innerHTML = '<div style="text-align:center; color:white; padding:50px;"><i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Cargando horarios...</div>';

    fetch(`assets/ajax/horarios.php?idcine=${estado.idCine}&idpelicula=${estado.peliculaSeleccionada.id}`)
        .then(response => response.json())
        .then(data => {
            container.innerHTML = '';

            if (data.error) {
                container.innerHTML = `<div style="text-align:center; color:red; padding:50px;">${data.error}</div>`;
                return;
            }

            if (!data.funciones || data.funciones.length === 0) {
                container.innerHTML = '<div style="text-align:center; color:white; padding:50px;">No hay funciones disponibles hoy</div>';
                return;
            }

            // Agrupar por día (hoy)
            const diaDiv = document.createElement('div');
            diaDiv.className = 'dia-horarios';
            diaDiv.innerHTML = `<h3>Hoy - ${new Date().toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'short' })}</h3>`;

            const horariosList = document.createElement('div');
            horariosList.className = 'horarios-list';

            data.funciones.forEach(funcion => {
                const card = document.createElement('div');
                card.className = 'horario-card';
                card.onclick = () => seleccionarFuncion(funcion);
                card.innerHTML = `
                    <div class="hora">${funcion.hora}</div>
                    <div class="sala">${funcion.sala}</div>
                    <div class="formato">${funcion.formato} ${funcion.idioma}</div>
                `;
                horariosList.appendChild(card);
            });

            diaDiv.appendChild(horariosList);
            container.appendChild(diaDiv);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div style="text-align:center; color:red; padding:50px;">Error al cargar horarios</div>';
        });
}

function seleccionarFuncion(funcion) {
    estado.funcionSeleccionada = funcion;
    goToScreen('butacas');
}

// ===== PANTALLA DE BUTACAS =====
function generarMapaButacas() {
    const info = document.getElementById('funcion-info');
    info.innerHTML = `
        ${estado.peliculaSeleccionada.nombre} - ${estado.funcionSeleccionada.hora} - 
        ${estado.funcionSeleccionada.sala} - ${estado.funcionSeleccionada.formato}
    `;

    const mapa = document.getElementById('mapa-butacas');
    mapa.innerHTML = '<div style="text-align:center; color:white; padding:50px;"><i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Cargando butacas...</div>';

    // Fetch real seat data from database
    fetch(`assets/ajax/obtener_butacas.php?id_cartelera=${estado.funcionSeleccionada.id_cartelera}&id_hora=${estado.funcionSeleccionada.id_hora}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mapa.innerHTML = `<div style="text-align:center; color:red; padding:50px;">${data.error}</div>`;
                return;
            }

            renderSeatMap(data);
        })
        .catch(error => {
            console.error('Error:', error);
            mapa.innerHTML = '<div style="text-align:center; color:red; padding:50px;">Error al cargar butacas</div>';
        });
}

function renderSeatMap(data) {
    const mapa = document.getElementById('mapa-butacas');
    mapa.innerHTML = '';

    // Get unique rows and sort
    const uniqueRows = [...new Set(data.layout.map(s => s.fila))];
    const cols = data.info.cols || 10;

    // Create row map
    const rowMap = {};
    uniqueRows.forEach((r, i) => rowMap[r] = i + 1);

    // Apply Grid Styles
    mapa.style.display = 'grid';
    mapa.style.gridTemplateColumns = `40px repeat(${cols}, 45px) 40px`;
    mapa.style.gap = '8px';
    mapa.style.alignItems = 'start';
    mapa.style.justifyContent = 'center';

    // Render row labels (left and right)
    uniqueRows.forEach((letter, index) => {
        const rowIndex = index + 1;
        // Left label
        mapa.appendChild(createSeatLabel(letter, 1, rowIndex));
        // Right label
        mapa.appendChild(createSeatLabel(letter, cols + 2, rowIndex));
    });

    // Create occupied set for quick lookup
    const occupiedSet = new Set(data.occupied.map(String));

    // Render seats
    data.layout.forEach(seat => {
        if (seat.tipo === 'PASILLO') return; // Skip aisles

        const rIndex = rowMap[seat.fila];
        const cIndex = parseInt(seat.columna) + 1; // +1 for label column

        const seatDiv = document.createElement('div');
        seatDiv.className = 'butaca';
        seatDiv.dataset.id = seat.id;
        seatDiv.dataset.rotulo = seat.fila + seat.num_asiento;
        seatDiv.style.gridColumn = cIndex;
        seatDiv.style.gridRow = rIndex;
        seatDiv.textContent = seat.num_asiento;

        // Check if occupied
        if (occupiedSet.has(String(seat.id))) {
            seatDiv.classList.add('ocupada');
        } else {
            seatDiv.classList.add('disponible');
            seatDiv.onclick = () => toggleButaca(seat.id, seatDiv);
        }

        mapa.appendChild(seatDiv);
    });

    actualizarButacasSeleccionadas();
}

function createSeatLabel(text, col, row) {
    const div = document.createElement('div');
    div.textContent = text;
    div.style.gridColumn = col;
    div.style.gridRow = row;
    div.style.fontWeight = 'bold';
    div.style.color = '#dc2626';
    div.style.display = 'flex';
    div.style.alignItems = 'center';
    div.style.justifyContent = 'center';
    div.style.fontSize = '18px';
    return div;
}

function toggleButaca(id, element) {
    if (element.classList.contains('ocupada')) return;

    const idStr = String(id);
    const index = estado.butacasSeleccionadas.indexOf(idStr);

    if (index > -1) {
        estado.butacasSeleccionadas.splice(index, 1);
        element.classList.remove('seleccionada');
        element.classList.add('disponible');
    } else {
        if (estado.butacasSeleccionadas.length >= 5) {
            alert('Solo puedes seleccionar un máximo de 5 butacas.');
            return;
        }
        estado.butacasSeleccionadas.push(idStr);
        element.classList.remove('disponible');
        element.classList.add('seleccionada');
    }

    actualizarButacasSeleccionadas();
}

function actualizarButacasSeleccionadas() {
    const count = document.getElementById('butacas-count');
    const list = document.getElementById('butacas-list');
    const btn = document.getElementById('btn-continuar-butacas');

    count.textContent = estado.butacasSeleccionadas.length;

    list.innerHTML = '';
    estado.butacasSeleccionadas.forEach(id => {
        const tag = document.createElement('div');
        tag.className = 'butaca-tag';
        // Find the seat element to get its rotulo
        const seatEl = document.querySelector(`[data-id="${id}"]`);
        tag.textContent = seatEl ? seatEl.dataset.rotulo : id;
        list.appendChild(tag);
    });

    btn.disabled = estado.butacasSeleccionadas.length === 0;
}

// ===== PANTALLA DE TARIFAS =====
const preciosTarifas = {
    general: 25.00,
    nino: 18.00,
    adultoMayor: 20.00,
    estudiante: 22.00
};

function cargarTarifas() {
    const totalButacas = estado.butacasSeleccionadas.length;
    document.getElementById('total-butacas').textContent = totalButacas;
    document.getElementById('butacas-total').textContent = totalButacas;

    // Resetear tarifas
    estado.tarifas = {
        general: 0,
        nino: 0,
        adultoMayor: 0,
        estudiante: 0
    };

    const container = document.getElementById('tarifas-container');
    container.innerHTML = '';

    const tarifasData = [
        { id: 'general', nombre: 'General', precio: preciosTarifas.general, descripcion: 'Entrada adulto' },
        { id: 'nino', nombre: 'Niño', precio: preciosTarifas.nino, descripcion: 'Menores de 12 años' },
        { id: 'adultoMayor', nombre: 'Adulto Mayor', precio: preciosTarifas.adultoMayor, descripcion: 'Mayores de 65 años' },
        { id: 'estudiante', nombre: 'Estudiante', precio: preciosTarifas.estudiante, descripcion: 'Con carnet vigente' }
    ];

    tarifasData.forEach(tarifa => {
        const card = document.createElement('div');
        card.className = 'tarifa-card';
        card.innerHTML = `
            <h4>${tarifa.nombre}</h4>
            <div class="precio">S/ ${tarifa.precio.toFixed(2)}</div>
            <div class="descripcion">${tarifa.descripcion}</div>
            <div class="cantidad-selector">
                <button class="btn-cantidad" onclick="cambiarTarifa('${tarifa.id}', -1)">-</button>
                <div class="cantidad-display" id="cantidad-${tarifa.id}">0</div>
                <button class="btn-cantidad" onclick="cambiarTarifa('${tarifa.id}', 1)">+</button>
            </div>
        `;
        container.appendChild(card);
    });

    actualizarResumenTarifas();
}

function cambiarTarifa(tipo, cambio) {
    const totalButacas = estado.butacasSeleccionadas.length;
    const totalAsignadas = Object.values(estado.tarifas).reduce((a, b) => a + b, 0);

    const nuevaCantidad = estado.tarifas[tipo] + cambio;

    if (nuevaCantidad < 0) return;
    if (cambio > 0 && totalAsignadas >= totalButacas) return;

    estado.tarifas[tipo] = nuevaCantidad;
    document.getElementById(`cantidad-${tipo}`).textContent = nuevaCantidad;

    actualizarResumenTarifas();
}

function actualizarResumenTarifas() {
    const totalAsignadas = Object.values(estado.tarifas).reduce((a, b) => a + b, 0);
    const totalButacas = estado.butacasSeleccionadas.length;

    document.getElementById('butacas-asignadas').textContent = totalAsignadas;

    let total = 0;
    total += estado.tarifas.general * preciosTarifas.general;
    total += estado.tarifas.nino * preciosTarifas.nino;
    total += estado.tarifas.adultoMayor * preciosTarifas.adultoMayor;
    total += estado.tarifas.estudiante * preciosTarifas.estudiante;

    estado.totales.entradas = total;
    document.getElementById('total-entradas').textContent = `S/ ${total.toFixed(2)}`;

    const btn = document.getElementById('btn-continuar-tarifas');
    btn.disabled = totalAsignadas !== totalButacas;
}

// ===== PANTALLA DE CONFITERÍA =====
const productosConfiteria = [
    { id: 1, nombre: "Combo Clásico", descripcion: "Pop corn grande + Gaseosa 32oz", precio: 22.00, emoji: "🍿" },
    { id: 2, nombre: "Combo Mega", descripcion: "2 Pop corn + 2 Gaseosas + Nachos", precio: 45.00, emoji: "🎬" },
    { id: 3, nombre: "Pop Corn Grande", descripcion: "Pop corn tamaño grande", precio: 12.00, emoji: "🍿" },
    { id: 4, nombre: "Gaseosa 32oz", descripcion: "Bebida tamaño grande", precio: 8.00, emoji: "🥤" },
    { id: 5, nombre: "Nachos", descripcion: "Con queso cheddar", precio: 15.00, emoji: "🧀" },
    { id: 6, nombre: "Hot Dog", descripcion: "Con salsa a elección", precio: 10.00, emoji: "🌭" },
    { id: 7, nombre: "Dulces", descripcion: "Variedad de dulces", precio: 5.00, emoji: "🍬" },
    { id: 8, nombre: "Agua Mineral", descripcion: "Agua sin gas 500ml", precio: 4.00, emoji: "💧" }
];

function cargarConfiteria() {
    const grid = document.getElementById('confiteria-grid');
    grid.innerHTML = '';

    // Resetear confitería
    estado.confiteria = {};
    productosConfiteria.forEach(p => estado.confiteria[p.id] = 0);

    productosConfiteria.forEach(producto => {
        const card = document.createElement('div');
        card.className = 'producto-card';
        card.innerHTML = `
            <div class="emoji">${producto.emoji}</div>
            <h4>${producto.nombre}</h4>
            <div class="descripcion">${producto.descripcion}</div>
            <div class="precio">S/ ${producto.precio.toFixed(2)}</div>
            <div class="cantidad-selector">
                <button class="btn-cantidad" onclick="cambiarConfiteria(${producto.id}, -1)">-</button>
                <div class="cantidad-display" id="confiteria-${producto.id}">0</div>
                <button class="btn-cantidad" onclick="cambiarConfiteria(${producto.id}, 1)">+</button>
            </div>
        `;
        grid.appendChild(card);
    });

    actualizarTotalConfiteria();
}

function cambiarConfiteria(id, cambio) {
    const nuevaCantidad = estado.confiteria[id] + cambio;
    if (nuevaCantidad < 0) return;

    estado.confiteria[id] = nuevaCantidad;
    document.getElementById(`confiteria-${id}`).textContent = nuevaCantidad;

    actualizarTotalConfiteria();
}

function actualizarTotalConfiteria() {
    let total = 0;

    productosConfiteria.forEach(producto => {
        total += estado.confiteria[producto.id] * producto.precio;
    });

    estado.totales.confiteria = total;
    document.getElementById('total-confiteria').textContent = `S/ ${total.toFixed(2)}`;
}

// ===== PANTALLA DE FACTURACIÓN =====
function cambiarComprobante() {
    const tipo = document.querySelector('input[name="comprobante"]:checked').value;

    document.getElementById('form-boleta').classList.remove('active');
    document.getElementById('form-factura').classList.remove('active');

    if (tipo === 'boleta') {
        document.getElementById('form-boleta').classList.add('active');
    } else {
        document.getElementById('form-factura').classList.add('active');
    }
}

// ===== PANTALLA DE PAGO =====
function mostrarResumenPago() {
    const resumen = document.getElementById('resumen-detalle');
    resumen.innerHTML = '';

    let html = '<h4 style="color: #dc2626; margin-bottom: 10px;">Entradas</h4>';

    if (estado.tarifas.general > 0) {
        html += `<div class="resumen-item">
            <span>${estado.tarifas.general}x General</span>
            <span>S/ ${(estado.tarifas.general * preciosTarifas.general).toFixed(2)}</span>
        </div>`;
    }
    if (estado.tarifas.nino > 0) {
        html += `<div class="resumen-item">
            <span>${estado.tarifas.nino}x Niño</span>
            <span>S/ ${(estado.tarifas.nino * preciosTarifas.nino).toFixed(2)}</span>
        </div>`;
    }
    if (estado.tarifas.adultoMayor > 0) {
        html += `<div class="resumen-item">
            <span>${estado.tarifas.adultoMayor}x Adulto Mayor</span>
            <span>S/ ${(estado.tarifas.adultoMayor * preciosTarifas.adultoMayor).toFixed(2)}</span>
        </div>`;
    }
    if (estado.tarifas.estudiante > 0) {
        html += `<div class="resumen-item">
            <span>${estado.tarifas.estudiante}x Estudiante</span>
            <span>S/ ${(estado.tarifas.estudiante * preciosTarifas.estudiante).toFixed(2)}</span>
        </div>`;
    }

    const tieneConfiteria = Object.values(estado.confiteria).some(c => c > 0);
    if (tieneConfiteria) {
        html += '<h4 style="color: #dc2626; margin: 20px 0 10px;">Confitería</h4>';
        productosConfiteria.forEach(producto => {
            if (estado.confiteria[producto.id] > 0) {
                html += `<div class="resumen-item">
                    <span>${estado.confiteria[producto.id]}x ${producto.nombre}</span>
                    <span>S/ ${(estado.confiteria[producto.id] * producto.precio).toFixed(2)}</span>
                </div>`;
            }
        });
    }

    resumen.innerHTML = html;

    const totalFinal = estado.totales.entradas + estado.totales.confiteria;
    document.getElementById('total-final').textContent = `S/ ${totalFinal.toFixed(2)}`;
    document.getElementById('pinpad-monto').textContent = `S/ ${totalFinal.toFixed(2)}`;
}

function procesarPago() {
    mostrarLoading();

    setTimeout(() => {
        document.getElementById('pinpad-mensaje').textContent = 'Procesando pago...';

        setTimeout(() => {
            document.getElementById('pinpad-mensaje').textContent = 'Ingrese su PIN';

            setTimeout(() => {
                document.getElementById('pinpad-mensaje').textContent = 'Verificando...';

                setTimeout(() => {
                    ocultarLoading();
                    mostrarConfirmacion();
                }, 2000);
            }, 2000);
        }, 1500);
    }, 1000);
}

function mostrarConfirmacion() {
    const codigo = generarCodigoCompra();
    document.getElementById('codigo-compra').textContent = codigo;
    goToScreen('confirmacion');
}

function generarCodigoCompra() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let codigo = '';
    for (let i = 0; i < 8; i++) {
        codigo += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return codigo;
}

function reiniciarKiosko() {
    estado.peliculaSeleccionada = null;
    estado.funcionSeleccionada = null;
    estado.butacasSeleccionadas = [];
    estado.tarifas = {};
    estado.confiteria = {};
    estado.facturacion = {};
    estado.totales = { entradas: 0, confiteria: 0 };

    goToScreen('inicio');
}

// ===== FUNCIONES AUXILIARES =====
function mostrarLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.id = 'loading';
    loading.innerHTML = `
        <div class="spinner"></div>
        <div style="font-size: 20px;">Procesando...</div>
    `;
    document.body.appendChild(loading);
}

function ocultarLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.remove();
    }
}

// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', () => {
    goToScreen('inicio');
});
