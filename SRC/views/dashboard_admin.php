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
$total_horas = 0;
$nome_aluno = '';
$errorMessage = '';
$mensagem_enviada = false;

/// Consultar o tipo de curso do aluno
if (!empty($matricula_aluno)) {
    $query_tipo_curso_aluno = "SELECT tipo_curso FROM alunos WHERE matricula = ?";
    $stmtAlunoCurso = $conexao->prepare($query_tipo_curso_aluno);
    $stmtAlunoCurso->bind_param('i', $matricula_aluno); // Aqui o $matricula_aluno é passado
    $stmtAlunoCurso->execute();
    $resultAlunoCurso = $stmtAlunoCurso->get_result();

    // Se o aluno for encontrado
    if ($alunoCurso = $resultAlunoCurso->fetch_assoc()) {
        // Atribuir o tipo de curso do aluno
        $tipo_curso_aluno = $alunoCurso['tipo_curso'];
    } else {
        $errorMessage = 'Aluno não encontrado.';
    }
    $stmtAlunoCurso->close();
} else {
    $errorMessage = 'Matrícula do aluno não fornecida.';
}

// Verifica se $tipo_curso_aluno foi definido antes de usá-lo
if (isset($tipo_curso_aluno)) {
    // Seleciona a tabela de acordo com o tipo de curso do aluno
    if ($tipo_curso_aluno == 'bacharelado') {
        $tabela_barema = 'baremabacharelado';  // Se for bacharelado, usa a tabela de bacharelado
    } elseif ($tipo_curso_aluno == 'licenciatura') {
        $tabela_barema = 'baremalicenciatura';  // Se for licenciatura, usa a tabela de licenciatura
    } else {
        $errorMessage = 'Tipo de curso do aluno não encontrado ou inválido.';
    }
} else {
    $errorMessage = 'Erro: Tipo de curso do aluno não está disponível.';
}


// Consultar o tipo de curso do administrador
$query_tipo_curso = "SELECT tipo FROM cursos WHERE id = ?";
$stmtCurso = $conexao->prepare($query_tipo_curso);
$stmtCurso->bind_param('i', $_SESSION['curso_id']);
$stmtCurso->execute();
$resultCurso = $stmtCurso->get_result();
$curso = $resultCurso->fetch_assoc();
$tipo_curso = $curso['tipo'] ?? null;
$tabela_barema = ($tipo_curso === 'bacharelado') ? 'baremabacharelado' : 'baremalicenciatura';
$stmtCurso->close();

// Obter informações do aluno
$queryAluno = "SELECT tipo_curso FROM alunos WHERE matricula = ?";
$stmtAluno = $conexao->prepare($queryAluno);
$stmtAluno->bind_param('i', $matricula);
$stmtAluno->execute();
$resultAluno = $stmtAluno->get_result();
$aluno = $resultAluno->fetch_assoc();
if ($aluno) {
    $tipo_curso = $aluno['tipo_curso'];
} else {
    $errorMessage = 'Aluno não encontrado.';
}
$stmtAluno->close();

function calcularHorasComplementares($atividades, $tipo_curso) {
    global $conexao;
    $total_ac_hours = 0;

    // Selecionar o barema correto de acordo com o tipo de curso
    $tabela_barema = ($tipo_curso === 'bacharelado') ? 'baremabacharelado' : 'baremalicenciatura';
    foreach ($atividades as $atividade) {
        $category_id = $atividade['categoria_id'];
        $us_hora = $atividade['horas'];

        // Pega os dados da categoria no barema correspondente
        $query = "SELECT horas_ad, horas_ac, horas_max FROM $tabela_barema WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, 'i', $category_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $horas_ad = $row['horas_ad'];
            $horas_ac = $row['horas_ac'];
            $max_ac_hours = $row['horas_max'];

            // Calcula as horas aproveitáveis
            $ac_hours = floor($us_hora / $horas_ad) * $horas_ac;

            // Limita ao máximo permitido
            $ac_hours = min($ac_hours, $max_ac_hours);

            // Adiciona ao total de horas aproveitáveis
            $total_ac_hours += $ac_hours;
        } else {
            // echo "Categoria não encontrada para ID: $category_id";
        }
    }

    return $total_ac_hours;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['validar_atividade'])) {
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

    if (isset($_POST['matricula_aluno']) && !empty($_POST['matricula_aluno'])) {
        $matricula_aluno = $_POST['matricula_aluno'];

        // Obter o curso do aluno
        $stmtAluno = $conexao->prepare("SELECT nome, curso_id FROM alunos WHERE matricula = ?");
        $stmtAluno->bind_param('i', $matricula_aluno);
        $stmtAluno->execute();
        $resultAluno = $stmtAluno->get_result();
        $aluno = $resultAluno->fetch_assoc();
        if ($aluno) {
            $nome_aluno = $aluno['nome'];
            $curso_aluno_id = $aluno['curso_id'];
        } else {
            $nome_aluno = 'Nome não encontrado';
            $curso_aluno_id = null;
        }
        $stmtAluno->close();

        // Verificar se o curso do aluno corresponde ao curso do administrador
        if ($curso_aluno_id != $_SESSION['curso_id']) {
            $errorMessage = "Erro: Você só pode acessar atividades de alunos do seu curso.";
        } else {
            // Consultar atividades do aluno
            $stmtAtividades = $conexao->prepare("SELECT * FROM atividades WHERE matricula_aluno = ?");
            $stmtAtividades->bind_param('i', $matricula_aluno);
            $stmtAtividades->execute();
            $resultAtividades = $stmtAtividades->get_result();
            $atividades = [];
            while ($atividade = $resultAtividades->fetch_assoc()) {
                $atividades[] = $atividade;
            }
            $stmtAtividades->close();
        }
    }
}

