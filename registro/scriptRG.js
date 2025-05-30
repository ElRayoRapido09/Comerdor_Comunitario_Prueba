document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registro-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            // 1. Enviar datos al servidor
            const response = await fetch('/api/procesar_registro.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' 
                }
            });

            // 2. Manejar errores HTTP (ej: 404, 500)
            if (!response.ok) {
                const error = await response.text();
                throw new Error(`Error ${response.status}: ${error}`);
            }

            // 3. Procesar respuesta JSON
            const data = await response.json();
            console.log("Respuesta del servidor:", data);

            if (data.success) {
                // 4. Mostrar mensaje de éxito y redirigir
                alert('Usuario registrado correctamente. Serás redirigido al login.');
                window.location.href = '/inicio/index.html';
            } else {
                // 5. Mostrar errores específicos
                if (data.errors && Array.isArray(data.errors)) {
                    alert('Errores encontrados:\n' + data.errors.join('\n'));
                } else {
                    alert(`Error: ${data.message || 'Error desconocido'}`);
                }
            }
        } catch (error) {
            console.error("Error completo:", error);
            alert("Error al comunicarse con el servidor. Ver consola para detalles.");
        }
    });
});