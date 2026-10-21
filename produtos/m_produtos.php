<?php
// Importa o arquivo com as funções do sistema e a conexão com o banco de dados
require_once __DIR__ . '/../includes/functions.php';

// Inicia ou recupera a sessão do usuário no navegador
session_start();

// Verifica se o usuário NÃO está logado
if (!isset($_SESSION['usuario_id'])) {
    // Preserva os parâmetros de busca da URL (caso existam)
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    // Redireciona para o login salvando a página de destino (checkout.php) para retornar após logar
    header('Location: ../login.php?redirect=' . urlencode('produtos/checkout.php' . $queryString));
    // Encerra a execução do script
    exit;
}

// Captura o termo digitado na barra de pesquisa (caso enviado via GET)
$busca = $_GET['busca'] ?? '';

// Se o usuário fez uma pesquisa, filtra os produtos no PostgreSQL por Nome ou Descrição
if (!empty($busca)) {
    $sql = "SELECT * FROM produtos WHERE nome ILIKE :busca OR descricao ILIKE :busca ORDER BY id DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':busca' => '%' . $busca . '%']);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Se não houver termo de busca, traz todos os produtos cadastrados normalmente
    $produtos = listar_p($conexao);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Lumina & Graça</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="header-vitrine">
            <h1>Nossa Coleção</h1>
            <p class="subtitulo-vitrine">Peças exclusivas desenhadas para vestir com elegância e modéstia</p>
            
            <!-- Exibe feedback do termo pesquisado caso exista uma busca ativa -->
            <?php if (!empty($busca)): ?>
                <p class="resultado-busca" style="margin-top: 10px; color: #8c6d3b;">
                    Exibindo resultados para: <strong>"<?php echo htmlspecialchars($busca); ?>"</strong>
                    <a href="m_produtos.php" style="margin-left: 8px; font-size: 0.85rem; color: #c5a880; text-decoration: underline;">(limpar pesquisa)</a>
                </p>
            <?php endif; ?>

            <div class="divisor-dourado"></div>
        </div>

        <!-- Verifica se a lista de produtos retornada do banco está vazia -->
        <?php if (empty($produtos)): ?>
            <div class="card-vazio">
                <?php if (!empty($busca)): ?>
                    <p>Nenhum produto foi encontrado para a pesquisa <strong>"<?php echo htmlspecialchars($busca); ?>"</strong>.</p>
                <?php else: ?>
                    <p>Nenhum produto disponível para venda no momento.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid-produtos">
                <!-- Percorre o array de produtos vindos do banco de dados um por um -->
                <?php foreach ($produtos as $p): ?>
                    <!-- Filtra no HTML exibindo apenas os produtos que possuem estoque disponível (> 0) -->
                    <?php if ($p['estoque'] > 0): ?>
                        <div class="card-produto">
                            <div class="img-container">
                                <!-- Verifica se o produto tem uma imagem cadastrada -->
                                <?php if (!empty($p['imagem_url'])): ?>
                                    <!-- Exibe a foto do produto tratando caracteres especiais para segurança -->
                                    <img src="/lumina_graca/uploads/<?php echo htmlspecialchars($p['imagem_url']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>">
                                <?php else: ?>
                                    <!-- Exibe aviso padrão se o produto não tiver imagem cadastrada -->
                                    <div class="sem-imagem">[Sem Imagem]</div>
                                <?php endif; ?>
                            </div>

                            <div class="produto-conteudo">
                                <!-- Exibe o nome do produto seguro contra XSS -->
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <!-- Exibe a descrição do produto -->
                                <p class="descricao-curta"><?php echo htmlspecialchars($p['descricao']); ?></p>
                                <!-- Formata o preço no padrão de moeda brasileira (R$ 0,00) -->
                                <p class="preco">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></p>

                                <form action="checkout.php" method="GET">
                                    <!-- Passa o ID, nome e preço do produto selecionado de forma oculta para o checkout -->
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                                    <input type="hidden" name="produto" value="<?php echo htmlspecialchars($p['nome']); ?>">
                                    <input type="hidden" name="preco" value="<?php echo htmlspecialchars($p['preco']); ?>">
                                    <button type="submit" class="btn-principal">Solicitar Pedido</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="link-voltar-container">
            <a href="/lumina_graca/index.php" class="btn-link">← Voltar ao Início</a>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>