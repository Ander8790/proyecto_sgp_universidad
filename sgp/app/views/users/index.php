

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
    
    /* DataTable Styling */
    table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0;
    }
    
    table.dataTable thead th {
        background: linear-gradient(180deg, #f8f9fa 0%, #f1f3f5 100%);
        color: var(--color-primary);
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 16px;
        border-bottom: 2px solid var(--color-primary);
    }
    
    table.dataTable tbody tr {
        transition: all var(--transition-fast);
    }
    
    table.dataTable tbody tr:hover {
        background: linear-gradient(90deg, 
            rgba(22, 38, 96, 0.02) 0%, 
            rgba(22, 38, 96, 0.05) 50%,
            rgba(22, 38, 96, 0.02) 100%
        );
        transform: scale(1.005);
    }
    
    table.dataTable tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
        color: var(--color-primary);
        font-weight: 500;
    }
    
    /* Action Buttons - Refined */
    .btn-action {
        padding: 8px 12px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 0 3px;
        min-width: 38px;
        height: 38px;
        position: relative;
        overflow: hidden;
    }
    
    .btn-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.3s, height 0.3s;
    }
    
    .btn-action:hover::before {
        width: 100%;
        height: 100%;
    }
    
    .btn-action i {
        position: relative;
        z-index: 1;
        font-size: 16px;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }
    
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .btn-reset {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    
    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    
    .btn-delete {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }
    
    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    /* Status Badge - Enhanced */
    .badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .badge-activo {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.1) 100%);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .badge-activo::before {
        background: #10B981;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
    }
    
    .badge-inactivo {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.1) 100%);
        color: #DC2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .badge-inactivo::before {
        background: #EF4444;
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
    }
    
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
    
    /* DataTable Search Input */
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 16px;
        margin-left: 8px;
        transition: all var(--transition-fast);
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 4px rgba(22, 38, 96, 0.1);
    }
    
    /* DataTable Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 2px;
        transition: all var(--transition-fast) !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--color-primary) !important;
        color: white !important;
        border-color: var(--color-primary) !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--color-accent) !important;
        color: var(--color-primary) !important;
        border-color: var(--color-accent) !important;
    }
    
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
    // LÓGICA TEMPORAL PARA KPIs (Basado en el array $users actual)
    // ==========================================================
    $totalUsers = count($users);
    $activos = count(array_filter($users, fn($u) => strtolower($u['estado']) === 'activo'));
    $inactivos = count(array_filter($users, fn($u) => strtolower($u['estado']) === 'inactivo'));
    $admins = count(array_filter($users, fn($u) => strtolower($u['role_name']) === 'administrador'));
    // ==========================================================
    ?>

    <!-- KPI CARDS ESTANDARIZADAS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">
        <?php
        $kpis = [
            ['label' => 'Total Usuarios', 'val' => $totalUsers, 'color' => '#2563eb', 'boxShadow' => 'rgba(37,99,235,0.15)',  'icon' => 'ti-users',        'sub' => 'registrados'],
            ['label' => 'Activos',        'val' => $activos,    'color' => '#10b981', 'boxShadow' => 'rgba(16,185,129,0.15)', 'icon' => 'ti-user-check',   'sub' => 'en el sistema'],
            ['label' => 'Administradores','val' => $admins,     'color' => '#f59e0b', 'boxShadow' => 'rgba(245,158,11,0.15)', 'icon' => 'ti-shield-check', 'sub' => 'acceso total'],
            ['label' => 'Inactivos',      'val' => $inactivos,  'color' => '#dc2626', 'boxShadow' => 'rgba(220,38,38,0.15)',  'icon' => 'ti-user-off',     'sub' => 'sin acceso'],
        ];
        foreach ($kpis as $k): ?>
        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid <?= $k['color'] ?>;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px <?= $k['boxShadow'] ?>'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;"><?= $k['label'] ?></p>
                <i class="ti <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;font-size:1.4rem;opacity:0.7;"></i>
            </div>
            <h2 style="font-size:2.4rem;font-weight:800;color:<?= $k['color'] ?>;margin:0;"><?= $k['val'] ?></h2>
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
            <button class="vento-pill active" data-role="" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                Todos
            </button>
            <button class="vento-pill" data-role="Administrador" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Administradores
            </button>
            <button class="vento-pill" data-role="Tutor" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Tutores
            </button>
            <button class="vento-pill" data-role="Pasante" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                Pasantes
            </button>
        </div>
    </div>

    <!-- BLOQUE 3: TABLA DE DATOS (Clean Card con Footer) -->
    <div class="card border-0 shadow-soft rounded-xl overflow-hidden">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="px-4 py-3 border-0">ID</th>
                        <th class="px-4 py-3 border-0">Nombre</th>
                        <th class="px-4 py-3 border-0">Correo</th>
                        <th class="px-4 py-3 border-0">Rol</th>
                        <th class="px-4 py-3 border-0">Institución</th>
                        <th class="px-4 py-3 border-0">Estado</th>
                        <th class="px-4 py-3 border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php foreach ($users as $user): ?>
                    <tr style="transition: all 0.2s;">
                        <td class="px-4 py-3 border-bottom" style="border-color: #f1f3f5;"><?= $user['id'] ?></td>
                        <td class="px-4 py-3 border-bottom" style="border-color: #f1f3f5;">
                            <div class="fw-medium text-dark"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td class="px-4 py-3 border-bottom" style="border-color: #f1f3f5;"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-4 py-3 border-bottom" style="border-color: #f1f3f5;">
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($user['role_name']) ?></span>
                        </td>
                        <td class="px-4 py-3 border-bottom text-center" style="border-color: #f1f3f5;">
                            <?php if (!empty($user['institucion_procedencia'])): ?>
                                <span class="text-muted small"><?= htmlspecialchars($user['institucion_procedencia']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 border-bottom" style="border-color: #f1f3f5;">
                            <span class="badge badge-<?= $user['estado'] ?>">
                                <?= ucfirst($user['estado']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 border-bottom text-center" style="border-color: #f1f3f5;">
                            <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                <button onclick="SGPModal.verUsuario(<?= $user['id'] ?>)" class="btn-action" title="Ver perfil" style="background: linear-gradient(135deg, #4b5563 0%, #1f2937 100%); color: white;">
                                    <i class="ti ti-eye"></i>
                                </button>
                                <button onclick="editUser('<?= UrlSecurity::encrypt($user['id']) ?>')" class="btn-action btn-edit" title="Editar usuario">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button onclick="resetUser('<?= UrlSecurity::encrypt($user['id']) ?>')" class="btn-action btn-reset" title="Resetear contraseña">
                                    <i class="ti ti-key"></i>
                                </button>
                                <?php if ($user['estado'] === 'activo'): ?>
                                <button onclick="toggleUser('<?= UrlSecurity::encrypt($user['id']) ?>', 'desactivar')"
                                    class="btn-action btn-delete"
                                    title="Desactivar usuario"
                                    style="background:#ef4444;color:white;border-color:#dc2626;">
                                    <i class="ti ti-user-off"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="toggleUser('<?= UrlSecurity::encrypt($user['id']) ?>', 'activar')"
                                    class="btn-action"
                                    title="Activar usuario"
                                    style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:10px;width:36px;height:36px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all 0.2s;">
                                    <i class="ti ti-user-check"></i>
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
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Nombre *
                </label>
                <input type="text" name="nombre" required class="input-modern" 
                       placeholder="Juan" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Apellido *
                </label>
                <input type="text" name="apellido" required class="input-modern" 
                       placeholder="Pérez" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Cédula * <small>(Base para contraseña temporal)</small>
                </label>
                <input type="text" name="cedula" required 
                       pattern="[0-9]{7,8}" 
                       class="input-modern" 
                       placeholder="12345678"
                       title="Entre 7 y 8 dígitos">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Correo *
                </label>
                <input type="email" name="correo" required class="input-modern validate-email" placeholder="usuario@ejemplo.com" autocomplete="off">
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

<!-- View User Modal -->
<div id="modalVerUsuario" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon">
                    <i class="ti ti-history"></i>
                </div>
                <div>
                    <h2 class="modal-title">Detalles del Usuario</h2>
                    <p class="modal-subtitle">Información completa de la cuenta</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeViewModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <div class="modal-body-scroll">
            <div id="viewDetailsContent" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Se llena vía JS -->
            </div>
            
            <button type="button" class="btn-primary" onclick="closeViewModal()" style="width:100%;justify-content:center;background:linear-gradient(135deg,#172554 0%,#1e3a8a 100%);color:white;padding:14px;font-size:1rem;margin-top:24px;border:none;border-radius:12px;font-weight:600;cursor:pointer;">
                <i class="ti ti-check"></i> Cerrar Vista
            </button>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/dataTables.dataTables.min.css">

<!-- DataTables JS (jQuery y Notyf ya están cargados en main_layout.php) -->
<script src="<?= URLROOT ?>/js/dataTables.min.js"></script>

<script>
    // Toast: usando NotificationService global (main_layout.php)
    
    // Initialize DataTable
    $(document).ready(function() {
        var table = $('#usersTable').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                }
            },
            dom: 'rtip', // Hide default search box
            order: [[0, 'desc']],
            drawCallback: function() {
                // Actualizar contador de resultados
                var count = this.api().rows({filter: 'applied'}).count();
                $('#resultCount').text(count);
            }
        });
        
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

        // 2. Role Filter (Column 3)
        $('#filterRole').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(3).search(val ? val : '', true, false).draw();
            updateChips();
        });

        // 3. Status Filter (Column 5)
        $('#filterStatus').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(5).search(val ? val : '', true, false).draw();
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

    // View User Functions
    function viewUser(encryptedId) {
        fetch('<?= URLROOT ?>/users/obtenerDetalles/' + encryptedId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    const content = `
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Nombre Completo</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.nombre_completo || 'No registrado'}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Rol</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.rol_nombre}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Cédula</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.cedula}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Correo</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.correo}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Teléfono</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.telefono || 'No registrado'}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Género</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.genero_texto}</span>
                        </div>
                        <div class="view-item">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Fecha de Nacimiento</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.fecha_nacimiento_formateada}</span>
                        </div>
                        <div class="view-item" style="grid-column: 1 / -1;">
                            <strong style="display:block;color:#64748b;font-size:0.75rem;text-transform:uppercase;margin-bottom:4px;">Institución de Procedencia</strong>
                            <span style="color:#1e293b;font-weight:600;">${user.nombre_institucion || (user.rol_id == 3 ? 'No registrada' : 'N/A')}</span>
                        </div>
                    `;
                    document.getElementById('viewDetailsContent').innerHTML = content;
                    document.getElementById('modalVerUsuario').classList.add('active');
                } else {
                    NotificationService.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                NotificationService.error('Error al cargar detalles del usuario');
            });
    }

    function closeViewModal() {
        document.getElementById('modalVerUsuario').classList.remove('active');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    
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

<script>
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
            
            // Accedemos directamente a la instancia de DataTables usando el ID de la tabla
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
                        card.style = "display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; transition: all 0.2s; margin-bottom: 8px;";
                        card.onmouseover = function() {this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'};
                        card.onmouseout = function() {this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'};
                        
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
                                <button onclick="closeSearchModal(); SGPModal.verUsuario(${u.id})" style="background:white; border:1px solid #e2e8f0; color:#3b82f6; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'" title="Ver perfil">
                                    <i class="ti ti-eye"></i>
                                </button>
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
                        
                        // B. Filtro DataTables Columna 3
                        const roleToFilter = this.getAttribute('data-role');
                        if (roleToFilter === "") {
                            // Limpiar filtro en columna 3
                            dtInstance.column(3).search('').draw();
                        } else {
                            // Búsqueda exacta con Regex anclado
                            dtInstance.column(3).search('^' + roleToFilter + '$', true, false).draw();
                        }
                    });
                });
            }
        }, 500); 
    });
</script>
