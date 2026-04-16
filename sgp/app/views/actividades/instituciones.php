<?php
/**
 * Vista: Gestión de Instituciones Externas
 * URL: /actividades/instituciones
 *
 * Esta vista fue migrada al modal embebido en actividades/index.php
 * Se redirige automáticamente y se abre el panel de Aliados Universitarios.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=<?= URLROOT ?>/actividades?aliados=1">
    <title>Redirigiendo...</title>
    <style>
        body {
            margin: 0; font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #172554, #1e3a8a, #2563eb);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: white; border-radius: 20px;
            padding: 48px 40px; text-align: center;
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.3);
            max-width: 400px; width: 90%;
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .spinner {
            width: 56px; height: 56px; border-radius: 50%;
            border: 4px solid #e2e8f0; border-top-color: #2563eb;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h2 { font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
        p  { font-size: 0.88rem; color: #64748b; margin: 0 0 24px; }
        a  {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #172554, #2563eb);
            color: white; padding: 11px 22px; border-radius: 10px;
            font-weight: 600; font-size: 0.9rem; text-decoration: none;
            transition: all 0.2s;
        }
        a:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2>Redirigiendo...</h2>
        <p>Las instituciones ahora se gestionan desde el panel de Actividades Extras.</p>
        <a href="<?= URLROOT ?>/actividades?aliados=1">
            &#8592; Ir al Panel de Aliados
        </a>
    </div>
    <script>
        // Redirigir inmediatamente via JS
        window.location.href = '<?= URLROOT ?>/actividades?aliados=1';
    </script>
</body>
</html>
