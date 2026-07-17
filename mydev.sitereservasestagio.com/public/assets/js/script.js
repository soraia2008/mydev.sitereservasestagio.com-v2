/* =========================================================
   Sala — calendário + reservas ligados à API real
   ========================================================= */

// ---- CONFIGURAÇÃO ----
const API_BASE = '/api';

// Ainda não existe login. Fixa-se o utilizador atual até isso ser implementado.
const CURRENT_USER_ID = 1;

const MESES = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];
const MESES_ABREV = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
const DIAS_SEMANA = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
const CORES = ['blue', 'green', 'purple', 'orange'];

const REAL_TODAY = new Date();

// ---- ESTADO ----
let viewYear = REAL_TODAY.getFullYear();
let viewMonth = REAL_TODAY.getMonth(); // 0-11
let currentDate = new Date(REAL_TODAY); // ponteiro usado nas vistas semana/dia
let viewMode = 'month'; // 'month' | 'week' | 'day'
let currentPolo = 'Todos'; // 'Todos' | 'Restelo' | 'Olivais'

let salas = [];           // [{id, nome, tipo, capacidade, ocupada, ativa}]
let salasPorId = {};
let equipamentosAgrupados = {}; // { "Projeção": [{id,nome,...}], ... }
let reservas = [];        // todas as reservas (todas as salas)

// ---- HELPERS ----
function pad(n) { return String(n).padStart(2, '0'); }

function dateKey(year, month, day) {
  return `${year}-${pad(month + 1)}-${pad(day)}`;
}

function dateKeyFromDate(d) {
  return dateKey(d.getFullYear(), d.getMonth(), d.getDate());
}

function corPorSala(idSala) {
  return CORES[idSala % CORES.length];
}

async function apiGet(caminho) {
  const resp = await fetch(`${API_BASE}${caminho}`);
  return resp.json();
}

async function apiPost(caminho, corpo) {
  const resp = await fetch(`${API_BASE}${caminho}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(corpo),
  });
  const dados = await resp.json();
  return { status: resp.status, dados };
}

async function apiPatch(caminho, corpo) {
  const resp = await fetch(`${API_BASE}${caminho}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(corpo),
  });
  const dados = await resp.json();
  return { status: resp.status, dados };
}

function mostrarToast(mensagem, tipo = 'sucesso') {
  const toast = document.getElementById('toast');
  toast.textContent = mensagem;
  toast.classList.toggle('erro', tipo === 'erro');
  toast.classList.add('mostrar');
  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(() => toast.classList.remove('mostrar'), 3500);
}

function horaSemSegundos(hora) {
  return (hora || '').slice(0, 5);
}

/* =========================================================
   CARREGAMENTO DE DADOS
   ========================================================= */
async function carregarDadosIniciais() {
  try {
    const [respSalas, respEquip, respReservas] = await Promise.all([
      apiGet('/salas'),
      apiGet('/equipamentos/agrupados'),
      apiGet('/reservas'),
    ]);

    salas = respSalas.sucesso ? respSalas.dados : [];
    salasPorId = Object.fromEntries(salas.map(s => [s.id, s]));
    equipamentosAgrupados = respEquip.sucesso ? respEquip.dados : {};
    reservas = respReservas.sucesso ? respReservas.dados : [];

    preencherSelectSalas();
    preencherEquipamentos();
    renderTudo();
  } catch (erro) {
    console.error(erro);
    mostrarToast('Não foi possível ligar à API. Verifica se o servidor está a correr.', 'erro');
  }
}

async function recarregarReservas() {
  const resp = await apiGet('/reservas');
  reservas = resp.sucesso ? resp.dados : [];
}

/* =========================================================
   RENDER GERAL
   ========================================================= */
function renderTudo() {
  renderCalendario();
  renderProximasReservas();
  renderSalasLista();
  renderRoomsGrid();
}

/* ---------- Calendário: despacho por vista ---------- */
function renderCalendario() {
  const tabela = document.getElementById('cal-table');
  tabela.classList.toggle('modo-dia', viewMode === 'day');

  if (viewMode === 'month') renderMes();
  else if (viewMode === 'week') renderSemana();
  else renderDia();
}

function reservasAtivasPorData(key) {
  return reservas.filter(r => r.data_reserva === key && r.estado !== 'cancelada');
}

function reservaVisivelPolo(r) {
  if (currentPolo === 'Todos') return true;
  const sala = salasPorId[r.id_sala];
  return sala && sala.localizacao === currentPolo;
}

