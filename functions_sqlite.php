<?php
// Versión SQLite para pruebas locales sin MySQL
function getDB() {
    static $pdo;
    if (!$pdo) {
        $db_file = __DIR__ . '/asistencia.db';
        try {
            $pdo = new PDO("sqlite:$db_file");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            initDB($pdo);
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    return $pdo;
}

function initDB($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            clave VARCHAR(100) UNIQUE,
            valor TEXT
        );

        CREATE TABLE IF NOT EXISTS asistencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            dni VARCHAR(20),
            nombres VARCHAR(255),
            fecha DATE,
            curso VARCHAR(255),
            cc DECIMAL(5,2),
            mp DECIMAL(5,2),
            nota DECIMAL(5,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $check = $pdo->query("SELECT COUNT(*) FROM config")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("
            INSERT INTO config (clave, valor) VALUES
            ('password', 'D3yf0rE1RL'),
            ('logo', 'assets/logo.svg'),
            ('favicon', 'assets/favicon.svg');
        ");
    }
}

function getConfig($clave) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT valor FROM config WHERE clave = ?");
    $stmt->execute([$clave]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['valor'] : null;
}

function setConfig($clave, $valor) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO config (clave, valor) VALUES (?, ?)");
    $stmt->execute([$clave, $valor]);
}

function checkAuth() {
    session_start();
    if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
