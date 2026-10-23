<?php
session_start();

// Proteção da rota para administrador
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: /lumina_graca/login.php");
    exit();
}

require_once __DIR__ . '/includes/functions.php';

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
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <div class="tabela-header-acoes">
            <div class="titulo-pagina">
                <h1>Gerenciamento de Estoque</h1>
                <p class="subtitulo-pagina">Controle e listagem de produtos cadastrados</p>
            </div>

            <a href="/lumina_graca/gerenciar_produtos/adicionar_p.php" class="btn-novo-produto">+ Novo Produto</a>
        </div>

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
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td>
                                    <div class="produto-info-celula">
                                        <?php if (!empty($p['imagem_url'])): ?>
                                            <!-- Caminho absoluto no navegador /lumina_graca/uploads/ -->
                                            <img src="/lumina_graca/uploads/<?php echo htmlspecialchars($p['imagem_url']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>" class="thumb-produto">
                                        <?php else: ?>
                                            <div class="thumb-sem-img">[IMG]</div>
                                        <?php endif; ?>

                                        <div class="detalhes-item">
                                            <strong><?php echo htmlspecialchars($p['nome']); ?></strong>
                                            <small>ID: #<?php echo $p['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="coluna-descricao"><?php echo htmlspecialchars($p['descricao'] ?? ''); ?></td>
                                <td class="preco-tabela">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge-estoque <?php echo ($p['estoque'] > 0) ? 'em-estoque' : 'sem-estoque'; ?>">
                                        <?php echo $p['estoque']; ?> un
                                    </span>
                                </td>
                                <td>
                                    <div class="acoes-celula">
                                        <a href="/lumina_graca/gerenciar_produtos/editar_p.php?id=<?php echo $p['id']; ?>" class="btn-acao btn-editar" title="Editar Produto">✏️</a>
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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>