<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Importa o arquivo com as funções globais e a conexão com o banco de dados
require_once __DIR__ . "/../includes/functions.php";

// Variável para controlar a exibição da mensagem de sucesso na tela
$mensagem_sucesso = false;

// Verifica se o formulário foi enviado através do método POST (ao clicar em 'CADASTRAR')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe o campo 'nome', remove espaços extras das pontas ou atribui texto vazio caso não exista
    $nome  = trim($_POST['nome'] ?? '');

    // Recebe o campo 'email' e limpa espaços das pontas
    $email = trim($_POST['email'] ?? '');

    // Recebe o campo 'senha' e limpa espaços das pontas
    $senha = trim($_POST['senha'] ?? '');

    // Define o tipo de usuário padrão como 'cliente'
    $tipo  = 'cliente';

    // Valida se todos os campos obrigatórios foram preenchidos
    if (!empty($nome) && !empty($email) && !empty($senha)) {
        // Executa a função que cadastra o novo usuário no banco de dados PostgreSQL
        adicionar_u($conexao, $nome, $email, $senha, $tipo);

        // Define a variável como verdadeira para mostrar o aviso de sucesso no HTML
        $mensagem_sucesso = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre-se</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card auth-card">
            <div class="form-header text-center">
                <h2>Criar Conta</h2>
                <p class="form-subtitulo">Junte-se à Lumina & Graça para uma experiência exclusiva</p>
                <small class="form-alerta">Campos obrigatórios</small>
                <div class="divisor-dourado"></div>
            </div>

            <!-- Verifica se o cadastro foi realizado com sucesso para exibir o banner verde de confirmação -->
            <?php if ($mensagem_sucesso): ?>
                <div class="alerta-sucesso-contato">
                    <span class="icone-sucesso">✨</span>
                    <div>
                        <strong>Cadastro realizado com sucesso!</strong>
                        <p>Sua conta foi criada. <a href="../login.php" class="btn-link" style="display: inline; text-decoration: underline;">Clique aqui para fazer login</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="form-produto">
                <div class="form-grupo">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required>
                </div>

                <div class="form-grupo">
                    <label for="email">E-mail *</label>
                    <input type="email" name="email" id="email" placeholder="seu@email.com" required>
                </div>

                <div class="form-grupo">
                    <label for="senha">Senha *</label>
                    <input type="password" name="senha" id="senha" placeholder="Crie uma senha segura" required>
                </div>

                <div class="form-acoes">
                    <a href="../index.php" class="btn-cancelar">VOLTAR</a>
                    <button type="reset" class="btn-limpar">LIMPAR</button>
                    <button type="submit" class="btn-adicionar">CADASTRAR</button>
                </div>
            </form>

            <div class="auth-footer text-center">
                <p style="font-size: 0.9rem; color: var(--cor-texto-suave);">
                    Já possui uma conta? <a href="../login.php" class="btn-link" style="display: inline;">Faça Login</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>