function salasFiltradas() {
  if (currentPolo === 'Todos') return salas;
  return salas.filter(s => s.localizacao === currentPolo);
}

/* ---------- Vista Mês ---------- */
function renderMes() {
  const tbody = document.getElementById('cal-body');
  const title = document.getElementById('cal-title');
  title.textContent = `${MESES[viewMonth]} ${viewYear}`;

  const firstOfMonth = new Date(viewYear, viewMonth, 1);
  const startWeekday = firstOfMonth.getDay();
  const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
  const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

  let html = '';
  let dayCounter = 1;
  let nextMonthCounter = 1;

  for (let row = 0; row < 6; row++) {
    if (row === 5 && dayCounter > daysInMonth) break;

    html += '<tr>';
    for (let col = 0; col < 7; col++) {
      const cellIndex = row * 7 + col;
      let cellContent = '';
      let cls = '';

      if (cellIndex < startWeekday) {
        const d = daysInPrevMonth - startWeekday + cellIndex + 1;
        cls = 'muted';
        cellContent = `<div class="daynum">${d}</div>`;
      } else if (dayCounter > daysInMonth) {
        cls = 'muted';
        cellContent = `<div class="daynum">${nextMonthCounter}</div>`;
        nextMonthCounter++;
      } else {
        const d = dayCounter;
        const isToday = d === REAL_TODAY.getDate() && viewMonth === REAL_TODAY.getMonth() && viewYear === REAL_TODAY.getFullYear();
        cls = isToday ? 'today' : '';
        cellContent = `<div class="daynum" data-dia="${d}" style="cursor:pointer">${d}</div>`;

        const key = dateKey(viewYear, viewMonth, d);
        reservasAtivasPorData(key).filter(reservaVisivelPolo).forEach(r => {
          const sala = salasPorId[r.id_sala];
          const nomeSala = sala ? sala.nome : `Sala #${r.id_sala}`;
          const pendenteClass = r.estado === 'pendente' ? ' esbotada' : '';
          cellContent += `<div class="event ${corPorSala(r.id_sala)}${pendenteClass}">${horaSemSegundos(r.hora_inicio)} - ${horaSemSegundos(r.hora_fim)}<br>${nomeSala}</div>`;
        });
        dayCounter++;
      }
      html += `<td class="${cls}">${cellContent}</td>`;
    }
    html += '</tr>';
  }

  tbody.innerHTML = html;

  tbody.querySelectorAll('.daynum[data-dia]').forEach(el => {
    el.addEventListener('click', () => {
      currentDate = new Date(viewYear, viewMonth, parseInt(el.dataset.dia, 10));
      setViewMode('day');
    });
  });
}

/* ---------- Vista Semana ---------- */
function inicioDaSemana(data) {
  const d = new Date(data);
  d.setDate(d.getDate() - d.getDay()); // recua até Domingo
  return d;
}

function renderSemana() {
  const tbody = document.getElementById('cal-body');
  const title = document.getElementById('cal-title');

  const inicio = inicioDaSemana(currentDate);
  const dias = Array.from({ length: 7 }, (_, i) => {
    const d = new Date(inicio);
    d.setDate(inicio.getDate() + i);
    return d;
  });

  const fim = dias[6];
  const mesmoMes = inicio.getMonth() === fim.getMonth();
  title.textContent = mesmoMes
    ? `${inicio.getDate()} - ${fim.getDate()} ${MESES[fim.getMonth()]} ${fim.getFullYear()}`
    : `${inicio.getDate()} ${MESES_ABREV[inicio.getMonth()]} - ${fim.getDate()} ${MESES_ABREV[fim.getMonth()]} ${fim.getFullYear()}`;

  let html = '<tr>';
  dias.forEach(d => {
    const isToday = dateKeyFromDate(d) === dateKeyFromDate(REAL_TODAY);
    const key = dateKeyFromDate(d);

    let cellContent = `<div class="daynum" data-key="${key}" style="cursor:pointer">${d.getDate()}</div>`;
    reservasAtivasPorData(key)
      .filter(reservaVisivelPolo)
      .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio))
      .forEach(r => {
        const sala = salasPorId[r.id_sala];
        const nomeSala = sala ? sala.nome : `Sala #${r.id_sala}`;
        const pendenteClass = r.estado === 'pendente' ? ' esbotada' : '';
        cellContent += `<div class="event ${corPorSala(r.id_sala)}${pendenteClass}">${horaSemSegundos(r.hora_inicio)} - ${horaSemSegundos(r.hora_fim)}<br>${nomeSala}</div>`;
      });

    html += `<td class="semana-col ${isToday ? 'today' : ''}">${cellContent}</td>`;
  });
  html += '</tr>';

  tbody.innerHTML = html;

  tbody.querySelectorAll('.daynum[data-key]').forEach(el => {
    el.addEventListener('click', () => {
      const [y, m, d] = el.dataset.key.split('-').map(Number);
      currentDate = new Date(y, m - 1, d);
      setViewMode('day');
    });
  });
}

