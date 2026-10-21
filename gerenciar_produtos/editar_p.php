<?php
session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    echo "Acesso negado. Esta página é restrita aos administradores.";
    echo "<br><a href='../index.php'>Voltar para o início</a>";
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: /lumina_graca/produto.php");
    exit();
}

// Busca os dados atuais do produto no banco via função
$produto = consulta_p($conexao, $id);

if (!$produto) {
    header("Location: /lumina_graca/produto.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $descricao = $_POST['descricao'];
    $imagem_url = $produto['imagem_url']; // Mantém a imagem atual caso não faça upload de uma nova

    if (!empty($_FILES['imagem_url']['name'])) {
        $imagem_url = time() . '_' . $_FILES['imagem_url']['name'];
        move_uploaded_file($_FILES['imagem_url']['tmp_name'], '../uploads/' . $imagem_url);
    }

    // Chama a função de atualização definida no functions.php
    atualizar_p($conexao, $id, $nome, $descricao, $preco, $estoque, $imagem_url);

    header("Location: /lumina_graca/produto.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card">
            <div class="form-header">
                <h2>Editar Produto</h2>
                <p class="form-subtitulo">Altere as informações do produto <?php echo $id; ?></p>
                <small class="form-alerta">* Campos obrigatórios</small>
            </div>

            <form action="/lumina_graca/app_p/editar_p.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="form-produto">
                <div class="form-grupo">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="preco">Preço (R$) *</label>
                        <input type="number" step="0.01" name="preco" id="preco" value="<?php echo $produto['preco']; ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label for="estoque">Estoque (unidades) *</label>
                        <input type="number" name="estoque" id="estoque" value="<?php echo $produto['estoque']; ?>" required>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Imagem Atual</label>
                    <div class="preview-imagem-container">
                        <?php if (!empty($produto['imagem_url'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="Imagem Atual" class="preview-thumb">
                            <span class="nome-imagem-atual"><?php echo htmlspecialchars($produto['imagem_url']); ?></span>
                        <?php else: ?>
                            <p class="sem-imagem-texto">Nenhuma imagem cadastrada</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="imagem_url">Trocar Imagem (Opcional)</label>
                    <div class="upload-custom-container">
                        <input type="file" name="imagem_url" id="imagem_url" accept="image/*" onchange="atualizarNomeArquivo(this)">
                        <label for="imagem_url" class="btn-upload-custom">
                            <span id="texto-upload">Clique para alterar a foto do produto</span>
                        </label>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="4"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                </div>

                <div class="form-acoes">
                    <a href="/lumina_graca/produto.php" class="btn-cancelar">CANCELAR</a>
                    <button type="submit" class="btn-adicionar">SALVAR ALTERAÇÕES</button>
                </div>
        </form>
        </div>
    </main>

    <script>
        function atualizarNomeArquivo(input) {
            const textoUpload = document.getElementById('texto-upload');
            if (input.files && input.files[0]) {
                textoUpload.textContent = 'NOVA IMAGEM: ' + input.files[0].name;
            } else {
                textoUpload.textContent = 'Clique para alterar a foto do produto';
            }
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>