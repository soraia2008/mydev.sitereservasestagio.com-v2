/* =========================================================
   Utilizadores — listagem e criação de novos utilizadores
   ========================================================= */

const API_BASE = '/api';
const CORES = ['blue', 'green', 'purple', 'orange'];

let utilizadores = [];

function corPorId(id) {
    return CORES[id % CORES.length];
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
async function carregar() {
    try {
        const respUtilizadores = await apiGet('/utilizadores');
        utilizadores = respUtilizadores.sucesso ? respUtilizadores.dados : [];
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
    const container = document.getElementById('utilizadores-grid');

    if (utilizadores.length === 0) {
        container.innerHTML = '<div class="room-card"><div class="room-body"><div class="rname">Nenhum utilizador registado.</div></div></div>';
        return;
    }

    container.innerHTML = utilizadores.map(u => {
        const cor = corPorId(u.id);
        const iniciais = u.nome.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        return `
      <div class="room-card">
        <div class="room-photo tile ${cor}">${iniciais}</div>
        <div class="room-body">
          <div class="row">
            <span class="rname">${u.nome}</span>
          </div>
          <div class="rcap">${u.email}</div>
        </div>
      </div>`;
    }).join('');
}

/* =========================================================
   PAINEL "NOVO UTILIZADOR"
   ========================================================= */
function abrirPainelUtilizador() {
    document.getElementById('painel-utilizador').classList.remove('painel-fechado');
    document.getElementById('overlay-utilizador').classList.add('ativo');
}

function fecharPainelUtilizador() {
    document.getElementById('painel-utilizador').classList.add('painel-fechado');
    document.getElementById('overlay-utilizador').classList.remove('ativo');
    document.getElementById('form-utilizador-erro').classList.add('hidden');
}

function mostrarErroFormularioUtilizador(msg) {
    const el = document.getElementById('form-utilizador-erro');
    el.textContent = msg;
    el.classList.remove('hidden');
}

async function submeterFormularioUtilizador(evento) {
    evento.preventDefault();
    document.getElementById('form-utilizador-erro').classList.add('hidden');

    const nome = document.getElementById('input-utilizador-nome').value.trim();
    const email = document.getElementById('input-utilizador-email').value.trim();

    if (!nome || !email) {
        mostrarErroFormularioUtilizador('Preenche o nome e o email.');
        return;
    }

    const corpo = { nome, email };

    const botao = document.getElementById('btn-submeter-utilizador');
    botao.disabled = true;
    botao.textContent = 'A criar…';

    try {
        const { status, dados } = await apiPost('/utilizadores', corpo);

        if (status === 201 && dados.sucesso) {
            mostrarToast('Utilizador criado com sucesso!');
            document.getElementById('form-utilizador').reset();
            fecharPainelUtilizador();
            await carregar();
        } else {
            mostrarErroFormularioUtilizador(dados.erro || 'Não foi possível criar o utilizador.');
        }
    } catch (erro) {
        console.error(erro);
        mostrarErroFormularioUtilizador('Erro de ligação à API.');
    } finally {
        botao.disabled = false;
        botao.textContent = 'Criar Utilizador';
    }
}

/* =========================================================
   INÍCIO
   ========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    carregar();

    document.getElementById('btn-novo-utilizador').addEventListener('click', abrirPainelUtilizador);
    document.getElementById('btn-fechar-painel-utilizador').addEventListener('click', fecharPainelUtilizador);
    document.getElementById('overlay-utilizador').addEventListener('click', fecharPainelUtilizador);
    document.getElementById('form-utilizador').addEventListener('submit', submeterFormularioUtilizador);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fecharPainelUtilizador();
    });
});
