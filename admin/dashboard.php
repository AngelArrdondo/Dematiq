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

$visitasDiarias = [];
try {
    $stmt = $pdo->query('SELECT fecha, total FROM visitas_diarias');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $visitasDiarias[$row['fecha']] = (int) $row['total'];
    }
} catch (PDOException $e) { /* tabla no migrada aún — ignore */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | DEMATIQ Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css?v=10">
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
    .chart-controls { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .period-tabs  { display: flex; background: #f1f4fa; border-radius: 8px; padding: 3px; gap: 2px; }
    .period-tab   {
      border: none; background: none; padding: 6px 13px; border-radius: 6px;
      font-size: .76rem; font-weight: 700; color: var(--text-lt);
      cursor: pointer; transition: background .15s, color .15s;
    }
    .period-tab:hover:not(.active) { color: var(--text); }
    .period-tab.active { background: #fff; color: var(--accent); box-shadow: 0 1px 3px rgba(0,0,0,.12); }
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
      .chart-controls { width: 100%; flex-direction: column; align-items: stretch; }
      .period-tabs { justify-content: center; }
      .chart-nav { width: 100%; justify-content: space-between; }
      .chart-week-btn { flex: 1; text-align: center; }
      .chart-body { height: 190px; }
    }
  </style>
</head>
<body>

<script>window.__VISITS_DAILY = <?= json_encode($visitasDiarias, JSON_UNESCAPED_UNICODE) ?>;</script>

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
        <span class="scard-label" id="sum-period-label">Esta semana</span>
        <span class="scard-value" id="sum-week">—</span>
        <span class="scard-sub">visitas totales</span>
      </div>
      <div class="scard teal">
        <span class="scard-label">Hoy</span>
        <span class="scard-value" id="sum-today">—</span>
        <span class="scard-sub" id="sum-today-label">—</span>
      </div>
      <div class="scard purple">
        <span class="scard-label" id="sum-best-title">Mejor día</span>
        <span class="scard-value" id="sum-best">—</span>
        <span class="scard-sub" id="sum-best-label">esta semana</span>
      </div>
    </div>

    <div class="chart-card">
      <div class="chart-header">
        <div>
          <div class="chart-title">Visitas</div>
          <div class="chart-range" id="chart-range"></div>
        </div>
        <div class="chart-controls">
          <div class="period-tabs" id="periodTabs">
            <button class="period-tab active" data-period="week"  onclick="setPeriod('week')">Semana</button>
            <button class="period-tab"        data-period="month" onclick="setPeriod('month')">Mes</button>
            <button class="period-tab"        data-period="year"  onclick="setPeriod('year')">Año</button>
          </div>
          <div class="chart-nav">
            <button class="chart-nav-btn" id="btn-prev" onclick="periodNav(-1)" aria-label="Periodo anterior">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15,18 9,12 15,6"/></svg>
            </button>
            <button class="chart-week-btn" id="btn-current" onclick="periodNav(0)">Esta semana</button>
            <button class="chart-nav-btn" id="btn-next" onclick="periodNav(1)" aria-label="Periodo siguiente" disabled>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,18 15,12 9,6"/></svg>
            </button>
          </div>
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
    const daily  = window.__VISITS_DAILY || {};
    const DAYS         = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const MONTHS       = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const MONTHS_SHORT = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const JUMP_LABEL   = { week: 'Esta semana', month: 'Este mes', year: 'Este año' };
    const PERIOD_LABEL = { week: 'Esta semana', month: 'Este mes', year: 'Este año' };
    const BEST_TITLE   = { week: 'Mejor día', month: 'Mejor día', year: 'Mejor mes' };

    let period  = 'week';
    let offsets = { week: 0, month: 0, year: 0 };
    let chart   = null;

    function iso(d) {
      return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function getWeek(off) {
      const mon = new Date();
      mon.setDate(mon.getDate() - ((mon.getDay()+6)%7) + off*7);
      mon.setHours(0,0,0,0);
      return Array.from({length:7}, (_,i) => { const d = new Date(mon); d.setDate(mon.getDate()+i); return d; });
    }

    function getMonthDays(off) {
      const now  = new Date();
      const year = now.getFullYear();
      const month = now.getMonth() + off;
      const base = new Date(year, month, 1);
      const daysInMonth = new Date(base.getFullYear(), base.getMonth()+1, 0).getDate();
      return Array.from({length: daysInMonth}, (_,i) => new Date(base.getFullYear(), base.getMonth(), i+1));
    }

    function getYearMonths(off) {
      const year = new Date().getFullYear() + off;
      return Array.from({length: 12}, (_,i) => new Date(year, i, 1));
    }

    function sumMonth(d) {
      const days = new Date(d.getFullYear(), d.getMonth()+1, 0).getDate();
      let total = 0;
      for (let i = 1; i <= days; i++) total += daily[iso(new Date(d.getFullYear(), d.getMonth(), i))] || 0;
      return total;
    }

    function fmtRange(dates) {
      if (period === 'week') {
        const s = dates[0], e = dates[6];
        return s.getMonth() === e.getMonth()
          ? `${s.getDate()} – ${e.getDate()} de ${MONTHS[s.getMonth()]} ${s.getFullYear()}`
          : `${s.getDate()} ${MONTHS[s.getMonth()]} – ${e.getDate()} ${MONTHS[e.getMonth()]} ${e.getFullYear()}`;
      }
      if (period === 'month') {
        const d = dates[0];
        return `${MONTHS[d.getMonth()][0].toUpperCase()}${MONTHS[d.getMonth()].slice(1)} ${d.getFullYear()}`;
      }
      return `${dates[0].getFullYear()}`;
    }

    function render() {
      const off = offsets[period];
      let dates, data, labels;

      if (period === 'week') {
        dates  = getWeek(off);
        data   = dates.map(d => daily[iso(d)] || 0);
        labels = dates.map(d => `${DAYS[d.getDay()]}\n${d.getDate()}/${d.getMonth()+1}`);
      } else if (period === 'month') {
        dates  = getMonthDays(off);
        data   = dates.map(d => daily[iso(d)] || 0);
        labels = dates.map(d => `${d.getDate()}`);
      } else {
        dates  = getYearMonths(off);
        data   = dates.map(sumMonth);
        labels = dates.map(d => MONTHS_SHORT[d.getMonth()]);
      }

      const total    = data.reduce((a,b) => a+b, 0);
      const best     = Math.max(...data);
      const bestIdx  = data.indexOf(best);
      const todayIso = iso(new Date());
      const now      = new Date();

      document.getElementById('sum-period-label').textContent = PERIOD_LABEL[period];
      document.getElementById('sum-week').textContent       = total.toLocaleString('es-MX');
      document.getElementById('sum-today').textContent      = (daily[todayIso]||0).toLocaleString('es-MX');
      document.getElementById('sum-today-label').textContent = `${DAYS[now.getDay()]} ${now.getDate()} de ${MONTHS[now.getMonth()]}`;
      document.getElementById('sum-best-title').textContent = BEST_TITLE[period];
      document.getElementById('sum-best').textContent       = best > 0 ? best.toLocaleString('es-MX') : '—';
      document.getElementById('sum-best-label').textContent = best <= 0
        ? 'sin datos'
        : period === 'year'
          ? `${MONTHS[dates[bestIdx].getMonth()]} ${dates[bestIdx].getFullYear()}`
          : `${DAYS[dates[bestIdx].getDay()]} ${dates[bestIdx].getDate()}/${dates[bestIdx].getMonth()+1}`;

      document.getElementById('chart-range').textContent  = fmtRange(dates);
      document.getElementById('btn-current').textContent  = JUMP_LABEL[period];
      document.getElementById('btn-next').disabled        = (off >= 0);
      document.getElementById('chart-empty').style.display = total === 0 ? 'flex' : 'none';

      const accent   = '#2e6bcf';
      const hlIdx    = period === 'year'
        ? dates.findIndex(d => d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth())
        : dates.findIndex(d => iso(d) === todayIso);
      const bg       = data.map((_,i) => i === hlIdx ? accent : accent+'55');
      const bdColor  = data.map((_,i) => i === hlIdx ? accent : accent+'99');

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
                title: items => {
                  const raw = items[0].label;
                  if (period === 'week')  return raw.replace('\n', ' ');
                  if (period === 'month') return `Día ${raw}`;
                  return raw;
                },
                label: item => `  ${item.raw} visita${item.raw !== 1 ? 's' : ''}`,
              },
              backgroundColor: '#0d2155', titleColor: '#fff', bodyColor: '#a8bce0', padding: 10, cornerRadius: 8,
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#6b7a99', maxRotation: 0, autoSkip: true } },
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 }, color: '#6b7a99' }, grid: { color: '#e8edf8' } }
          }
        }
      });
    }

    window.periodNav = function(dir) {
      offsets[period] = dir === 0 ? 0 : Math.min(0, offsets[period] + dir);
      render();
    };

    window.setPeriod = function(p) {
      period = p;
      document.querySelectorAll('.period-tab').forEach(t => t.classList.toggle('active', t.dataset.period === p));
      render();
    };

    render();
  })();
</script>

</body>
</html>
