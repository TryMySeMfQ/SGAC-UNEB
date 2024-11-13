<?php
session_start();
include('../config/conexao.php');

// Verifica se o usuário está logado e é gerente
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'gerente') {
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

// Consulta para pegar todos os usuários
$query = "SELECT u.matricula, u.nome, c.nome AS curso, u.tipo_curso, u.tipo_modalidade 
          FROM usuarios u
          LEFT JOIN cursos c ON u.curso_id = c.id";

if ($stmt = $conexao->prepare($query)) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Checa se há erros na consulta
    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        // Retorna os usuários em formato JSON
        echo json_encode($users);
    } else {
        // Nenhum usuário encontrado
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'Erro ao consultar os usuários']);
}
?>

