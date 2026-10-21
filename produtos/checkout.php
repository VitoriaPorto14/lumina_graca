<?php
// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Verifica se o usuário NÃO está logado
if (!isset($_SESSION['usuario_id'])) {
    // Guarda os parâmetros da requisição atual (se houver) para não perder o produto selecionado
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    // Redireciona para a tela de login enviando a URL de retorno para o checkout após autenticação
    header('Location: ../login.php?redirect=' . urlencode('produtos/checkout.php' . $queryString));
    // Interrompe o carregamento da página
    exit;
}

// Importa o arquivo com as funções globais e a conexão com o banco de dados
require_once __DIR__ . "/../includes/functions.php";

// Recebe as informações do produto via parâmetro GET ou formulário POST
$produto_id     = $_GET['id'] ?? $_POST['id'] ?? '';
$produto_nome   = $_GET['produto'] ?? $_POST['produto'] ?? '';
$produto_preco  = $_GET['preco'] ?? $_POST['preco'] ?? '';

// Inicializa variáveis de controle da tela
$pedido_finalizado = false;
$dados_pedido = [];
$pedido_id_gerado = null;

// Número de suporte da loja para atendimento no WhatsApp
$numero_whatsapp = "5511999999999";

// Processa a gravação da compra quando o formulário é enviado pelo método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define a flag como verdadeira para exibir a tela de confirmação/sucesso
    $pedido_finalizado = true;

    // Decrementa 1 unidade do estoque no banco de dados se o ID do produto for informado
    if (!empty($produto_id)) {
        decrementar_estoque($conexao, $produto_id);
    }

    // Limpa os espaços dos dados de endereço preenchidos no formulário
    $cep         = trim($_POST['cep'] ?? '');
    $rua         = trim($_POST['rua'] ?? '');
    $numero      = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro      = trim($_POST['bairro'] ?? '');
    $cidade      = trim($_POST['cidade'] ?? '');
    $estado      = trim($_POST['estado'] ?? '');

    // Formata a frase completa de endereço para exibição e mensagem
    $endereco_completo = "Rua {$rua}, Nº {$numero}" . ($complemento ? " ({$complemento})" : "") . " Bairro: {$bairro}, {$cidade}/{$estado} CEP: {$cep}";

    // Organiza os dados da compra em um array associativo
    $dados_pedido = [
        'nome'              => trim($_POST['nome'] ?? ''),
        'endereco_completo' => $endereco_completo,
        'pagamento'         => trim($_POST['pagamento'] ?? ''),
        'produto'           => trim($_POST['produto'] ?? ''),
        'preco'             => trim($_POST['preco'] ?? '')
    ];

    // =========================================================================
    // GRAVAÇÃO NO BANCO DE DADOS (PostgreSQL)
    // =========================================================================
    $usuario_id = $_SESSION['usuario_id'];
    $valor_total = (float)$dados_pedido['preco'];

    // 1. Insere o registro principal na tabela 'pedidos' com status 'pendente' e resgata o ID gerado
    $stmt_ped = $conexao->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (:usuario_id, :total, 'pendente') RETURNING id");
    $stmt_ped->execute([
        ':usuario_id' => $usuario_id,
        ':total'      => $valor_total
    ]);
    $res_ped = $stmt_ped->fetch(PDO::FETCH_ASSOC);
    $pedido_id_gerado = $res_ped['id'] ?? null;

    // 2. Insere o item comprado na tabela relacional 'item_pedido'
    if ($pedido_id_gerado && !empty($produto_id)) {
        $stmt_item = $conexao->prepare("INSERT INTO item_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (:pedido_id, :produto_id, 1, :preco)");
        $stmt_item->execute([
            ':pedido_id'  => $pedido_id_gerado,
            ':produto_id' => $produto_id,
            ':preco'      => $valor_total
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card checkout-card">
            <!-- SE O PEDIDO FOI CONCLUÍDO: Exibe a tela de confirmação e o link do WhatsApp -->
            <?php if ($pedido_finalizado): ?>
                <div class="checkout-sucesso">
                    <div class="sucesso-icone">✨</div>
                    <h2>Pedido Solicitado com Sucesso!</h2>
                    <!-- Exibe o nome do cliente formatado para evitar XSS -->
                    <p class="sucesso-subtexto">Obrigado pela preferência, <strong><?php echo htmlspecialchars($dados_pedido['nome']); ?></strong>.</p>

                    <!-- Verifica se o ID do pedido foi gerado no banco e formata com zeros à esquerda (ex: #0005) -->
                    <?php if ($pedido_id_gerado): ?>
                        <p class="sucesso-instrucao">Número do seu Pedido: <strong>#<?php echo str_pad($pedido_id_gerado, 4, '0', STR_PAD_LEFT); ?></strong></p>
                    <?php endif; ?>

                    <p class="sucesso-instrucao">Acompanhe seu pedido pelo nosso WhatsApp para confirmar os detalhes de envio e pagamento.</p>

                    <!-- Monta a mensagem pré-formatada do WhatsApp com os dados da compra -->
                    <?php
                    $texto_wa = "Olá! Fiz um pedido no site Lumina & Graça (#" . str_pad($pedido_id_gerado, 4, '0', STR_PAD_LEFT) . ") e gostaria de acompanhar:\n"
                        . "Produto: " . $dados_pedido['produto'] . "\n"
                        . "Cliente: " . $dados_pedido['nome'] . "\n"
                        . "Forma de Pagamento: " . $dados_pedido['pagamento'] . "\n"
                        . "Endereço: " . $dados_pedido['endereco_completo'];
                    // Converte o texto da mensagem para o formato válido de URL (urlencode)
                    $link_wa = "https://wa.me/" . $numero_whatsapp . "?text=" . urlencode($texto_wa);
                    ?>

                    <div class="sucesso-acoes">
                        <!-- Botão que abre a conversa direta com a loja no WhatsApp -->
                        <a href="<?php echo $link_wa; ?>" target="_blank" class="btn-whatsapp">
                            <span>💬 Acompanhar pelo WhatsApp</span>
                        </a>
                        <a href="/lumina_graca/produtos/m_produtos.php" class="btn-link">Voltar para a Vitrine</a>
                    </div>
                </div>
            <!-- SE O PEDIDO AINDA NÃO FOI FINALIZADO: Exibe o formulário de checkout -->
            <?php else: ?>
                <div class="form-header">
                    <h2>Finalizar Pedido</h2>
                    <p class="form-subtitulo">Preencha seus dados para concluir a solicitação</p>
                    <small class="form-alerta">Campos obrigatórios</small>
                </div>

                <div class="resumo-produto-card">
                    <span class="resumo-label">Item Selecionado</span>
                    <!-- Exibe o nome do produto vindo do formulário/URL -->
                    <h3 class="resumo-titulo"><?php echo htmlspecialchars($produto_nome); ?></h3>
                    <!-- Se houver preço informado, exibe formatado no padrão brasileiro (R$ 0,00) -->
                    <?php if (!empty($produto_preco)): ?>
                        <p class="resumo-preco">R$ <?php echo number_format((float)$produto_preco, 2, ',', '.'); ?></p>
                    <?php endif; ?>
                </div>

                <form action="checkout.php" method="POST" class="form-produto">
                    <!-- Campos ocultos mantendo o ID, nome e preço do produto selecionado -->
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($produto_id); ?>">
                    <input type="hidden" name="produto" value="<?php echo htmlspecialchars($produto_nome); ?>">
                    <input type="hidden" name="preco" value="<?php echo htmlspecialchars($produto_preco); ?>">

                    <div class="secao-checkout">
                        <h3 class="checkout-subtitulo">Dados do Cliente</h3>
                        <div class="form-grupo">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required>
                        </div>
                    </div>

                    <div class="secao-checkout">
                        <h3 class="checkout-subtitulo">Endereço de Entrega</h3>

                        <div class="form-grupo">
                            <label for="cep">CEP *</label>
                            <input type="text" name="cep" id="cep" placeholder="00000-000" required>
                        </div>

                        <div class="form-linha-dupla">
                            <div class="form-grupo form-flex-3">
                                <label for="rua">Logradouro / Rua *</label>
                                <input type="text" name="rua" id="rua" placeholder="Ex: Av. Brasil ou Rua das Flores" required>
                            </div>
                            <div class="form-grupo form-flex-1">
                                <label for="numero">Número *</label>
                                <input type="text" name="numero" id="numero" placeholder="Ex: 123" required>
                            </div>
                        </div>

                        <div class="form-linha-dupla">
                            <div class="form-grupo">
                                <label for="complemento">Complemento (opcional)</label>
                                <input type="text" name="complemento" id="complemento" placeholder="Ex: Apto 102">
                            </div>
                            <div class="form-grupo">
                                <label for="bairro">Bairro *</label>
                                <input type="text" name="bairro" id="bairro" placeholder="Digite o bairro" required>
                            </div>
                        </div>

                        <div class="form-linha-dupla">
                            <div class="form-grupo form-flex-3">
                                <label for="cidade">Cidade *</label>
                                <input type="text" name="cidade" id="cidade" placeholder="Digite a cidade" required>
                            </div>
                            <div class="form-grupo form-flex-1">
                                <label for="estado">Estado (UF) *</label>
                                <input type="text" name="estado" id="estado" placeholder="SP" maxlength="2" style="text-transform: uppercase;" required>
                            </div>
                        </div>
                    </div>

                    <div class="secao-checkout">
                        <h3 class="checkout-subtitulo">Forma de Pagamento</h3>
                        <div class="opcoes-pagamento">
                            <label class="radio-card">
                                <input type="radio" name="pagamento" value="PIX" checked>
                                <span class="radio-custom"></span>
                                <span class="radio-label">PIX (Aprovação Imediata)</span>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="pagamento" value="Cartão de Crédito">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Cartão de Crédito</span>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="pagamento" value="Dinheiro">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Dinheiro na Entrega</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-acoes">
                        <a href="/lumina_graca/index.php" class="btn-cancelar">VOLTAR</a>
                        <button type="submit" class="btn-adicionar">FINALIZAR PEDIDO</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>