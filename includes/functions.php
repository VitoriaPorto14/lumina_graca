<?php
require_once __DIR__ . '/../database/connect.php';

function adicionar_u($conexao, $nome, $email, $senha, $tipo)
{

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";

    try {
        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha);
        $stmt->bindParam(":tipo", $tipo);

        $stmt->execute();
        // echo "<br>Usuário cadastrado com sucesso!";
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
    }
}

function apagar_u($conexao, $id)
{
    $sql = "DELETE FROM usuarios WHERE id = :id";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        //echo "Usuário deletado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

function atualizar_u($conexao, $id, $nome, $email, $senha = '', $tipo = 'cliente')
{
    try {
        if (!empty($senha)) {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, tipo = :tipo WHERE id = :id";
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(":senha", $senha);
        } else {
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


// 1. Função para Adicionar Produto
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

// 2. Função para Listar Todos os Produtos
function listar_p($conexao)
{
    $sql = "SELECT id, nome, descricao, preco, estoque, imagem_url FROM produtos";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->execute();

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $produtos;
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
        return [];
    }
}

// 3. Função para Consultar um Único Produto pelo ID (necessário para a página de edição)
function consulta_p($conexao, $id)
{
    $sql = "SELECT id, nome, descricao, preco, estoque, imagem_url FROM produtos WHERE id = :id";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<br>Erro: " . $e->getMessage();
        return false;
    }
}

// 4. Função para Atualizar/Editar Produto
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

// Decrementa em 1 unidade o estoque do produto
function decrementar_estoque($conexao, $id)
{
    $sql = "UPDATE produtos SET estoque = estoque - 1 WHERE id = :id AND estoque > 0";
    $stmt = $conexao->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

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

function autenticar_usuario($conexao, $email, $senha) {
    $sql = "SELECT id, nome, email, tipo, senha FROM usuarios WHERE email = :email";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $usuario['senha'] === $senha) {
        unset($usuario['senha']);
        return $usuario;
    }

    return false;
}

// Salva um novo pedido e seus itens no banco de dados
function criar_pedido($conexao, $usuario_id, $carrinho) {
    if (empty($carrinho)) {
        return false;
    }

    // Calcula o total do pedido
    $total = 0;
    foreach ($carrinho as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }

    // Insere na tabela 'pedidos'
    $sql_pedido = "INSERT INTO pedidos (usuario_id, total) VALUES (:usuario_id, :total) RETURNING id";
    $stmt = $conexao->prepare($sql_pedido);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':total' => $total
    ]);
    
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    $pedido_id = $pedido['id'];

    // Insere cada produto na tabela 'item_pedido'
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

// Busca todos os pedidos cadastrados (para a área do Admin)
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

// Salva a mensagem enviada pelo cliente no banco de dados
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

// Busca todas as mensagens enviadas para a área do admin
function buscar_mensagens_contato($conexao) {
    $sql = "SELECT * FROM mensagens ORDER BY data_envio DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}