/* ---------- Vista Dia ---------- */
function renderDia() {
  const tbody = document.getElementById('cal-body');
  const title = document.getElementById('cal-title');

  const DIAS_SEMANA_COMPLETO = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
  const diaSemana = DIAS_SEMANA_COMPLETO[currentDate.getDay()];
  title.textContent = `${diaSemana}, ${currentDate.getDate()} de ${MESES[currentDate.getMonth()]} de ${currentDate.getFullYear()}`;

  const key = dateKeyFromDate(currentDate);
  const doDia = reservas
    .filter(r => r.data_reserva === key)
    .filter(reservaVisivelPolo)
    .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));

  let corpo = '';
  if (doDia.length === 0) {
    corpo = '<div class="sem-reservas">Sem reservas para este dia.</div>';
  } else {
    doDia.forEach(r => {
      const sala = salasPorId[r.id_sala];
      const nomeSala = sala ? sala.nome : `Sala #${r.id_sala}`;
      corpo += `
        <div class="day-evento">
          <div class="hora">${horaSemSegundos(r.hora_inicio)} – ${horaSemSegundos(r.hora_fim)}</div>
          <div class="info">
            <div class="titulo">${r.acao || 'Reserva'} · ${nomeSala}</div>
            <div class="sub">${r.n_participantes ? r.n_participantes + ' participantes' : ''}${r.observacoes ? ' · ' + r.observacoes : ''}</div>
          </div>
          <div class="estado-pill estado-${r.estado}">${r.estado}</div>
        </div>`;
    });
  }

  tbody.innerHTML = `<tr><td colspan="7"><div class="day-agenda">${corpo}</div></td></tr>`;
}

/* =========================================================
   NAVEGAÇÃO (prev / next / hoje) — depende da vista atual
   ========================================================= */
function setViewMode(modo) {
  viewMode = modo;
  document.getElementById('btn-view-mes').classList.toggle('active', modo === 'month');
  document.getElementById('btn-view-semana').classList.toggle('active', modo === 'week');
  document.getElementById('btn-view-dia').classList.toggle('active', modo === 'day');
  renderCalendario();
}

function irParaAnterior() {
  if (viewMode === 'month') {
    viewMonth--;
    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
  } else if (viewMode === 'week') {
    currentDate.setDate(currentDate.getDate() - 7);
  } else {
    currentDate.setDate(currentDate.getDate() - 1);
  }
  renderCalendario();
}

function irParaSeguinte() {
  if (viewMode === 'month') {
    viewMonth++;
    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
  } else if (viewMode === 'week') {
    currentDate.setDate(currentDate.getDate() + 7);
  } else {
    currentDate.setDate(currentDate.getDate() + 1);
  }
  renderCalendario();
}

function irParaHoje() {
  viewYear = REAL_TODAY.getFullYear();
  viewMonth = REAL_TODAY.getMonth();
  currentDate = new Date(REAL_TODAY);
  renderCalendario();
}

/* =========================================================
   PAINÉIS LATERAIS (right column) E ROOMS GRID
   ========================================================= */
function agoraStr() {
  const n = new Date();
  return `${pad(n.getHours())}:${pad(n.getMinutes())}:${pad(n.getSeconds())}`;
}

function salaOcupadaAgora(idSala) {
  const hoje = dateKeyFromDate(REAL_TODAY);
  const agora = agoraStr();
  return reservas.some(r =>
    r.id_sala === idSala &&
    r.data_reserva === hoje &&
    r.estado !== 'cancelada' &&
    r.hora_inicio <= agora && r.hora_fim > agora
  );
}

