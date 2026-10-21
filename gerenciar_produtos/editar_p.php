<?php
// Inicia ou recupera a sessão do usuário
session_start();

// Verifica se o usuário NÃO está logado ou se o tipo de conta NÃO é 'admin'
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    // Exibe mensagem de acesso negado para usuários sem privilégio de administrador
    echo "Acesso negado. Esta página é restrita aos administradores.";
    echo "<br><a href='../index.php'>Voltar para o início</a>";
    // Interrompe o carregamento da página imediatamente
    exit;
}

// Importa o arquivo de funções do sistema e a conexão com a base de dados
require_once __DIR__ . '/../includes/functions.php';

// Captura o ID do produto passado pela URL via parâmetro GET
$id = $_GET['id'] ?? null;

// Se nenhum ID for informado na URL, redireciona para a página de estoque
if (!$id) {
    header("Location: /lumina_graca/estoque.php");
    exit();
}

// Busca os dados atuais do produto no banco de dados usando o ID recebido
$produto = consulta_p($conexao, $id);

// Se o produto não for encontrado no banco de dados, redireciona para a página de estoque
if (!$produto) {
    header("Location: /lumina_graca/estoque.php");
    exit();
}

// Verifica se o formulário foi enviado via método POST (ao clicar em 'SALVAR ALTERAÇÕES')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados alterados no formulário
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $descricao = $_POST['descricao'];
    // Mantém o nome da imagem antiga caso o usuário não selecione um novo arquivo
    $imagem_url = $produto['imagem_url'];

    // Verifica se uma nova imagem foi enviada pelo formulário
    if (!empty($_FILES['imagem_url']['name'])) {
        // Gera um nome único para a nova imagem com o timestamp atual + nome original do arquivo
        $imagem_url = time() . '_' . $_FILES['imagem_url']['name'];
        // Salva a nova imagem enviada na pasta 'uploads'
        move_uploaded_file($_FILES['imagem_url']['tmp_name'], '../uploads/' . $imagem_url);
    }

    // Executa a função que atualiza os dados do produto no banco PostgreSQL
    atualizar_p($conexao, $id, $nome, $descricao, $preco, $estoque, $imagem_url);

    // Redireciona o administrador de volta para a lista de estoque
    header("Location: /lumina_graca/estoque.php");
    // Encerra a execução do script
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
    <!-- Inclui o cabeçalho/menu de navegação superior da aplicação -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <div class="form-card">
            <div class="form-header">
                <h2>Editar Produto</h2>
                <!-- Exibe dinamicamente na tela o ID do produto que está sendo editado -->
                <p class="form-subtitulo">Altere as informações do produto <?php echo $id; ?></p>
                <small class="form-alerta">Campos obrigatórios</small>
            </div>

            <!-- Formulário com action vazio enviando para a própria URL atual de edição -->
            <form action="" method="POST" enctype="multipart/form-data" class="form-produto">
                <div class="form-grupo">
                    <label for="nome">Nome do Produto *</label>
                    <!-- Preenche o campo de texto com o nome atual salvo no banco, tratando caracteres especiais -->
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="preco">Preço (R$) *</label>
                        <!-- Exibe o preço atual gravado no banco -->
                        <input type="number" step="0.01" name="preco" id="preco" value="<?php echo $produto['preco']; ?>" required>
                    </div>

                    <div class="form-grupo">
                        <label for="estoque">Estoque (unidades) *</label>
                        <!-- Exibe a quantidade atual em estoque gravada no banco -->
                        <input type="number" name="estoque" id="estoque" value="<?php echo $produto['estoque']; ?>" required>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Imagem Atual</label>
                    <div class="preview-imagem-container">
                        <!-- Verifica se o produto tem uma imagem cadastrada no banco -->
                        <?php if (!empty($produto['imagem_url'])): ?>
                            <!-- Mostra a miniatura da imagem atual e o nome do arquivo -->
                            <img src="../uploads/<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="Imagem Atual" class="preview-thumb">
                            <span class="nome-imagem-atual"><?php echo htmlspecialchars($produto['imagem_url']); ?></span>
                        <?php else: ?>
                            <!-- Caso não exista imagem, exibe aviso informativo -->
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
                    <!-- Preenche a área de texto com a descrição atual do produto -->
                    <textarea name="descricao" id="descricao" rows="4"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                </div>

                <div class="form-acoes">
                    <a href="../estoque.php" class="btn-cancelar">CANCELAR</a>
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

    <!-- Inclui o rodapé reutilizável na parte inferior da página -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>