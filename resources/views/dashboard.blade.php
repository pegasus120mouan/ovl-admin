@extends('layout.main')

@section('title', 'Tableau de bord')

@section('page_title', 'Tableau de bord')

@section('content')
    <style>
        .ovl-panel-title {
            background: #3f4f59;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 10px 14px;
            border-radius: 6px 6px 0 0;
            text-transform: uppercase;
            font-size: 14px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .ovl-panel-body {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-top: 0;
            padding: 14px;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }
        .ovl-kpi {
            border: 1px solid rgba(0, 0, 0, 0.07);
            background: #ffffff;
            padding: 14px;
            height: 100%;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .ovl-kpi .ovl-kpi-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .ovl-kpi .ovl-kpi-value {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1;
        }
        .ovl-tile-green {
            background: #36b58f;
            color: #ffffff;
            padding: 14px;
            height: 100%;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.10);
        }
        .ovl-tile-green .ovl-tile-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.95;
        }
        .ovl-tile-green .ovl-tile-value {
            font-size: 56px;
            font-weight: 800;
            line-height: 1;
            margin-top: 8px;
        }
        .ovl-mini-metric {
            display: flex;
            gap: 16px;
            align-items: flex-end;
            margin-top: 10px;
        }
        .ovl-mini-metric .ovl-mini-item {
            font-size: 11px;
            color: #6c757d;
        }
        .ovl-mini-metric .ovl-mini-item strong {
            display: block;
            font-size: 13px;
            color: #343a40;
        }
        .ovl-delta {
            font-weight: 700;
        }
        .ovl-delta.up {
            color: #28a745;
        }
        .ovl-delta.down {
            color: #dc3545;
        }
        .ovl-list-compact {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .ovl-list-compact li {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 3px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            font-size: 13px;
        }
        .ovl-list-compact li:last-child {
            border-bottom: 0;
        }
        .ovl-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            transform: translateY(1px);
        }
        .ovl-chart-wrap {
            position: relative;
            height: 130px;
        }
        .ovl-donut-wrap {
            position: relative;
            height: 170px;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($nbColisLivresMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Total colis livrés ce mois</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($montantLivraisonsPayeesMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Total livraisons payées ce mois</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-list"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($depensesMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Dépenses Effectuées ce mois</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-alert-circled"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format(($gainMois ?? 0), 0, ',', ' ') }}</h3>
                        <p>Gain du mois en cours</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="ovl-kpi">
                    <div class="ovl-kpi-label">Colis Recus</div>
                    <div class="ovl-kpi-value">{{ number_format($nbColisRecusAnnee ?? 0, 0, ',', ' ') }}</div>
                    <div class="ovl-chart-wrap">
                        <canvas id="ovlPageEngagement"></canvas>
                    </div>

                    <div class="row mt-3" style="gap: 10px;">
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #6c757d; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Montant Total Factures</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantFacturesTotal ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #17a2b8; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Montant Factures en attente</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantFacturesValideesAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="row mt-3" style="gap: 10px;">
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #6c757d; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Montant Total Dettes</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantDettesTotal ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #17a2b8; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Montant Dettes remboursées</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantDettesRembourseesTotal ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #17a2b8; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Montant dette restant à payer</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantDettesRestantTotal ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ovl-kpi">
                    <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                        <div class="ovl-kpi-label" style="margin: 0;">Stats colis (année en cours)</div>
                        <div class="text-muted" style="font-size: 12px;">01/01 - Aujourd'hui</div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <ul class="ovl-list-compact">
                                <li>
                                    <div><span class="ovl-dot" style="background:#28a745"></span>Livrés</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisLivresAnnee ?? 0, 0, ',', ' ') }}</div>
                                </li>
                                <li>
                                    <div><span class="ovl-dot" style="background:#dc3545"></span>Non Livrés</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisNonLivresAnnee ?? 0, 0, ',', ' ') }}</div>
                                </li>
                                <li>
                                    <div><span class="ovl-dot" style="background:#ffc107"></span>Retours</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisRetoursAnnee ?? 0, 0, ',', ' ') }}</div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="ovl-tile-green" style="background:#2bb3c0; border-radius:2px;">
                                <div class="ovl-tile-label">Total colis reçus (année)</div>
                                <div class="ovl-tile-value" style="font-size:46px;">{{ number_format($nbColisRecusAnnee ?? 0, 0, ',', ' ') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="ovl-kpi">
                                <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                    <div class="ovl-kpi-label" style="margin: 0;">Gain par livreur</div>
                                    <div class="text-muted" style="font-size: 12px;">Top 8 + Autre</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-5">
                                        <div class="ovl-donut-wrap" style="height: 190px;">
                                            <canvas id="ovlGainsLivreursDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlGainsLivreursLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlGainsLivreursTotal">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi">
                                <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                    <div class="ovl-kpi-label" style="margin: 0;">Dépenses par livreur</div>
                                    <div class="text-muted" style="font-size: 12px;">Top 8 + Autre</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-5">
                                        <div class="ovl-donut-wrap" style="height: 190px;">
                                            <canvas id="ovlDepensesLivreursDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlDepensesLivreursLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlDepensesLivreursTotal">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Synthèse financière (année en cours)</div>
                <div class="ovl-panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="ovl-kpi" style="border-left: 6px solid #28a745;">
                                <div class="ovl-kpi-label">Revenus Total</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($revenusTotalAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-md-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #fd7e14;">
                                <div class="ovl-kpi-label">Montant livraison</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($chargesVariablesAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #6c757d;">
                                <div class="ovl-kpi-label">Dépenses fonctionnement</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($chargesFixesAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>



                        <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #0d6efd;">
                                <div class="ovl-kpi-label">Paiement livreurs</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($paiementLivreursAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #ffc107;">
                                <div class="ovl-kpi-label">Gain</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($gainSyntheseAnnee ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Répartition des colis reçus (année en cours)</div>
                <div class="ovl-panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="ovl-kpi">
                                <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                    <div class="ovl-kpi-label" style="margin: 0;">Par boutique</div>
                                    <div class="text-muted" style="font-size: 12px;">Top 8 + Autre</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-5">
                                        <div class="ovl-donut-wrap" style="height: 190px;">
                                            <canvas id="ovlBoutiquesDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlBoutiquesLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlBoutiquesTotal">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi">
                                <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                    <div class="ovl-kpi-label" style="margin: 0;">Par client</div>
                                    <div class="text-muted" style="font-size: 12px;">Top 8 + Autre</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-5">
                                        <div class="ovl-donut-wrap" style="height: 190px;">
                                            <canvas id="ovlClientsDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlClientsLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlClientsTotal">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var boot = function () {
                if (typeof Chart === 'undefined') {
                    return;
                }

            var fmt = function (n) {
                try {
                    return new Intl.NumberFormat('fr-FR').format(n);
                } catch (e) {
                    return String(n);
                }
            };

            var pageCtx = document.getElementById('ovlPageEngagement');
            if (pageCtx) {
                new Chart(pageCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],
                        datasets: [
                            {
                                label: 'Colis reçus',
                                data: [18, 12, 10, 11, 9, 14, 19],
                                borderColor: '#2bb3c0',
                                backgroundColor: 'rgba(43, 179, 192, 0.12)',
                                pointRadius: 0,
                                tension: 0.35,
                                fill: true,
                                borderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        }
                    }
                });
            }

            var boutiquesLabels = @json($repartitionBoutiquesLabels ?? []);
            var boutiquesData = @json($repartitionBoutiquesData ?? []);
            var boutiquesColors = ['#3fb98f', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#63e6be', '#74c0fc', '#ced4da'];

            var boutiquesTotal = boutiquesData.reduce(function (a, b) { return a + b; }, 0);
            var boutiquesTotalEl = document.getElementById('ovlBoutiquesTotal');
            if (boutiquesTotalEl) {
                boutiquesTotalEl.textContent = fmt(boutiquesTotal);
            }

            var boutiquesCanvas = document.getElementById('ovlBoutiquesDonut');
            if (boutiquesCanvas && boutiquesLabels.length) {
                new Chart(boutiquesCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: boutiquesLabels,
                        datasets: [
                            { data: boutiquesData, backgroundColor: boutiquesLabels.map(function (_, i) { return boutiquesColors[i % boutiquesColors.length]; }), borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } }
                    }
                });
            }

            var boutiquesLegend = document.getElementById('ovlBoutiquesLegend');
            if (boutiquesLegend && boutiquesLabels.length) {
                boutiquesLegend.innerHTML = boutiquesLabels
                    .map(function (label, i) {
                        var color = boutiquesColors[i % boutiquesColors.length];
                        var val = boutiquesData[i] || 0;
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }

            var clientsLabels = @json($repartitionClientsLabels ?? []);
            var clientsData = @json($repartitionClientsData ?? []);
            var clientsColors = ['#2bb3c0', '#36b58f', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#63e6be', '#74c0fc', '#ced4da'];

            var clientsTotal = clientsData.reduce(function (a, b) { return a + b; }, 0);
            var clientsTotalEl = document.getElementById('ovlClientsTotal');
            if (clientsTotalEl) {
                clientsTotalEl.textContent = fmt(clientsTotal);
            }

            var clientsCanvas = document.getElementById('ovlClientsDonut');
            if (clientsCanvas && clientsLabels.length) {
                new Chart(clientsCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: clientsLabels,
                        datasets: [
                            { data: clientsData, backgroundColor: clientsLabels.map(function (_, i) { return clientsColors[i % clientsColors.length]; }), borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } }
                    }
                });
            }

            var clientsLegend = document.getElementById('ovlClientsLegend');
            if (clientsLegend && clientsLabels.length) {
                clientsLegend.innerHTML = clientsLabels
                    .map(function (label, i) {
                        var color = clientsColors[i % clientsColors.length];
                        var val = clientsData[i] || 0;
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }

            var gainsLivreursLabels = @json($repartitionGainsLivreursLabels ?? []);
            var gainsLivreursData = @json($repartitionGainsLivreursData ?? []);
            var gainsLivreursColors = ['#36b58f', '#2bb3c0', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#ced4da'];

            var gainsLivreursTotal = gainsLivreursData.reduce(function (a, b) { return a + b; }, 0);
            var gainsLivreursTotalEl = document.getElementById('ovlGainsLivreursTotal');
            if (gainsLivreursTotalEl) {
                gainsLivreursTotalEl.textContent = fmt(gainsLivreursTotal);
            }

            var gainsLivreursCanvas = document.getElementById('ovlGainsLivreursDonut');
            if (gainsLivreursCanvas && gainsLivreursLabels.length) {
                new Chart(gainsLivreursCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: gainsLivreursLabels,
                        datasets: [
                            { data: gainsLivreursData, backgroundColor: gainsLivreursLabels.map(function (_, i) { return gainsLivreursColors[i % gainsLivreursColors.length]; }), borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } }
                    }
                });
            }

            var gainsLivreursLegend = document.getElementById('ovlGainsLivreursLegend');
            if (gainsLivreursLegend && gainsLivreursLabels.length) {
                gainsLivreursLegend.innerHTML = gainsLivreursLabels
                    .map(function (label, i) {
                        var color = gainsLivreursColors[i % gainsLivreursColors.length];
                        var val = gainsLivreursData[i] || 0;
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }

            var depensesLivreursLabels = @json($repartitionDepensesLivreursLabels ?? []);
            var depensesLivreursData = @json($repartitionDepensesLivreursData ?? []);
            var depensesLivreursColors = ['#fd7e14', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#7b5cd6', '#36b58f', '#2bb3c0', '#ced4da'];

            var depensesLivreursTotal = depensesLivreursData.reduce(function (a, b) { return a + b; }, 0);
            var depensesLivreursTotalEl = document.getElementById('ovlDepensesLivreursTotal');
            if (depensesLivreursTotalEl) {
                depensesLivreursTotalEl.textContent = fmt(depensesLivreursTotal);
            }

            var depensesLivreursCanvas = document.getElementById('ovlDepensesLivreursDonut');
            if (depensesLivreursCanvas && depensesLivreursLabels.length) {
                new Chart(depensesLivreursCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: depensesLivreursLabels,
                        datasets: [
                            { data: depensesLivreursData, backgroundColor: depensesLivreursLabels.map(function (_, i) { return depensesLivreursColors[i % depensesLivreursColors.length]; }), borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } }
                    }
                });
            }

            var depensesLivreursLegend = document.getElementById('ovlDepensesLivreursLegend');
            if (depensesLivreursLegend && depensesLivreursLabels.length) {
                depensesLivreursLegend.innerHTML = depensesLivreursLabels
                    .map(function (label, i) {
                        var color = depensesLivreursColors[i % depensesLivreursColors.length];
                        var val = depensesLivreursData[i] || 0;
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    boot();
                });
            } else {
                boot();
            }
        })();
    </script>
@endsection
