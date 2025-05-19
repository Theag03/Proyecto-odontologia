document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const teeth = document.querySelectorAll('.tooth');
    const selectedTeethContainer = document.getElementById('selectedTeeth');
    const clearTeethBtn = document.getElementById('clearTeethBtn');
    const patientForm = document.getElementById('patientForm');
    const printBtn = document.getElementById('printBtn');
    const painLevelInput = document.getElementById('painLevel');
    const painLevelValue = document.getElementById('painLevelValue');
    const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
    const printFromModal = document.getElementById('printFromModal');
    
    // Almacén de dientes seleccionados
    let selectedTeeth = [];
    
    // Inicializar el valor del dolor
    painLevelValue.textContent = painLevelInput.value;
    
    // Event listeners
    painLevelInput.addEventListener('input', function() {
        painLevelValue.textContent = this.value;
    });
    
    // Selección de dientes
    teeth.forEach(tooth => {
        tooth.addEventListener('click', function() {
            const toothNumber = this.getAttribute('data-fdi');
            const toothName = this.getAttribute('data-name');
            
            // Verificar si el diente ya está seleccionado
            const index = selectedTeeth.findIndex(t => t.number === toothNumber);
            
            if (index === -1) {
                // Añadir diente
                selectedTeeth.push({ number: toothNumber, name: toothName });
                this.classList.add('selected');
            } else {
                // Quitar diente
                selectedTeeth.splice(index, 1);
                this.classList.remove('selected');
            }
            
            updateSelectedTeethDisplay();
        });
    });
    
    // Limpiar dientes seleccionados
    clearTeethBtn.addEventListener('click', function() {
        selectedTeeth = [];
        teeth.forEach(tooth => tooth.classList.remove('selected'));
        updateSelectedTeethDisplay();
    });
    
    // Enviar formulario
    patientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener datos del formulario
        const patientData = {
            name: document.getElementById('patientName').value,
            email: document.getElementById('patientEmail').value,
            phone: document.getElementById('patientPhone').value,
            painLevel: painLevelInput.value,
            symptoms: document.getElementById('symptoms').value,
            selectedTeeth: [...selectedTeeth] // Copia del array
        };
        
        // Mostrar en modal
        displayPatientData(patientData);
        
        // Mostrar modal
        infoModal.show();
    });
    
    // Botón de imprimir desde la página principal
    printBtn.addEventListener('click', function() {
        if (selectedTeeth.length === 0) {
            alert('Por favor, selecciona al menos un diente antes de imprimir.');
            return;
        }
        
        const patientData = {
            name: document.getElementById('patientName').value || 'No proporcionado',
            email: document.getElementById('patientEmail').value || 'No proporcionado',
            phone: document.getElementById('patientPhone').value || 'No proporcionado',
            painLevel: painLevelInput.value,
            symptoms: document.getElementById('symptoms').value || 'No hay síntomas adicionales',
            selectedTeeth: [...selectedTeeth]
        };
        
        displayPatientData(patientData);
        infoModal.show();
    });
    
    // Botón de imprimir desde el modal
    printFromModal.addEventListener('click', function() {
        window.print();
    });
    
    // Función para actualizar la visualización de dientes seleccionados
    function updateSelectedTeethDisplay() {
        if (selectedTeeth.length === 0) {
            selectedTeethContainer.innerHTML = '<p class="text-muted">No hay dientes seleccionados</p>';
            return;
        }
        
        let html = '<ul class="list-group">';
        selectedTeeth.forEach(tooth => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        ${tooth.number} - ${tooth.name}
                        <span class="badge bg-primary rounded-pill">${tooth.number}</span>
                    </li>`;
        });
        html += '</ul>';
        
        selectedTeethContainer.innerHTML = html;
    }
    
    // Función para mostrar los datos en el modal
    function displayPatientData(data) {
        let html = `
            <div class="patient-data-print">
                <h4 class="mb-3">Datos del Paciente</h4>
                <p><strong>Nombre:</strong> ${data.name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Teléfono:</strong> ${data.phone}</p>
                <p><strong>Nivel de dolor:</strong> ${data.painLevel}/10</p>
                <p><strong>Síntomas adicionales:</strong> ${data.symptoms}</p>
                
                <h4 class="mt-4">Dientes con molestias:</h4>
                <ul class="list-group">
        `;
        
        data.selectedTeeth.forEach(tooth => {
            html += `<li class="list-group-item">${tooth.number} - ${tooth.name}</li>`;
        });
        
        html += `
                </ul>
                <div class="mt-4 text-muted text-end">
                    <small>Fecha: ${new Date().toLocaleDateString()}</small>
                </div>
            </div>
        `;
        
        document.getElementById('modalBody').innerHTML = html;
    }
    
    // Inicializar la visualización
    updateSelectedTeethDisplay();
});
