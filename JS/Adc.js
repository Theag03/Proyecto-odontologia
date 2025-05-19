//Codigo de Agenda de Citas Odontologia

document.addEventListener('DOMContentLoaded', function() {
    // Configurar fecha mínima (hoy)
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('appointmentDate').min = today;
    
    // Variables para horarios disponibles
    const availableHours = {
        weekdays: ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        saturdays: ['09:00', '10:00', '11:00'],
        unavailableDates: ['2023-12-25', '2024-01-01'] // Fechas no laborables
    };
    
    // Calcular total cuando se seleccionan servicios
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('input[name="service"]:checked').forEach(service => {
            total += parseInt(service.dataset.price);
        });
        document.getElementById('totalAmount').textContent = `$${total}`;
    }
    
    // Generar horarios disponibles
    function generateTimeSlots(selectedDate) {
        const container = document.querySelector('.time-slots-container');
        container.innerHTML = '';
        
        // Verificar si es fecha no laborable
        if (availableHours.unavailableDates.includes(selectedDate)) {
            container.innerHTML = '<div class="alert alert-warning">No hay horarios disponibles para esta fecha</div>';
            return;
        }
        
        // Determinar si es sábado (día 6)
        const dateObj = new Date(selectedDate);
        const isSaturday = dateObj.getDay() === 6;
        const hours = isSaturday ? availableHours.saturdays : availableHours.weekdays;
        
        // Generar botones de horarios
        hours.forEach(time => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-slot-btn btn btn-outline-primary me-2 mb-2';
            btn.textContent = time;
            btn.addEventListener('click', function() {
                // Remover clase activa de otros botones
                document.querySelectorAll('.time-slot-btn').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                
                // Añadir clase activa al botón seleccionado
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                
                // Guardar el valor seleccionado
                document.getElementById('selectedTime').value = time;
            });
            
            container.appendChild(btn);
        });
    }
    
    // Limpiar selección de servicios
    document.getElementById('clearServices').addEventListener('click', function() {
        document.querySelectorAll('input[name="service"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        calculateTotal();
    });
    
    // Imprimir formulario
    document.getElementById('printForm').addEventListener('click', function() {
        const printContent = generatePrintContent();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Resumen de Cita Dental</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                    <style>
                        body { padding: 20px; }
                        .print-header { margin-bottom: 30px; text-align: center; }
                        .service-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
                        .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() { window.print(); setTimeout(function(){ window.close(); }, 1000); }
                    </script>
                </body>
            </html>
        `);
        printWindow.document.close();
    });
    
    // Generar contenido para imprimir
    function generatePrintContent() {
        const firstName = document.getElementById('firstName').value || 'No especificado';
        const lastName = document.getElementById('lastName').value || 'No especificado';
        const phone = document.getElementById('phone').value || 'No especificado';
        const email = document.getElementById('email').value || 'No especificado';
        const date = document.getElementById('appointmentDate').value || 'No especificada';
        const time = document.getElementById('selectedTime').value || 'No especificada';
        
        let services = [];
        document.querySelectorAll('input[name="service"]:checked').forEach(service => {
            services.push({
                name: service.value,
                price: service.dataset.price
            });
        });
        
        const total = document.getElementById('totalAmount').textContent;
        
        let servicesHtml = services.map(s => `
            <div class="service-item">
                <span>${s.name}</span>
                <span>$${s.price}</span>
            </div>
        `).join('');
        
        if (services.length === 0) {
            servicesHtml = '<div class="text-center">No se seleccionaron servicios</div>';
        }
        
        return `
            <div class="print-header">
                <h2>Resumen de Cita Dental</h2>
                <p>${new Date().toLocaleDateString()}</p>
            </div>
            
            <h5>Información del Paciente</h5>
            <p><strong>Nombre:</strong> ${firstName} ${lastName}</p>
            <p><strong>Teléfono:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email}</p>
            
            <h5 class="mt-4">Detalles de la Cita</h5>
            <p><strong>Fecha:</strong> ${date}</p>
            <p><strong>Hora:</strong> ${time}</p>
            
            <h5 class="mt-4">Servicios Seleccionados</h5>
            ${servicesHtml}
            
            <div class="total text-end mt-3">
                <strong>Total:</strong> ${total}
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                Por favor presente este resumen el día de su cita.
            </div>
        `;
    }
    
    // Event listeners
    document.querySelectorAll('input[name="service"]').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });
    
    document.getElementById('appointmentDate').addEventListener('change', function() {
        generateTimeSlots(this.value);
    });
    
    document.getElementById('appointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validaciones
        if (!document.getElementById('selectedTime').value) {
            alert('Por favor seleccione un horario disponible');
            return;
        }
        
        // Recolectar datos del formulario
        const formData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            date: document.getElementById('appointmentDate').value,
            time: document.getElementById('selectedTime').value,
            services: Array.from(document.querySelectorAll('input[name="service"]:checked')).map(s => ({
                name: s.value,
                price: s.dataset.price
            })),
            total: document.getElementById('totalAmount').textContent
        };
        
        // Guardar datos en sessionStorage para la página de pagos
        sessionStorage.setItem('appointmentData', JSON.stringify(formData));
        
        // Redirigir a la página de métodos de pago
        window.location.href = 'metodos-pago.html';
    });
});