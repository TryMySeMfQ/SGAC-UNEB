<?php
session_start();
include('../config/conexao.php');
include('../config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = $_POST['matricula'];
    $senha = $_POST['senha'];

    // Verificação da chave do gerente
    if ($senha === GERENTE_CHAVE) {
        $_SESSION['matricula'] = $matricula;
        $_SESSION['tipo'] = 'gerente';
        header('Location: confirmacao_gerente.php');
        exit();
    }

    // Consulta preparada para buscar o hash da senha
    $stmt = $conexao->prepare("SELECT senha, tipo FROM usuarios WHERE matricula = ?");
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $senha_hash = $row['senha'];
        $tipo_usuario = $row['tipo'];

        // Verifica se a senha fornecida corresponde ao hash no banco de dados
        if (password_verify($senha, $senha_hash)) {
            $_SESSION['matricula'] = $matricula;
            $_SESSION['tipo'] = $tipo_usuario;

            if ($tipo_usuario === 'aluno') {
                header('Location: dashboard_aluno.php');
            }
            if ($tipo_usuario === 'admin') {
                header('Location: dashboard_admin.php');
            }
        } else {
            echo "Matrícula ou senha inválidos!";
        }
    } else {
        echo "Matrícula ou senha inválidos!";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../../Public/CSS/login.css">
</head>
<body>
    <div class="container">
        <img src="../../Public/IMG/Brasão_da_UNEB.png" alt="Brasão da UNEB" class="brasao">
        <h1>Login</h1>
        <form method="post" action="">
            <label for="matricula">Matrícula:</label>
            <input type="text" id="matricula" name="matricula" required><br>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br>
            <input type="submit" value="Entrar">
        </form>
        <footer>&copy; 2024 UNEB</footer>
    </div>
</body>
</html>
