<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalaFácil — Utilizadores</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">🗓️</div>
            SalaFácil
        </div>
        <ul class="nav">
            <li><a href="#"><span class="icon">🏠</span> Dashboard</a></li>
            <li><a href="index.php"><span class="icon">📅</span> Calendário</a></li>
            <li><a href="salas.php"><span class="icon">🚪</span> Salas</a></li>
            <li><a href="utilizadorHistoricoReservas.php"><span class="icon">📋</span> Minhas Reservas</a></li>
            <li><a href="relatorios.php"><span class="icon">📊</span> Relatórios</a></li>
            <li><a href="utilizadores.php" class="active"><span class="icon">👥</span> Utilizadores</a></li>
            <li><a href="definicoes.php"><span class="icon">⚙️</span> Definições</a></li>
        </ul>
        <div class="user-box">
            <div class="avatar">JS</div>
            <div>
                <div class="name">João Silva</div>
                <div class="role">Administrador</div>
            </div>
            <div class="chevron">›</div>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Utilizadores</h1>
                <p>Consulta e gere os utilizadores do sistema.</p>
            </div>
            <div class="top-actions">
                <button class="btn btn-primary" id="btn-novo-utilizador">+ Novo Utilizador</button>
            </div>
        </div>

        <div class="rooms-section" style="margin-top:0;">
            <div class="rooms-grid" id="utilizadores-grid">
                <div class="room-card">
                    <div class="room-body">
                        <div class="rname">A carregar…</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- OVERLAY -->
    <div id="overlay-utilizador" class="overlay"></div>

    <!-- PAINEL LATERAL: NOVO UTILIZADOR -->
    <aside id="painel-utilizador" class="painel-lateral painel-fechado">
        <div class="painel-header">
            <h2>Novo Utilizador</h2>
            <button id="btn-fechar-painel-utilizador" class="icon-btn-close" aria-label="Fechar">✕</button>
        </div>
        <form id="form-utilizador" class="painel-body">
            <div id="form-utilizador-erro" class="form-erro hidden"></div>

            <label class="campo">
                <span>Nome *</span>
                <input type="text" id="input-utilizador-nome" maxlength="100" placeholder="Ex: Maria Santos" required>
            </label>

            <label class="campo">
                <span>Email *</span>
                <input type="email" id="input-utilizador-email" maxlength="150"
                    placeholder="Ex: maria.santos@email.com" required>
            </label>

            <button type="submit" class="btn btn-primary btn-full" id="btn-submeter-utilizador">Criar Utilizador</button>
        </form>
    </aside>

    <div id="toast" class="toast"></div>

    <script src="/assets/js/utilizadores.js"></script>
</body>

</html>
