<?php
session_start();
include('../config/conexao.php');

// Ativar a exibição de erros para depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o usuário está logado como administrador
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'admin') {
    header('Location: login_admin.php');
    exit();
}

// Obter a matrícula do administrador da sessão
$matricula = $_SESSION['matricula'];

// Obter informações do administrador
$query = "SELECT nome, curso_id FROM administradores WHERE matricula = ?";
$stmt = $conexao->prepare($query);
if (!$stmt) {
    die('Erro na preparação da consulta: ' . $conexao->error);
}

$stmt->bind_param('i', $matricula);
$stmt->execute();
$result = $stmt->get_result();
if ($admin = $result->fetch_assoc()) {
    $_SESSION['nome'] = $admin['nome'];
    $_SESSION['curso_id'] = $admin['curso_id'];
} else {
    $_SESSION['nome'] = 'Administrador';
    $_SESSION['curso_id'] = null;
}
$stmt->close();

$matricula_aluno = '';
$atividades = [];
$total_horas_validadas = 0;
$total_horas_pendentes = 0;
$nome_aluno = '';
$errorMessage = '';
$mensagem_enviada = false;

// Consultar tipo de curso do administrador
$query_tipo_curso = "SELECT tipo FROM cursos WHERE id = ?";
$stmtCurso = $conexao->prepare($query_tipo_curso);
$stmtCurso->bind_param('i', $_SESSION['curso_id']);
$stmtCurso->execute();
$resultCurso = $stmtCurso->get_result();
$curso = $resultCurso->fetch_assoc();
$tipo_curso = $curso['tipo'] ?? null;
$tabela_barema = ($tipo_curso === 'bacharelado') ? 'baremabacharelado' : 'baremalicenciatura';
$stmtCurso->close();
$tipo_curso_aluno = ''; 

// Carregar as atividades pendentes para o curso do administrador
// Correct way to prepare and bind parameters
$query_pendentes = "SELECT a.*, b.nome AS categoria_nome, al.nome AS aluno_nome, al.matricula AS aluno_matricula
                    FROM atividades a
                    LEFT JOIN $tabela_barema b ON a.categoria_id = b.id
                    LEFT JOIN alunos al ON a.matricula_aluno = al.matricula
                    WHERE a.validado = 0 
                    AND a.pendente_atualizacao = 0
                    AND al.curso_id = ?";

// Prepare the statement
$stmt_pendentes = $conexao->prepare($query_pendentes);
if (!$stmt_pendentes) {
    die('Erro na preparação da consulta: ' . $conexao->error);
}

// Bind the parameter (course_id)
$stmt_pendentes->bind_param('i', $_SESSION['curso_id']);

// Execute the statement
$stmt_pendentes->execute();

// Get the result
$result_pendentes = $stmt_pendentes->get_result();
$atividades_pendentes = [];

if ($result_pendentes && $result_pendentes->num_rows > 0) {
    while ($atividade = $result_pendentes->fetch_assoc()) {
        $atividades_pendentes[] = $atividade;
    }
}

// Close the prepared statement
$stmt_pendentes->close();

// Consultar atividades pendentes
$query_pendentes = "SELECT COUNT(*) AS total_pendentes 
                    FROM atividades a
                    LEFT JOIN alunos al ON a.matricula_aluno = al.matricula
                    WHERE a.validado = 0 
                    AND a.pendente_atualizacao = 0 
                    AND al.curso_id = ?";
$stmt_pendentes = $conexao->prepare($query_pendentes);
$stmt_pendentes->bind_param('i', $_SESSION['curso_id']);
$stmt_pendentes->execute();
$result_pendentes = $stmt_pendentes->get_result();
$total_pendentes = $result_pendentes ? $result_pendentes->fetch_assoc()['total_pendentes'] : 0;
$stmt_pendentes->close();


