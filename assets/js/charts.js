/**
 * GreenDC Advisor — graphiques Chart.js (tableau de bord)
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined' || !window.GDC_CHARTS) {
        return;
    }

    const data = window.GDC_CHARTS;
    const colors = {
        green: '#1b7a4e',
        greenSoft: 'rgba(27, 122, 78, 0.18)',
        blue: '#1a5f8a',
        blueSoft: 'rgba(26, 95, 138, 0.18)',
        teal: '#0d9488',
        gray: '#94a3b8',
        palette: ['#1b7a4e', '#1a5f8a', '#0d9488', '#f59e0b', '#dc2626', '#6366f1', '#0891b2'],
    };

    Chart.defaults.font.family = '"Segoe UI", system-ui, sans-serif';
    Chart.defaults.color = '#64748b';

    const elMensuel = document.getElementById('chartMensuel');
    if (elMensuel) {
        new Chart(elMensuel, {
            type: 'line',
            data: {
                labels: data.mois || [],
                datasets: [
                    {
                        label: 'Consommation (kWh)',
                        data: data.conso_mensuelle || [],
                        borderColor: colors.blue,
                        backgroundColor: colors.blueSoft,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Production PV (kWh)',
                        data: data.prod_mensuelle || [],
                        borderColor: colors.green,
                        backgroundColor: colors.greenSoft,
                        fill: true,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    const elMix = document.getElementById('chartMix');
    if (elMix) {
        new Chart(elMix, {
            type: 'doughnut',
            data: {
                labels: (data.mix_energie && data.mix_energie.labels) || [],
                datasets: [{
                    data: (data.mix_energie && data.mix_energie.values) || [],
                    backgroundColor: [colors.green, colors.gray],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed} %`,
                        },
                    },
                },
            },
        });
    }

    const elComp = document.getElementById('chartComparaison');
    if (elComp) {
        const cmp = data.comparaison || { labels: [], consommation: [], production: [] };
        new Chart(elComp, {
            type: 'bar',
            data: {
                labels: cmp.labels,
                datasets: [
                    {
                        label: 'Consommation',
                        data: cmp.consommation,
                        backgroundColor: colors.blue,
                    },
                    {
                        label: 'Production PV',
                        data: cmp.production,
                        backgroundColor: colors.green,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    const elRep = document.getElementById('chartRepartition');
    if (elRep) {
        const rep = data.repartition || { labels: [], values: [] };
        new Chart(elRep, {
            type: 'pie',
            data: {
                labels: rep.labels,
                datasets: [{
                    data: rep.values,
                    backgroundColor: colors.palette,
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const val = ctx.parsed || 0;
                                return ` ${ctx.label}: ${Number(val).toLocaleString('fr-FR')} kWh/an`;
                            },
                        },
                    },
                },
            },
        });
    }
});
