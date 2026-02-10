<?php
$idPelicula = $rutas[1] ?? null;
$idCine = $rutas[2] ?? null;
// If using query extraction for routed URLs, verify your routing engine.
// Assuming simple splitting of URL: domain/horarios/idpelicula/idcine
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios - CINERAMA</title>
    <link rel="stylesheet" href="<?php echo $url; ?>assets/css/horarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container">
        <div class="header-nav">
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a películas
            </a>
        </div>

        <!-- Movie Details Header -->
        <div id="movie-header" class="movie-header-section" style="display:none;">
            <img id="movie-poster" src="" alt="Poster" class="movie-poster-large" onerror="this.onerror=null;this.src='assets/img/no-poster.jpg';">
            <div class="movie-details-header">
                <h1 id="movie-title">Cargando...</h1>
                <div class="movie-meta">
                    <span id="movie-genre">Genero</span> |
                    <span id="movie-duration">0h 0m</span>
                </div>
                <p id="movie-synopsis" style="color: #ccc; max-width: 600px;">...</p>
            </div>
        </div>

        <div class="schedule-section">
            <h2 class="section-title">ELIGE <span>HORARIO</span></h2>

            <div id="schedule-grid" class="schedule-grid">
                <!-- Loading State -->
                <div style="color:white; grid-column: 1/-1; text-align:center;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i><br>Cargando horarios...
                </div>
            </div>
        </div>

        <!-- Hidden Input for Logic -->
        <input type="hidden" id="idcine" value="<?php echo htmlspecialchars($idCine); ?>">
        <input type="hidden" id="idpelicula" value="<?php echo htmlspecialchars($idPelicula); ?>">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const idCine = document.getElementById('idcine').value;
            const idPelicula = document.getElementById('idpelicula').value;
            const urlBase = '<?php echo $url; ?>'; // Base URL from PHP config

            if (idCine && idPelicula) {
                fetchSchedules(idCine, idPelicula);
            } else {
                document.getElementById('schedule-grid').innerHTML = '<p style="color:red">Error: Faltan datos de navegación.</p>';
            }

            function fetchSchedules(cine, movie) {
                fetch(`${urlBase}assets/ajax/horarios.php?idcine=${cine}&idpelicula=${movie}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        renderMovieInfo(data.pelicula);
                        renderSchedules(data.funciones);
                    })
                    .catch(err => {
                        console.error(err);
                        document.getElementById('schedule-grid').innerHTML = `<p style="color:red">Error al cargar horarios: ${err.message}</p>`;
                    });
            }

            function renderMovieInfo(movie) {
                if (!movie) return;
                document.getElementById('movie-header').style.display = 'flex';
                document.getElementById('movie-title').textContent = movie.nombre;
                document.getElementById('movie-genre').textContent = movie.genero || 'General';
                document.getElementById('movie-duration').textContent = movie.duracion + ' min';

                // Image path
                const imgPath = `http://localhost/cineramaonline/panel/assets/img/peliculas/${movie.imagen}`;
                document.getElementById('movie-poster').src = imgPath;
                document.getElementById('movie-synopsis').textContent = movie.sinopsis || '';
            }

            function renderSchedules(funciones) {
                const grid = document.getElementById('schedule-grid');
                grid.innerHTML = '';

                if (!funciones || funciones.length === 0) {
                    grid.innerHTML = '<p style="color:white; grid-column:1/-1;">No hay funciones disponibles hoy.</p>';
                    return;
                }

                funciones.forEach(func => {
                    const card = document.createElement('div');
                    card.className = 'schedule-card';
                    card.onclick = () => selectFunction(func);

                    card.innerHTML = `
                <div class="time-row">
                    <i class="far fa-clock icon"></i>
                    ${func.hora}
                </div>
                <div class="location-row">
                    <i class="fas fa-map-marker-alt icon"></i>
                    ${func.sala}
                </div>
                <div class="format-row">
                    <i class="fas fa-film icon"></i>
                    ${func.formato} ${func.idioma}
                </div>
                <div class="seats-row">
                    ${func.asientos} asientos disponibles
                </div>
            `;
                    grid.appendChild(card);
                });
            }

            function selectFunction(func) {
                // Go to seat selection
                // We pass the cart or booking ID
                // URL needs idcine, idpelicula, idcartelera, idhora?
                // Or just a booking session ID?
                // Let's pass parameters in URL for now
                window.location.href = `${urlBase}butacas/${func.id_cartelera}/${func.id_hora}`;
            }
        });
    </script>

</body>

</html>