<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Importa o arquivo com as funções globais e a conexão com o banco de dados
require_once __DIR__ . '/../includes/functions.php';

// Variável para armazenar o ID digitado para exclusão
$id = '';

// Processa a exclusão quando o formulário é enviado pelo método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura o ID enviado no formulário e remove espaços extras das pontas
    $id = trim($_POST['id'] ?? '');

    // Verifica se um ID válido foi informado
    if (!empty($id)) {
        // Executa a função que apaga o registro do usuário no banco PostgreSQL
        apagar_u($conexao, $id);

        // Se o usuário estiver apagando a PRÓPRIA conta atualmente conectada, encerra a sessão dele
        if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $id) {
            // Limpa todas as variáveis armazenadas na sessão
            session_unset();
            // Destrói a sessão do usuário
            session_destroy();
        }

        // Redireciona para a página inicial informando a exclusão via URL
        header("Location: ../index.php?conta_excluida=1");
        // Encerra a execução do script
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletar Conta</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card auth-card">
            <div class="form-header text-center">
                <h2>Deletar Conta</h2>
                <p class="form-subtitulo">Informe o ID do usuário que deseja remover do sistema</p>
                <small class="form-alerta">Esta ação é irreversível</small>
                <div class="divisor-dourado"></div>
            </div>

            <form action="" method="post" class="form-produto">
                <div class="form-grupo">
                    <label for="id">ID do Registro *</label>
                    <input type="number" name="id" id="id" placeholder="Digite o ID para exclusão" required min="1">
                </div>

                <div class="form-acoes">
                    <a href="../conta.php" class="btn-cancelar">VOLTAR</a>
                    <button type="submit" class="btn-adicionar btn-deletar-destaque">APAGAR REGISTRO</button>
                </div>
            </form>

            <div class="auth-footer text-center">
                <a href="../index.php" class="btn-link">← Voltar para o Início</a>
            </div>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>