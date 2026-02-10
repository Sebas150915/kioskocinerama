<?php
require_once("../../config/config.php");
require_once("../../helpers/helpers.php");
require_once("../../libraries/conexion.php");
session_start();

header('Content-Type: application/json');

// Validar ID cine
if (!isset($_GET['idcine']) || empty($_GET['idcine'])) {
    echo json_encode(['error' => 'Falta el ID del cine']);
    exit;
}

$idCine = (int)$_GET['idcine'];

try {
    $connect = new PDO(
        "mysql:host=" . BD_HOST . ";dbname=" . BD_NAME . ";charset=" . BD_CHARSET,
        BD_USER,
        BD_PASSWORD
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query para obtener peliculas en cartelera HOY para este local
    // DISTINCT para evitar duplicados si hay múltiples horarios
    $sql = "SELECT DISTINCT 
                p.id, 
                p.nombre, 
                p.img, 
                p.duracion, 
                p.censura,
                p.genero,
                p.sinopsis
            FROM tbl_cartelera c
            INNER JOIN tbl_pelicula p ON c.pelicula = p.id
            WHERE c.local = :idcine 
              AND CURDATE() BETWEEN c.fecha_inicio AND c.fecha_fin
              AND c.estado = '1'
              AND p.estado = '1'";

    $stmt = $connect->prepare($sql);
    $stmt->bindParam(':idcine', $idCine, PDO::PARAM_INT);
    $stmt->execute();

    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay peliculas, devolver array vacio (no error)
    echo json_encode($peliculas);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
