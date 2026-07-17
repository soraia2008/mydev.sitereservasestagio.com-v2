/* =========================================================
   Salas — listagem com estado atual + criação de novas salas
   ========================================================= */

const API_BASE = '/api';
const CORES = ['blue', 'green', 'purple', 'orange'];

let salas = [];
let reservas = [];

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

function mostrarToast(mensagem, tipo = 'sucesso') {
    const toast = document.getElementById('toast');
    toast.textContent = mensagem;
    toast.classList.toggle('erro', tipo === 'erro');
    toast.classList.add('mostrar');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('mostrar'), 3500);
}

/* =========================================================
   CARREGAMENTO
   ========================================================= */
function pad(n) { return String(n).padStart(2, '0'); }

function agoraStr() {
    const d = new Date();
    return `${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
}

function dateKeyHoje() {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function salaOcupadaAgora(idSala) {
    const hoje = dateKeyHoje();
    const agora = agoraStr();
    return reservas.some(r =>
        r.id_sala === idSala &&
        r.data_reserva === hoje &&
        r.estado !== 'cancelada' &&
        r.hora_inicio <= agora && r.hora_fim > agora
    );
}

async function carregar() {
    try {
        const [respSalas, respReservas] = await Promise.all([
            apiGet('/salas'),
            apiGet('/reservas'),
        ]);

        salas = respSalas.sucesso ? respSalas.dados : [];
        reservas = respReservas.sucesso ? respReservas.dados : [];

        renderGrid();
    } catch (erro) {
        console.error(erro);
        mostrarToast('Não foi possível ligar à API. Verifica se o servidor está a correr.', 'erro');
    }
}

/* =========================================================
   RENDER
   ========================================================= */
function renderGrid() {
    const container = document.getElementById('salas-grid');

    if (salas.length === 0) {
        container.innerHTML = '<div class="room-card"><div class="room-body"><div class="rname">Nenhuma sala registada.</div></div></div>';
        return;
    }

    container.innerHTML = salas.map(s => {
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
   PAINEL "NOVA SALA"
   ========================================================= */
function abrirPainelSala() {
    document.getElementById('painel-sala').classList.remove('painel-fechado');
    document.getElementById('overlay-sala').classList.add('ativo');
}

function fecharPainelSala() {
    document.getElementById('painel-sala').classList.add('painel-fechado');
    document.getElementById('overlay-sala').classList.remove('ativo');
    document.getElementById('form-sala-erro').classList.add('hidden');
}

function mostrarErroFormularioSala(msg) {
    const el = document.getElementById('form-sala-erro');
    el.textContent = msg;
    el.classList.remove('hidden');
}

async function submeterFormularioSala(evento) {
    evento.preventDefault();
    document.getElementById('form-sala-erro').classList.add('hidden');

    const nome = document.getElementById('input-sala-nome').value.trim();
    const tipo = document.getElementById('input-sala-tipo').value.trim();
    const capacidade = document.getElementById('input-sala-capacidade').value;

    if (!nome || !capacidade) {
        mostrarErroFormularioSala('Preenche o nome e a capacidade.');
        return;
    }

    const corpo = {
        nome,
        tipo: tipo || null,
        capacidade: parseInt(capacidade, 10),
    };

    const botao = document.getElementById('btn-submeter-sala');
    botao.disabled = true;
    botao.textContent = 'A criar…';

    try {
        const { status, dados } = await apiPost('/salas', corpo);

        if (status === 201 && dados.sucesso) {
            mostrarToast('Sala criada com sucesso!');
            document.getElementById('form-sala').reset();
            fecharPainelSala();
            await carregar();
        } else {
            mostrarErroFormularioSala(dados.erro || 'Não foi possível criar a sala.');
        }
    } catch (erro) {
        console.error(erro);
        mostrarErroFormularioSala('Erro de ligação à API.');
    } finally {
        botao.disabled = false;
        botao.textContent = 'Criar Sala';
    }
}

/* =========================================================
   INÍCIO
   ========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    carregar();

    document.getElementById('btn-nova-sala').addEventListener('click', abrirPainelSala);
    document.getElementById('btn-fechar-painel-sala').addEventListener('click', fecharPainelSala);
    document.getElementById('overlay-sala').addEventListener('click', fecharPainelSala);
    document.getElementById('form-sala').addEventListener('submit', submeterFormularioSala);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fecharPainelSala();
    });
});