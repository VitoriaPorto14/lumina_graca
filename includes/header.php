<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="header-principal">
    <div class="header-container">
        
        <!-- Linha do Topo: Branding + Barra de Pesquisa no Canto -->
        <div class="header-topo">
            <div class="header-branding">
                <a href="/lumina_graca/index.php" class="logo-link">
                    <img src="/lumina_graca/css/imagens/logo.png" alt="Lumina & Graça" class="header-logo">
                    <div class="brand-texto">
                        <h1 class="header-titulo">Lumina & Graça</h1>
                        <p class="header-subtitulo">Moda Cristã & Beleza Consciente</p>
                    </div>
                </a>
            </div>

            <!-- Formulário de Pesquisa no Canto Direito -->
            <div class="header-acoes">
                <form action="/lumina_graca/produtos/m_produtos.php" method="GET" class="search-form">
                    <input type="text" name="busca" placeholder="Pesquisar produtos..." class="search-input" required>
                    <button type="submit" class="search-btn" title="Buscar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
            </div>
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