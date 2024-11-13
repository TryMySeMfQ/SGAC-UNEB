<?php 
session_start();
include('../config/conexao.php');

// Verifica se o usuário está logado e é gerente
if (!isset($_SESSION['matricula']) || $_SESSION['tipo'] != 'gerente') {
    header('Location: login.php');
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
        <div class="options-list">
            <ul>
                <li><a href="cadastro.php" class="btn-dashboard">Cadastrar Usuário</a></li>
                <li><a href="curso.php" class="btn-dashboard">Cursos Usuário</a></li>
            </ul>
        </div>
        
        <!-- Card de usuários -->
        <div class="card">
            <button class="btn-show-users" id="show-users-btn">Mostrar Usuários</button>
            <div id="users-table-container" style="display: none;">
                <div id="loading" style="display: none;">Carregando...</div>
                <table class="users-table" id="users-table">
                    <thead>
                        <tr>
                            <th>Matricula</th>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Tipo de Curso</th>
                            <th>Modalidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- A tabela será preenchida aqui -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('show-users-btn').addEventListener('click', function() {
            let tableContainer = document.getElementById('users-table-container');
            
            // Alterna a visibilidade da tabela
            if (tableContainer.style.display === 'none') {
                tableContainer.style.display = 'block';
                loadUsers(); // Chama a função para carregar os dados
            } else {
                tableContainer.style.display = 'none';
            }
        });

        // Função para carregar os usuários via Fetch
        function loadUsers() {
            document.getElementById('loading').style.display = 'block'; // Exibe o carregamento

            fetch('get_users.php')
                .then(response => response.json()) // Converte a resposta para JSON
                .then(data => {
                    const tableBody = document.querySelector('#users-table tbody');
                    tableBody.innerHTML = ''; // Limpa a tabela antes de preencher

                    // Se não houver dados, mostra uma mensagem
                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5">Nenhum usuário encontrado.</td></tr>`;
                    } else {
                        data.forEach(user => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${user.matricula}</td>
                                <td>${user.nome}</td>
                                <td>${user.curso}</td>
                                <td>${user.tipo_curso}</td>
                                <td>${user.tipo_modalidade}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar os usuários:', error);
                    alert('Houve um erro ao carregar os dados dos usuários.');
                })
                .finally(() => {
                    document.getElementById('loading').style.display = 'none'; // Esconde o carregamento
                });
        }
    </script>
</body>
</html>






