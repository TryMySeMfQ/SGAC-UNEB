<?php
session_start();
include('../config/conexao.php');

// Inicializar mensagens
$success_message = '';
$error_message = '';

// Verifica se o usuário é aluno
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'aluno') {
    header('Location: login_aluno.php');
    exit();
}



$matricula = $_SESSION['matricula'];

// Função para calcular horas complementares com limite por categoria, considerando o curso do aluno
$queryCurso = "SELECT tipo_curso FROM alunos WHERE matricula = ?";
$stmtCurso = mysqli_prepare($conexao, $queryCurso);
mysqli_stmt_bind_param($stmtCurso, 'i', $matricula);
mysqli_stmt_execute($stmtCurso);
$resultCurso = mysqli_stmt_get_result($stmtCurso);
$aluno = mysqli_fetch_assoc($resultCurso);

// Verifique se o aluno foi encontrado
if ($aluno) {
    $tipo_curso = $aluno['tipo_curso']; // Atribuindo à variável
} else {
    $error_message = 'Aluno não encontrado.';
    // Tratar o erro (por exemplo, redirecionar ou exibir uma mensagem)
}

// Função para calcular horas complementares com limite por categoria, considerando o curso do aluno
function calcularHorasComplementares($atividades, $tipo_curso) {
    global $conexao; 
    $total_ac_hours = 0;

    // Seleciona o barema correto de acordo com o tipo de curso
    if ($tipo_curso === 'bacharelado') {
        $tabela_barema = 'baremabacharelado';
    } elseif ($tipo_curso === 'licenciatura') {
        $tabela_barema = 'baremalicenciatura';
    } else {
        return 0; // Caso o tipo de curso seja inválido
    }

    foreach ($atividades as $atividade) {
        $category_id = $atividade['categoria_id'];
        $us_hora = $atividade['horas']; // Horas informadas pelo usuário

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
            // Tratar erro caso a categoria não seja encontrada
            echo "Categoria não encontrada para ID: $category_id";
        }
    }

    return $total_ac_hours;
}

// Processar o envio da nova atividade
$queryCursos = "SELECT tipo_curso FROM alunos WHERE matricula = ?";
$stmtCursos = mysqli_prepare($conexao, $queryCursos);
mysqli_stmt_bind_param($stmtCursos, 'i', $matricula);
mysqli_stmt_execute($stmtCursos);
$resultCursos = mysqli_stmt_get_result($stmtCursos);
$aluno = mysqli_fetch_assoc($resultCursos);

if ($aluno) {
    $tipo_curso = $aluno['tipo_curso']; // Atribuindo à variável
} else {
    // Tratar erro se o aluno não for encontrado
    $error_message = 'Aluno não encontrado.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os dados do formulário
    $descricao = $_POST['descricao'] ?? '';
    $horas = $_POST['horas'] ?? '';
    $certificado = $_FILES['certificado'] ?? null;
    $categoria_id = $_POST['categoria'] ?? null; // Capturando o categoria_id do formulário

    // Validar dados
    if (empty($descricao) || empty($horas) || empty($certificado['name']) || empty($categoria_id)) {
        $error_message = 'Todos os campos são obrigatórios.';
    } else {
        // Calcula as horas válidas com base no tipo de curso
        $horas_validas = calcularHorasComplementares([['categoria_id' => $categoria_id, 'horas' => $horas]], $tipo_curso);

        if ($horas_validas == 0) {
            $error_message = 'As horas informadas excedem o limite permitido para esta categoria.';
        } else {
            // Processar o upload do certificado
            $upload_dir = 'uploads/';
            
            // Criar o diretório se não existir
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_file = $upload_dir . basename($certificado['name']);
            $upload_ok = 1;
            $file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

            // Verificar se o arquivo é uma imagem ou PDF
            if ($file_type != 'pdf' && !in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error_message = 'Somente arquivos PDF e imagens são permitidos.';
                $upload_ok = 0;
            }

            // Verificar se o upload foi bem-sucedido
            if ($upload_ok && move_uploaded_file($certificado['tmp_name'], $upload_file)) {
                // Inserir dados no banco de dados, incluindo horas ajustadas
                $query = "INSERT INTO atividades (matricula_aluno, descricao, horas, certificado, status, categoria_id) VALUES (?, ?, ?, ?, 'pendente', ?)";
                $stmt = $conexao->prepare($query);
                $stmt->bind_param('isssi', $matricula, $descricao, $horas_validas, $upload_file, $categoria_id);

                if ($stmt->execute()) {
                    $success_message = 'Atividade enviada com sucesso!';
                } else {
                    $error_message = 'Erro ao enviar a atividade.';
                }
                $stmt->close();
            } else {
                $error_message = 'Erro ao fazer o upload do certificado.';
            }
        }
    }
}



