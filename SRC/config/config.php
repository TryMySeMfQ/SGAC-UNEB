<?php
// Definição da chave especial para o gerente
define('GERENTE_CHAVE', '2547');  // Substitua pelo valor real da chave do gerente
define('SENHA_MESTRA', '3488322183');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'acc_db');

// Função para conectar ao banco de dados
function conectarBanco() {
    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (mysqli_connect_errno()) {
        die('Falha na conexão com o MySQL: ' . mysqli_connect_error());
    }

    return $conexao;
}
?>

