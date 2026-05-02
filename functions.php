<?php
function getDB() {
    static $pdo;
    if (!$pdo) {
        if (getenv('DATABASE_URL')) {
            $db_url = parse_url(getenv('DATABASE_URL'));
            $host = $db_url['host'];
            $port = $db_url['port'] ?? 3306;
            $user = $db_url['user'];
            $pass = $db_url['pass'] ?? '';
            $dbname = ltrim($db_url['path'], '/');
            try {
                $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                initDBTables($pdo, 'mysql');
            } catch (Exception $e) {
                die("Error de conexión MySQL: " . $e->getMessage());
            }
        } elseif (getenv('MYSQLHOST')) {
            $host = getenv('MYSQLHOST');
            $port = getenv('MYSQLPORT') ?: 3306;
            $user = getenv('MYSQLUSER');
            $pass = getenv('MYSQLPASSWORD') ?: '';
            $dbname = getenv('MYSQLDATABASE');
            try {
                $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                initDBTables($pdo, 'mysql');
            } catch (Exception $e) {
                die("Error de conexión MySQL: " . $e->getMessage());
            }
        } elseif (file_exists(__DIR__ . '/config_db.php')) {
            require_once __DIR__ . '/config_db.php';
            try {
                $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die("Error de conexión MySQL: " . $e->getMessage());
            }
        } else {
            $db_file = __DIR__ . '/asistencia.db';
            try {
                $pdo = new PDO("sqlite:$db_file");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                initDBTables($pdo, 'sqlite');
            } catch (Exception $e) {
                die("Error de conexión SQLite: " . $e->getMessage());
            }
        }
    }
    return $pdo;
}

function initDBTables($pdo, $driver) {
    if ($driver == 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS config (
                id INT PRIMARY KEY AUTO_INCREMENT,
                clave VARCHAR(100) UNIQUE,
                valor TEXT
            );
            CREATE TABLE IF NOT EXISTS asistencias (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20),
                nombres VARCHAR(255),
                fecha DATE,
                curso VARCHAR(255),
                cc DECIMAL(5,2),
                mp DECIMAL(5,2),
                nota DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS perfiles (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20) UNIQUE,
                nombres VARCHAR(255),
                cc DECIMAL(5,2),
                mp DECIMAL(5,2)
            );
            CREATE TABLE IF NOT EXISTS cursos (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(255) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS inscripciones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20),
                curso VARCHAR(255),
                fecha DATE,
                cc DECIMAL(5,2),
                mp DECIMAL(5,2),
                UNIQUE KEY unique_insc (dni, curso, fecha)
            );
        ");
        $check = $pdo->query("SELECT COUNT(*) FROM config")->fetchColumn();
        if ($check == 0) {
            $pdo->exec("
                INSERT IGNORE INTO config (clave, valor) VALUES
                ('password', 'D3yf0rE1RL'),
                ('logo', 'assets/logo.svg'),
                ('favicon', 'assets/favicon.svg');
            ");
        }
    } else {
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
            CREATE TABLE IF NOT EXISTS perfiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                dni VARCHAR(20) UNIQUE,
                nombres VARCHAR(255),
                cc DECIMAL(5,2),
                mp DECIMAL(5,2)
            );
            CREATE TABLE IF NOT EXISTS cursos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre VARCHAR(255) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        CREATE TABLE IF NOT EXISTS inscripciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            dni VARCHAR(20),
            curso VARCHAR(255),
            fecha DATE,
            cc DECIMAL(5,2),
            mp DECIMAL(5,2),
            UNIQUE(dni, curso, fecha)
        );
        CREATE TABLE IF NOT EXISTS proveedores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ruc VARCHAR(20),
            nombre VARCHAR(255),
            razon_social VARCHAR(255),
            linea VARCHAR(255),
            curso VARCHAR(255),
            fecha DATE,
            tiempos DECIMAL(5,2),
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
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver == 'mysql') {
        $stmt = $pdo->prepare("INSERT INTO config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$clave, $valor, $valor]);
    } else {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO config (clave, valor) VALUES (?, ?)");
        $stmt->execute([$clave, $valor]);
    }
}

function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
