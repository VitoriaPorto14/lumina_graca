<?php
// Inicia ou recupera a sessão ativa do usuário no navegador
session_start();

// Limpa todas as variáveis salvas na sessão ($_SESSION)
session_unset();

// Destrói completamente a sessão do usuário no servidor
session_destroy();

// Redireciona o usuário de volta para a tela de login
header('Location: login.php');

// Interrompe a execução do script imediatamente após o redirecionamento
exit;
