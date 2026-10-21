<?php
// Inicia a sessão do usuário
session_start();

// Importa as funções globais e a conexão com o banco de dados ($conexao)
require_once __DIR__ . '/../includes/functions.php';

// Redireciona para o login caso o usuário não esteja logado
if (!isset($_SESSION['usuario_id'])) {
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ../login.php?redirect=' . urlencode('produtos/checkout.php' . $queryString));
    exit;
}

// Recebe as informações do produto via GET ou POST
$produto_id     = $_GET['id'] ?? $_POST['id'] ?? '';
$produto_nome   = $_GET['produto'] ?? $_POST['produto'] ?? '';
$produto_preco  = $_GET['preco'] ?? $_POST['preco'] ?? '';

// Inicializa variáveis da página
$pedido_finalizado = false;
$dados_pedido = [];
$pedido_id_gerado = null;
$numero_whatsapp = "5511999999999";

// Processa a gravação da compra quando enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_finalizado = true;

    // Tenta decrementar estoque se a função existir
    if (!empty($produto_id) && function_exists('decrementar_estoque')) {
        decrementar_estoque($conexao, $produto_id);
    }

    // Limpa dados de endereço
    $cep         = trim($_POST['cep'] ?? '');
    $rua         = trim($_POST['rua'] ?? '');
    $numero      = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro      = trim($_POST['bairro'] ?? '');
    $cidade      = trim($_POST['cidade'] ?? '');
    $estado      = trim($_POST['estado'] ?? '');

    $endereco_completo = "Rua {$rua}, Nº {$numero}" . ($complemento ? " ({$complemento})" : "") . " Bairro: {$bairro}, {$cidade}/{$estado} CEP: {$cep}";

    $dados_pedido = [
        'nome'              => trim($_POST['nome'] ?? ''),
        'endereco_completo' => $endereco_completo,
        'pagamento'         => trim($_POST['pagamento'] ?? ''),
        'produto'           => trim($_POST['produto'] ?? ''),
        'preco'             => trim($_POST['preco'] ?? '')
    ];

    // Gravação no banco de dados (PostgreSQL)
    if (isset($conexao) && $conexao instanceof PDO) {
        try {
            $usuario_id = $_SESSION['usuario_id'];
            $valor_total = (float)$dados_pedido['preco'];

            // 1. Insere o pedido principal
            $stmt_ped = $conexao->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (:usuario_id, :total, 'pendente') RETURNING id");
            $stmt_ped->execute([
                ':usuario_id' => $usuario_id,
                ':total'      => $valor_total
            ]);
            $res_ped = $stmt_ped->fetch(PDO::FETCH_ASSOC);
            $pedido_id_gerado = $res_ped['id'] ?? null;

            // 2. Insere o item do pedido
            if ($pedido_id_gerado && !empty($produto_id)) {
                $stmt_item = $conexao->prepare("INSERT INTO item_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (:pedido_id, :produto_id, 1, :preco)");
                $stmt_item->execute([
                    ':pedido_id'  => $pedido_id_gerado,
                    ':produto_id' => $produto_id,
                    ':preco'      => $valor_total
                ]);
            }
        } catch (PDOException $e) {
            // Previne travamento em caso de erro na query do PostgreSQL
            error_log("Erro no registro do pedido: " . $e->getMessage());
        }
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
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card checkout-card">
            <?php if ($pedido_finalizado): ?>
                <div class="checkout-sucesso">
                    <div class="sucesso-icone">✨</div>
                    <h2>Pedido Solicitado com Sucesso!</h2>
                    <p class="sucesso-subtexto">Obrigado pela preferência, <strong><?php echo htmlspecialchars($dados_pedido['nome']); ?></strong>.</p>

                    <?php if ($pedido_id_gerado): ?>
                        <p class="sucesso-instrucao">Número do seu Pedido: <strong>#<?php echo str_pad($pedido_id_gerado, 4, '0', STR_PAD_LEFT); ?></strong></p>
                    <?php endif; ?>

                    <p class="sucesso-instrucao">Acompanhe seu pedido pelo nosso WhatsApp para confirmar os detalhes de envio e pagamento.</p>

                    <?php
                    $num_pedido_fmt = $pedido_id_gerado ? str_pad($pedido_id_gerado, 4, '0', STR_PAD_LEFT) : 'NOVO';
                    $texto_wa = "Olá! Fiz um pedido no site Lumina & Graça (#" . $num_pedido_fmt . ") e gostaria de acompanhar:\n"
                        . "Produto: " . $dados_pedido['produto'] . "\n"
                        . "Cliente: " . $dados_pedido['nome'] . "\n"
                        . "Forma de Pagamento: " . $dados_pedido['pagamento'] . "\n"
                        . "Endereço: " . $dados_pedido['endereco_completo'];
                    $link_wa = "https://wa.me/" . $numero_whatsapp . "?text=" . urlencode($texto_wa);
                    ?>

                    <div class="sucesso-acoes">
                        <a href="<?php echo $link_wa; ?>" target="_blank" class="btn-whatsapp">
                            <span>💬 Acompanhar pelo WhatsApp</span>
                        </a>
                        <a href="/lumina_graca/produtos/m_produtos.php" class="btn-link">Voltar para a Vitrine</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-header">
                    <h2>Finalizar Pedido</h2>
                    <p class="form-subtitulo">Preencha seus dados para concluir a solicitação</p>
                    <small class="form-alerta">Campos obrigatórios</small>
                </div>

                <div class="resumo-produto-card">
                    <span class="resumo-label">Item Selecionado</span>
                    <h3 class="resumo-titulo"><?php echo htmlspecialchars($produto_nome); ?></h3>
                    <?php if (!empty($produto_preco)): ?>
                        <p class="resumo-preco">R$ <?php echo number_format((float)$produto_preco, 2, ',', '.'); ?></p>
                    <?php endif; ?>
                </div>

                <form action="checkout.php" method="POST" class="form-produto">
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

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>