function renderProximasReservas() {
  const container = document.getElementById('proximas-reservas-lista');
  const hoje = dateKeyFromDate(REAL_TODAY);
  const agora = agoraStr();

  const proximas = reservas
    .filter(r => r.estado !== 'cancelada' && (r.data_reserva > hoje || (r.data_reserva === hoje && r.hora_fim >= agora)))
    .filter(reservaVisivelPolo)
    .sort((a, b) => (a.data_reserva + a.hora_inicio).localeCompare(b.data_reserva + b.hora_inicio))
    .slice(0, 4);

  if (proximas.length === 0) {
    container.innerHTML = '<div class="reserva-item"><div class="meta">Sem reservas agendadas.</div></div>';
    return;
  }

  container.innerHTML = proximas.map(r => {
    const sala = salasPorId[r.id_sala];
    const nomeSala = sala ? sala.nome : `Sala #${r.id_sala}`;
    const rotuloData = r.data_reserva === hoje ? 'Hoje' : r.data_reserva.split('-').reverse().slice(0, 2).join('/');
    return `
      <div class="reserva-item">
        <span class="dot ${corPorSala(r.id_sala)}"></span>
        <div>
          <div class="title">${nomeSala} – ${r.acao || 'Reserva'}</div>
          <div class="meta">${rotuloData}, ${horaSemSegundos(r.hora_inicio)} – ${horaSemSegundos(r.hora_fim)}</div>
        </div>
      </div>`;
  }).join('');
}

function renderSalasLista() {
  const container = document.getElementById('salas-lista');
  const salasParaMostrar = salasFiltradas();
  
  if (salasParaMostrar.length === 0) {
    container.innerHTML = '<div class="sala-item"><div class="sala-info"><div class="name">Sem salas.</div></div></div>';
    return;
  }
  container.innerHTML = salasParaMostrar.map(s => {
    const ocupada = salaOcupadaAgora(s.id);
    return `
      <div class="sala-item">
        <span class="sala-dot ${corPorSala(s.id)}"></span>
        <div class="sala-info">
          <div class="name">${s.nome}</div>
          <div class="cap">${s.capacidade} lugares</div>
        </div>
        <span class="status-pill ${ocupada ? 'uso' : 'disp'}">${ocupada ? 'Em uso' : 'Disponível'}</span>
      </div>`;
  }).join('');
}

function renderRoomsGrid() {
  const container = document.getElementById('rooms-grid');
  const salasParaMostrar = salasFiltradas();

  if (salasParaMostrar.length === 0) {
    container.innerHTML = '<div class="room-card"><div class="room-body"><div class="rname">Sem salas.</div></div></div>';
    return;
  }
  container.innerHTML = salasParaMostrar.map(s => {
    const ocupada = salaOcupadaAgora(s.id);
    const cor = corPorSala(s.id);
    return `
      <div class="room-card">
        <div class="room-photo tile ${cor}">🚪</div>
        <div class="room-body">
          <div class="row">
            <span class="rname">${s.nome}</span>
            <span class="status-pill ${ocupada ? 'uso' : 'disp'}">${ocupada ? 'Em uso' : 'Disponível'}</span>
          </div>
          <div class="rcap">${s.capacidade} lugares${s.tipo ? ' · ' + s.tipo : ''}</div>
        </div>
      </div>`;
  }).join('');
}

/* =========================================================
   PAINEL "NOVA RESERVA"
   ========================================================= */
function preencherSelectSalas() {
  const select = document.getElementById('input-sala');
  const salasParaMostrar = salasFiltradas();
  
  if (salasParaMostrar.length === 0) {
    select.innerHTML = '<option value="">Nenhuma sala disponível</option>';
    return;
  }
  select.innerHTML = salasParaMostrar.map(s => `<option value="${s.id}">${s.nome} (${s.capacidade} lugares)</option>`).join('');
}

function preencherEquipamentos() {
  const container = document.getElementById('equipamentos-container');
  const tipos = Object.keys(equipamentosAgrupados);

  if (tipos.length === 0) {
    container.innerHTML = '<div class="meta">Sem equipamentos registados.</div>';
    return;
  }

  container.innerHTML = tipos.map(tipo => {
    const itens = equipamentosAgrupados[tipo].map(eq => `
      <label class="equip-check">
        <input type="checkbox" value="${eq.id}" name="equipamento">
        ${eq.nome}
      </label>`).join('');
    return `<div class="equip-tipo-titulo">${tipo}</div>${itens}`;
  }).join('');
}

function abrirPainelReserva() {
  document.getElementById('painel-reserva').classList.remove('painel-fechado');
  document.getElementById('overlay-reserva').classList.add('ativo');

  const dataInput = document.getElementById('input-data');
  if (!dataInput.value) {
    dataInput.value = dateKeyFromDate(viewMode === 'day' ? currentDate : REAL_TODAY);
  }
}

