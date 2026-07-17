<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalaFácil — Definições</title>
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
            <li><a href="utilizadores.php"><span class="icon">👥</span> Utilizadores</a></li>
            <li><a href="definicoes.php" class="active"><span class="icon">⚙️</span> Definições</a></li>
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
                <h1>Definições</h1>
                <p>Configurações do sistema e preferências pessoais.</p>
            </div>
            <div class="top-actions">
                <button class="btn btn-primary" id="btn-salvar-def">Salvar Alterações</button>
            </div>
        </div>

        <div class="content-grid" style="grid-template-columns: 2fr 1fr; margin-top:20px;">
            <div class="card panel">
                <h3>Preferências Gerais</h3>
                
                <form id="form-definicoes" style="margin-top: 20px;">
                    <label class="campo">
                        <span>Fuso Horário</span>
                        <select>
                            <option>Lisboa / Londres (WET)</option>
                            <option>Europa Central (CET)</option>
                        </select>
                    </label>

                    <label class="campo">
                        <span>Formato de Data</span>
                        <select>
                            <option>DD/MM/AAAA</option>
                            <option>MM/DD/AAAA</option>
                        </select>
                    </label>

                    <label class="campo">
                        <span style="display:block; margin-bottom: 10px;">Notificações por Email</span>
                        <div style="display:flex; align-items:center; gap: 10px; margin-top: 5px;">
                            <input type="checkbox" id="notif1" checked> <label for="notif1">Novas reservas</label>
                        </div>
                        <div style="display:flex; align-items:center; gap: 10px; margin-top: 5px;">
                            <input type="checkbox" id="notif2" checked> <label for="notif2">Cancelamentos</label>
                        </div>
                    </label>
                </form>
            </div>

            <div class="card panel">
                <h3>Aparência</h3>
                <div style="margin-top: 20px;">
                    <label class="campo">
                        <span>Tema</span>
                        <select>
                            <option>Sistema (Automático)</option>
                            <option>Claro</option>
                            <option>Escuro</option>
                        </select>
                    </label>
                </div>
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>
    <script>
        document.getElementById('btn-salvar-def').addEventListener('click', () => {
            const toast = document.getElementById('toast');
            toast.textContent = 'Definições guardadas com sucesso!';
            toast.classList.add('mostrar');
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => toast.classList.remove('mostrar'), 3000);
        });
    </script>
</body>

</html>