// Consultar o número de atividades pendentes de validação
$query_pendentes = "SELECT COUNT(*) as total_pendentes FROM atividades WHERE validado = 0 AND pendente_atualizacao = 0";
$result_pendentes = $conexao->query($query_pendentes);
$total_pendentes = $result_pendentes ? $result_pendentes->fetch_assoc()['total_pendentes'] : 0;

// Consultar os alunos com atividades pendentes de validação
$query_alunos_pendentes = "SELECT DISTINCT u.matricula, u.nome FROM atividades a JOIN alunos u ON a.matricula_aluno = u.matricula WHERE a.validado = 0 AND a.pendente_atualizacao = 0";
$result_alunos_pendentes = $conexao->query($query_alunos_pendentes);
$alunos_pendentes = [];
if ($result_alunos_pendentes) {
    while ($row = $result_alunos_pendentes->fetch_assoc()) {
        $alunos_pendentes[] = $row;
    }
}

// Calcular total de horas validadas e pendentes
$total_horas_validadas = 0;
$total_horas_pendentes = 0;
foreach ($atividades as $atividade) {
    if ($atividade['validado']) {
        $total_horas_validadas += $atividade['horas'];
    } else {
        $total_horas_pendentes += $atividade['horas'];
    }
}



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
        <h4>Alunos com Atividades Pendentes:</h4>
        <?php if (!empty($alunos_pendentes)) { ?>
            <div class="alert alert-info text-center">
    <h4>Atividades pendentes de validação: <strong><?php echo htmlspecialchars($total_pendentes ?? 0); ?></strong></h4>
</div>
<ul class="list-group">
    <?php foreach ($alunos_pendentes as $aluno) { ?>
        <li class="list-group-item">
            <strong>Nome:</strong> <?= htmlspecialchars($aluno['nome'] ?? ''); ?> |
            <strong>Matrícula:</strong> <?= htmlspecialchars($aluno['matricula'] ?? ''); ?>
            <a href="?matricula_aluno=<?= htmlspecialchars($aluno['matricula'] ?? ''); ?>" class="btn btn-primary btn-sm float-right">
                Ver Atividades
            </a>
        </li>
    <?php } ?>
</ul>

        <?php } else { ?>
            <p>Nenhum aluno com atividades pendentes de validação.</p>
        <?php } ?>
    </div>
<div class="container mt-5">
    <div class="card p-4 my-4">
        <form method="post" action="">
            <div class="form-group">
                <label for="matricula_aluno">Matrícula do Aluno:</label>
                <input type="text" id="matricula_aluno" name="matricula_aluno" class="form-control" value="<?= htmlspecialchars($matricula_aluno) ?>" required>
            </div>
            <input type="submit" value="Buscar" class="btn btn-primary">
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
    $dias_pendentes = isset($atividade['data_envio']) 
        ? (time() - strtotime($atividade['data_envio'])) / (60 * 60 * 24) 
        : 0; 
    $classeDestaque = ($dias_pendentes > 30) ? 'alert-warning' : '';
?>
    <tr class="<?= $classeDestaque ?>">
        <td><?= htmlspecialchars($atividade['descricao'] ?? 'Descrição não disponível') ?></td>
        <td><?= htmlspecialchars($atividade['horas'] ?? '0') ?></td>
        <td><?= htmlspecialchars($atividade['categoria_nome'] ?? 'Categoria não encontrada') ?></td>
        <td><?= $atividade['certificado'] ? "<a href='" . htmlspecialchars($atividade['certificado']) . "' target='_blank'>Visualizar</a>" : "Nenhum certificado disponível" ?></td>
        <td><?= $atividade['validado'] ? 'Validado' : ($atividade['pendente_atualizacao'] ? 'Pendente de Atualização' : 'Não Validado') ?></td>
        <td>
            <?php if ($atividade['pendente_atualizacao']) { ?>
                <span>Pendente de Atualização do Aluno</span>
            <?php } elseif (!$atividade['validado']) { ?>
                <div class="acoes-container">
                    <form method="post" action="" class="form-validar">
                        <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                        <input type="hidden" name="id_atividade" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
                        <input type="hidden" name="acao" value="validar">
                        <button type="submit" class="btn btn-success">Validar</button>
                    </form>

                    <form method="post" action="" class="form-negar">
                        <input type="hidden" name="matricula_aluno" value="<?= htmlspecialchars($matricula_aluno ?? '') ?>">
                        <input type="hidden" name="id_atividade" value="<?= htmlspecialchars($atividade['id'] ?? '') ?>">
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




























