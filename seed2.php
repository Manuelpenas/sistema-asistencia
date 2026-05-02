<?php
require_once 'functions.php';
$pdo = getDB();

$stmt = $pdo->prepare('INSERT OR IGNORE INTO perfiles (dni, nombres, cc, mp) VALUES (?, ?, ?, ?)');
$perfiles = [
    ['12345678','Juan Perez',18,17],
    ['87654321','Maria Lopez',15,16],
    ['11223344','Carlos Ruiz',10,11],
    ['55667788','Ana Torres',20,19],
    ['99887766','Luis Gomez',14,15],
    ['44556677','Sofia Vargas',16,18],
];
foreach($perfiles as $row) { $stmt->execute($row); }

$stmt2 = $pdo->prepare('INSERT OR IGNORE INTO cursos (nombre) VALUES (?)');
$cursos = ['Matematicas','Comunicacion','Historia','Ciencias','Ingles'];
foreach($cursos as $c) { $stmt2->execute([$c]); }

$stmt3 = $pdo->prepare('INSERT OR IGNORE INTO inscripciones (dni, curso, fecha, cc, mp) VALUES (?, ?, ?, ?, ?)');
$inscripciones = [
    ['12345678','Matematicas','2026-05-01',18,17],
    ['87654321','Matematicas','2026-05-01',15,16],
    ['11223344','Matematicas','2026-05-01',10,11],
    ['55667788','Matematicas','2026-05-01',20,19],
    ['99887766','Matematicas','2026-05-01',14,15],
    ['12345678','Comunicacion','2026-05-02',18,17],
    ['87654321','Comunicacion','2026-05-02',15,16],
    ['55667788','Comunicacion','2026-05-02',20,19],
    ['44556677','Comunicacion','2026-05-02',16,18],
];
foreach($inscripciones as $row) { $stmt3->execute($row); }

echo "Datos de perfiles, cursos e inscripciones cargados correctamente.\n";
echo "Perfiles: " . $pdo->query("SELECT COUNT(*) FROM perfiles")->fetchColumn() . "\n";
echo "Cursos: " . $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn() . "\n";
echo "Inscripciones: " . $pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn() . "\n";
