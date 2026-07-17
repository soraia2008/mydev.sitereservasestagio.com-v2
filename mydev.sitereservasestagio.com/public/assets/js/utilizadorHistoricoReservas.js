/* =========================================================
   Minhas Reservas — histórico completo (todas + canceladas)
   ========================================================= */

const API_BASE = '/api';

// Ainda não existe login. Fixa-se o utilizador atual até isso ser implementado.
const CURRENT_USER_ID = 1;

const MESES_ABREV = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

let salasPorId = {};
let reservasDoUtilizador = [];
let filtroAtual = 'todas';

function pad(n) { return String(n).padStart(2, '0'); }

async function apiGet(caminho) {
  const resp = await fetch(`${API_BASE}${caminho}`);
  return resp.json();
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

function formatarDataBox(dataISO) {
  const [ano, mes, dia] = dataISO.split('-').map(Number);
  return { dia, mes: MESES_ABREV[mes - 1] };
}

async function carregar() {
  try {
    const [respSalas, respReservas] = await Promise.all([
      apiGet('/salas'),
      apiGet(`/reservas/utilizador/${CURRENT_USER_ID}`),
    ]);

    salasPorId = Object.fromEntries((respSalas.sucesso ? respSalas.dados : []).map(s => [s.id, s]));
    reservasDoUtilizador = respReservas.sucesso ? respReservas.dados : [];

    renderLista();
  } catch (erro) {
    console.error(erro);
    document.getElementById('historico-lista').innerHTML =
      '<div class="sem-dados">Não foi possível ligar à API. Verifica se o servidor está a correr.</div>';
  }
}

function renderLista() {
  const container = document.getElementById('historico-lista');

  const filtradas = filtroAtual === 'todas'
    ? reservasDoUtilizador
    : reservasDoUtilizador.filter(r => r.estado === filtroAtual);

  if (filtradas.length === 0) {
    container.innerHTML = '<div class="sem-dados">Nenhuma reserva encontrada.</div>';
    return;
  }

  container.innerHTML = filtradas.map(r => {
    const sala = salasPorId[r.id_sala];
    const nomeSala = sala ? sala.nome : `Sala #${r.id_sala}`;
    const { dia, mes } = formatarDataBox(r.data_reserva);
    const podeCancelar = r.estado === 'pendente' || r.estado === 'confirmada';

    return `
      <div class="historico-item" data-id="${r.id}">
        <div class="data-box">
          <div class="dia">${dia}</div>
          <div class="mes">${mes}</div>
        </div>
        <div class="detalhes">
          <div class="titulo">${nomeSala} — ${r.acao || 'Reserva'}</div>
          <div class="sub">
            ${horaSemSegundos(r.hora_inicio)} – ${horaSemSegundos(r.hora_fim)}
            ${r.n_participantes ? ' · ' + r.n_participantes + ' participantes' : ''}
            ${r.observacoes ? ' · ' + r.observacoes : ''}
          </div>
        </div>
        <div class="acoes">
          <span class="estado-pill estado-${r.estado}">${r.estado}</span>
          ${podeCancelar ? `<button class="btn-cancelar" data-id="${r.id}">Cancelar</button>` : ''}
        </div>
      </div>`;
  }).join('');

  container.querySelectorAll('.btn-cancelar').forEach(btn => {
    btn.addEventListener('click', () => cancelarReserva(parseInt(btn.dataset.id, 10)));
  });
}

async function cancelarReserva(id) {
  if (!confirm('Tens a certeza que queres cancelar esta reserva?')) return;

  try {
    const { status, dados } = await apiPatch(`/reservas/${id}/estado`, { estado: 'cancelada' });
    if (status === 200 && dados.sucesso) {
      mostrarToast('Reserva cancelada.');
      const reserva = reservasDoUtilizador.find(r => r.id === id);
      if (reserva) reserva.estado = 'cancelada';
      renderLista();
    } else {
      mostrarToast(dados.erro || 'Não foi possível cancelar a reserva.', 'erro');
    }
  } catch (erro) {
    console.error(erro);
    mostrarToast('Erro de ligação à API.', 'erro');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  carregar();

  document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filtroAtual = btn.dataset.filtro;
      renderLista();
    });
  });
});