// Processar formulário de busca de atividades
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['matricula_aluno'])) {
    $matricula_aluno = $_POST['matricula_aluno'];

$tabela_barema = ($tipo_curso === 'bacharelado') ? 'baremabacharelado' : 'baremalicenciatura';

    

    // Definir a variável $tipo_curso_aluno antes do uso
$tipo_curso_aluno = ''; 

$stmtAluno = $conexao->prepare("SELECT nome, curso_id, tipo_curso FROM alunos WHERE matricula = ?");
$stmtAluno->bind_param('i', $matricula_aluno);
$stmtAluno->execute();
$resultAluno = $stmtAluno->get_result();
if ($aluno = $resultAluno->fetch_assoc()) {
    $nome_aluno = $aluno['nome'];
    $curso_aluno_id = $aluno['curso_id'];
    $tipo_curso_aluno = $aluno['tipo_curso']; // Definir corretamente
} else {
    $errorMessage = "Erro: Aluno não encontrado.";
}
$stmtAluno->close();

// Agora você pode usar $tipo_curso_aluno com segurança


    // Verificar se o curso do aluno corresponde ao curso do administrador
    if (!empty($curso_aluno_id) && $curso_aluno_id != $_SESSION['curso_id']) {
        $errorMessage = "Erro: Você só pode acessar atividades de alunos do seu curso.";
    } else {
        // Consultar atividades do aluno
        $stmtAtividades = $conexao->prepare("SELECT a.*, b.nome AS categoria_nome
                                            FROM atividades a
                                            LEFT JOIN $tabela_barema b ON a.categoria_id = b.id
                                            WHERE a.matricula_aluno = ?;");
        $stmtAtividades->bind_param('i', $matricula_aluno);
        $stmtAtividades->execute();
        $resultAtividades = $stmtAtividades->get_result();
        while ($atividade = $resultAtividades->fetch_assoc()) {
            $atividades[] = $atividade;
            if ($atividade['validado']) {
                $total_horas_validadas += $atividade['horas'];
            } else {
                $total_horas_pendentes += $atividade['horas'];
            }
        }
        $stmtAtividades->close();
    }
}

// Validar ou negar atividade
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar atividade
    if (isset($_POST['validar_atividade']) && isset($_POST['atividade_id'])) {
        $atividade_id = intval($_POST['atividade_id']);
        $query_validar = "UPDATE atividades SET validado = 1 WHERE id = ?";
        $stmt_validar = $conexao->prepare($query_validar);
        $stmt_validar->bind_param('i', $atividade_id);
        if ($stmt_validar->execute()) {
            $mensagem_enviada = true;
        } else {
            $errorMessage = "Erro ao validar a atividade: " . $stmt_validar->error;
        }
        $stmt_validar->close();
    }

    // Negar atividade
    if (isset($_POST['acao']) && $_POST['acao'] === 'negar') {
        $atividade_id = intval($_POST['atividade_id']);
        $motivo_negacao = trim($_POST['justificativa']);
        if (!empty($atividade_id) && !empty($motivo_negacao)) {
            $query_negar = "UPDATE atividades SET status = 'pendente', motivo_negacao = ?, pendente_atualizacao = 1, notificado = 0 WHERE id = ?";
            $stmt_negar = $conexao->prepare($query_negar);
            $stmt_negar->bind_param('si', $motivo_negacao, $atividade_id);
            if ($stmt_negar->execute()) {
                $mensagem_enviada = true;
            } else {
                $errorMessage = "Erro ao negar a atividade: " . $stmt_negar->error;
            }
            $stmt_negar->close();
        } else {
            $errorMessage = "Erro: Atividade ou justificativa não fornecida.";
        }
    }
}


$queryAtualizarStatus = "
    UPDATE atividades 
    SET status = ?, notificado = 0 
    WHERE id = ?";
$stmt = $conexao->prepare($queryAtualizarStatus);
$stmt->bind_param('si', $novo_status, $atividade_id);
$stmt->execute();

$atividades = mysqli_fetch_all($result, MYSQLI_ASSOC);

$atividades = [];

// Primeiro, obter o tipo de curso do aluno
$queryTipoCurso = "SELECT tipo_curso FROM alunos WHERE matricula = ?";
$stmtTipoCurso = $conexao->prepare($queryTipoCurso);
$stmtTipoCurso->bind_param('i', $matricula_aluno);
$stmtTipoCurso->execute();
$resultTipoCurso = $stmtTipoCurso->get_result();

// Verificar se o tipo de curso foi encontrado
if ($row = $resultTipoCurso->fetch_assoc()) {
    $tipo_curso_aluno = $row['tipo_curso'];
} else {
    // Se o tipo de curso não for encontrado, você pode definir um valor padrão
    $tipo_curso_aluno = null;
}
$stmtTipoCurso->close();


?>

<!-- Formulário HTML e visualização das atividades -->



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Public/CSS/dashboard_admin.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@latest/dist/jspdf.umd.min.js"></script>
</head>
<body>
<header>
<nav class="navbar">
            <h1 class="navbar-title">SGAC</h1>
            <div class="navbar-right">
                <i class="fas fa-user-circle profile-icon"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nome'] ?? ''); ?></span>
                <a href="../helpers/logout.php" class="logout-link">Sair</a>
            </div>
    </nav>
</header>
<div class="container mt-4">
    <div class="alert alert-info text-center">
        <h4>Atividades pendentes de validação: <strong><?php echo $total_pendentes; ?></strong></h4>
    </div>
