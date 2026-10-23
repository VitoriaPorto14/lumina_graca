<?php
session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    echo "Acesso negado. Esta página é restrita aos administradores.";
    echo "<br><a href='../index.php'>Voltar para o início</a>";
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = strip_tags(trim($_POST['nome'] ?? ''));
    $preco     = (float)($_POST['preco'] ?? 0);
    $estoque   = (int)($_POST['estoque'] ?? 0);
    $descricao = strip_tags(trim($_POST['descricao'] ?? ''));
    $imagem_url = '';

    if (isset($_FILES['imagem_url']) && $_FILES['imagem_url']['error'] === UPLOAD_ERR_OK) {
        
        // Como o adicionar_p.php está em /gerenciar_produtos/, subimos um nível (../) para chegar a /uploads/
        $pasta_uploads = realpath(__DIR__ . '/..') . '/uploads/';

        // Cria a pasta uploads na raiz do projeto se ela não existir
        if (!is_dir($pasta_uploads)) {
            mkdir($pasta_uploads, 0777, true);
        }

        // Gera o nome único do ficheiro
        $nome_original = basename($_FILES['imagem_url']['name']);
        $imagem_url    = time() . '_' . $nome_original;
        
        // Move o ficheiro para /lumina_graca/uploads/
        $caminho_final = $pasta_uploads . $imagem_url;
        move_uploaded_file($_FILES['imagem_url']['tmp_name'], $caminho_final);
    }

    // Salva no PostgreSQL (guarda apenas o nome da imagem, ex: "1729690000_foto.jpg")
    adicionar_p($conexao, $nome, $descricao, $preco, $estoque, $imagem_url);

    header("Location: /lumina_graca/estoque.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto</title>
    <link rel="stylesheet" href="/lumina_graca/css/style.css">
    <link rel="icon" type="image/png" href="/lumina_graca/css/imagens/logo_guia.png">
</head>

<body>
    <!-- Inclui o cabeçalho/menu topo reutilizável da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card">
            <div class="form-header">
                <h2>Adicionar Novo Produto</h2>
                <p class="form-subtitulo">Preencha os dados abaixo para cadastrar a peça no estoque</p>
                <small class="form-alerta">Campos obrigatórios</small>
            </div>

            <form action="adicionar_p.php" method="POST" enctype="multipart/form-data" class="form-produto">
                <div class="form-grupo">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" name="nome" id="nome" placeholder="ex: Vestido Midi Floral" required>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="preco">Preço (R$) *</label>
                        <input type="number" step="0.01" name="preco" id="preco" placeholder="ex: 159.90" required>
                    </div>

                    <div class="form-grupo">
                        <label for="estoque">Estoque (unidades) *</label>
                        <input type="number" name="estoque" id="estoque" placeholder="ex: 10" required>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="imagem_url">Imagem do Produto *</label>
                    <div class="upload-custom-container">
                        <input type="file" name="imagem_url" id="imagem_url" accept="image/*" required onchange="atualizarNomeArquivo(this)">
                        <label for="imagem_url" class="btn-upload-custom">
                            <span id="texto-upload">Clique para selecionar a foto do produto</span>
                        </label>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="4" placeholder="Detalhes de tecido, modelagem, caimento e acabamento..."></textarea>
                </div>

                <div class="form-acoes">
                    <a href="../estoque.php" class="btn-cancelar">CANCELAR</a>
                    <button type="submit" class="btn-adicionar">ADICIONAR PRODUTO</button>
                </div>
            </form>
        </div>
    </main>

    <script>
    function atualizarNomeArquivo(input) {
        const textoUpload = document.getElementById('texto-upload');
        if (input.files && input.files[0]) {
            textoUpload.textContent = 'SELECIONADO: ' + input.files[0].name;
        } else {
            textoUpload.textContent = 'Clique para selecionar a foto do produto';
        }
    }
    </script>

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>