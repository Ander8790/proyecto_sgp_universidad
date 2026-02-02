

<style>
    /* =====================================================
       MODERN USERS MANAGEMENT STYLES
       ===================================================== */
    
    /* Page Header - Modern with gradient */
    .page-header-modern {
        background: linear-gradient(135deg, 
            var(--color-primary) 0%, 
            #0d1a3d 100%
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
    
    /* Modal Styles - Enhanced */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(22, 38, 96, 0.6);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.3s;
    }
    
    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 36px;
        max-width: 520px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s;
        box-shadow: 0 20px 60px rgba(22, 38, 96, 0.3);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--color-bg);
    }
    
    .modal-title {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--color-primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .modal-close {
        background: var(--color-bg);
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: var(--text-body);
        padding: 0;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all var(--transition-fast);
    }
    
    .modal-close:hover {
        background: var(--color-primary);
        color: white;
        transform: rotate(90deg);
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

<div class="dashboard-container">
    <!-- BANNER MEJORADO (Estilo Dashboard) -->
    <div class="welcome-banner welcome-banner-compact mb-4">
        <div class="welcome-icon">
            <i class="ti ti-users"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">Gestión de Usuarios</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-shield-check"></i>
                    <span>Control de Accesos</span>
                    <span class="subtitle-separator">-</span>
                    <span>Administración</span>
                </p>
            </div>
        </div>
        
        <div class="welcome-meta">
            <div class="welcome-stats">
                <i class="ti ti-users-group"></i>
                <span id="totalUsers">0</span> usuarios
            </div>
        </div>
        
        <button onclick="openCreateModal()" class="btn btn-stitch-gold shadow-sm" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
            <i class="ti ti-user-plus"></i>
            Nuevo Usuario
        </button>
    </div>

    <!-- BARRA DE BÚSQUEDA Y FILTROS FUNCIONALES -->
    <div class="card border-0 shadow-sm mb-4 rounded-3">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Búsqueda Global -->
                <div class="col-md-5">
                    <div class="search-input-wrapper">
                        <i class="ti ti-search text-muted"></i>
                        <input type="text" id="globalSearch" placeholder="Buscar por nombre, cédula o institución...">
                    </div>
                </div>

                <!-- Filtros -->
                <div class="col-md-7 d-flex justify-content-end gap-2 flex-wrap">
                    <select id="filterRole" class="form-select filter-select" style="width: auto; min-width: 150px;">
                        <option value="">Todos los Roles</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Tutor">Tutor</option>
                        <option value="Pasante">Pasante</option>
                    </select>
                    
                    <select id="filterStatus" class="form-select filter-select" style="width: auto; min-width: 150px;">
                        <option value="">Todos los Estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    
                    <button id="clearFilters" class="btn btn-outline-secondary btn-clear-filters">
                        <i class="ti ti-filter-off"></i> Limpiar
                    </button>
                </div>
            </div>
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
                            <button onclick="editUser('<?= UrlSecurity::encrypt($user['id']) ?>')" class="btn-action btn-edit" title="Editar">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button onclick="resetUser('<?= UrlSecurity::encrypt($user['id']) ?>')" class="btn-action btn-reset" title="Resetear">
                                <i class="ti ti-key"></i>
                            </button>
                            <button onclick="deleteUser('<?= UrlSecurity::encrypt($user['id']) ?>')" class="btn-action btn-delete" title="Eliminar">
                                <i class="ti ti-trash"></i>
                            </button>
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
            <h2 class="modal-title">Crear Usuario</h2>
            <button class="modal-close" onclick="closeCreateModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
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
            
            <button type="submit" class="btn-primary">
                <i class="ti ti-check"></i> Crear Usuario
            </button>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Editar Usuario</h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <form id="editUserForm" method="POST">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Nombres *
                </label>
                <input type="text" name="nombres" id="edit_nombres" required class="input-modern"
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Apellidos *
                </label>
                <input type="text" name="apellidos" id="edit_apellidos" required class="input-modern"
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                       maxlength="100"
                       title="Solo letras y espacios">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Cédula *
                </label>
                <input type="text" name="cedula" id="edit_cedula" required class="input-modern" 
                       pattern="[0-9]{7,8}"
                       title="Entre 7 y 8 dígitos">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Correo *
                </label>
                <input type="email" name="correo" id="edit_correo" required class="input-modern validate-email" autocomplete="off">
                <div class="email-feedback" style="min-height: 20px; font-size: 0.85rem; margin-top: 4px; font-weight: 600;"></div>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Rol *
                </label>
                <select name="rol_id" id="edit_rol_id" required class="input-modern">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->id ?>"><?= htmlspecialchars($role->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Departamento (solo para Tutores) -->
            <div class="form-group" id="edit-departamento-group" style="margin-bottom: 24px; display: none;">
                <label style="display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                    Departamento *
                </label>
                <select name="departamento_id" id="edit_departamento_id" class="input-modern">
                    <option value="">Seleccione...</option>
                    <?php foreach ($departamentos as $depto): ?>
                        <option value="<?= $depto->id ?>"><?= htmlspecialchars($depto->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="ti ti-check"></i> Actualizar Usuario
            </button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="<?= URLROOT ?>/css/datatables.min.css">
<script src="<?= URLROOT ?>/js/jquery.min.js"></script>
<script src="<?= URLROOT ?>/js/dataTables.min.js"></script>
<script src="<?= URLROOT ?>/js/notyf.min.js"></script>

<script>
    // Initialize Notyf
    const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        ripple: true
    });
    
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
                // Update total users count on every draw
                $('#totalUsers').text(this.api().rows({filter: 'applied'}).count());
            }
        });
        
        // --- FUNCTIONAL FILTERS IMPLEMENTATION ---
        
        // 1. Global Search
        $('#globalSearch').on('keyup change clear', function() {
            table.search(this.value).draw();
        });
        
        // 2. Role Filter (Column 3)
        $('#filterRole').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(3).search(val ? val : '', true, false).draw();
        });
        
        // 3. Status Filter (Column 5)
        $('#filterStatus').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(5).search(val ? val : '', true, false).draw();
        });
        
        // 4. Clear Filters Button
        $('#clearFilters').on('click', function() {
            $('#globalSearch').val('');
            $('#filterRole').val('');
            $('#filterStatus').val('');
            
            table.search('').columns().search('').draw();
            $('#totalUsers').text(table.rows().count());
        });
        
        // Prevent form submission on enter in search
        $('#globalSearch').on('keypress', function(e) {
            if(e.which == 13) {
                e.preventDefault();
                return false;
            }
        });
        
        // Set initial count
        $('#totalUsers').text(table.rows().count());
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
                notyf.success(data.message);
                closeCreateModal();
                setTimeout(() => location.reload(), 2500);
            } else {
                notyf.error(data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notyf.error('Error al crear usuario');
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
                    notyf.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notyf.error('Error al cargar datos del usuario');
            });
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
                notyf.success(data.message);
                closeEditModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                notyf.error(data.message);
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
                        notyf.error(data.message);
                    }
                });
            }
        });
    }
    
    // Delete User
    function deleteUser(encryptedId) {
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: 'El usuario será desactivado en el sistema',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= URLROOT ?>/users/delete/' + encryptedId, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        notyf.error(data.message);
                    }
                });
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