</div>
<div class="container mt-4">
    <div class="card p-4">
        <h2>Atividades Pendentes de Validação</h2>
        <?php 
        // Simulando atividades pendentes
        if (!empty($atividades_pendentes)) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Matricula</th>
                        <th>Nome do Aluno</th>
                        <th>Descrição</th>
                        <th>Horas</th>
                        <th>Certificado</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($atividades_pendentes as $atividade) { ?>
                        <tr>
                            <td><?= htmlspecialchars($atividade['aluno_matricula']) ?></td>
                            <td><?= htmlspecialchars($atividade['aluno_nome']) ?></td>
                            <td><?= htmlspecialchars($atividade['descricao']) ?></td>
                            <td><?= htmlspecialchars($atividade['horas']) ?></td>
                            <td><?= $atividade['certificado'] ? "<a href='" . htmlspecialchars($atividade['certificado']) . "' target='_blank'>Visualizar</a>" : "Nenhum certificado disponível" ?></td>
                            <td><?= 'Pendente' ?></td>
                            <td>
                            <div class="acoes-container">
                                <form method="post" action="" class="form-validar">
                                    <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                                    <input type="hidden" name="atividade_id" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
                                    <input type="hidden" name="validar_atividade" value="validar">
                                    <button type="submit" class="btn btn-success">Validar</button>
                                </form>

                                <form method="post" action="" class="form-negar">
                                    <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                                    <input type="hidden" name="atividade_id" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
                                    <input type="hidden" name="acao" value="negar">
                                    <input type="text" name="justificativa" class="form-control reason-input" placeholder="Motivo da negação" required>
                                    <button type="submit" class="btn btn-danger mt-2">Negar</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Não há atividades pendentes.</p>
        <?php } ?>
    </div>
</div>

<div class="container mt-5">
    <div class="card p-4 my-4">
        <form method="post" action="">
            <div class="form-group">
                <label for="matricula_aluno">Matrícula do Aluno:</label>
                <input type="text" id="matricula_aluno" name="matricula_aluno" class="form-control" value="<?= htmlspecialchars($matricula_aluno) ?>" required>
            </div>
            <input type="submit" value="Buscar" class="btn btn-primary">
            <?php if (!empty($errorMessage)) { ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($errorMessage); ?>
    </div>
<?php } ?>

        </form>
    </div>
    <div class="card p-4 my-4">
        <h2>Resumo do Aluno</h2>
            <p><strong>Total de Horas Validadas:</strong> <?= htmlspecialchars($total_horas_validadas) ?></p>
            <p><strong>Total de Horas Pendentes:</strong> <?= htmlspecialchars($total_horas_pendentes) ?></p>
    </div>
    <?php if (!empty($atividades)) { ?>
        <div class="card p-4 my-4">
            <h2>Atividades do Aluno</h2>
            <table class="table table-striped">
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Horas</th>
            <th>Categoria</th> 
            <th>Certificado</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($atividades as $atividade) {
                $status = $atividade['validado'] ? 'Validado' : ($atividade['pendente_atualizacao'] ? 'Pendente de Atualização' : 'Não Validado');
            ?>
                <tr>
                    <td><?= htmlspecialchars($atividade['descricao'] ?? 'Descrição não disponível') ?></td>
                    <td><?= htmlspecialchars($atividade['horas'] ?? '0') ?></td>
                    <td><?= htmlspecialchars($atividade['categoria_nome'] ?? 'Categoria não encontrada') ?></td>
                    <td><?= $atividade['certificado'] ? "<a href='" . htmlspecialchars($atividade['certificado']) . "' target='_blank'>Visualizar</a>" : "Nenhum certificado disponível" ?></td>
                    <td><?= $status ?></td>
                    <td>
            <?php if ($atividade['pendente_atualizacao']) { ?>
                <span>Pendente de Atualização do Aluno</span>
            <?php } elseif (!$atividade['validado']) { ?>
                <div class="acoes-container">
                    <form method="post" action="" class="form-validar">
                        <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                        <input type="hidden" name="atividade_id" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
                        <input type="hidden" name="validar_atividade" value="validar">
                        <button type="submit" class="btn btn-success">Validar</button>
                    </form>

                    <form method="post" action="" class="form-negar">
                        <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                        <input type="hidden" name="atividade_id" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
                        <input type="hidden" name="acao" value="negar">
                        <input type="text" name="justificativa" class="form-control reason-input" placeholder="Motivo da negação" required>
                        <button type="submit" class="btn btn-danger mt-2">Negar</button>
                    </form>
                </div>
            <?php } ?>
        </td>
    </tr>
<?php } ?>
</tbody>


</table>
            <div class="text-center mb-4">
                <button id="gerarPDF" class="btn btn-success">Gerar PDF</button>
            </div>
            <script>
                const atividadesValidas = <?php echo json_encode($atividades); ?>;
            </script>
            <?php if ($mensagem_enviada) { ?>
                <p class="alert alert-success">Mensagem de negação enviada com sucesso!</p>
            <?php } ?>

        </div>
    <?php } else { ?>
        <div class="alert alert-info">Nenhuma atividade encontrada.</div>
    <?php } ?>

    <?php if (isset($atividade_atualizar)) { ?>
        <div class="card p-4 my-4">
            <h2>Atualizar Atividade</h2>
            <form method="post" action="">
                <input type="hidden" name="id_atividade_atualizar" value="<?= htmlspecialchars($atividade_atualizar['id']) ?>">
                <div class="form-group">
                    <label for="nova_descricao">Nova Descrição:</label>
                    <input type="text" id="nova_descricao" name="nova_descricao" class="form-control" value="<?= htmlspecialchars($atividade_atualizar['descricao']) ?>" required>
                </div>
                <button type="submit" name="atualizar_atividade" class="btn btn-primary">Atualizar Atividade</button>
            </form>
        </div>
    <?php } ?>
</div>
<script src="../../Public/JS/gerar_pdf.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




























