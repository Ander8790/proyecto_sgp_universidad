<?php require_once APPROOT . '/views/inc/header.php'; ?>

<!-- ============================================ -->
<!-- GESTIÓN DE PASANTES - DATATABLE AVANZADO -->
<!-- ============================================ -->

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header de Página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="ti ti-users"></i> Gestión de Pasantes
                    </h2>
                    <p class="text-muted">Administra el ciclo de vida completo de los pasantes</p>
                </div>
                <div>
                    <span class="badge badge-info">
                        Total: <?= count($pasantes) ?> pasantes
                    </span>
                </div>
            </div>

            <!-- Tarjeta Principal -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- DataTable -->
                    <table id="tablaPasantes" class="table table-hover table-striped" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>Cédula</th>
                                <th>Pasante</th>
                                <th>Institución</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Departamento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pasantes as $pasante): ?>
                                <tr>
                                    <!-- Cédula (Gris) -->
                                    <td class="text-muted">
                                        <small>V-<?= htmlspecialchars($pasante['cedula']) ?></small>
                                    </td>

                                    <!-- Nombre (Negrita, Azul, Enlace) -->
                                    <td>
                                        <a href="<?= URLROOT ?>/pasantes/show/<?= $pasante['id'] ?>" 
                                           class="font-weight-bold text-primary">
                                            <?= htmlspecialchars($pasante['nombres'] . ' ' . $pasante['apellidos']) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($pasante['correo']) ?></small>
                                    </td>

                                    <!-- Institución -->
                                    <td>
                                        <small><?= htmlspecialchars($pasante['institucion_procedencia'] ?? 'No especificado') ?></small>
                                    </td>

                                    <!-- Estado (Badge) -->
                                    <td>
                                        <?php
                                        $estado = $pasante['estado_pasantia'] ?? 'Pendiente';
                                        $badgeClass = match($estado) {
                                            'Pendiente' => 'badge-warning',
                                            'Activo' => 'badge-success',
                                            'Finalizado' => 'badge-info',
                                            'Retirado' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($estado) ?>
                                        </span>
                                    </td>

                                    <!-- Progreso (Barra) -->
                                    <td>
                                        <?php 
                                        $progreso = (int) ($pasante['progreso_porcentaje'] ?? 0);
                                        $horasAcumuladas = (int) ($pasante['horas_acumuladas'] ?? 0);
                                        $horasMeta = (int) ($pasante['horas_meta'] ?? 240);
                                        ?>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" 
                                                 role="progressbar" 
                                                 style="width: <?= $progreso ?>%;" 
                                                 aria-valuenow="<?= $progreso ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= $progreso ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?= $horasAcumuladas ?> / <?= $horasMeta ?> horas
                                        </small>
                                    </td>

                                    <!-- Departamento -->
                                    <td>
                                        <small><?= htmlspecialchars($pasante['departamento_nombre'] ?? 'Sin asignar') ?></small>
                                    </td>

                                    <!-- Acciones -->
                                    <td>
                                        <?php if ($estado === 'Pendiente'): ?>
                                            <button class="btn btn-sm btn-success btn-formalizar" 
                                                    data-pasante-id="<?= $pasante['id'] ?>"
                                                    data-pasante-nombre="<?= htmlspecialchars($pasante['nombres'] . ' ' . $pasante['apellidos']) ?>">
                                                <i class="ti ti-check"></i> Formalizar
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= URLROOT ?>/pasantes/show/<?= $pasante['id'] ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="ti ti-eye"></i> Ver Kardex
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/inc/footer.php'; ?>

<!-- ============================================ -->
<!-- SCRIPTS: DATATABLE + FORMALIZACIÓN -->
<!-- ============================================ -->

<script>
$(document).ready(function() {
    // ============================================
    // DATATABLE: Configuración Avanzada
    // ============================================
    $('#tablaPasantes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[3, 'asc']], // Ordenar por Estado (Pendientes primero)
        columnDefs: [
            { orderable: false, targets: [6] } // Deshabilitar orden en Acciones
        ],
        responsive: true
    });

    // ============================================
    // FORMALIZACIÓN: SweetAlert + AJAX
    // ============================================
    $('.btn-formalizar').on('click', function() {
        const pasanteId = $(this).data('pasante-id');
        const pasanteNombre = $(this).data('pasante-nombre');

        Swal.fire({
            title: 'Formalizar Pasantía',
            html: `
                <p>Estás formalizando el inicio de pasantía de:</p>
                <h5 class="text-primary">${pasanteNombre}</h5>
                <hr>
                <div class="form-group text-left">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" class="form-control" required>
                </div>
                <div class="form-group text-left">
                    <label for="institucion_procedencia">Institución de Procedencia:</label>
                    <input type="text" id="institucion_procedencia" class="form-control" 
                           placeholder="Ej: IUTEPAL, UCV, etc." required>
                </div>
                <div class="form-group text-left">
                    <label for="departamento_id">Departamento:</label>
                    <select id="departamento_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($departamentos as $dept): ?>
                            <option value="<?= $dept->id ?>">
                                <?= htmlspecialchars($dept->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Formalizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#162660',
            preConfirm: () => {
                const fechaInicio = document.getElementById('fecha_inicio').value;
                const institucion = document.getElementById('institucion_procedencia').value;
                const departamentoId = document.getElementById('departamento_id').value;

                if (!fechaInicio || !institucion || !departamentoId) {
                    Swal.showValidationMessage('Por favor completa todos los campos');
                    return false;
                }

                return { fechaInicio, institucion, departamentoId };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar AJAX
                $.ajax({
                    url: '<?= URLROOT ?>/pasantes/formalizar',
                    method: 'POST',
                    data: {
                        pasante_id: pasanteId,
                        fecha_inicio: result.value.fechaInicio,
                        institucion_procedencia: result.value.institucion,
                        departamento_id: result.value.departamentoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Formalizado!',
                                text: response.message,
                                confirmButtonColor: '#162660'
                            }).then(() => {
                                location.reload(); // Recargar para ver cambios
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#162660'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error del Sistema',
                            text: 'No se pudo completar la operación',
                            confirmButtonColor: '#162660'
                        });
                    }
                });
            }
        });
    });
});
</script>