// Consulta as atividades do aluno
$tabela_barema = $tipo_curso === 'bacharelado' ? 'baremabacharelado' : 'baremalicenciatura';

// Consulta as atividades do aluno
$query = "
    SELECT a.*, b.nome AS categoria_nome
    FROM atividades a
    LEFT JOIN $tabela_barema b ON a.categoria_id = b.id
    WHERE a.matricula_aluno = ?
";
$stmt = mysqli_prepare($conexao, $query);
mysqli_stmt_bind_param($stmt, 'i', $matricula);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Consulta as mensagens do aluno
$queryMensagens = "SELECT * FROM mensagens WHERE matricula_aluno = ?";
$stmtMensagens = mysqli_prepare($conexao, $queryMensagens);
mysqli_stmt_bind_param($stmtMensagens, 'i', $matricula);
mysqli_stmt_execute($stmtMensagens);
$resultMensagens = mysqli_stmt_get_result($stmtMensagens);

// Atualiza a atividade
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_atividade'])) {
    $atividade_id = $_POST['atividade_id'];
    $nova_descricao = $_POST['nova_descricao'];
    $novas_horas = $_POST['novas_horas'];

    // Processa o upload do novo certificado se fornecido
    $certificado = null;
    if (isset($_FILES['novo_certificado']) && $_FILES['novo_certificado']['error'] == UPLOAD_ERR_OK) {
        $certificado = 'uploads/' . basename($_FILES['novo_certificado']['name']);
        if (!move_uploaded_file($_FILES['novo_certificado']['tmp_name'], $certificado)) {
            $error_message = 'Erro ao fazer o upload do certificado.';
        }
    }

    $update_query = "UPDATE atividades SET descricao = ?, horas = ?, certificado = ?, pendente_atualizacao = FALSE WHERE id = ?";
    $stmtUpdate = mysqli_prepare($conexao, $update_query);
    mysqli_stmt_bind_param($stmtUpdate, 'sisi', $nova_descricao, $novas_horas, $certificado, $atividade_id);

    if (mysqli_stmt_execute($stmtUpdate)) {
        $success_message = 'Atividade atualizada com sucesso!';
        header("Location: login_aluno.php"); // Redireciona para a mesma página ou página de sucesso
        exit();
    } else {
        $error_message = 'Erro ao atualizar a atividade: ' . mysqli_error($conexao);
    }
}

// Calcula o total de horas validadas
$total_horas_exigidas = 200; // Exemplo: 200 horas
$total_horas = 0;
$atividades = mysqli_fetch_all($result, MYSQLI_ASSOC);
foreach ($atividades as $atividade) {
    if ($atividade['validado']) {
        $total_horas += $atividade['horas'];
    }
}

// Calcule as horas faltantes
$horas_faltantes = $total_horas_exigidas - $total_horas;

// Recupera todas as atividades
$atividadesValidas = array_filter($atividades, function($atividade) {
    return $atividade['validado'];
});

// Codifica para JSON
$jsonAtividades = json_encode($atividadesValidas, JSON_UNESCAPED_UNICODE);

// Após o login, recupera o nome
$queryNome = "SELECT nome FROM alunos WHERE matricula = ?";
$stmtNome = mysqli_prepare($conexao, $queryNome);
mysqli_stmt_bind_param($stmtNome, 'i', $matricula);
mysqli_stmt_execute($stmtNome);
$resultNome = mysqli_stmt_get_result($stmtNome);
$aluno = mysqli_fetch_assoc($resultNome);

