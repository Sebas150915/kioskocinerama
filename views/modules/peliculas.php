<?php
$idcine = $rutas[1] ?? '1'; // Default to cinema 1 if not specified
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Cartelera - CINERAMA</title>
  <link rel="stylesheet" href="assets/css/peliculas.css" />
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <div class="container">
    <input type="hidden" id="idcine" value="<?php echo htmlspecialchars($idcine); ?>">

    <div class="header">
      <h1>CARTELERA</h1>
      <p>Selecciona una película para ver los horarios disponibles</p>
    </div>

    <div class="gallery" id="movie-gallery">
      <!-- Movies will be rendered here -->
      <div class="empty-state">
        <i class="fas fa-spinner fa-spin empty-state-icon"></i>
        <h2>Cargando películas...</h2>
      </div>
    </div>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const idcine = document.getElementById('idcine').value;
      if (idcine) {
        cargarPeliculas(idcine);
      } else {
        showError('No se ha especificado un cine.');
      }
    });

    function cargarPeliculas(idcine) {
      const gallery = document.getElementById('movie-gallery');
      gallery.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin empty-state-icon"></i><h2>Cargando cartelera...</h2></div>';

      fetch(`assets/ajax/peliculas.php?idcine=${encodeURIComponent(idcine)}`)
        .then(response => {
          if (!response.ok) throw new Error('Error en la respuesta del servidor');
          return response.json();
        })
        .then(data => {
          console.log('Películas cargadas:', data);
          gallery.innerHTML = '';

          if (data.length === 0) {
            gallery.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-film empty-state-icon"></i>
                            <h2>No hay funciones disponibles hoy</h2>
                            <p>Por favor intenta con otra fecha o cine.</p>
                        </div>
                    `;
            return;
          }

          data.forEach(movie => {
            const card = document.createElement('div');
            card.className = 'movie-card';
            card.onclick = () => selectMovie(movie.id);

            // Construct image path
            const imagePath = `http://localhost/kioskocinerama/assets/img/peliculas/${movie.img}`;
            const items = `
                        <div class="movie-poster">
                            <img src="${imagePath}" alt="${movie.nombre}" onerror="this.onerror=null;this.src='assets/img/no-poster.jpg';">
                        </div>
                        <div class="movie-info">
                            <h3 class="movie-title">${movie.nombre}</h3>
                            <div class="movie-details">
                                <span class="movie-rating">${movie.clasificacion || 'G'}</span>
                                <span class="movie-duration">${movie.duracion ? movie.duracion + ' min' : 'N/A'}</span>
                            </div>
                            <div class="movie-synopsis">${movie.sinopsis || 'Sin sinopsis disponible.'}</div>
                            <button class="btn-select">VER HORARIOS</button>
                        </div>
                    `;
            card.innerHTML = items;
            gallery.appendChild(card);
          });
        })
        .catch(error => {
          console.error('Error:', error);
          showError('Error al cargar la cartelera. Por favor intente nuevamente.');
        });
    }

    function selectMovie(movieId) {
      const idcine = document.getElementById('idcine').value;
      // Navigate to horarios page with cine ID
      window.location.href = `horarios/${movieId}/${idcine}`;
    }

    function showError(message) {
      const gallery = document.getElementById('movie-gallery');
      gallery.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>Oops!</h2>
                <p>${message}</p>
            </div>
        `;
    }
  </script>
</body>

</html>