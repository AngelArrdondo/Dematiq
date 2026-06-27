<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$initials  = strtoupper(substr($user['nombre'], 0, 1));
$csrfToken = Auth::csrfToken();

$fotoPath = '';
try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
    $stmtFoto->execute([$user['id']]);
    $fotoRaw  = $stmtFoto->fetchColumn();
    $fotoPath = $fotoRaw ? '../' . htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) {}

// Load last 200 rows server-side for initial render
$logRows = [];
try {
    $stmt    = $pdo->query(
        'SELECT la.id, la.username, la.ip, la.resultado, la.creado_en,
                u.nombre
         FROM log_accesos la
         LEFT JOIN usuarios u ON u.id = la.usuario_id
         ORDER BY la.creado_en DESC
         LIMIT 200'
    );
    $logRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accesos | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>
    .log-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .log-table th { background:#f0f4ff; padding:9px 13px; text-align:left; font-weight:700; color:var(--accent); font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid var(--border); white-space:nowrap; }
    .log-table td { padding:9px 13px; border-bottom:1px solid var(--border); color:var(--text); vertical-align:middle; }
    .log-table tr:last-child td { border-bottom:none; }
    .log-table tr:hover td { background:#f8faff; }
    .badge-exito    { display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#e6f4ea;color:#1e7e34; }
    .badge-fallo    { display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#fef0f0;color:#c0392b; }
    .badge-bloqueado{ display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#fff8e1;color:#b7700a; }
    .filter-bar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 16px;border-bottom:1px solid var(--border); }
    .filter-bar label { font-size:.78rem;font-weight:600;color:var(--text-lt); }
    .filter-bar select { padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;color:var(--text);background:#f8faff;outline:none;cursor:pointer; }
    .filter-bar select:focus { border-color:var(--accent-lt); }
    .table-wrap { overflow-x:auto; }
    .ua-cell { max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-lt); font-size:.72rem; }
    .ip-cell  { font-family:monospace; font-size:.8rem; white-space:nowrap; }
    .stats-row { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px; }
    .stat-chip { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px 18px;min-width:110px;text-align:center; }
    .stat-chip .stat-num { font-size:1.5rem;font-weight:800;color:var(--accent); }
    .stat-chip .stat-lbl { font-size:.7rem;color:var(--text-lt);margin-top:2px; }
  </style>
</head>
<body>
<script>window.__DB_CONTENT = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar"></aside>

<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="admin-topbar-title">Registro de Accesos</span>
    </div>
    <div class="user-menu" id="userMenuBtn" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
      <div class="admin-avatar" id="topbarAvatar" style="overflow:hidden">
        <?php if ($fotoPath): ?><img src="<?= $fotoPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%"><?php else: ?><?= $initials ?><?php endif; ?>
      </div>
      <div class="user-menu-info">
        <span class="user-menu-name" id="topbarName"><?= htmlspecialchars($user['nombre']) ?></span>
        <span class="user-menu-role">Administrador</span>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron"><polyline points="6 9 12 15 18 9"/></svg>
      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" role="menuitem" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" role="menuitem" href="../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">
    <div class="section-header">
      <h1>Registro de Accesos</h1>
      <p>Últimos 200 intentos de inicio de sesión.</p>
    </div>

    <div class="stats-row" id="statsRow"></div>

    <div class="admin-card">
      <div class="filter-bar">
        <label>Filtrar:</label>
        <select id="filterResultado" onchange="renderTable()">
          <option value="">Todos</option>
          <option value="exito">Exitosos</option>
          <option value="fallo">Fallidos</option>
          <option value="bloqueado">Bloqueados</option>
        </select>
        <input type="text" class="form-control" id="filterUser" placeholder="Buscar usuario / IP…"
               oninput="renderTable()" style="max-width:220px;padding:6px 10px;font-size:.82rem">
        <span style="margin-left:auto;font-size:.78rem;color:var(--text-lt)" id="countLabel"></span>
      </div>
      <div class="table-wrap">
        <table class="log-table">
          <thead>
            <tr>
              <th>#</th><th>Usuario</th><th>IP</th><th>Resultado</th><th>Fecha</th><th>User-Agent</th>
            </tr>
          </thead>
          <tbody id="log-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('accesos', '../', '../../');

  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', e => { e.stopPropagation(); const o=userMenuBtn.classList.toggle('open'); userMenuBtn.setAttribute('aria-expanded',o); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));


  /* ── Access log ───────────────────────────────────────────── */
  const RAW = <?= json_encode($logRows, JSON_UNESCAPED_UNICODE) ?>;

  function badge(r) {
    return `<span class="badge-${r}">${r}</span>`;
  }
  function fmt(ts) {
    if (!ts) return '—';
    return new Date(ts).toLocaleString('es-MX', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }

  function renderStats() {
    const counts = { exito: 0, fallo: 0, bloqueado: 0 };
    RAW.forEach(r => { if (counts[r.resultado] !== undefined) counts[r.resultado]++; });
    document.getElementById('statsRow').innerHTML = [
      { lbl:'Total', num: RAW.length, color:'var(--accent)' },
      { lbl:'Exitosos', num: counts.exito, color:'#1e7e34' },
      { lbl:'Fallidos', num: counts.fallo, color:'#c0392b' },
      { lbl:'Bloqueados', num: counts.bloqueado, color:'#b7700a' },
    ].map(s => `<div class="stat-chip"><div class="stat-num" style="color:${s.color}">${s.num}</div><div class="stat-lbl">${s.lbl}</div></div>`).join('');
  }

  function renderTable() {
    const filtR = document.getElementById('filterResultado').value;
    const filtU = (document.getElementById('filterUser').value || '').toLowerCase();
    const rows  = RAW.filter(r => {
      if (filtR && r.resultado !== filtR) return false;
      if (filtU && !(r.username||'').toLowerCase().includes(filtU) && !(r.ip||'').includes(filtU)) return false;
      return true;
    });
    document.getElementById('countLabel').textContent = `Mostrando ${rows.length} de ${RAW.length}`;
    const tbody = document.getElementById('log-tbody');
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-lt)">Sin resultados</td></tr>'; return; }
    tbody.innerHTML = rows.map((r, i) => `
      <tr>
        <td style="color:var(--text-lt);font-size:.75rem">${r.id}</td>
        <td><strong>${escH(r.username||'—')}</strong>${r.nombre?`<br><span style="font-size:.72rem;color:var(--text-lt)">${escH(r.nombre)}</span>`:''}</td>
        <td class="ip-cell">${escH(r.ip)}</td>
        <td>${badge(r.resultado)}</td>
        <td style="white-space:nowrap;font-size:.78rem">${fmt(r.creado_en)}</td>
        <td class="ua-cell" title="${escH(r.user_agent||'')}">${escH(r.user_agent||'—')}</td>
      </tr>`).join('');
  }
  function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  renderStats();
  renderTable();
</script>
</body>
</html>
