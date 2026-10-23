<?php
session_start();

// Proteção da rota: apenas administradores podem excluir produtos
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: /lumina_graca/login.php");
    exit();
}

// Sobe um nível para carregar o functions.php
require_once __DIR__ . '/../includes/functions.php';

// Converte o ID para inteiro para evitar SQL Injection ou valores inválidos
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    // Apaga o produto no banco de dados
    apagar_p($conexao, $id);
}

// Redireciona obrigatoriamente para a página correta de gerenciamento (estoque.php)
header("Location: /lumina_graca/estoque.php");
exit();