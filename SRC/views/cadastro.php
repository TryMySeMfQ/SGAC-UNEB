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

// Busca cursos disponíveis
$cursosQuery = "SELECT * FROM cursos";
$cursosResult = mysqli_query($conexao, $cursosQuery);
$cursos = mysqli_fetch_all($cursosResult, MYSQLI_ASSOC); // Armazena os cursos em um array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];
    $curso_id = $_POST['curso_id']; // Captura o curso selecionado
    $tipoCurso = $_POST['tipo_curso']; // Captura o tipo do curso (Bacharelado/Licenciatura)
    $tipoModalidade = $_POST['tipo_modalidade']; // Captura o tipo de modalidade (Presencial/A Distância)

    // Verifica se a matrícula já está cadastrada
    $checkQuery = "SELECT * FROM usuarios WHERE matricula = '$matricula'";
    $checkResult = mysqli_query($conexao, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $errorMessage = "Erro: A matrícula já está em uso.";
    } else {
        // Insere o novo usuário no banco de dados
        $query = "INSERT INTO usuarios (nome, matricula, senha, tipo) VALUES ('$nome', '$matricula', '$senha', '$tipo')";
        if (mysqli_query($conexao, $query)) {
            $successMessage = "Usuário cadastrado com sucesso!";

            // Checa o tipo e insere na tabela correspondente
            if ($tipo == 'aluno') {
                $queryAluno = "INSERT INTO alunos (matricula, nome, senha, curso_id, tipo_curso, tipo_modalidade) 
                               VALUES ('$matricula', '$nome', '$senha', '$curso_id', '$tipoCurso', '$tipoModalidade')";
                mysqli_query($conexao, $queryAluno);
            } elseif ($tipo == 'admin') {
                $queryAdmin = "INSERT INTO administradores (matricula, nome, senha, curso_id, tipo_curso, tipo_modalidade) 
                               VALUES ('$matricula', '$nome', '$senha', '$curso_id', '$tipoCurso', '$tipoModalidade')";
                mysqli_query($conexao, $queryAdmin);
            }
        } else {
            $errorMessage = "Erro ao cadastrar o usuário.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="../../Public/CSS/cadastro.css">
</head>
<body>
    <div class="container">
        <h1>Cadastro de Usuário</h1>
        
        <?php if ($errorMessage): ?>
            <div class="alert"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert" style="color: green;"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            Nome: <input type="text" name="nome" required><br>
            Matrícula: <input type="text" name="matricula" required><br>
            Senha: <input type="password" name="senha" required><br>
            Tipo: 
            <select name="tipo" required>
                <option value="aluno">Aluno</option>
                <option value="admin">Administrador</option>
            </select><br>

            Curso: 
            <select name="curso_id" required>
                <option value="">Selecione um curso</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?php echo $curso['id']; ?>"><?php echo $curso['nome']; ?></option>
                <?php endforeach; ?>
            </select><br>

            Tipo de Curso:
            <select name="tipo_curso" required>
                <option value="bacharelado">Bacharelado</option>
                <option value="licenciatura">Licenciatura</option>
            </select><br>

            Tipo de Modalidade:
            <select name="tipo_modalidade" required>
                <option value="presencial">Presencial</option>
                <option value="a_distancia">A Distância</option>
            </select><br>

            <input type="submit" value="Cadastrar">
        </form>

        <a href="dashboard_gerente.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>





