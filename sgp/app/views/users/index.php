

<style>
    /* =====================================================
       MODERN USERS MANAGEMENT STYLES
       ===================================================== */
    
    /* Page Header - Modern with gradient */
    .page-header-modern {
        background: linear-gradient(135deg, 
            #172554 0%, 
            #1e3a8a 50%,
            #2563eb 100%
        );
        border-radius: 20px;
        padding: 32px 40px;
        margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(22, 38, 96, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .page-header-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }
    
    .page-header-content {
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 1;
    }
    
    .page-header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    
    .page-header-text h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 6px 0;
        color: white;
    }
    
    .page-header-text p {
        font-size: 1.05rem;
        opacity: 0.9;
        margin: 0;
    }
    
    /* Primary Button - Enhanced */
    .btn-primary {
        background: linear-gradient(135deg, var(--color-accent) 0%, #e8d4b8 100%);
        color: var(--color-primary);
        padding: 11px 20px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all var(--transition-fast);
        box-shadow: 0 4px 12px rgba(241, 228, 209, 0.3);
        z-index: 1;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(241, 228, 209, 0.4);
    }
    
    .btn-primary i {
        font-size: 16px;
    }
    
    /* Table Container - Enhanced */
    .table-card-modern {
        background: var(--color-card);
        border-radius: 20px;
        padding: 0;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    /* Filtros Rápidos (Cápsulas) movidos a la vista principal */
    
    /* =======================================
       Modal Styles (Estandarizado Pasantes)
       ======================================= */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15,23,42,0.7); backdrop-filter: blur(6px);
        z-index: 9999; align-items: center; justify-content: center;
        animation: fadeIn 0.2s ease;
    }
    .modal-overlay.active { display: flex; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    @keyframes slideUp { from { transform:translateY(24px);opacity:0; } to { transform:translateY(0);opacity:1; } }

    .modal-box {
        background: white;
        border-radius: 24px;
        width: 90%;
        max-width: 580px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden; 
        box-shadow: 0 32px 80px rgba(15,23,42,0.3);
        animation: slideUp 0.3s ease;
    }
    .modal-head {
        background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0; 
        color: white;
    }
    .modal-head h2 { font-size:1.3rem; font-weight:700; margin:0; color:white !important; }
    .modal-head p  { font-size:0.85rem; margin:4px 0 0; color:rgba(255,255,255,0.8) !important; }
    .modal-head * { color: white !important; }
    
    .btn-close-modal {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white !important;
        width: 36px; height: 36px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    .btn-close-modal:hover { background: rgba(255,255,255,0.35); }
    .btn-close-modal i { color: white !important; }
    
    .modal-body {
        padding: 28px 32px;
        overflow-y: auto; 
        flex: 1;
    }

    .form-group { margin-bottom: 20px; }
    .form-label {
        display:block; font-size:0.82rem; font-weight:700; color:#374151;
        margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;
    }
    .form-input {
        width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px;
        font-size:0.95rem; color:#1e293b; transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing:border-box; background:#fafafa;
    }
    .form-input:focus {
        outline:none; border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(79,70,229,0.1); background:white;
    }
    
    /* Vento Pills (Filtros) */
    .vento-pill.active {
        box-shadow: 0 4px 12px rgba(37,99,235,0.25);
    }

    /* Modal Styles - Consistente con modal de Pasantes */
    .modal {
        display: none;
        position: fixed;
        z-index: 1100; /* Mayor que sidebar-mobile (1050) para cubrirlo */
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        animation: fadeIn 0.3s;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    /* Modal box: flexbox para header fijo + body scrollable */
    .modal-content {
        background: white;
        border-radius: 24px;
        max-width: 520px;
        width: 90%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden; /* Clip esquinas redondeadas */
        animation: slideUp 0.3s;
        box-shadow: 0 32px 80px rgba(15, 23, 42, 0.3);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(24px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Header con gradiente azul institucional */
    .modal-header {
        background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        color: white;
    }
    
    .modal-header-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .modal-header-icon {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    
    .modal-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: white !important;
        margin: 0;
    }
    
    .modal-subtitle {
        font-size: 0.82rem;
        color: rgba(255,255,255,0.75);
        margin: 3px 0 0;
    }
    
    /* Cuerpo scrolleable */
    .modal-body-scroll {
        padding: 28px;
        overflow-y: auto;
        flex: 1;
    }
    
    /* Botón cerrar modal */
    .modal-close {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.1rem;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    
    .modal-close:hover {
        background: rgba(255,255,255,0.3);
        transform: none;
    }
    
    .modal-close i {
        color: white !important;
    }

    /* 3. BOTÓN PREMIUM (Blanco/Azul - Alto Contraste) */
    .btn-stitch-gold {
        background-color: #ffffff !important;
        color: var(--color-primary) !important;
        font-weight: 700;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }
    .btn-stitch-gold:hover {
        background-color: #f8fafc !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }
    
    /* Form Inputs - Modern */
    .input-modern {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all var(--transition-fast);
        background: white;
        color: var(--color-primary);
        font-weight: 500;
    }
    
    .input-modern:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 4px rgba(22, 38, 96, 0.1);
        transform: translateY(-1px);
    }
    
    .input-modern::placeholder {
        color: #9ca3af;
    }
    
    /* Form Labels */
    label {
        display: block;
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    /* Selectors and Wrappers - Core DT Overrides movidos a global */
    
    /* === MICROANIMACIONES === */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .welcome-icon {
        animation: pulse 2s ease-in-out infinite;
    }
    
    /* Filtros con hover */
    .filter-select {
        transition: all 0.2s ease;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .filter-select:hover {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(22, 38, 96, 0.1);
    }
    
    .filter-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(22, 38, 96, 0.15);
        outline: none;
    }
    
    /* Botón limpiar */
    .btn-clear-filters {
        transition: all 0.2s ease;
    }
    
    .btn-clear-filters:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Input de búsqueda mejorado */
    .search-input-wrapper {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 4px 12px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    
    .search-input-wrapper:focus-within {
        background-color: #fff;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(22, 38, 96, 0.1);
    }
    
    .search-input-wrapper input {
        border: none;
        background: transparent;
        width: 100%;
        padding: 8px;
        color: #374151;
        outline: none;
    }
    
    .search-input-wrapper input::placeholder {
        color: #9ca3af;
    }

    /* Ajuste del contenedor de DataTables */
    .top {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .dataTables_filter {
        margin: 0 !important;
    }
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- BANNER ESTANDARIZADO SGP -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-users" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Gestión de Usuarios</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;display:flex;align-items:center;">
                    <i class="ti ti-shield-check" style="margin-right: 6px;"></i>
                    <span>Control de Accesos · Administración</span>
                    <span id="totalUsersBadge" style="display:inline-block; background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:50px; padding:4px 14px; margin-left:12px; color:white; font-weight:700; font-size:0.8rem; box-shadow:0 4px 6px rgba(0,0,0,0.05); white-space:nowrap;">
                        <?= count($users) ?> usuarios
                    </span>
                </p>
            </div>
        </div>
        <div style="display:flex; gap:16px; z-index:1; align-items:center;">
            <!-- Botón Secundario: Consulta Rápida -->
            <button onclick="openSearchModal()" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 12px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="ti ti-search" style="font-size: 1.1rem;"></i> Consulta Rápida
            </button>

            <!-- Botón Principal: Nuevo Usuario (Destacado en Blanco) -->
            <button onclick="openCreateModal()" style="background: white; color: #1e3a8a; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'">
                <i class="ti ti-user-plus" style="font-size: 1.1rem;"></i> Nuevo Usuario
            </button>
        </div>
    </div>


    <?php
    // ==========================================================
    // OPTIMIZACIÓN: KPIs precalculados en SQL (UserModel->getUsersKPIs)
    // ==========================================================
    $activos   = $kpis['activos'] ?? 0;
    $pasantes  = $kpis['pasantes'] ?? 0;
    $tutores   = count(array_filter($users, fn($u) => strtolower($u['role_name']) === 'tutor'));
    $inactivos = $kpis['inactivos'] ?? 0;
    // ==========================================================
    ?>

    <!-- KPI CARDS ESTANDARIZADAS E INTERACTIVAS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">
        <?php
        $kpis = [
            ['label' => 'Total Activos',      'val' => $activos,   'color' => '#10b981', 'boxShadow' => 'rgba(16,185,129,0.15)', 'icon' => 'ti-user-check',   'sub' => 'operando en sistema', 'filterVal' => 'activo'],
            ['label' => 'Pasantes',           'val' => $pasantes,  'color' => '#2563eb', 'boxShadow' => 'rgba(37,99,235,0.15)',  'icon' => 'ti-school',       'sub' => 'métrica principal',   'filterVal' => 'Pasante'],
            ['label' => 'Tutores Disponibles','val' => $tutores,   'color' => '#f59e0b', 'boxShadow' => 'rgba(245,158,11,0.15)', 'icon' => 'ti-briefcase',    'sub' => 'capacitadores',       'filterVal' => 'Tutor'],
            ['label' => 'Inactivos',          'val' => $inactivos, 'color' => '#dc2626', 'boxShadow' => 'rgba(220,38,38,0.15)',  'icon' => 'ti-user-off',     'sub' => 'requiere atención',   'filterVal' => 'inactivo'],
        ];
        foreach ($kpis as $k): 
        ?>
        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid <?= $k['color'] ?>;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px <?= $k['boxShadow'] ?>'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;"><?= $k['label'] ?></p>
                <i class="ti <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;font-size:1.4rem;opacity:0.7;"></i>
            </div>
            <h2 style="font-size:2.4rem;font-weight:800;color:<?= $k['color'] ?>;margin:0;" data-kpi-value="<?= $k['val'] ?>"><?= $k['val'] ?></h2>
            <p style="color:#94a3b8;font-size:0.8rem;margin:4px 0 0;"><?= $k['sub'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Filtros Rápidos (Cápsulas) movidos a la vista principal -->
    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px; background: white; padding: 16px 24px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);">
        <p style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin: 0; display:flex; align-items:center; gap:6px;">
            <i class="ti ti-filter" style="font-size:1.1rem; color:#2563eb;"></i> Filtrar Rol:
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" id="ventoFilterPills">
            <button class="vento-pill active" data-role="" onclick="filtrarPorRol('Todos')" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                Todos
            </button>
            <button class="vento-pill" data-role="Administrador" onclick="filtrarPorRol('Administrador')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Administradores
            </button>
            <button class="vento-pill" data-role="Tutor" onclick="filtrarPorRol('Tutor')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Tutores
            </button>
            <button class="vento-pill" data-role="Pasante" onclick="filtrarPorRol('Pasante')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Pasantes
            </button>
        </div>
    </div>

    <!-- BLOQUE 3: TABLA DE DATOS (Clean Card con Footer) -->
    <div class="card border-0 shadow-soft rounded-xl overflow-hidden">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover align-middle mb-0" style="width:100%; opacity: 0; transition: opacity 0.4s ease-in-out;">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="px-4 py-3 border-0">Nombre</th>
                        <th class="px-4 py-3 border-0">Rol</th>
                        <th class="px-4 py-3 border-0">Adscripción / Institución</th>
                        <th class="px-4 py-3 border-0">Estado</th>
                        <th class="px-4 py-3 border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php foreach ($users as $user):
                        $inicial = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
                    ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="dt-name-cell">
                                <div class="dt-avatar"><?= $inicial ?></div>
                                <div>
                                    <div class="dt-cell-primary"><?= htmlspecialchars($user['name']) ?></div>
                                    <div class="dt-cell-secondary"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <?php 
                                $rolColor = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                if ($user['role_name'] == 'Administrador') $rolColor = 'bg-primary-subtle text-primary border border-primary-subtle';
                                elseif ($user['role_name'] == 'Tutor') $rolColor = 'bg-warning-subtle text-warning border border-warning-subtle';
                                elseif ($user['role_name'] == 'Pasante') $rolColor = 'bg-info-subtle text-info border border-info-subtle';
                            ?>
                            <span class="badge rounded-pill <?= $rolColor ?> px-3 py-2 fw-bold">
                                <?= htmlspecialchars($user['role_name']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <?php 
                                // Lógica Quirúrgica: Depto para Admin/Tutor, Institución para Pasante
                                $infoContextual = '';
                                $icon = 'ti-building';
                                
                                if (in_array($user['role_id'], [1, 2])) {
                                    $infoContextual = $user['departamento_nombre'] ?? '';
                                    $icon = 'ti-hierarchy-2';
                                } else {
                                    $infoContextual = $user['institucion_nombre'] ?? '';
                                    $icon = 'ti-school';
                                }
                            ?>
                            <?php if (!empty($infoContextual)): ?>
                                <span class="dt-cell-truncate text-dark fw-medium" title="<?= htmlspecialchars($infoContextual) ?>">
                                    <i class="ti <?= $icon ?> text-muted me-1"></i>
                                    <?= htmlspecialchars($infoContextual) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small italic">
                                    <i class="ti ti-alert-circle me-1"></i> Sin asignar
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php
                                $estadoClass = $user['estado'] === 'activo'
                                    ? 'bg-success-subtle text-success border border-success-subtle'
                                    : 'bg-danger-subtle text-danger border border-danger-subtle';
                            ?>
                            <span class="badge rounded-pill <?= $estadoClass ?> px-3 py-2 fw-bold">
                                <?= ucfirst($user['estado']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center align-middle">
                            <div class="d-flex justify-content-center" style="gap: 12px;">
                                <button class="btn btn-sm border-0 shadow-sm transition-all" data-bs-toggle="tooltip" title="Ver Perfil" onclick="SGPModal.verUsuario(<?= $user['id'] ?>)" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2563eb; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-eye fs-5 text-white"></i>
                                </button>
                                
                                <button class="btn btn-sm border-0 shadow-sm transition-all" data-bs-toggle="tooltip" title="Editar Usuario" onclick="editUser('<?= UrlSecurity::encrypt($user['id']) ?>')" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #f59e0b; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-pencil fs-5 text-white"></i>
                                </button>
                                
                                <button class="btn btn-sm border-0 shadow-sm transition-all" data-bs-toggle="tooltip" title="Cambiar Clave" onclick="resetUser('<?= UrlSecurity::encrypt($user['id']) ?>')" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #64748b; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-key fs-5 text-white"></i>
                                </button>
                                
                                <?php if($user['estado'] == 'activo'): ?>
                                    <button class="btn btn-sm border-0 shadow-sm transition-all" data-bs-toggle="tooltip" title="Desactivar" onclick="toggleUser('<?= UrlSecurity::encrypt($user['id']) ?>', 'desactivar')" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #dc2626; color: #ffffff; border-radius: 6px !important;">
                                        <i class="ti ti-user-off fs-5 text-white"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm border-0 shadow-sm transition-all" data-bs-toggle="tooltip" title="Activar" onclick="toggleUser('<?= UrlSecurity::encrypt($user['id']) ?>', 'activar')" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #16a34a; color: #ffffff; border-radius: 6px !important;">
                                        <i class="ti ti-user-check fs-5 text-white"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top border-light p-3">
            <!-- DataTables pagination will be injected here -->
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon">
                    <i class="ti ti-user-plus"></i>
                </div>
                <div>
                    <h2 class="modal-title">Crear Usuario</h2>
                    <p class="modal-subtitle">Complete los datos del nuevo usuario</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeCreateModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <div class="modal-body-scroll">
        <form id="createUserForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Nombre *
                </label>
                <input type="text" name="nombre" id="nombreCreate" required class="input-modern" 
                       placeholder="Juan" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Apellido *
                </label>
                <input type="text" name="apellido" id="apellidoCreate" required class="input-modern" 
                       placeholder="Pérez" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Cédula * <small>(Base para contraseña temporal)</small>
                </label>
                <input type="text" name="cedula" id="cedulaCreate" required 
                       pattern="[0-9]{7,8}" 
                       class="input-modern" 
                       placeholder="12345678"
                       title="Entre 7 y 8 dígitos">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Correo *
                </label>
                <input type="email" name="correo" id="correoCreate" required class="input-modern validate-email" placeholder="usuario@ejemplo.com" autocomplete="off">
                <div class="email-feedback" style="min-height: 20px; font-size: 0.85rem; margin-top: 4px; font-weight: 600;"></div>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Rol *
                </label>
                <select name="rol_id" id="create_rol_id" required class="input-modern">
                    <option value="">Seleccione...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->id ?>"><?= htmlspecialchars($role->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Departamento (solo para Tutores) -->
            <div class="form-group" id="departamento-group" style="margin-bottom: 24px; display: none;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Departamento *
                </label>
                <select name="departamento_id" id="create_departamento_id" class="input-modern">
                    <option value="">Seleccione...</option>
                    <?php foreach ($departamentos as $depto): ?>
                        <option value="<?= $depto->id ?>"><?= htmlspecialchars($depto->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;background:linear-gradient(135deg,#172554 0%,#1e3a8a 100%);color:white;padding:14px;font-size:1rem;">
                <i class="ti ti-check"></i> Crear Usuario
            </button>
        </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon">
                    <i class="ti ti-edit"></i>
                </div>
                <div>
                    <h2 class="modal-title">Editar Usuario</h2>
                    <p class="modal-subtitle">Modifique los datos del usuario seleccionado</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <div class="modal-body-scroll">
            <form id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_nombres" class="input-modern" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Apellido</label>
                    <input type="text" name="apellido" id="edit_apellidos" class="input-modern" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Cédula</label>
                    <input type="text" name="cedula" id="edit_cedula" class="input-modern" required pattern="[0-9]{7,8}">
                </div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" id="edit_correo" class="input-modern" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Rol</label>
                    <select name="rol_id" id="edit_rol_id" class="input-modern" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->id ?>"><?= htmlspecialchars($role->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Departamento (Solo para Tutores) -->
                <div id="edit-departamento-group" class="form-group" style="margin-bottom: 24px; display: none;">
                    <label>Departamento</label>
                    <select name="departamento_id" id="edit_departamento_id" class="input-modern">
                        <option value="">Seleccione...</option>
                        <?php foreach ($departamentos as $depto): ?>
                            <option value="<?= $depto->id ?>"><?= htmlspecialchars($depto->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;background:linear-gradient(135deg,#172554 0%,#1e3a8a 100%);color:white;padding:14px;font-size:1rem;">
                    <i class="ti ti-device-floppy"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Toast: usando NotificationService global (main_layout.php)
    
    // Initialize DataTable
    $(document).ready(function() {
        var dtOptions = {
            language: {
                url: '<?= URLROOT ?>/assets/libs/datatables/es-ES.json'
            },
            dom: '<"top"f>rt<"bottom"ip><"clear">',
            order: [[0, 'asc']], // Ordenar por Nombre por defecto
            responsive: true,
            pageLength: 10,
            columnDefs: [
                { responsivePriority: 1, targets: 1 },   // Nombre — siempre visible
                { responsivePriority: 2, targets: -1 },  // Acciones — siempre visible
                { responsivePriority: 3, targets: 4 },   // Estado
                { responsivePriority: 4, targets: 2 },   // Rol
                { responsivePriority: 8, targets: 3 },   // Institución
                { orderable: false, targets: -1 }
            ],
            drawCallback: function() {
                var api = this.api();
                if(api.rows({filter: 'applied'}).count) {
                    var count = api.rows({filter: 'applied'}).count();
                    $('#resultCount').text(count);
                }
                
                // Initialize Bootstrap Tooltips for the newly drawn rows
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            },
            initComplete: function(settings, json) {
                $(this.api().table().node()).css('opacity', '1');
            }
        };

        // Se inyecta solo la 'f' (filtro) en la parte superior
        dtOptions.dom = '<"top"f>rt<"bottom"ip><"clear">';

        // SGP-FIX DATATABLES CELLINDEX
        var $tablaUsers = $('#usersTable');
        if ($tablaUsers.length && !$.fn.DataTable.isDataTable($tablaUsers)) {
            window.table = $tablaUsers.DataTable(dtOptions);
        } else if ($tablaUsers.length && $.fn.DataTable.isDataTable($tablaUsers)) {
            window.table = $tablaUsers.DataTable();
            window.table.draw(false);
        }
        
        // Función para actualizar chips de filtros activos
        function updateChips() {
            var chips = '';
            var role = $('#filterRole').val();
            var status = $('#filterStatus').val();
            var search = $('#globalSearch').val();

            if (role) chips += `<span style="background:#eff6ff;color:#2563eb;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                <i class="ti ti-user-circle"></i>${role}
                <i class="ti ti-x" style="cursor:pointer;font-size:0.75rem;" onclick="$('#filterRole').val('').trigger('change')"></i></span>`;
            if (status) chips += `<span style="background:#f0fdf4;color:#16a34a;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                <i class="ti ti-circle-check"></i>${status.charAt(0).toUpperCase()+status.slice(1)}
                <i class="ti ti-x" style="cursor:pointer;font-size:0.75rem;" onclick="$('#filterStatus').val('').trigger('change')"></i></span>`;
            if (search) chips += `<span style="background:#faf5ff;color:#7c3aed;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                <i class="ti ti-search"></i>"${search}"
                <i class="ti ti-x" style="cursor:pointer;font-size:0.75rem;" onclick="$('#clearSearch').click()"></i></span>`;
            $('#activeFilterChips').html(chips);
        }

        // Inicializar contador
        $('#resultCount').text(table.rows().count());

        // 1. Global Search — mostrar/ocultar botón X + actualizar chips
        $('#globalSearch').on('input keyup change', function() {
            table.search(this.value).draw();
            document.getElementById('clearSearch').style.display = this.value ? 'block' : 'none';
            updateChips();
        });

        // 2. Role Filter (Column 2)
        $('#filterRole').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(2).search(val ? val : '', true, false).draw();
            updateChips();
        });

        // 3. Status Filter (Column 4)
        $('#filterStatus').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(4).search(val ? val : '', true, false).draw();
            updateChips();
        });

        // 4. Clear Filters Button
        $('#clearFilters').on('click', function() {
            $('#globalSearch').val('');
            $('#filterRole').val('');
            $('#filterStatus').val('');
            document.getElementById('clearSearch').style.display = 'none';
            table.search('').columns().search('').draw();
            updateChips();
        });

        // Prevent form submission on enter in search
        $('#globalSearch').on('keypress', function(e) {
            if(e.which == 13) { e.preventDefault(); return false; }
        });
    });
    
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
    }
    
    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
        document.getElementById('createUserForm').reset();
    }
    
    // Create User Form Submit
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Creando...';
        btn.disabled = true;
        
        fetch('<?= URLROOT ?>/users/create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Extraer la contraseña temporal del mensaje
                // El mensaje tiene formato: "Usuario creado exitosamente. Contraseña temporal: Sgp.XXXXXXXX"
                const match = data.message.match(/Contraseña temporal:\s*(\S+)/);
                const tempPass = match ? match[1] : '';

                closeCreateModal();

                Swal.fire({
                    icon: 'success',
                    title: '✅ Usuario Creado',
                    html: `
                        <p style="color:#374151;margin-bottom:12px;">El usuario fue creado correctamente.</p>
                        <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:16px;margin:12px 0;">
                            <p style="font-size:0.85rem;color:#0369a1;font-weight:600;margin:0 0 8px;">🔑 Contraseña Temporal</p>
                            <p style="font-size:1.5rem;font-weight:800;color:#162660;letter-spacing:2px;margin:0;font-family:monospace;">
                                ${tempPass}
                            </p>
                        </div>
                        <p style="font-size:0.8rem;color:#6b7280;margin-top:8px;">
                            <i>El usuario deberá cambiarla en su primer inicio de sesión.</i>
                        </p>
                    `,
                    confirmButtonColor: '#162660',
                    confirmButtonText: '<i class="ti ti-check"></i> Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    location.reload();
                });
            } else {
                NotificationService.error(data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            NotificationService.error('Error al crear usuario');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
    
    // Edit User Functions
    function editUser(encryptedId) {
        fetch('<?= URLROOT ?>/users/edit/' + encryptedId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill all fields
                    document.getElementById('edit_id').value = data.data.id;
                    document.getElementById('edit_nombres').value = data.data.nombres || '';
                    document.getElementById('edit_apellidos').value = data.data.apellidos || '';
                    document.getElementById('edit_cedula').value = data.data.cedula || '';
                    document.getElementById('edit_correo').value = data.data.correo;
                    document.getElementById('edit_rol_id').value = data.data.rol_id;
                    
                    // Handle department visibility and value
                    const editDeptoGroup = document.getElementById('edit-departamento-group');
                    const editDeptoSelect = document.getElementById('edit_departamento_id');
                    
                    if (data.data.rol_id == '2') { // Tutor
                        editDeptoGroup.style.display = 'block';
                        editDeptoSelect.required = true;
                        editDeptoSelect.value = data.data.departamento_id || '';
                    } else {
                        editDeptoGroup.style.display = 'none';
                        editDeptoSelect.required = false;
                        editDeptoSelect.value = '';
                    }
                    
                    document.getElementById('editModal').classList.add('active');
                } else {
                    NotificationService.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                NotificationService.error('Error al cargar datos del usuario');
            });
    }



    function closeViewModal() {
        document.getElementById('modalVerUsuario').classList.remove('active');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    
    // Create User Form Submit
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 1. Basic Frontend Validation
        const cedula = this.querySelector('[name="cedula"]').value.trim();
        const correo = this.querySelector('[name="correo"]').value.trim();
        
        if (!/^[0-9]{7,8}$/.test(cedula)) {
            NotificationService.error('La cédula debe contener entre 7 y 8 dígitos numéricos.');
            return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            NotificationService.error('Por favor, ingresa un correo electrónico válido.');
            return;
        }

        // 2. Loading UI State
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Guardando...';
        btn.disabled = true;
        
        // 3. AJAX / Fetch call
        fetch('<?= URLROOT ?>/users/create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // 4. Promise Resolution (Success/Error)
            if (data.success) {
                NotificationService.success(data.message);
                this.reset(); // Clear form
                closeCreateModal();
                // Silent / Fast reload (or DT refresh if configured)
                setTimeout(() => location.reload(), 1500); 
            } else {
                NotificationService.error(data.message || 'Error al crear usurio.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            NotificationService.error('Fallo en la conexión al servidor.');
        })
        .finally(() => {
            // Restore button
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    // Edit User Form Submit
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Actualizando...';
        btn.disabled = true;
        
        fetch('<?= URLROOT ?>/users/update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                NotificationService.success(data.message);
                closeEditModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                NotificationService.error(data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });
    
    // Reset User Password
    function resetUser(encryptedId) {
        Swal.fire({
            title: '¿Restablecer credenciales?',
            html: 'La contraseña volverá a ser <code>Sgp.[Cédula]*</code><br>El usuario deberá cambiarla en el próximo inicio de sesión.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= URLROOT ?>/users/reset/' + encryptedId, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Restablecido!',
                            text: data.message,
                            confirmButtonColor: '#162660'
                        });
                    } else {
                        NotificationService.error(data.message);
                    }
                });
            }
        });
    }
    
    // Toggle Activar / Desactivar Usuario
    function toggleUser(encryptedId, accion) {
        const esActivar = accion === 'activar';
        Swal.fire({
            title: esActivar ? '¿Activar usuario?' : '¿Desactivar usuario?',
            text: esActivar
                ? 'El usuario podrá iniciar sesión en el sistema nuevamente.'
                : 'El usuario no podrá iniciar sesión. Puedes reactivarlo cuando quieras.',
            icon: esActivar ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: esActivar ? '#16a34a' : '#ef4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: esActivar ? '✅ Sí, activar' : '🚫 Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= URLROOT ?>/users/toggleStatus/' + encryptedId, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        NotificationService.success(data.message);
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        NotificationService.error(data.message);
                    }
                })
                .catch(() => NotificationService.error('Error de conexión'));
            }
        });
    }
    
    // Close modals on outside click
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
    
    // Department field visibility control (Create Modal)
    const createRolSelect = document.getElementById('create_rol_id');
    const departamentoGroup = document.getElementById('departamento-group');
    const departamentoSelect = document.getElementById('create_departamento_id');

    if (createRolSelect) {
        createRolSelect.addEventListener('change', function() {
            if (this.value == '2') { // Tutor
                departamentoGroup.style.display = 'block';
                departamentoSelect.required = true;
            } else {
                departamentoGroup.style.display = 'none';
                departamentoSelect.required = false;
                departamentoSelect.value = '';
            }
        });
    }
    
    // Department field visibility control (Edit Modal)
    const editRolSelect = document.getElementById('edit_rol_id');
    const editDepartamentoGroup = document.getElementById('edit-departamento-group');
    const editDepartamentoSelect = document.getElementById('edit_departamento_id');

    if (editRolSelect) {
        editRolSelect.addEventListener('change', function() {
            if (this.value == '2') { // Tutor
                editDepartamentoGroup.style.display = 'block';
                editDepartamentoSelect.required = true;
            } else {
                editDepartamentoGroup.style.display = 'none';
                editDepartamentoSelect.required = false;
                editDepartamentoSelect.value = '';
            }
        });
    }
</script>

<!-- Search Modal (Clonado de Pasantes) -->
<div id="searchModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-search" style="margin-right:8px;"></i>Consulta Rápida</h2>
                <p>Encuentra usuarios en el sistema por nombre, correo o rol.</p>
            </div>
            <button class="btn-close-modal" onclick="closeSearchModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            
            <div class="form-group">
                <label class="form-label" style="display:flex; align-items:center; gap:6px;"><i class="ti ti-users" style="font-size:1.1rem;"></i> Búsqueda General</label>
                <div style="position: relative;">
                    <i class="ti ti-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; color: #94a3b8; pointer-events: none;"></i>
                    <input type="text" class="form-input" id="ventoSearchInput" placeholder="Buscar por nombre o correo..." style="padding-left: 44px; padding-right: 44px;">
                    <button id="ventoClearSearch" onclick="document.getElementById('ventoSearchInput').value=''; document.getElementById('ventoSearchInput').dispatchEvent(new Event('input'))" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: none; background: #e2e8f0; border: none; width: 24px; height: 24px; border-radius: 50%; color: #64748b; cursor: pointer; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1';this.style.color='#334155'" onmouseout="this.style.background='#e2e8f0';this.style.color='#64748b'">
                        <i class="ti ti-x" style="font-size: 0.8rem;"></i>
                    </button>
                </div>
            </div>

            <!-- Contenedor de Resultados (Búsqueda Interna) -->
            <div id="ventoSearchResults" style="margin-top: 16px; max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                <div style="text-align:center; padding: 20px; color:#94a3b8; font-size:0.9rem;">
                    Escribe para buscar...
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- DataTables Buttons Assets -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
// 1. Helper para obtener la tabla
function getSGPDataTable() {
    return typeof table !== 'undefined' ? table : (typeof miTabla !== 'undefined' ? miTabla : null);
}

function obtenerIndiceColumna(dt, nombreColumna) {
    let indice = -1;
    dt.columns().every(function() {
        let titulo = this.header().textContent.trim().toUpperCase();
        if (titulo === nombreColumna.toUpperCase()) {
            indice = this.index();
        }
    });
    return indice;
}

window.filtrarPorRol = function(rolSeleccionado) {
    let dt = getSGPDataTable();
    if (!dt) return;

    let colIndex = obtenerIndiceColumna(dt, 'ROL');
    if (colIndex === -1) {
        console.error('No se encontró la columna ROL');
        return;
    }

    if (!rolSeleccionado || rolSeleccionado === 'Todos') {
        dt.column(colIndex).search('').draw(); 
        return;
    } 
    
    let rolStr = String(rolSeleccionado).toLowerCase();
    let rolBuscar = 'Pasante'; 
    if (rolStr.includes('admin')) rolBuscar = 'Administrador';
    if (rolStr.includes('tutor')) rolBuscar = 'Tutor';

    dt.column(colIndex).search('^\\s*' + rolBuscar + '\\s*$', true, false, true).draw();
};

    function openSearchModal() {
        document.getElementById('searchModal').classList.add('active');
        setTimeout(() => {
            const searchInput = document.getElementById('ventoSearchInput');
            if(searchInput) searchInput.focus();
        }, 100);
    }
    
    function closeSearchModal() {
        document.getElementById('searchModal').classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // LÓGICA DEL BUSCADOR "CONSULTA RÁPIDA" Y PILLS
        // ==========================================
        
        // Pasamos la data de PHP a JS para el buscador interno
        const allUsersData = <?= json_encode($users) ?>;
        console.log("Datos de usuarios cargados:", allUsersData);
        
        // Damos un pequeño respiro para que DataTables termine de inicializarse en el DOM
        setTimeout(() => {
            const ventoInput = document.getElementById('ventoSearchInput');
            const ventoClear = document.getElementById('ventoClearSearch');
            const ventoResults = document.getElementById('ventoSearchResults');
            const ventoPills = document.querySelectorAll('.vento-pill');
            
            // Usamos la instancia existente de la tabla (inicializada arriba)
            const dtInstance = $('#usersTable').DataTable();
            
            // 1. Lógica de Consulta Rápida (Modal Independiente)
            if (ventoInput && ventoResults && allUsersData) {
                ventoInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    ventoResults.innerHTML = ''; // Limpiamos resultados anteriores

                    if (query.length === 0) {
                        ventoResults.innerHTML = '<p style="text-align:center; color:#94a3b8; padding: 20px;">Escribe para buscar...</p>';
                        if (ventoClear) ventoClear.style.display = 'none';
                        return;
                    } else {
                        if (ventoClear) ventoClear.style.display = 'flex';
                    }

                    // Filtramos el arreglo de manera segura (soportando varios nombres de columnas)
                    const filtered = allUsersData.filter(u => {
                        const nombre = String(u.nombre || u.name || '').toLowerCase();
                        const correo = String(u.correo || u.email || '').toLowerCase();
                        const cedula = String(u.cedula || u.ci || u.documento || '').toLowerCase();
                        
                        return nombre.includes(query) || correo.includes(query) || cedula.includes(query);
                    });

                    if (filtered.length === 0) {
                        ventoResults.innerHTML = '<p style="text-align:center; color:#94a3b8; padding: 20px;">No se encontraron resultados para "'+query+'".</p>';
                        return;
                    }

                    // Renderizamos las tarjetas HTML
                    filtered.forEach(u => {
                        // Obtenemos los valores de forma segura
                        const uNombre = u.nombre || u.name || 'Usuario';
                        const uCorreo = u.correo || u.email || 'Sin correo';
                        const uCedula = u.cedula || u.ci || '-';
                        const uRol = u.rol || u.role_name || 'Desconocido';
                        const inicial = uNombre.charAt(0).toUpperCase();

                        const card = document.createElement('div');
                        card.style = "display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; transition: all 0.2s; margin-bottom: 8px; cursor:pointer;";
                        card.onmouseover = function() {this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'};
                        card.onmouseout = function() {this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'};
                        card.onclick = function() { closeSearchModal(); SGPModal.verUsuario(u.id); };
                        
                        card.innerHTML = `
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.1rem; box-shadow: 0 4px 6px rgba(37,99,235,0.2);">
                                    ${inicial}
                                </div>
                                <div>
                                    <h4 style="margin:0; font-size:0.95rem; font-weight:700; color:#0f172a;">${uNombre}</h4>
                                    <p style="margin:0; font-size:0.8rem; color:#64748b;">
                                        <i class="ti ti-id"></i> ${uCedula} &nbsp;·&nbsp; ${uCorreo}
                                    </p>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span style="font-size:0.75rem; background:#e2e8f0; color:#475569; padding:4px 10px; border-radius:20px; font-weight:700; text-transform:uppercase;">${uRol}</span>
                            </div>
                        `;
                        ventoResults.appendChild(card);
                    });
                });
            }

            // 2. Filtros Rápidos (Pills) reubicados en la vista principal
            if (dtInstance && ventoPills.length > 0) {
                ventoPills.forEach(pill => {
                    pill.addEventListener('click', function() {
                        // A. Gestionar visual
                        ventoPills.forEach(p => {
                            p.classList.remove('active');
                            p.style.background = '#f1f5f9';
                            p.style.color = '#475569';
                        });
                        
                        this.classList.add('active');
                        this.style.background = '#2563eb';
                        this.style.color = 'white';
                        
                        // La llamada a filtrarPorRol ya se hace mediante el onclick en el HTML.
                        // Aquí solo mantenemos la lógica visual si es necesario.
                    });
                });
            }
        }, 500); 
    });
</script>
