# SGAC-UNEB - Sistema de Gerenciamento de Atividades Complementares

## Descrição
O SGAC-UNEB é um sistema web desenvolvido para gerenciar as atividades complementares dos alunos da UNEB. O sistema permite que os alunos submetam certificados de atividades, que são validados pelos administradores, enquanto os professores têm acesso para cadastrar novos usuários e administradores.

## Funcionalidades

### Para Alunos (Usuários Comuns)
- Cadastro de atividades complementares
- Upload de certificados/documentos comprobatórios
- Classificação das atividades por categorias pré-definidas
- Acompanhamento do status das atividades (pendentes, validadas, recusadas)
- Visualização do histórico de atividades e carga horária acumulada

### Para Administradores
- Validação ou recusa de atividades submetidas pelos alunos
- Atribuição de carga horária às atividades
- Gerenciamento de categorias de atividades
- Visualização de relatórios e estatísticas

### Para Professores (Frontes)
- Cadastro de novos usuários (alunos e administradores)
- Gerenciamento de perfis de acesso
- Configuração do sistema

## Tecnologias Utilizadas
- **Frontend:** HTML, CSS, JavaScript (React/Vue/Angular - especificar)
- **Backend:** (Node.js/PHP/Java - especificar)
- **Banco de Dados:** (MySQL/PostgreSQL/MongoDB - especificar)
- **Outras:** (listar bibliotecas ou frameworks relevantes)

## Instalação e Configuração
1. Clone o repositório:
```bash
git clone [URL do repositório]
```
2. Instale as dependências:
```bash
npm install
# ou
composer install
```
3. Configure o arquivo `.env` com as credenciais do banco de dados.
4. Execute as migrações:
```bash
npm run migrate
# ou
php artisan migrate
```
5. Inicie o servidor:
```bash
npm start
# ou
php artisan serve
```

## Contribuição
Contribuições são bem-vindas! Para contribuir:
1. Faça um fork do projeto
2. Crie uma branch para sua feature:
```bash
git checkout -b feature/nova-feature
```
3. Faça commit das suas alterações:
```bash
git commit -m 'Adiciona nova feature'
```
4. Faça push para a branch:
```bash
git push origin feature/nova-feature
```
5. Abra um Pull Request

## Licença
[Especificar a licença - MIT/GPL/etc.]

## Contato
[Julia Tito e Jorge Assunção]
