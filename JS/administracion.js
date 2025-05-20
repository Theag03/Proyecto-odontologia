// Gráfico de estadísticas de tratamientos
        const ctx = document.getElementById('treatmentChart').getContext('2d');
        const treatmentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Limpieza', 'Ortodoncia', 'Blanqueamiento', 'Extracciones', 'Implantes', 'Otros'],
                datasets: [{
                    data: [35, 20, 15, 10, 12, 8],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#f1c40f',
                        '#e74c3c',
                        '#9b59b6',
                        '#34495e'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });