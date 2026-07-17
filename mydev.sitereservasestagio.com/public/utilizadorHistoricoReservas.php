<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SalaFácil — Minhas Reservas</title>
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
    <li><a href="utilizadorHistoricoReservas.php" class="active"><span class="icon">📋</span> Minhas Reservas</a></li>
    <li><a href="relatorios.php"><span class="icon">📊</span> Relatórios</a></li>
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
      <h1>Minhas Reservas</h1>
      <p>Histórico completo de todas as reservas que já fizeste, incluindo canceladas.</p>
    </div>
  </div>

  <div class="card panel" style="padding-bottom:20px;">
    <div class="historico-filtros">
      <button class="filtro-btn active" data-filtro="todas">Todas</button>
      <button class="filtro-btn" data-filtro="pendente">Pendentes</button>
      <button class="filtro-btn" data-filtro="confirmada">Confirmadas</button>
      <button class="filtro-btn" data-filtro="cancelada">Canceladas</button>
    </div>

    <div id="historico-lista" class="historico-lista">
      <div class="sem-dados">A carregar reservas…</div>
    </div>
  </div>
</main>

<div id="toast" class="toast"></div>

<script src="/assets/js/utilizadorHistoricoReservas.js"></script>
</body>
</html>