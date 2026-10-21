<?php
// Importa o arquivo que contém as funções do sistema e a conexão com o banco de dados
require_once __DIR__ . '/includes/functions.php';

// Array com os IDs dos produtos selecionados para aparecerem na seção "Mais Vendidos"
$ids_destaque = [7, 8, 9, 10];

// Executa a função que busca no banco apenas os produtos dos IDs definidos no array acima
$produtos_destaque = listar_produtos_por_ids($conexao, $ids_destaque);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumina & Graça</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <!-- Verifica se o usuário veio do redirecionamento de exclusão de conta para exibir mensagem de confirmação -->
    <?php if (isset($_GET['conta_excluida'])): ?>
        <div class="container text-center margin-top">
            <p style="color: #a93226; font-weight: bold;">Sua conta foi excluída com sucesso.</p>
        </div>
    <?php endif; ?>

    <main class="container">
        <!-- Hero Section / Boas-vindas -->
        <section class="hero-section">
            <h1>Bem-vinda à Lumina & Graça!</h1>
            <p class="slogan"><em>"Com Cristo, tudo se ilumina."</em></p>

            <div class="divisor-dourado"></div>

            <p class="descricao-hero">
                Moda modesta e elegante para a mulher cristã se vestir com sofisticação, beleza e sem abrir mão de seus valores.
            </p>
        </section>

        <!-- Seção de Lançamento Exclusivo -->
        <section class="secao-lancamento">
            <h2>Lançamento Exclusivo - Vestido Lumina Graça</h2>

            <div class="card-lancamento">
                <div class="lancamento-imagem">
                    <img src="/lumina_graca/uploads/vestbranco.jpg" alt="Vestido Lumina Graça">
                </div>
                <div class="lancamento-info">
                    <p class="destaque-p">
                        Apresentamos o mais novo destaque da nossa coleção: um vestido longo ombro a ombro na cor branco pérola, desenhado para unir elegância, sofisticação e modéstia.
                    </p>
                    <ul class="lista-detalhes">
                        <li>Tecido estruturado com acabamento acetinado e brilho sutil</li>
                        <li>Modelagem ombro a ombro com capa elegante sobre o busto</li>
                        <li>Cintura marcada e saia fluida de caimento impecável</li>
                        <li>Design atemporal perfeito para momentos e celebrações especiais</li>
                    </ul>
                    <a href="/lumina_graca/produtos/m_produtos.php" class="btn-secundario">Ver os produtos →</a>
                </div>
            </div>
        </section>

        <!-- Seção de Mais Vendidos -->
        <section class="secao-produtos">
            <h2>Mais Vendidos</h2>

            <!-- Verifica se a busca de produtos em destaque não retornou nenhum item -->
            <?php if (empty($produtos_destaque)): ?>
                <p class="sem-produtos">Nenhum produto disponível no momento.</p>
            <?php else: ?>
                <div class="grid-produtos">
                    <!-- Percorre o array de produtos em destaque um por um -->
                    <?php foreach ($produtos_destaque as $p): ?>
                        <div class="card-produto">
                            <div class="img-container">
                                <!-- Verifica se o produto possui uma imagem cadastrada -->
                                <?php if (!empty($p['imagem_url'])): ?>
                                    <!-- Exibe a foto do produto tratando caracteres especiais contra XSS -->
                                    <img src="/lumina_graca/uploads/<?php echo htmlspecialchars($p['imagem_url']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>">
                                <?php else: ?>
                                    <!-- Exibe aviso padrão se não houver imagem -->
                                    <div class="sem-imagem">[Sem Imagem]</div>
                                <?php endif; ?>
                            </div>

                            <div class="produto-conteudo">
                                <!-- Exibe o nome do produto seguro contra XSS -->
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <!-- Exibe a descrição do produto -->
                                <p class="descricao-curta"><?php echo htmlspecialchars($p['descricao']); ?></p>
                                <!-- Formata o valor no padrão de moeda brasileira (R$ 0,00) -->
                                <p class="preco">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></p>

                                <form action="/lumina_graca/produtos/checkout.php" method="GET">
                                    <!-- Envia os dados do produto selecionado via GET para a página de checkout -->
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                                    <input type="hidden" name="produto" value="<?php echo htmlspecialchars($p['nome']); ?>">
                                    <input type="hidden" name="preco" value="<?php echo htmlspecialchars($p['preco']); ?>">
                                    <button type="submit" class="btn-principal">Solicitar Pedido</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="link-rodape-secao">
                <a href="/lumina_graca/produtos/m_produtos.php" class="btn-link">Ver todos os produtos →</a>
            </div>
        </section>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>