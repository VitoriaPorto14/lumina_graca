<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="header-principal">
    <div class="header-container">

        <!-- Branding / Logo com Texto -->
        <div class="header-branding">
            <a href="/lumina_graca/index.php" class="logo-link">
                <img src="/lumina_graca/css/imagens/logo.png" alt="Lumina & Graça" class="header-logo">
                <div class="brand-texto">
                    <h1 class="header-titulo">Lumina & Graça</h1>
                    <p class="header-subtitulo">Moda Cristã & Beleza Consciente</p>
                </div>
            </a>
        </div>

        <!-- Menu de Navegação -->
        <nav class="header-nav">
            <ul class="nav-lista">
                <li><a href="/lumina_graca/index.php">Início</a></li>
                <li><a href="/lumina_graca/produtos/m_produtos.php">Produtos</a></li>
                <li><a href="/lumina_graca/conta.php">Conta</a></li>
                <li><a href="/lumina_graca/atendimento/contato.php">Atendimento</a></li>

                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                    <li><a href="/lumina_graca/estoque.php" class="nav-admin">Estoque</a></li>
                    <li><a href="/lumina_graca/mensagens.php" class="nav-admin">Mensagens</a></li>
                    <li><a href="/lumina_graca/pedidos.php" class="nav-admin">Pedidos</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>