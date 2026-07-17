<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalaFácil — Relatórios</title>
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
            <li><a href="relatorios.php" class="active"><span class="icon">📊</span> Relatórios</a></li>
            <li><a href="utilizadores.php"><span class="icon">👥</span> Utilizadores</a></li>
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
                <h1>Relatórios</h1>
                <p>Estatísticas e gráficos sobre a utilização das salas.</p>
            </div>
            <div class="top-actions">
                <button class="btn" style="background:var(--bg-2); color:var(--text-1);">Exportar PDF</button>
            </div>
        </div>

        <div class="content-grid" style="grid-template-columns: 1fr 1fr 1fr; margin-top:20px;">
            <div class="card panel">
                <h3>Total de Reservas</h3>
                <h1 style="font-size: 3rem; margin: 10px 0; color: var(--primary);">142</h1>
                <p style="color: var(--text-2);">Este mês</p>
            </div>
            <div class="card panel">
                <h3>Sala mais utilizada</h3>
                <h1 style="font-size: 2rem; margin: 10px 0; color: var(--primary);">Auditório</h1>
                <p style="color: var(--text-2);">45h de ocupação</p>
            </div>
            <div class="card panel">
                <h3>Cancelamentos</h3>
                <h1 style="font-size: 3rem; margin: 10px 0; color: var(--primary);">12</h1>
                <p style="color: var(--text-2);">Este mês</p>
            </div>
        </div>
        
        <div class="card panel" style="margin-top: 20px;">
            <h3>Ocupação por Pólo</h3>
            <div style="height: 250px; display: flex; align-items: center; justify-content: center; border: 1px dashed var(--border); border-radius: var(--radius); margin-top: 15px; color: var(--text-2);">
              
            </div>
        </div>
    </main>

</body>

</html>
