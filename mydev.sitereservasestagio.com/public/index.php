<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SalaFácil — Calendário de Reservas</title>
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
    <li><a href="index.php" class="active"><span class="icon">📅</span> Calendário</a></li>
    <li><a href="salas.php"><span class="icon">🚪</span> Salas</a></li>
    <li><a href="utilizadorHistoricoReservas.php"><span class="icon">📋</span> Minhas Reservas</a></li>
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
      <h1>Calendário de Reservas</h1>
      <p>Visualize a ocupação das salas e faça novas reservas.</p>
    </div>
    <div class="top-actions">
      <button class="btn btn-primary" id="btn-nova-reserva">+ Nova Reserva</button>
      <div class="icon-btn">🔔<span class="badge">2</span></div>
      <div class="icon-btn">📅</div>
    </div>
  </div>

  <div class="content-grid">
    <!-- Calendar -->
    <div class="card">
      <div class="cal-header">
        <div class="cal-nav">
          <button id="prev-month" aria-label="Anterior">‹</button>
          <button id="next-month" aria-label="Seguinte">›</button>
          <button id="today-btn" class="today-btn">Hoje</button>
          <span class="cal-title" id="cal-title">Junho 2025</span>
        </div>
        <div class="view-toggle" id="filtro-polo">
          <button class="active" data-polo="Todos">Todos os Pólos</button>
          <button data-polo="Restelo">Restelo</button>
          <button data-polo="Olivais">Olivais</button>
        </div>
        <div class="view-toggle">
          <button class="active" id="btn-view-mes">Mês</button>
          <button id="btn-view-semana">Semana</button>
          <button id="btn-view-dia">Dia</button>
        </div>
      </div>

      <table class="cal" id="cal-table">
        <thead id="cal-thead">
          <tr>
            <th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th>
          </tr>
        </thead>
        <tbody id="cal-body"></tbody>
      </table>
    </div>

    <!-- Right column -->
    <div class="right-col">
      <div class="card panel">
        <h3>Próximas Reservas</h3>
        <div id="proximas-reservas-lista">
          <div class="reserva-item"><div class="meta">A carregar…</div></div>
        </div>
      </div>

      <div class="card panel">
        <h3>Salas</h3>
        <div id="salas-lista">
          <div class="sala-item"><div class="sala-info"><div class="name">A carregar…</div></div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Salas disponíveis -->
  <div class="rooms-section">
    <h2>Salas Disponíveis</h2>
    <div class="rooms-grid" id="rooms-grid">
      <div class="room-card"><div class="room-body"><div class="rname">A carregar…</div></div></div>
    </div>
  </div>
</main>

<!-- OVERLAY (transparente — só para fechar clicando fora, não tapa o calendário) -->
<div id="overlay-reserva" class="overlay"></div>

<!-- PAINEL LATERAL: NOVA RESERVA -->
<aside id="painel-reserva" class="painel-lateral painel-fechado">
  <div class="painel-header">
    <h2>Nova Reserva</h2>
    <button id="btn-fechar-painel" class="icon-btn-close" aria-label="Fechar">✕</button>
  </div>
  <form id="form-reserva" class="painel-body">
    <div id="form-erro" class="form-erro hidden"></div>

    <label class="campo">
      <span>Sala *</span>
      <select id="input-sala" required>
        <option value="">A carregar salas…</option>
      </select>
    </label>

    <div class="campo-grid">
      <label class="campo">
        <span>Data *</span>
        <input type="date" id="input-data" required>
      </label>
      <label class="campo">
        <span>Nº participantes</span>
        <input type="number" id="input-participantes" min="1">
      </label>
    </div>

    <div class="campo-grid">
      <label class="campo">
        <span>Hora início *</span>
        <input type="time" id="input-hora-inicio" required>
      </label>
      <label class="campo">
        <span>Hora fim *</span>
        <input type="time" id="input-hora-fim" required>
      </label>
    </div>

    <label class="campo">
      <span>Ação / motivo</span>
      <input type="text" id="input-acao" maxlength="150" placeholder="Ex: Reunião de equipa">
    </label>

    <label class="campo">
      <span>Observações</span>
      <textarea id="input-observacoes" rows="3" placeholder="Opcional"></textarea>
    </label>

    <div class="campo">
      <span>Equipamentos</span>
      <div id="equipamentos-container" class="equipamentos-grid">
        <div class="meta">A carregar equipamentos…</div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-full" id="btn-submeter-reserva">Criar Reserva</button>
  </form>
</aside>

<div id="toast" class="toast"></div>

<script src="/assets/js/script.js"></script>
</body>
</html>