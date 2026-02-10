<?php
require_once("../../config/config.php");
require_once("../../helpers/helpers.php");
session_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Validar parámetros
if (!isset($_GET['id_cartelera']) || empty($_GET['id_cartelera'])) {
    echo json_encode(['error' => 'Falta el ID de cartelera']);
    exit;
}
if (!isset($_GET['id_hora']) || empty($_GET['id_hora'])) {
    echo json_encode(['error' => 'Falta el ID de hora']);
    exit;
}

$idCartelera = (int)$_GET['id_cartelera'];
$idHora = (int)$_GET['id_hora'];

try {
    $connect = new PDO(
        "mysql:host=" . BD_HOST . ";dbname=" . BD_NAME . ";charset=" . BD_CHARSET,
        BD_USER,
        BD_PASSWORD
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Obtener información de la sala desde cartelera
    $stmtFunc = $connect->prepare("
        SELECT 
            c.id as id_cartelera,
            c.sala as id_sala,
            s.nombre as sala_nombre,
            s.filas,
            s.columnas,
            p.nombre as pelicula_nombre,
            h.hora,
            l.nombre as cine_nombre
        FROM tbl_cartelera c
        INNER JOIN tbl_sala s ON c.sala = s.id
        INNER JOIN tbl_pelicula p ON c.pelicula = p.id
        INNER JOIN tbl_hora h ON h.id = :id_hora
        INNER JOIN tbl_locales l ON s.local = l.id
        WHERE c.id = :id_cartelera AND c.estado = '1'
    ");
    $stmtFunc->execute([':id_cartelera' => $idCartelera, ':id_hora' => $idHora]);
    $funcion = $stmtFunc->fetch(PDO::FETCH_ASSOC);

    if (!$funcion) {
        echo json_encode(['error' => 'Función no encontrada o inactiva']);
        exit;
    }

    // 2. Obtener todos los asientos de la sala (Layout)
    $stmtAsientos = $connect->prepare("
        SELECT id, fila, columna, num_asiento, tipo 
        FROM tbl_sala_asiento 
        WHERE idsala = :id_sala 
        ORDER BY fila, columna
    ");
    $stmtAsientos->execute([':id_sala' => $funcion['id_sala']]);
    $asientos = $stmtAsientos->fetchAll(PDO::FETCH_ASSOC);

    if (empty($asientos)) {
        echo json_encode(['error' => 'No se encontraron butacas para esta sala. Por favor verifique la configuración de la sala.']);
        exit;
    }

    // 3. Obtener asientos ocupados
    // Por ahora, devolver un array vacío ya que no tenemos tbl_funciones en este sistema
    // TODO: Implementar lógica de reservas cuando se cree el sistema de ventas
    $ocupadosIds = [];

    // 4. Construir respuesta
    $response = [
        'info' => [
            'pelicula' => $funcion['pelicula_nombre'],
            'cine' => $funcion['cine_nombre'],
            'sala' => $funcion['sala_nombre'],
            'hora' => substr($funcion['hora'], 0, 5), // HH:MM
            'cols' => (int)$funcion['columnas'],
            'filas' => (int)$funcion['filas']
        ],
        'layout' => $asientos,
        'occupied' => $ocupadosIds
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
