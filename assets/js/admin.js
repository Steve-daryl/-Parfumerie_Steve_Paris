// js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // Fonction pour charger les données via Fetch API
    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la récupération des données:", error);
            return null;
        }
    }

    // Graphique d'évolution des performances (exemple avec Chart.js)
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        fetchData('api/get_performance_data.php').then(data => {
            if (data) {
                new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels, // Ex: ['Jan', 'Fev', 'Mar', ...]
                        datasets: [{
                            label: 'Ventes',
                            data: data.sales,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.3,
                            fill: false
                        }, {
                            label: 'Bénéfice',
                            data: data.profit,
                            borderColor: 'rgb(153, 102, 255)',
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: '#E0E0E0'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#A0A0A0' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            },
                            y: {
                                ticks: { color: '#A0A0A0' },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            }
                        }
                    }
                });
            }
        });
    }

    // Graphique des dépenses (exemple avec Chart.js)
    const expensesCtx = document.getElementById('expensesChart');
    if (expensesCtx) {
        fetchData('api/get_expenses_data.php').then(data => {
            if (data) {
                new Chart(expensesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels, // Ex: ['Achats Produits', 'Marketing', 'Salaires', ...]
                        datasets: [{
                            data: data.amounts,
                            backgroundColor: [
                                '#4A90E2', // Accent Blue
                                '#FFC107', // Warning Yellow
                                '#DC3545', // Danger Red
                                '#28A745', // Success Green
                                '#17A2B8'  // Info Blue
                            ],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: {
                                    color: '#E0E0E0'
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    // Exemple de gestion d'événement (peut-être pour un bouton de réapprovisionnement)
    document.querySelectorAll('.btn-primary').forEach(button => {
        button.addEventListener('click', (event) => {
            alert('Action de réapprovisionnement déclenchée !');
            // Ici, vous feriez une requête AJAX vers le backend PHP pour mettre à jour le stock
        });
    });

    // Vous pouvez ajouter d'autres logiques JavaScript ici
    // comme la gestion de la navigation latérale, des filtres, etc.
});