function fecharPainelReserva() {
  document.getElementById('painel-reserva').classList.add('painel-fechado');
  document.getElementById('overlay-reserva').classList.remove('ativo');
  document.getElementById('form-erro').classList.add('hidden');
}

function mostrarErroFormulario(msg) {
  const el = document.getElementById('form-erro');
  el.textContent = msg;
  el.classList.remove('hidden');
}

async function submeterFormularioReserva(evento) {
  evento.preventDefault();
  document.getElementById('form-erro').classList.add('hidden');

  const idSala = parseInt(document.getElementById('input-sala').value, 10);
  const dataReserva = document.getElementById('input-data').value;
  const horaInicio = document.getElementById('input-hora-inicio').value;
  const horaFim = document.getElementById('input-hora-fim').value;
  const nParticipantes = document.getElementById('input-participantes').value;
  const acao = document.getElementById('input-acao').value.trim();
  const observacoes = document.getElementById('input-observacoes').value.trim();
  const equipamentos = Array.from(document.querySelectorAll('input[name="equipamento"]:checked'))
    .map(el => parseInt(el.value, 10));

  if (!idSala || !dataReserva || !horaInicio || !horaFim) {
    mostrarErroFormulario('Preenche a sala, a data e as horas.');
    return;
  }
  if (horaFim <= horaInicio) {
    mostrarErroFormulario('A hora de fim tem de ser depois da hora de início.');
    return;
  }

  const corpo = {
    id_utilizador: CURRENT_USER_ID,
    id_sala: idSala,
    data_reserva: dataReserva,
    hora_inicio: `${horaInicio}:00`,
    hora_fim: `${horaFim}:00`,
    n_participantes: nParticipantes ? parseInt(nParticipantes, 10) : null,
    acao: acao || null,
    observacoes: observacoes || null,
    equipamentos,
  };

  const botao = document.getElementById('btn-submeter-reserva');
  botao.disabled = true;
  botao.textContent = 'A criar…';

  try {
    const { status, dados } = await apiPost('/reservas', corpo);

    if (status === 201 && dados.sucesso) {
      mostrarToast('Reserva criada com sucesso!');
      document.getElementById('form-reserva').reset();
      fecharPainelReserva();
      await recarregarReservas();
      renderTudo();
    } else if (status === 409) {
      mostrarErroFormulario(dados.erro || 'Já existe uma reserva para esta sala nesse horário.');
    } else {
      mostrarErroFormulario(dados.erro || 'Não foi possível criar a reserva.');
    }
  } catch (erro) {
    console.error(erro);
    mostrarErroFormulario('Erro de ligação à API.');
  } finally {
    botao.disabled = false;
    botao.textContent = 'Criar Reserva';
  }
}

/* =========================================================
   INÍCIO
   ========================================================= */
document.addEventListener('DOMContentLoaded', () => {
  carregarDadosIniciais();

  document.getElementById('prev-month').addEventListener('click', irParaAnterior);
  document.getElementById('next-month').addEventListener('click', irParaSeguinte);
  document.getElementById('today-btn').addEventListener('click', irParaHoje);

  document.getElementById('btn-view-mes').addEventListener('click', () => setViewMode('month'));
  document.getElementById('btn-view-semana').addEventListener('click', () => setViewMode('week'));
  document.getElementById('btn-view-dia').addEventListener('click', () => setViewMode('day'));

  // Filtro de Pólos
  const btnPolos = document.querySelectorAll('#filtro-polo button');
  if (btnPolos) {
    btnPolos.forEach(btn => {
      btn.addEventListener('click', (e) => {
        btnPolos.forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        currentPolo = e.target.dataset.polo;
        preencherSelectSalas();
        renderTudo();
      });
    });
  }

  document.getElementById('btn-nova-reserva').addEventListener('click', abrirPainelReserva);
  document.getElementById('btn-fechar-painel').addEventListener('click', fecharPainelReserva);
  document.getElementById('overlay-reserva').addEventListener('click', fecharPainelReserva);
  document.getElementById('form-reserva').addEventListener('submit', submeterFormularioReserva);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fecharPainelReserva();
    if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;
    if (e.key === 'ArrowLeft') irParaAnterior();
    if (e.key === 'ArrowRight') irParaSeguinte();
  });
});