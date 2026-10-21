<?php
// Inicia ou recupera a sessão do usuário (necessário para saber se ele está logado)
session_start();

// Importa o arquivo de funções gerais do sistema, que contém a conexão com o banco e a função de salvar mensagens
require_once __DIR__ . '/../includes/functions.php';

// Verifica se o formulário foi enviado pelo método POST (ou seja, se o usuário clicou no botão de enviar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega o campo 'nome' enviado, remove espaços extras das pontas; se estiver vazio, define como texto em branco
    $nome     = trim($_POST['nome'] ?? '');

    // Pega o campo 'email' enviado e limpa espaços nas pontas
    $email    = trim($_POST['email'] ?? '');

    // Pega o campo 'assunto' enviado e limpa espaços nas pontas
    $assunto  = trim($_POST['assunto'] ?? '');

    // Pega o campo 'mensagem' enviado e limpa espaços nas pontas
    $mensagem = trim($_POST['mensagem'] ?? '');

    // Verifica se os campos obrigatórios (nome, e-mail e mensagem) não estão vazios
    if (!empty($nome) && !empty($email) && !empty($mensagem)) {
        // Executa a função que grava a mensagem no banco de dados
        salvar_mensagem_contato($conexao, $nome, $email, $assunto, $mensagem);

        // Retorna uma resposta em formato JSON para o JavaScript informando o sucesso
        echo json_encode(['status' => 'sucesso']);

        // Interrompe a execução do script PHP imediatamente após responder
        exit();
    } else {
        // Caso algum campo obrigatório esteja faltando, retorna uma resposta de erro em JSON
        echo json_encode(['status' => 'erro']);

        // Interrompe a execução do script PHP
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contate-nos</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o arquivo do cabeçalho (menu topo) reutilizável nesta parte da página -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card contato-card">
            <div class="form-header text-center">
                <h2>Fale Conosco</h2>
                <p class="form-subtitulo">Entre em contato preenchendo o formulário ou através dos nossos canais diretos</p>
                <div class="divisor-dourado"></div>
            </div>

            <!-- Caixa de mensagem de sucesso integrada -->
            <div id="mensagem-sucesso" class="alerta-sucesso-contato" style="display: none;">
                <span class="icone-sucesso">✨</span>
                <div>
                    <strong>Mensagem enviada com sucesso!</strong>
                    <p>Agradecemos o seu contato. Responderemos o mais breve possível.</p>
                </div>
            </div>

            <div class="contato-grid">
                <!-- Formulário com envio suave e gravação no banco de dados -->
                <form id="form-contato" onsubmit="enviarContato(event)" class="form-produto">
                    <div class="form-grupo">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required>
                    </div>

                    <div class="form-grupo">
                        <label for="email">E-mail *</label>
                        <input type="email" name="email" id="email" placeholder="seuemail@exemplo.com" required>
                    </div>

                    <div class="form-grupo">
                        <label for="assunto">Assunto</label>
                        <input type="text" name="assunto" id="assunto" placeholder="Ex: Dúvidas sobre produto">
                    </div>

                    <div class="form-grupo">
                        <label for="mensagem">Mensagem *</label>
                        <textarea name="mensagem" id="mensagem" rows="5" placeholder="Escreva sua mensagem aqui..." required></textarea>
                    </div>

                    <div class="form-acoes-contato">
                        <button type="submit" class="btn-adicionar btn-block">ENVIAR MENSAGEM</button>
                    </div>
                </form>

                <!-- Informações, Canais Diretos e Redes Sociais -->
                <div class="canais-atendimento">
                    <h3>Nossos Canais</h3>
                    <p class="canais-descricao">Estamos à disposição para ajudar você a escolher a peça ideal ou tirar dúvidas sobre o seu pedido.</p>

                    <div class="canal-item">
                        <span class="canal-icone">✉️</span>
                        <div>
                            <strong>E-mail:</strong>
                            <p>contato@luminagraca.com</p>
                        </div>
                    </div>

                    <div class="canal-item">
                        <span class="canal-icone">📱</span>
                        <div>
                            <strong>Telefone / WhatsApp:</strong>
                            <p>(11) 99999-9999</p>
                        </div>
                    </div>

                    <div class="canal-item">
                        <span class="canal-icone">📸</span>
                        <div>
                            <strong>Instagram:</strong>
                            <p>@lumina&graça</p>
                        </div>
                    </div>

                    <div class="canal-item">
                        <span class="canal-icone">📘</span>
                        <div>
                            <strong>Facebook:</strong>
                            <p>lumina&graça</p>
                        </div>
                    </div>

                    <div class="canal-item">
                        <span class="canal-icone">🎵</span>
                        <div>
                            <strong>TikTok:</strong>
                            <p>@lumina&graça</p>
                        </div>
                    </div>

                    <div class="atendimento-horario">
                        <strong>Horário de Atendimento:</strong>
                        <p>Segunda a Sexta, das 09h às 18h</p>
                    </div>
                </div>
            </div>

            <div class="contato-footer">
                <a href="/lumina_graca/index.php" class="btn-link">← Voltar para o Início</a>
            </div>
        </div>
    </main>

    <script>
        function enviarContato(event) {
            event.preventDefault();

            const form = document.getElementById('form-contato');
            const formData = new FormData(form);

            // Envia os dados assincronamente via Fetch API para o PHP salvar na base de dados
            fetch('contato.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'sucesso') {
                        const banner = document.getElementById('mensagem-sucesso');
                        banner.style.display = 'flex';

                        // Limpa os campos
                        form.reset();

                        // Rola suavemente até o aviso
                        banner.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                })
                .catch(error => console.error('Erro ao salvar mensagem:', error));
        }
    </script>

    <!-- Inclui o arquivo do rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>