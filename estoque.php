<?php
// Importa o arquivo com as funções do sistema e a conexão com o banco de dados
require_once __DIR__ . '/includes/functions.php';

// Busca a lista de todos os produtos cadastrados no banco usando a função do functions.php
$produtos = listar_p($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <div class="tabela-header-acoes">
            <div class="titulo-pagina">
                <h1>Gerenciamento de Estoque</h1>
                <p class="subtitulo-pagina">Controle e listagem de produtos cadastrados</p>
            </div>

            <!-- Botão + Novo Produto -->
            <a href="/lumina_graca/gerenciar_produtos/adicionar_p.php" class="btn-novo-produto">+ Novo Produto</a>
        </div>

        <!-- Verifica se a lista de produtos retornada do banco está vazia -->
        <?php if (empty($produtos)): ?>
            <div class="card-vazio">
                <p>Nenhum produto cadastrado no momento.</p>
            </div>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table class="tabela-estoque">
                    <thead>
                        <tr>
                            <th>PRODUTO</th>
                            <th>DESCRIÇÃO</th>
                            <th>PREÇO</th>
                            <th>ESTOQUE</th>
                            <th class="texto-centro">AÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Percorre o array de produtos vindo do banco de dados linha por linha -->
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td>
                                    <div class="produto-info-celula">
                                        <!-- Verifica se o produto tem imagem cadastrada -->
                                        <?php if (!empty($p['imagem_url'])): ?>
                                            <!-- Exibe a miniatura da imagem tratando caracteres especiais contra XSS -->
                                            <img src="uploads/<?php echo htmlspecialchars($p['imagem_url']); ?>" alt="Imagem" class="thumb-produto">
                                        <?php else: ?>
                                            <!-- Exibe caixa padrão caso não haja imagem -->
                                            <div class="thumb-sem-img">[IMG]</div>
                                        <?php endif; ?>

                                        <div class="detalhes-item">
                                            <!-- Exibe o nome do produto -->
                                            <strong><?php echo htmlspecialchars($p['nome']); ?></strong>
                                            <!-- Exibe o ID único do produto -->
                                            <small>ID: #<?php echo $p['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <!-- Exibe a descrição do produto -->
                                <td class="coluna-descricao"><?php echo htmlspecialchars($p['descricao']); ?></td>
                                <!-- Formata o preço no padrão de moeda brasileira (R$ 0,00) -->
                                <td class="preco-tabela">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                                <td>
                                    <!-- Aplica classe CSS dinamica conforme a quantidade em estoque (>0 verde, <=0 vermelho) -->
                                    <span class="badge-estoque <?php echo ($p['estoque'] > 0) ? 'em-estoque' : 'sem-estoque'; ?>">
                                        <?php echo $p['estoque']; ?> un
                                    </span>
                                </td>
                                <td>
                                    <div class="acoes-celula">
                                        <!-- Link para editar o produto enviando seu ID via GET -->
                                        <a href="/lumina_graca/gerenciar_produtos/editar_p.php?id=<?php echo $p['id']; ?>" class="btn-acao btn-editar" title="Editar Produto">✏️</a>
                                        <!-- Link para excluir o produto enviando seu ID via GET com confirmação em JS -->
                                        <a href="/lumina_graca/gerenciar_produtos/excluir_p.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Deseja excluir este produto?')" class="btn-acao btn-excluir" title="Excluir Produto">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="link-voltar-container">
            <a href="/lumina_graca/index.php" class="btn-link">← Voltar ao Início</a>
        </div>
    </main>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>