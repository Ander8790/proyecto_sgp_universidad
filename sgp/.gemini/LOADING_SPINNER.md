# Integración de Loading Spinner

## Sistema de Carga Global SGP

Se ha integrado un sistema completo de loading spinners con el color azul corporativo (#162660) del sistema.

### Archivos Creados/Modificados

1. **`public/css/loading.css`** - Estilos de loading spinner
2. **`app/views/layouts/main_layout.php`** - Funciones helper JavaScript

### Componentes Disponibles

#### 1. Loading Overlay (Pantalla Completa)
```javascript
// Mostrar overlay
showLoading('Cargando datos...');

// Ocultar overlay
hideLoading();
```

#### 2. Loading en Botones
```javascript
const btn = document.querySelector('#miBoton');

// Activar loading
setButtonLoading(btn, 'Guardando...');

// Desactivar loading
resetButton(btn);
```

#### 3. Loading Inline (HTML)
```html
<div class="loading-inline">
    <div class="loader"></div>
    <span class="loading-text">Cargando contenido...</span>
</div>
```

#### 4. Loading Small (HTML)
```html
<button class="btn-primary">
    <span class="loader-small"></span> Cargando...
</button>
```

### Ejemplo de Uso en Formularios

```javascript
// En submit de formulario
document.getElementById('miFormulario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, 'Guardando...');
    
    // Simular petición AJAX
    fetch('/api/endpoint', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        resetButton(submitBtn);
        // Manejar respuesta
    })
    .catch(error => {
        resetButton(submitBtn);
        // Manejar error
    });
});
```

### Ejemplo con Overlay en Cambio de Página

```javascript
// Mostrar loading al navegar
function navigateWithLoading(url) {
    showLoading('Cargando página...');
    window.location.href = url;
}
```

### Estilos Incluidos

- **Dual Color Spinner**: Estilo moderno con animación cubic-bezier
- **Color**: Azul corporativo (#162660)
- **Tamaños**: Normal (48px), Small (16px), Large para overlay (60px)
- **Animación**: Rotación suave y fluida

### Funciones Globales Disponibles

| Función | Descripción |
|---------|-------------|
| `showLoading(message)` | Muestra overlay de carga con mensaje opcional |
| `hideLoading()` | Oculta overlay de carga |
| `setButtonLoading(button, text)` | Activa estado de carga en botón |
| `resetButton(button)` | Restaura botón a estado normal |
