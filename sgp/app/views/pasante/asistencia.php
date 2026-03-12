<?php
// Vista Historial de Asistencia del Pasante
$user_name = $data['user_name'] ?? 'Pasante';
$role = $data['role'] ?? 'Pasante';
$pasante = $data['pasante'] ?? null;
$asistencias = $data['asistencias'] ?? [];

$estadoPasantia = $pasante->estado_pasantia ?? 'Pendiente';
?>

<div class="dashboard-bento" style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Encabezado de la Sección -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="color: #162660; font-size: 1.8rem; font-weight: 800; margin: 0 0 8px 0;">Historial de Asistencias</h1>
            <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Registro completo de tus marcas de entrada/salida y justificaciones.</p>
        </div>
    </div>

    <?php if ($estadoPasantia === 'Sin Asignar' || empty($pasante->estado_pasantia) || $estadoPasantia === 'Pendiente'): ?>
    
    <!-- ============================================== -->
    <!-- EMPTY STATE PREMIUM (Sin Asignar / En Revisión)-->
    <!-- ============================================== -->
    <div class="card slide-up" style="animation-delay: 0.1s; padding: 60px 40px; text-align: center; border-radius: 20px; background: linear-gradient(to bottom, #ffffff, #f8fafc); border: 2px dashed #cbd5e1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        
        <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.1);">
            <i class="ti ti-calendar-off" style="font-size: 3.5rem; color: #2563eb;"></i>
        </div>
        
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.5px;">Sin Historial Disponible</h2>
        
        <p style="font-size: 1rem; color: #64748b; max-width: 500px; line-height: 1.6; margin-bottom: 30px;">
            Aún no has sido asignado a un Departamento. El historial de asistencias se habilitará una vez Telemática complete tu asignación.
        </p>
        
        <a href="<?= URLROOT ?>/dashboard" style="display: inline-flex; gap: 8px; align-items: center; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
            <i class="ti ti-arrow-left"></i> Volver al Inicio
        </a>
    </div>

    <?php else: ?>

    <!-- ============================================== -->
    <!-- VISTA NORMAL (Historial Completo)               -->
    <!-- ============================================== -->
    <div class="card slide-up" style="animation-delay: 0.1s;">
        <div class="table-container" style="overflow-x: auto;">
            <table class="modern-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 16px;">Fecha</th>
                        <th style="padding: 16px;">Hora Registro</th>
                        <th style="padding: 16px;">Método</th>
                        <th style="padding: 16px;">Justificación</th>
                        <th style="text-align: right; padding: 16px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($asistencias)): ?>
                        <?php foreach ($asistencias as $act):
                            $fecha   = !empty($act->fecha) ? date('d/m/Y', strtotime($act->fecha)) : '—';
                            $hora    = !empty($act->hora_registro) ? date('h:i A', strtotime($act->hora_registro)) : '—';
                            $estadoBadge = ($act->estado ?? '') === 'Presente' ? 'success' : (($act->estado ?? '') === 'Justificado' ? 'warning' : ($act->estado === 'Ausente' ? 'danger' : 'info'));
                        ?>
                        <tr>
                            <td style="padding: 16px;"><strong><?= htmlspecialchars($fecha) ?></strong></td>
                            <td style="color: var(--text-muted); padding: 16px; font-weight: 500;"><i class="ti ti-clock ti-sm text-primary"></i> <?= htmlspecialchars($hora) ?></td>
                            <td style="padding: 16px; font-size: 0.85rem;"><i class="ti ti-device-desktop"></i> <?= htmlspecialchars($act->metodo ?? 'Kiosco') ?></td>
                            <td style="padding: 16px; font-size: 0.85rem; color: #64748b;">
                                <?= !empty($act->motivo_justificacion) ? htmlspecialchars($act->motivo_justificacion) : '- - -' ?>
                                <?php if (!empty($act->ruta_evidencia)): ?>
                                    <br>
                                    <a href="<?= URLROOT ?><?= $act->ruta_evidencia ?>" target="_blank" style="color:#2563eb; font-weight:bold; font-size: 0.75rem;"><i class="ti ti-file-text"></i> Ver Anexo</a>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; padding: 16px;"><span class="badge <?= $estadoBadge ?>"><?= htmlspecialchars($act->estado ?? 'Indefinido') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:60px 24px;color:#94a3b8;border-bottom:none;">
                                <i class="ti ti-calendar-off" style="font-size:42px;display:block;margin-bottom:16px;opacity:0.5;"></i>
                                <span style="font-size:1rem;font-weight:500;">Tu historial de asistencias está impecable, pero también vacío.</span>
                                <p style="margin-top: 8px; font-size: 0.85rem;">Marca tu primera asistencia desde el Kiosco.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php endif; ?>
</div>