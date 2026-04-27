<!-- JavaScript Assets (Before closing body tag) -->
<script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
<script src="<?= URLROOT ?>/js/notyf.min.js"></script>
<script src="<?= URLROOT ?>/js/notification-service.js"></script>
<script src="<?= URLROOT ?>/js/notifications.js"></script>

<!-- Footer Institucional sobre fondo oscuro -->
<footer class="auth-institutional-footer">
    <p class="copyright" style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
        <span>Copyleft <?= date('Y') ?> Instituto de Salud Pública. Algunos derechos reservados.</span>
        <a href="<?= URLROOT ?>/ayuda/pdf" target="_blank" data-tooltip="Abrir Manual PDF" style="color: rgba(255,255,255,0.7); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 500; font-size: 0.9em; transition: color 0.2s ease;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">
            <i class="ti ti-book-download" style="font-size: 1.1rem;"></i> Manual de Usuario
        </a>
    </p>
</footer>
