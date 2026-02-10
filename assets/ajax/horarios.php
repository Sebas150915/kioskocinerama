<?php
require_once("../../config/config.php");
require_once("../../helpers/helpers.php");
require_once("../../libraries/conexion.php");
session_start();

header('Content-Type: application/json');

// Validar parametros
if (!isset($_GET['idcine']) || empty($_GET['idcine'])) {
    echo json_encode(['error' => 'Falta el ID del cine']);
    exit;
}
if (!isset($_GET['idpelicula']) || empty($_GET['idpelicula'])) {
    echo json_encode(['error' => 'Falta el ID de la pelicula']);
    exit;
}

$idCine = (int)$_GET['idcine'];
$idPelicula = (int)$_GET['idpelicula'];
$fecha = $_GET['fecha'] ?? date('Y-m-d'); // Default today

try {
    $connect = new PDO(
        "mysql:host=" . BD_HOST . ";dbname=" . BD_NAME . ";charset=" . BD_CHARSET,
        BD_USER,
        BD_PASSWORD
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener información de la película para el header
    $stmtPeli = $connect->prepare("SELECT nombre, duracion, censura, genero, img, sinopsis FROM tbl_pelicula WHERE id = :id");
    $stmtPeli->execute([':id' => $idPelicula]);
    $pelicula = $stmtPeli->fetch(PDO::FETCH_ASSOC);

    // 1. Obtener mapa de horas
    $stmtHoras = $connect->query("SELECT id, hora FROM tbl_hora");
    $mapaHoras = [];
    while ($row = $stmtHoras->fetch(PDO::FETCH_ASSOC)) {
        // Formatear hora user friendly (e.g., 14:30:00 -> 14:30)
        $mapaHoras[$row['id']] = substr($row['hora'], 0, 5);
    }

    // 2. Obtener carteleras que coinciden
    $sql = "SELECT 
                c.id as id_cartelera,
                c.id_hora_f1, c.id_hora_f2, c.id_hora_f3, 
                c.id_hora_f4, c.id_hora_f5, c.id_hora_f6,
                c.formato,
                c.idioma,
                s.nombre as nombre_sala,
                s.id as id_sala
            FROM tbl_cartelera c
            INNER JOIN tbl_sala s ON c.sala = s.id
            WHERE c.local = :idcine 
              AND c.pelicula = :idpelicula
              AND :fecha BETWEEN c.fecha_inicio AND c.fecha_fin
              AND c.estado = '1'";

    $stmt = $connect->prepare($sql);
    $stmt->execute([':idcine' => $idCine, ':idpelicula' => $idPelicula, ':fecha' => $fecha]);

    $carteleras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $funciones = [];

    foreach ($carteleras as $c) {
        // Check columns f1..f6
        for ($i = 1; $i <= 6; $i++) {
            $col = "id_hora_f$i";
            $idHora = $c[$col];

            if ($idHora && isset($mapaHoras[$idHora])) {
                $horaStr = $mapaHoras[$idHora];

                // Filtrar funciones pasadas (si es hoy)
                if ($fecha == date('Y-m-d')) {
                    $horaActual = date('H:i');
                    if ($horaStr < $horaActual) continue; // Skip past shows
                }

                // TODO: Calcular disponibilidad real (butacas ocupadas)
                // Por ahora mockeamos un valor aleatorio o basado en capacidad de sala
                $asientosDisponibles = 50; // Placeholder

                $funciones[] = [
                    'id_cartelera' => $c['id_cartelera'],
                    'id_hora' => $idHora,
                    'hora' => $horaStr,
                    'sala' => $c['nombre_sala'],
                    'id_sala' => $c['id_sala'],
                    'formato' => $c['formato'],
                    'idioma' => $c['idioma'],
                    'asientos' => $asientosDisponibles,
                    'booking_id' => $c['id_cartelera'] . '_' . $idHora
                ];
            }
        }
    }

    // Ordenar por hora
    usort($funciones, function ($a, $b) {
        return strcmp($a['hora'], $b['hora']);
    });

    echo json_encode([
        'pelicula' => $pelicula,
        'funciones' => $funciones
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
