<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Importa o arquivo com as funções globais e a conexão com o banco de dados
require_once __DIR__ . '/../includes/functions.php';

// Bloqueia o acesso de usuários não logados e redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    // Interrompe a execução do script
    exit;
}

// Guarda o ID do usuário logado armazenado na sessão
$usuario_id = $_SESSION['usuario_id'];

// Processa a atualização dos dados quando o formulário é enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados digitados e remove espaços extras do início e do fim
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    // Mantém o tipo de conta salvo na sessão ou define como 'cliente' por padrão
    $tipo  = $_SESSION['usuario_tipo'] ?? 'cliente';

    // Verifica se a função de atualizar usuário existe antes de executá-la
    if (function_exists('atualizar_u')) {
        // Atualiza os dados do usuário no banco de dados PostgreSQL
        atualizar_u($conexao, $usuario_id, $nome, $email, $senha, $tipo);
    }

    // Atualiza imediatamente os dados da sessão ativa para refletir as mudanças no sistema
    $_SESSION['usuario_nome']  = $nome;
    $_SESSION['usuario_email'] = $email;

    // Redireciona de volta para a página 'Minha Conta' enviando o parâmetro de sucesso na URL
    header("Location: ../conta.php?sucesso=1");
    // Encerra o script
    exit();
}

// Prepara a consulta SQL para buscar o nome e e-mail atuais do usuário no banco
$stmt = $conexao->prepare("SELECT nome, email FROM usuarios WHERE id = :id");
// Executa a busca passando o ID do usuário conectado
$stmt->execute([':id' => $usuario_id]);
// Retorna os dados do usuário em formato de array associativo
$usuario_atual = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Informações - Lumina & Graça</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card auth-card">
            <div class="form-header text-center">
                <h2>Atualizar Informações</h2>
                <p class="form-subtitulo">Mantenha seus dados de acesso sempre atualizados</p>
                <small class="form-alerta">Campos obrigatórios</small>
                <div class="divisor-dourado"></div>
            </div>

            <form action="atualizar_u.php" method="POST" class="form-produto">
                <div class="form-grupo">
                    <label for="nome">Nome Completo *</label>
                    <!-- Preenche o campo de texto com o nome atual do usuário, tratando caracteres especiais com htmlspecialchars -->
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($usuario_atual['nome'] ?? ''); ?>" required placeholder="Seu nome completo">
                </div>

                <div class="form-grupo">
                    <label for="email">E-mail *</label>
                    <!-- Preenche o campo de e-mail com o endereço atual registrado no banco -->
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario_atual['email'] ?? ''); ?>" required placeholder="seu@email.com">
                </div>

                <div class="form-grupo">
                    <label for="senha">Nova Senha</label>
                    <input type="password" name="senha" id="senha" placeholder="Preencha apenas se desejar alterar">
                </div>

                <div class="form-acoes">
                    <a href="../conta.php" class="btn-cancelar">CANCELAR</a>
                    <button type="reset" class="btn-limpar">LIMPAR</button>
                    <button type="submit" class="btn-adicionar">SALVAR ALTERAÇÕES</button>
                </div>
            </form>

            <div class="auth-footer text-center">
                <a href="../conta.php" class="btn-link">← Voltar para Minha Conta</a>
            </div>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>