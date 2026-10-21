<?php
// Inclui o arquivo de conexão com o banco de dados PostgreSQL
require_once __DIR__ . '/../database/connect.php';

/* ==========================================================================
   MÓDULO 1: GERENCIAMENTO DE USUÁRIOS
   ========================================================================== */

// Cadastra um novo usuário no banco de dados (função CREATE do CRUD)
function adicionar_u($conexao, $nome, $email, $senha, $tipo)
{
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";

    try {
        // Prepara o comando SQL contra SQL Injection
        $stmt = $conexao->prepare($sql);

        // Vincula os valores informados aos parâmetros nomeados da consulta
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha);
        $stmt->bindParam(":tipo", $tipo);

        // Executa a inserção no banco
        $stmt->execute();
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
    }
}

// Remove um usuário pelo seu ID (função DELETE do CRUD)
function apagar_u($conexao, $id)
{
    $sql = "DELETE FROM usuarios WHERE id = :id";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Atualiza os dados de um usuário (função UPDATE do CRUD)
function atualizar_u($conexao, $id, $nome, $email, $senha = '', $tipo = 'cliente')
{
    try {
        // Se uma nova senha for informada, atualiza todos os campos incluindo a senha
        if (!empty($senha)) {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, tipo = :tipo WHERE id = :id";
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(":senha", $senha);
        } else {
            // Se a senha estiver em branco, atualiza apenas nome, e-mail e tipo sem alterar a senha cadastrada
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo WHERE id = :id";
            $stmt = $conexao->prepare($sql);
        }

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":tipo", $tipo);

        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

// Valida as credenciais digitadas na tela de login
function autenticar_usuario($conexao, $email, $senha) {
    // Busca o usuário pelo e-mail
    $sql = "SELECT id, nome, email, tipo, senha FROM usuarios WHERE email = :email";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compara se o usuário existe e se a senha confere
    if ($usuario && $usuario['senha'] === $senha) {
        // Remove a senha do array antes de retornar por motivos de segurança
        unset($usuario['senha']);
        return $usuario;
    }

    return false;
}


/* ==========================================================================
   MÓDULO 2: GERENCIAMENTO DE PRODUTOS
   ========================================================================== */

// 1. Cadastra um novo produto na loja (função CREATE do CRUD)
function adicionar_p($conexao, $nome, $descricao, $preco, $estoque, $imagem_url)
{
    $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, imagem_url) VALUES (:nome, :descricao, :preco, :estoque, :imagem_url)";

    try {
        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":estoque", $estoque);
        $stmt->bindParam(":imagem_url", $imagem_url);

        $stmt->execute();
        echo "<br>Produto cadastrado com sucesso!";
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
    }
}

// 2. Busca e retorna a lista de todos os produtos cadastrados (função READ do CRUD)
function listar_p($conexao)
{
    $sql = "SELECT id, nome, descricao, preco, estoque, imagem_url FROM produtos";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        // Retorna todas as linhas encontradas em formato de matriz/array associativo
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $produtos;
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
        return [];
    }
}

// 3. Consulta as informações de um único produto específico pelo seu ID
function consulta_p($conexao, $id)
{
    $sql = "SELECT id, nome, descricao, preco, estoque, imagem_url FROM produtos WHERE id = :id";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        // Retorna apenas um único registro
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
        return false;
    }
}

// 4. Edita e salva as novas informações de um produto existente (função UPDATE do CRUD)
function atualizar_p($conexao, $id, $nome, $descricao, $preco, $estoque, $imagem_url)
{
    $sql = "UPDATE produtos SET nome = :nome, descricao = :descricao, preco = :preco, estoque = :estoque, imagem_url = :imagem_url WHERE id = :id";

    try {
        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":estoque", $estoque);
        $stmt->bindParam(":imagem_url", $imagem_url);
        $stmt->bindParam(":id", $id);

        $stmt->execute();
        echo "<br>Produto atualizado com sucesso!";
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
    }
}

// Exclui um produto do banco de dados (função DELETE do CRUD)
function apagar_p($conexao, $id)
{
    $sql = "DELETE FROM produtos WHERE id = :id";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "<br>Erro ao excluir: " . $e->getMessage();
    }
}

// Diminui 1 unidade do estoque de um produto no momento de uma venda
function decrementar_estoque($conexao, $id)
{
    // Garante que o estoque só é baixado se for maior que zero
    $sql = "UPDATE produtos SET estoque = estoque - 1 WHERE id = :id AND estoque > 0";
    $stmt = $conexao->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

// Filtra e retorna apenas os produtos em destaque que possuem estoque disponível
function listar_produtos_por_ids($conexao, array $ids)
{
    $todos = listar_p($conexao);
    $destaques = [];

    if (!empty($todos)) {
        $produtos_por_id = [];
        foreach ($todos as $p) {
            $produtos_por_id[$p['id']] = $p;
        }

        foreach ($ids as $id) {
            if (isset($produtos_por_id[$id]) && $produtos_por_id[$id]['estoque'] > 0) {
                $destaques[] = $produtos_por_id[$id];
            }
        }
    }

    return $destaques;
}


/* ==========================================================================
   MÓDULO 3: VENDAS E PEDIDOS
   ========================================================================== */

// Registra uma nova compra, criando o registro em 'pedidos' e os itens em 'item_pedido'
function criar_pedido($conexao, $usuario_id, $carrinho) {
    if (empty($carrinho)) {
        return false;
    }

    // Passagem 1: Calcula a soma do valor total dos itens do carrinho
    $total = 0;
    foreach ($carrinho as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }

    // Passagem 2: Salva a cabaça/resumo do pedido na tabela 'pedidos' e recupera o ID gerado (RETURNING id)
    $sql_pedido = "INSERT INTO pedidos (usuario_id, total) VALUES (:usuario_id, :total) RETURNING id";
    $stmt = $conexao->prepare($sql_pedido);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':total' => $total
    ]);
    
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    $pedido_id = $pedido['id'];

    // Passagem 3: Insere cada item do carrinho na tabela relacional 'item_pedido'
    $sql_item = "INSERT INTO item_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)";
    $stmt_item = $conexao->prepare($sql_item);

    foreach ($carrinho as $produto_id => $item) {
        $stmt_item->execute([
            ':pedido_id' => $pedido_id,
            ':produto_id' => $produto_id,
            ':quantidade' => $item['quantidade'],
            ':preco_unitario' => $item['preco']
        ]);
    }

    return $pedido_id;
}

// Busca todos os pedidos cadastrados no sistema unindo com o nome e e-mail do cliente (JOIN)
function buscar_todos_pedidos($conexao) {
    $sql = "SELECT p.id AS pedido_id, p.data_pedido, p.total, 
                   u.id AS usuario_id, u.nome AS cliente_nome, u.email AS cliente_email
            FROM pedidos p
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.data_pedido DESC";
            
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* ==========================================================================
   MÓDULO 4: ATENDIMENTO / MENSAGENS (FALE CONOSCO)
   ========================================================================== */

// Grava no banco a mensagem enviada pelo formulário de contato
function salvar_mensagem_contato($conexao, $nome, $email, $assunto, $mensagem) {
    $sql = "INSERT INTO mensagens (nome, email, assunto, mensagem) 
            VALUES (:nome, :email, :assunto, :mensagem)";
    $stmt = $conexao->prepare($sql);
    return $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':assunto'  => $assunto,
        ':mensagem' => $mensagem
    ]);
}

// Retorna todas as mensagens registradas para exibição no painel administrativo
function buscar_mensagens_contato($conexao) {
    $sql = "SELECT * FROM mensagens ORDER BY data_envio DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}