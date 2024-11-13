<?php
session_start();
include('../config/conexao.php');

// Verifica se o usuário é gerente
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'gerente') {
    header('Location: login.php');
    exit();
}

$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $tipo = trim($_POST['tipo']); // Presencial ou A Distância
    $tipoCurso = trim($_POST['tipo_curso']); // Bacharelado ou Licenciatura

    // Verifica se o curso com a mesma combinação de nome, tipo e tipo_curso já está cadastrado
    $checkQuery = "SELECT * FROM cursos WHERE nome = ? AND tipo = ? AND tipo_curso = ?";
    $stmt = $conexao->prepare($checkQuery);
    $stmt->bind_param("sss", $nome, $tipo, $tipoCurso);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        $errorMessage = "Erro: O curso com esta combinação de nome, tipo e tipo de curso já está cadastrado.";
    } else {
        // Insere o novo curso no banco de dados
        $query = "INSERT INTO cursos (nome, descricao, tipo, tipo_curso) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("ssss", $nome, $descricao, $tipo, $tipoCurso);

        if ($stmt->execute()) {
            $successMessage = "Curso cadastrado com sucesso!";
        } else {
            $errorMessage = "Erro ao cadastrar o curso.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Curso</title>
    <link rel="stylesheet" href="../../Public/CSS/cadastro.css">
</head>
<body>
    <div class="container">
        <h1>Cadastro de Curso</h1>
        
        <?php if ($errorMessage): ?>
            <div class="alert" style="color: red;"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="alert" style="color: green;"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            Nome do Curso: <input type="text" name="nome" required><br>
            Descrição: <textarea name="descricao" required></textarea><br>
            Tipo: 
            <select name="tipo" required>
                <option value="presencial">Presencial</option>
                <option value="a_distancia">A Distância</option>
            </select><br>
            Tipo de Curso:
            <select name="tipo_curso" required>
                <option value="bacharelado">Bacharelado</option>
                <option value="licenciatura">Licenciatura</option>
            </select><br>
            <input type="submit" value="Cadastrar">
        </form>

        <a href="dashboard_gerente.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>


