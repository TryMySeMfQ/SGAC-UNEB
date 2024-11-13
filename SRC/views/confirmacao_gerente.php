<?php
session_start();
include('../config/conexao.php');
include('../config/config.php');

// Verifica se o gerente está logado
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'gerente') {
    header('Location: login.php');
    exit();
}

$erro = ''; // Variável para armazenar mensagens de erro

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha_master = $_POST['senha_master']; // Corrigido para nome do campo correto

    // Verificar se a senha mestra está correta
    if ($senha_master === SENHA_MESTRA) {
        // Permitir acesso ao dashboard
        header('Location: dashboard_gerente.php');
        exit();
    } else {
        $erro = "Senha mestra incorreta!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Confirmação de Segurança</title>
    <link rel="stylesheet" href="../../Public/CSS/confirmacao.css">
</head>
<body>
    <div class="container">
        <h1>Confirmação de Segurança</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="senha_master">Informe a senha mestra:</label>
                <input type="password" id="senha_master" name="senha_master" required>
            </div>
            <input type="submit" value="Confirmar">
            <?php if (!empty($erro)) { echo "<p class='error'>$erro</p>"; } ?>
        </form>
    </div>
</body>
</html>

