<?php
session_start();
include('../config/conexao.php');

// Verifica se o usuário está logado e é gerente
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'gerente') {
    header('Location: login.php');
    exit();
}

// Verifica se o formulário de busca foi enviado
$search = isset($_POST['search']) ? '%' . $_POST['search'] . '%' : '%';  // Usa '%' para busca ampla

// Exibir o valor de search para depuração
// var_dump($search); 

$query = "SELECT u.matricula, u.nome, c.nome AS cursos, c.tipo, c.tipo AS modalidade
          FROM usuarios u
          LEFT JOIN cursos c ON u.curso_id = c.id
          WHERE u.nome LIKE ? OR u.matricula LIKE ?";

if ($stmt = $conexao->prepare($query)) {
    // Liga os parâmetros
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'matricula' => $row['matricula'],
                'nome' => $row['nome'],
                'curso' => $row['cursos'],
                'tipo' => $row['tipo'],
                'tipo_modalidade' => $row['modalidade']
            ];
        }
    }
} else {
    echo json_encode(['error' => 'Erro ao consultar os usuários']);
    exit();
}
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gerente</title>
    <link rel="stylesheet" href="../../Public/CSS/dashboard_gerente.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="dashboard-title">SGAC - Dashboard Gerente</h1>
            <div class="nav-links">
                <a href="../helpers/logout.php" class="logout-link">Sair</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Bem-vindo, Gerente</h1>
        
        <!-- Formulário de busca -->
        <form method="POST" action="">
            <input type="text" name="search" placeholder="Buscar por nome ou matrícula" />
            <button type="submit">Buscar</button>
        </form>

        <div class="options-list">
            <ul>
                <li><a href="cadastro.php" class="btn-dashboard">Cadastrar Usuário</a></li>
                <li><a href="curso.php" class="btn-dashboard">Cadastrar Cursos</a></li>
            </ul>
        </div>
        
        <!-- Card de usuários -->
        <div class="card">
            <h2>Usuários do Sistema</h2>
            <div id="users-table-container">
                <div id="loading" style="display: none;">Carregando...</div>
                <table class="users-table" id="users-table">
                    <thead>
                        <tr>
                            <th>Matricula</th>
                            <th>Nome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Preenchendo os dados dos usuários -->
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['matricula']) ?></td>
                                    <td><?= htmlspecialchars($user['nome']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>









