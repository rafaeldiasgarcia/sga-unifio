<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'sga_db');

// Conexão
$conexao = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8mb4");

// Funções de Controle de Acesso
function check_login() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: /sga_atleticas/login.php");
        exit;
    }
}

function is_aluno() {
    check_login();
    if ($_SESSION["role"] !== 'aluno') {
        die("Acesso negado. Área restrita para Alunos.");
    }
}

function is_admin() {
    check_login();
    if ($_SESSION["role"] !== 'admin') {
        die("Acesso negado. Área restrita para Admins de Atlética.");
    }
}

function is_superadmin() {
    check_login();
    if ($_SESSION["role"] !== 'superadmin') {
        die("Acesso negado. Área restrita para o Super Administrador.");
    }
}
?>