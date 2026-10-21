<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Importa o arquivo com as funções do sistema e a conexão com o banco de dados
require_once __DIR__ . '/includes/functions.php';

// Variável para armazenar mensagens de erro no login
$erro = '';

// Verifica se o formulário foi enviado pelo método POST (ao clicar em 'ENTRAR')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados digitados e limpa espaços extras das pontas
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Executa a função do functions.php para verificar e-mail e senha no banco de dados
    $usuario = autenticar_usuario($conexao, $email, $senha);

    // Se as credenciais estiverem corretas (retornar os dados do usuário)
    if ($usuario) {
        // Salva os dados essenciais do usuário na sessão ativa do navegador
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['usuario_nome']  = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo']  = $usuario['tipo']; // Define o perfil: 'admin' ou 'cliente'

        // Redireciona o usuário para a página inicial
        header("Location: index.php");
        // Encerra a execução do script
        exit;
    } else {
        // Se as credenciais forem inválidas, define a mensagem de aviso
        $erro = 'E-mail ou senha inválidos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <div class="form-card auth-card">
            <div class="form-header text-center">
                <h2>Acessar Minha Conta</h2>
                <p class="form-subtitulo">Informe suas credenciais para continuar</p>
            </div>

            <!-- Exibe o bloco de alerta caso exista alguma mensagem de erro cadastrada -->
            <?php if (!empty($erro)): ?>
                <div class="alerta-erro">
                    <!-- Trata os caracteres especiais para evitar ataques do tipo XSS -->
                    <span>⚠️ <?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="form-produto">
                <div class="form-grupo">
                    <label for="email">E-mail *</label>
                    <input type="email" name="email" id="email" required placeholder="seu@email.com">
                </div>

                <div class="form-grupo">
                    <label for="senha">Senha *</label>
                    <input type="password" name="senha" id="senha" required placeholder="Sua senha de acesso">
                </div>

                <div class="form-acoes-auth">
                    <button type="submit" class="btn-adicionar btn-block">ENTRAR</button>
                </div>
            </form>

            <div class="auth-footer">
                <p>Ainda não tem conta? <a href="gerenciar_usuario/adicionar_u.php" class="link-destaque">Criar nova conta</a></p>
                <a href="index.php" class="btn-link">← Voltar para o Início</a>
            </div>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>