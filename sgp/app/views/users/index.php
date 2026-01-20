<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s;
    }
    
    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-body);
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .modal-close:hover {
        background: #F3F4F6;
    }
    
    /* Action Buttons */
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-edit {
        background: #3B82F6;
        color: white;
    }
    
    .btn-edit:hover {
        background: #2563EB;
    }
    
    .btn-reset {
        background: #F59E0B;
        color: white;
    }
    
    .btn-reset:hover {
        background: #D97706;
    }
    
    .btn-delete {
        background: #EF4444;
        color: white;
    }
    
    .btn-delete:hover {
        background: #DC2626;
    }
    
    /* Status Badge */
    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-activo {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }
    
    .badge-inactivo {
        background: rgba(239, 68, 68, 0.1);
        color: #DC2626;
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">
                Gestión de Usuarios
            </h1>
            <p style="color: var(--text-body);">Administra los usuarios del sistema</p>
        </div>
        <button onclick="openCreateModal()" class="btn-primary">
            <i class="ti ti-user-plus"></i> Nuevo Usuario
        </button>
    </div>
    
    <!-- Users Table -->
    <div class="smart-card">
        <table id="usersTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                    <td>
                        <span class="badge badge-<?= $user['estado'] ?>">
                            <?= ucfirst($user['estado']) ?>
                        </span>
                    </td>
                    <td>
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
        $('#usersTable').DataTable({
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
            order: [[0, 'desc']]
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
