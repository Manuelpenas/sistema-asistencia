<?php
require_once 'functions.php';
$pdo = getDB();

$stmt = $pdo->prepare('INSERT OR IGNORE INTO asistencias (dni, nombres, fecha, curso, cc, mp, nota) VALUES (?, ?, ?, ?, ?, ?, ?)');

$data = [
    ['12345678','Juan Perez','2026-01-15','Matematicas',18,17,18.5],
    ['12345678','Juan Perez','2026-01-16','Comunicacion',17,18,17.5],
    ['12345678','Juan Perez','2026-01-17','Historia',16,15,15.5],
    ['87654321','Maria Lopez','2026-01-15','Matematicas',15,16,15.5],
    ['87654321','Maria Lopez','2026-01-16','Comunicacion',18,17,17.5],
    ['87654321','Maria Lopez','2026-01-17','Historia',19,18,18.5],
    ['11223344','Carlos Ruiz','2026-01-15','Matematicas',10,11,10.5],
    ['11223344','Carlos Ruiz','2026-01-16','Comunicacion',12,13,12.5],
    ['55667788','Ana Torres','2026-01-15','Matematicas',20,19,19.5],
    ['55667788','Ana Torres','2026-01-16','Comunicacion',18,20,19.0],
];

foreach($data as $row) {
    $stmt->execute($row);
}

echo "Datos de prueba insertados correctamente. Total: " . $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
