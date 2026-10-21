<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Importa o arquivo com as funções globais e a conexão com o banco de dados
require_once __DIR__ . '/includes/functions.php';

// Bloqueia o acesso caso o usuário NÃO esteja logado ou NÃO seja um administrador
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    // Redireciona o usuário comum para a tela de login
    header('Location: login.php');
    // Encerra a execução do script
    exit;
}

// Executa a função do functions.php que busca todas as mensagens de contato registradas no banco
$mensagens = buscar_mensagens_contato($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens de Contato</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <div class="admin-card">
            <div class="form-header text-center">
                <h2>Mensagens Recebidas</h2>
                <p class="form-subtitulo">Acompanhe as dúvidas e contatos enviados pelos clientes</p>
                <div class="divisor-dourado"></div>
            </div>

            <!-- Verifica se a lista de mensagens retornada do banco está vazia -->
            <?php if (empty($mensagens)): ?>
                <p class="pedidos-vazio text-center">
                    Nenhuma mensagem recebida até o momento.
                </p>
            <?php else: ?>
                <div class="tabela-responsive">
                    <table class="tabela-admin">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Assunto</th>
                                <th>Mensagem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Percorre a lista de mensagens recebidas linha por linha -->
                            <?php foreach ($mensagens as $m): ?>
                                <tr>
                                    <!-- Converte o formato do banco de dados para a data/hora brasileira (dd/mm/aaaa hh:mm) -->
                                    <td style="white-space: nowrap;"><?php echo date('d/m/Y H:i', strtotime($m['data_envio'])); ?></td>
                                    <!-- Exibe o nome de quem enviou, tratando caracteres especiais contra XSS -->
                                    <td><strong><?php echo htmlspecialchars($m['nome']); ?></strong></td>
                                    <!-- Cria um link mailto para responder diretamente ao e-mail informado -->
                                    <td><a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" class="btn-link"><?php echo htmlspecialchars($m['email']); ?></a></td>
                                    <!-- Exibe o assunto da mensagem -->
                                    <td><?php echo htmlspecialchars($m['assunto']); ?></td>
                                    <!-- Converte as quebras de linha da mensagem (\n) em tags HTML (<br>) com nl2br -->
                                    <td><?php echo nl2br(htmlspecialchars($m['mensagem'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="auth-footer text-center" style="margin-top: 30px;">
                <a href="index.php" class="btn-link">← Voltar para o Inicio</a>
            </div>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>