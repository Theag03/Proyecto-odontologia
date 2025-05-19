//Codigo de Metodos de Pago

document.addEventListener('DOMContentLoaded', function() {
    // Mostrar resumen de la cita
    const appointmentData = JSON.parse(sessionStorage.getItem('appointmentData'));
    if (appointmentData) {
        let servicesHtml = appointmentData.services.map(s => `
            <tr>
                <td>${s.name}</td>
                <td class="text-end">$${s.price}</td>
            </tr>
        `).join('');
        
        document.getElementById('summaryContent').innerHTML = `
            <table class="table table-sm">
                <tbody>
                    ${servicesHtml}
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total:</th>
                        <th class="text-end">${appointmentData.total}</th>
                    </tr>
                </tfoot>
            </table>
            <p><strong>Fecha:</strong> ${appointmentData.date}</p>
            <p><strong>Hora:</strong> ${appointmentData.time}</p>
        `;
    } else {
        document.getElementById('appointmentSummary').style.display = 'none';
    }
    
    // Función para copiar al portapapeles
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const range = document.createRange();
        range.selectNode(element);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        
        return element.innerText;
    }
    
    // Copiar datos de transferencia
    document.getElementById('copyPagoMovil').addEventListener('click', function() {
        copyToClipboard('pagoMovilInfo');
        const message = document.getElementById('pagoMovilCopyMessage');
        message.style.display = 'block';
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
        
        // Mostrar alerta con SweetAlert2 (opcional)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Datos copiados',
                text: 'La información de transferencia ha sido copiada al portapapeles',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
    
    // Copiar datos de Zelle
    document.getElementById('copyZelle').addEventListener('click', function() {
        copyToClipboard('zelleInfo');
        const message = document.getElementById('zelleCopyMessage');
        message.style.display = 'block';
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
        
        // Mostrar alerta con SweetAlert2 (opcional)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Datos copiados',
                text: 'La información de Zelle ha sido copiada al portapapeles',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
    
    // Manejar subida de comprobante
    document.getElementById('receiptFile').addEventListener('change', function() {
        const filePreview = document.getElementById('filePreview');
        filePreview.innerHTML = '';
        
        if (this.files.length > 0) {
            const file = this.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo excede el tamaño máximo de 5MB');
                this.value = '';
                return;
            }
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'alert alert-info d-flex justify-content-between align-items-center';
            fileInfo.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <strong>${file.name}</strong> (${(file.size / 1024).toFixed(2)} KB)
                </div>
                <button type="button" class="btn-close" onclick="document.getElementById('receiptFile').value = ''; this.parentElement.remove()"></button>
            `;
            filePreview.appendChild(fileInfo);
        }
    });
    
    // Enviar formulario de comprobante
    document.getElementById('receiptUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Aquí iría la lógica para subir el archivo al servidor
        // Por ahora solo mostramos el modal de confirmación
        
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
        
        // Limpiar formulario después de 2 segundos
        setTimeout(() => {
            this.reset();
            document.getElementById('filePreview').innerHTML = '';
        }, 2000);
    });
});