if ($aluno) {
    $_SESSION['nome'] = $aluno['nome']; // Armazena o nome do aluno na sessão
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Aluno</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Public/CSS/dashboard_aluno.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@latest/dist/jspdf.umd.min.js"></script>
</head>
<body>
<header>
<nav class="navbar">
            <h1 class="navbar-title">SGAC</h1>
            <div class="navbar-right">
                <i class="fas fa-user-circle profile-icon"></i>
                <span class="aluno-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                <a href="../helpers/logout.php" class="logout-link">Sair</a>
            </div>
    </nav>
</header>
<div class="container mt-5">
        <div class="card p-4 my-4">
        <h2>Minhas Atividades</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Horas</th>
                    <th>Categoria</th>
                    <th>Certificado</th>
                    <th>Status</th>
                    <th>Motivo de Negação</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($atividades as $atividade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($atividade['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($atividade['horas']); ?></td>
                        <td><?php echo htmlspecialchars($atividade['categoria_nome']); ?></td>
                        <td>
                            <?php if (!empty($atividade['certificado'])): ?>
                                <a href="<?php echo htmlspecialchars($atividade['certificado']); ?>" target="_blank">Visualizar</a>
                            <?php else: ?>
                                Nenhum certificado disponível
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($atividade['validado'] ? 'Validado' : ($atividade['pendente_atualizacao'] ? 'Pendente de Atualização' : 'Não Validado')); ?></td>
                        <td><?php echo htmlspecialchars($atividade['motivo_negacao'] ?? ''); ?></td>
                        <td>
                            <?php if ($atividade['pendente_atualizacao']): ?>
                                <!-- Botão para exibir o formulário de edição -->
                                <button class="btn btn-warning edit-btn" data-id="<?php echo $atividade['id']; ?>" data-descricao="<?php echo htmlspecialchars($atividade['descricao']); ?>" data-horas="<?php echo $atividade['horas']; ?>">Editar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total de Horas: <?php echo htmlspecialchars($total_horas); ?></h3>
        <h3>Horas Faltantes: <?php echo htmlspecialchars($horas_faltantes); ?></h3>
        <div class="text-center mb-4">
        <button id="gerarPDF">Gerar PDF</button>
        <script>
                const atividadesValidas = <?php echo json_encode($atividades); ?>; // already escaped by json_encode
        </script>
        </div>
        </div>

        <!-- Formulário de edição que aparece ao clicar em "Editar" -->
        <div class="card p-4 my-4">
            <div id="edit-form-container" style="display: none;">
                <h3>Editar Atividade</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="atividade_id" id="edit-atividade-id">
                    <div class="form-group">
                        <label for="nova_descricao">Nova Descrição</label>
                        <input type="text" class="form-control" name="nova_descricao" id="edit-descricao" required>
                    </div>
                    <div class="form-group">
                        <label for="novas_horas">Novas Horas</label>
                        <input type="number" class="form-control" name="novas_horas" id="edit-horas" required>
                    </div>
                    <div class="form-group">
                        <label for="novo_certificado">Novo Certificado (opcional)</label>
                        <input type="file" class="form-control" name="novo_certificado">
                    </div>
                    <button type="submit" class="btn btn-primary" name="editar_atividade">Atualizar Atividade</button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancelar</button>
                </form>
            </div>
        </div>
        
    </div>
</div>

<!-- Script JavaScript para preencher o formulário de edição -->
<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            // Preencher o formulário de edição com os dados da atividade selecionada
            document.getElementById('edit-atividade-id').value = this.getAttribute('data-id');
            document.getElementById('edit-descricao').value = this.getAttribute('data-descricao');
            document.getElementById('edit-horas').value = this.getAttribute('data-horas');
            document.getElementById('edit-form-container').style.display = 'block';
        });
    });

    function hideEditForm() {
        document.getElementById('edit-form-container').style.display = 'none';
    }
</script>

    <div class="card p-4 my-4">
        <h2>Mensagens do Administrador</h2>
        <?php if (mysqli_num_rows($resultMensagens) > 0) { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Mensagem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mensagem = mysqli_fetch_assoc($resultMensagens)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mensagem['data_envio']); ?></td>
                            <td><?php echo htmlspecialchars($mensagem['mensagem']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Não há mensagens do administrador no momento.</p>
        <?php } ?>
    </div>
    <div class="card p-4 my-4">
    <div class="form-group">
        <h2>Enviar Nova Atividade</h2>
        <?php if ($success_message) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php } ?>
        <?php if ($error_message) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php } ?>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="descricao">Descrição da Atividade:</label>
            <input type="text" id="descricao" name="descricao" required>

            <label for="horas">Horas:</label>
            <input type="number" id="horas" name="horas" required>

            <label for="certificado">Enviar Certificado (PDF ou Imagem):</label>
            <input type="file" id="certificado" name="certificado" accept=".pdf, image/*" required>

            <label for="categoria">Categoria:</label>
<select name="categoria" id="categoria" class="form-control" required>
    <option value="">Selecione uma categoria</option>
    <?php
    // Aqui você pode construir as opções com base no barema correto do tipo de curso
    if (isset($tipo_curso)) {
        $tabela_barema = $tipo_curso === 'bacharelado' ? 'baremabacharelado' : 'baremalicenciatura';
        
        $queryCategorias = "SELECT * FROM $tabela_barema"; // Seleciona todas as categorias do barema
        $resultCategorias = mysqli_query($conexao, $queryCategorias);

        while ($categoria = mysqli_fetch_assoc($resultCategorias)) {
            echo '<option value="' . $categoria['id'] . '">' . htmlspecialchars($categoria['nome']) . '</option>';
        }
    }
    ?>
</select>
            <div class="text-center mb-4">
                <input type="submit" value="Enviar Atividade">
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../../Public/JS/gerar_pdf.js"></script>
</div><!-- Fim do container -->
</body>
</html>




















