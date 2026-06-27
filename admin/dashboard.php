<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/conexion.php';
$user      = Auth::require('/pages/corporativo/login.php');
$initials  = strtoupper(substr($user['nombre'], 0, 1));
$csrfToken = Auth::csrfToken();

// Fetch foto separately so auth.php doesn't depend on the column existing yet
$fotoPath = '';
try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
    $stmtFoto->execute([$user['id']]);
    $fotoRaw  = $stmtFoto->fetchColumn();
    $fotoPath = $fotoRaw ? htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) { /* column not yet migrated — ignore */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | DEMATIQ Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../assets/images/logos/favicon-d.svg">
  <style>
    .summary-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    .scard {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px 22px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .scard-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-lt); }
    .scard-value { font-size: 2.1rem; font-weight: 700; color: var(--text); line-height: 1.1; }
    .scard-sub   { font-size: .75rem; color: var(--text-lt); }
    .scard.blue   { border-top: 3px solid #2e6bcf; }
    .scard.teal   { border-top: 3px solid #14b8a6; }
    .scard.purple { border-top: 3px solid #a855f7; }

    .chart-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 22px 24px; }
    .chart-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    .chart-title  { font-size: .95rem; font-weight: 700; color: var(--text); }
    .chart-range  { font-size: .78rem; color: var(--text-lt); margin-top: 2px; }
    .chart-nav    { display: flex; align-items: center; gap: 8px; }
    .chart-nav-btn {
      background: none; border: 1px solid var(--border); border-radius: 8px;
      width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--text-lt); transition: background .15s, color .15s, border-color .15s;
    }
    .chart-nav-btn:hover:not(:disabled) { background: var(--accent); border-color: var(--accent); color: #fff; }
    .chart-nav-btn:disabled { opacity: .35; cursor: not-allowed; }
    .chart-week-btn {
      background: var(--accent); border: none; border-radius: 8px;
      padding: 5px 14px; font-size: .76rem; font-weight: 700; color: #fff;
      cursor: pointer; transition: filter .15s;
    }
    .chart-week-btn:hover { filter: brightness(1.1); }
    .chart-body   { position: relative; height: 300px; }
    .chart-empty  {
      display: none; position: absolute; inset: 0;
      flex-direction: column; align-items: center; justify-content: center; gap: 8px;
      background: rgba(255,255,255,.88); border-radius: 8px;
      color: var(--text-lt); font-size: .85rem; text-align: center; pointer-events: none;
    }
    .chart-empty svg { opacity: .22; }

    @media (max-width: 600px) {
      .summary-row { grid-template-columns: 1fr 1fr; }
      .summary-row .scard:last-child { grid-column: span 2; }
      .chart-body { height: 220px; }
      .scard-value { font-size: 1.7rem; }
      .user-menu-info { display: none; }
    }
    @media (max-width: 420px) {
      .summary-row { grid-template-columns: 1fr; }
      .summary-row .scard:last-child { grid-column: unset; }
      .chart-header { flex-direction: column; align-items: flex-start; gap: 10px; }
      .chart-nav { width: 100%; justify-content: space-between; }
      .chart-week-btn { flex: 1; text-align: center; }
      .chart-body { height: 190px; }
    }
  </style>
</head>
<body>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar" aria-label="Navegación del panel"></aside>

<div class="admin-main">

  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Dashboard</span>
    </div>

    <!-- User menu -->
    <div class="user-menu" id="userMenuBtn" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
      <div class="admin-avatar" id="topbarAvatar" style="overflow:hidden">
        <?php if ($fotoPath): ?>
          <img src="<?= $fotoPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
        <?php else: ?>
          <?= $initials ?>
        <?php endif; ?>
      </div>
      <div class="user-menu-info">
        <span class="user-menu-name" id="topbarName"><?= htmlspecialchars($user['nombre']) ?></span>
        <span class="user-menu-role">Administrador</span>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron" aria-hidden="true">
        <polyline points="6 9 12 15 18 9"/>
      </svg>

      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" role="menuitem" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" role="menuitem" href="logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="admin-content">
    <div class="summary-row">
      <div class="scard blue">
        <span class="scard-label">Esta semana</span>
        <span class="scard-value" id="sum-week">—</span>
        <span class="scard-sub">visitas totales</span>
      </div>
      <div class="scard teal">
        <span class="scard-label">Hoy</span>
        <span class="scard-value" id="sum-today">—</span>
        <span class="scard-sub" id="sum-today-label">—</span>
      </div>
      <div class="scard purple">
        <span class="scard-label">Mejor día</span>
        <span class="scard-value" id="sum-best">—</span>
        <span class="scard-sub" id="sum-best-label">esta semana</span>
      </div>
    </div>

    <div class="chart-card">
      <div class="chart-header">
        <div>
          <div class="chart-title">Visitas por día</div>
          <div class="chart-range" id="chart-range"></div>
        </div>
        <div class="chart-nav">
          <button class="chart-nav-btn" id="btn-prev" onclick="weekNav(-1)" aria-label="Semana anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15,18 9,12 15,6"/></svg>
          </button>
          <button class="chart-week-btn" onclick="weekNav(0)">Esta semana</button>
          <button class="chart-nav-btn" id="btn-next" onclick="weekNav(1)" aria-label="Semana siguiente" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="visitsChart" aria-label="Visitas por día de la semana" role="img"></canvas>
        <div class="chart-empty" id="chart-empty">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="1" x2="23" y2="23"/>
          </svg>
          <span>Sin datos aún.<br>Visita el sitio público para empezar a registrar visitas.</span>
        </div>
      </div>
    </div>
  </main>
</div>

<?php $profileApiPath = 'api/profile.php'; require __DIR__ . '/includes/profile-modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('dashboard', './', '../');

  /* ── User menu dropdown ─────────────────────────── */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const open = this.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
  });
  userMenuBtn.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
    if (e.key === 'Escape') this.classList.remove('open');
  });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

  /* ── Visits chart ───────────────────────────────── */
  (function () {
    const daily  = JSON.parse(localStorage.getItem('dematiq_visits_daily') || '{}');
    const DAYS   = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const MONTHS = ['enero','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    let offset = 0, chart = null;

    function iso(d) {
      return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function getWeek(off) {
      const mon = new Date();
      mon.setDate(mon.getDate() - ((mon.getDay()+6)%7) + off*7);
      mon.setHours(0,0,0,0);
      return Array.from({length:7}, (_,i) => { const d = new Date(mon); d.setDate(mon.getDate()+i); return d; });
    }

    function fmtRange(dates) {
      const s = dates[0], e = dates[6];
      return s.getMonth() === e.getMonth()
        ? `${s.getDate()} – ${e.getDate()} de ${MONTHS[s.getMonth()]} ${s.getFullYear()}`
        : `${s.getDate()} ${MONTHS[s.getMonth()]} – ${e.getDate()} ${MONTHS[e.getMonth()]} ${e.getFullYear()}`;
    }

    function render(off) {
      const dates    = getWeek(off);
      const data     = dates.map(d => daily[iso(d)] || 0);
      const total    = data.reduce((a,b) => a+b, 0);
      const best     = Math.max(...data);
      const bestIdx  = data.indexOf(best);
      const todayIso = iso(new Date());
      const now      = new Date();

      document.getElementById('sum-week').textContent       = total.toLocaleString('es-MX');
      document.getElementById('sum-today').textContent      = (daily[todayIso]||0).toLocaleString('es-MX');
      document.getElementById('sum-today-label').textContent = `${DAYS[now.getDay()]} ${now.getDate()} de ${MONTHS[now.getMonth()]}`;
      document.getElementById('sum-best').textContent       = best > 0 ? best.toLocaleString('es-MX') : '—';
      document.getElementById('sum-best-label').textContent = best > 0
        ? `${DAYS[dates[bestIdx].getDay()]} ${dates[bestIdx].getDate()}/${dates[bestIdx].getMonth()+1}`
        : 'sin datos';

      document.getElementById('chart-range').textContent  = fmtRange(dates);
      document.getElementById('btn-next').disabled        = (off >= 0);
      document.getElementById('chart-empty').style.display = total === 0 ? 'flex' : 'none';

      const labels   = dates.map(d => `${DAYS[d.getDay()]}\n${d.getDate()}/${d.getMonth()+1}`);
      const accent   = '#2e6bcf';
      const todayIdx = dates.findIndex(d => iso(d) === todayIso);
      const bg       = data.map((_,i) => i === todayIdx ? accent : accent+'55');
      const bdColor  = data.map((_,i) => i === todayIdx ? accent : accent+'99');

      if (chart) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.data.datasets[0].backgroundColor = bg;
        chart.data.datasets[0].borderColor = bdColor;
        chart.update('active');
        return;
      }

      chart = new Chart(document.getElementById('visitsChart').getContext('2d'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{ label: 'Visitas', data, backgroundColor: bg, borderColor: bdColor, borderWidth: 1.5, borderRadius: 8, borderSkipped: false }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                title: items => items[0].label.replace('\n', ' '),
                label: item  => `  ${item.raw} visita${item.raw !== 1 ? 's' : ''}`,
              },
              backgroundColor: '#0d2155', titleColor: '#fff', bodyColor: '#a8bce0', padding: 10, cornerRadius: 8,
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#6b7a99', maxRotation: 0 } },
            y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 11 }, color: '#6b7a99' }, grid: { color: '#e8edf8' } }
          }
        }
      });
    }

    window.weekNav = function(dir) {
      offset = dir === 0 ? 0 : Math.min(0, offset + dir);
      render(offset);
    };

    render(0);
  })();
</script>

</body>
</html>
