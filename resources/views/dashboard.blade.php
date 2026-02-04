@extends('layout.main')

@section('title', 'Tableau de bord - Mois en cours')

@section('page_title', 'Tableau de bord - Mois en cours')

@section('content')
    <style>
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
        .ovl-donut-wrap {
            position: relative;
            height: 170px;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($nbColisRecusMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Colis reçus ce mois</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios-list"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($montantLivraisonsPayeesMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Total livraisons payées ce mois</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-cash"></i>
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
                        <i class="ion ion-card"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($gainMois ?? 0, 0, ',', ' ') }}</h3>
                        <p>Gain du mois en cours</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="ovl-kpi">
                    <div class="ovl-kpi-label">Stats colis (mois en cours)</div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <ul class="ovl-list-compact">
                                <li>
                                    <div><span class="ovl-dot" style="background:#17a2b8"></span>Reçus</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisRecusMois ?? 0, 0, ',', ' ') }}</div>
                                </li>
                                <li>
                                    <div><span class="ovl-dot" style="background:#28a745"></span>Livrés</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisLivresMois ?? 0, 0, ',', ' ') }}</div>
                                </li>
                                <li>
                                    <div><span class="ovl-dot" style="background:#dc3545"></span>Non Livrés</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisNonLivresMois ?? 0, 0, ',', ' ') }}</div>
                                </li>
                                <li>
                                    <div><span class="ovl-dot" style="background:#ffc107"></span>Retours</div>
                                    <div style="font-weight:700;">{{ number_format($nbColisRetoursMois ?? 0, 0, ',', ' ') }}</div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="ovl-tile-green" style="background:#2bb3c0; border-radius:2px;">
                                <div class="ovl-tile-label">Total colis reçus (mois)</div>
                                <div class="ovl-tile-value" style="font-size:46px;">{{ number_format($nbColisRecusMois ?? 0, 0, ',', ' ') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ovl-kpi">
                    <div class="ovl-kpi-label">Synthèse financière (mois en cours)</div>
                    <div class="row mt-3" style="gap: 10px;">
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #28a745; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Livraisons payées</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantLivraisonsPayeesMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #17a2b8; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Factures payées</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($montantFacturesPayeesMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" style="gap: 10px;">
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #fd7e14; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Dépenses</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($depensesMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                        <div class="col-12 col-md" style="padding: 0;">
                            <div class="ovl-kpi" style="border-left: 6px solid #0d6efd; padding: 10px 12px; box-shadow: none;">
                                <div class="ovl-kpi-label" style="margin-bottom: 4px;">Gain du mois</div>
                                <div class="ovl-kpi-value" style="font-size: 20px;">{{ number_format($gainMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
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
                                <canvas id="ovlGainsLivreursMoisDonut"></canvas>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <ul class="ovl-list-compact" id="ovlGainsLivreursMoisLegend"></ul>
                            <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                <div>Total</div>
                                <div id="ovlGainsLivreursMoisTotal">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ovl-kpi">
                    <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                        <div class="ovl-kpi-label" style="margin: 0;">Dépenses par livreur</div>
                        <div class="text-muted" style="font-size: 12px;">Top 8 + Autre</div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-5">
                            <div class="ovl-donut-wrap" style="height: 190px;">
                                <canvas id="ovlDepensesLivreursMoisDonut"></canvas>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <ul class="ovl-list-compact" id="ovlDepensesLivreursMoisLegend"></ul>
                            <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                <div>Total</div>
                                <div id="ovlDepensesLivreursMoisTotal">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Synthèse financière (mois en cours)</div>
                <div class="ovl-panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="ovl-kpi" style="border-left: 6px solid #28a745;">
                                <div class="ovl-kpi-label">Revenus Total</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($revenusTotalMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-md-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #fd7e14;">
                                <div class="ovl-kpi-label">Montant livraison</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($montantLivraisonsPayeesMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #6c757d;">
                                <div class="ovl-kpi-label">Dépenses fonctionnement</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($depensesLivreursMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                            <div class="ovl-kpi" style="border-left: 6px solid #0d6efd;">
                                <div class="ovl-kpi-label">Paiement livreurs</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($paieLivreursMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="ovl-kpi" style="border-left: 6px solid #ffc107;">
                                <div class="ovl-kpi-label">Gain</div>
                                <div class="ovl-kpi-value" style="font-size: 22px;">{{ number_format($gainMois ?? 0, 0, ',', ' ') }} XOF</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Répartition des colis reçus (mois en cours)</div>
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
                                            <canvas id="ovlBoutiquesMoisDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlBoutiquesMoisLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlBoutiquesMoisTotal">0</div>
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
                                            <canvas id="ovlClientsMoisDonut"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <ul class="ovl-list-compact" id="ovlClientsMoisLegend"></ul>
                                        <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                            <div>Total</div>
                                            <div id="ovlClientsMoisTotal">0</div>
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

                var gainsLivreursMoisLabels = @json($repartitionGainsLivreursMoisLabels ?? []);
                var gainsLivreursMoisData = @json($repartitionGainsLivreursMoisData ?? []);
                var gainsLivreursMoisColors = ['#36b58f', '#2bb3c0', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#ced4da'];

                var gainsLivreursMoisTotal = gainsLivreursMoisData.reduce(function (a, b) { return a + b; }, 0);
                var gainsLivreursMoisTotalEl = document.getElementById('ovlGainsLivreursMoisTotal');
                if (gainsLivreursMoisTotalEl) {
                    gainsLivreursMoisTotalEl.textContent = fmt(gainsLivreursMoisTotal);
                }

                var gainsLivreursMoisCanvas = document.getElementById('ovlGainsLivreursMoisDonut');
                if (gainsLivreursMoisCanvas && gainsLivreursMoisLabels.length) {
                    new Chart(gainsLivreursMoisCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: gainsLivreursMoisLabels,
                            datasets: [
                                { data: gainsLivreursMoisData, backgroundColor: gainsLivreursMoisLabels.map(function (_, i) { return gainsLivreursMoisColors[i % gainsLivreursMoisColors.length]; }), borderWidth: 0 }
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

                var gainsLivreursMoisLegend = document.getElementById('ovlGainsLivreursMoisLegend');
                if (gainsLivreursMoisLegend && gainsLivreursMoisLabels.length) {
                    gainsLivreursMoisLegend.innerHTML = gainsLivreursMoisLabels
                        .map(function (label, i) {
                            var color = gainsLivreursMoisColors[i % gainsLivreursMoisColors.length];
                            var val = gainsLivreursMoisData[i] || 0;
                            return (
                                '<li>' +
                                '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                                '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                                '</li>'
                            );
                        })
                        .join('');
                }

                var depensesLivreursMoisLabels = @json($repartitionDepensesLivreursMoisLabels ?? []);
                var depensesLivreursMoisData = @json($repartitionDepensesLivreursMoisData ?? []);
                var depensesLivreursMoisColors = ['#fd7e14', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#7b5cd6', '#36b58f', '#2bb3c0', '#ced4da'];

                var depensesLivreursMoisTotal = depensesLivreursMoisData.reduce(function (a, b) { return a + b; }, 0);
                var depensesLivreursMoisTotalEl = document.getElementById('ovlDepensesLivreursMoisTotal');
                if (depensesLivreursMoisTotalEl) {
                    depensesLivreursMoisTotalEl.textContent = fmt(depensesLivreursMoisTotal);
                }

                var depensesLivreursMoisCanvas = document.getElementById('ovlDepensesLivreursMoisDonut');
                if (depensesLivreursMoisCanvas && depensesLivreursMoisLabels.length) {
                    new Chart(depensesLivreursMoisCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: depensesLivreursMoisLabels,
                            datasets: [
                                { data: depensesLivreursMoisData, backgroundColor: depensesLivreursMoisLabels.map(function (_, i) { return depensesLivreursMoisColors[i % depensesLivreursMoisColors.length]; }), borderWidth: 0 }
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

                var depensesLivreursMoisLegend = document.getElementById('ovlDepensesLivreursMoisLegend');
                if (depensesLivreursMoisLegend && depensesLivreursMoisLabels.length) {
                    depensesLivreursMoisLegend.innerHTML = depensesLivreursMoisLabels
                        .map(function (label, i) {
                            var color = depensesLivreursMoisColors[i % depensesLivreursMoisColors.length];
                            var val = depensesLivreursMoisData[i] || 0;
                            return (
                                '<li>' +
                                '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                                '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                                '</li>'
                            );
                        })
                        .join('');
                }

                var boutiquesMoisLabels = @json($repartitionBoutiquesMoisLabels ?? []);
                var boutiquesMoisData = @json($repartitionBoutiquesMoisData ?? []);
                var boutiquesMoisColors = ['#3fb98f', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#63e6be', '#74c0fc', '#ced4da'];

                var boutiquesMoisTotal = boutiquesMoisData.reduce(function (a, b) { return a + b; }, 0);
                var boutiquesMoisTotalEl = document.getElementById('ovlBoutiquesMoisTotal');
                if (boutiquesMoisTotalEl) {
                    boutiquesMoisTotalEl.textContent = fmt(boutiquesMoisTotal);
                }

                var boutiquesMoisCanvas = document.getElementById('ovlBoutiquesMoisDonut');
                if (boutiquesMoisCanvas && boutiquesMoisLabels.length) {
                    new Chart(boutiquesMoisCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: boutiquesMoisLabels,
                            datasets: [
                                { data: boutiquesMoisData, backgroundColor: boutiquesMoisLabels.map(function (_, i) { return boutiquesMoisColors[i % boutiquesMoisColors.length]; }), borderWidth: 0 }
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

                var boutiquesMoisLegend = document.getElementById('ovlBoutiquesMoisLegend');
                if (boutiquesMoisLegend && boutiquesMoisLabels.length) {
                    boutiquesMoisLegend.innerHTML = boutiquesMoisLabels
                        .map(function (label, i) {
                            var color = boutiquesMoisColors[i % boutiquesMoisColors.length];
                            var val = boutiquesMoisData[i] || 0;
                            return (
                                '<li>' +
                                '<div><span class="ovl-dot" style="background:' + color + '"></span>' + label + '</div>' +
                                '<div style="font-weight:700;">' + fmt(val) + '</div>' +
                                '</li>'
                            );
                        })
                        .join('');
                }

                var clientsMoisLabels = @json($repartitionClientsMoisLabels ?? []);
                var clientsMoisData = @json($repartitionClientsMoisData ?? []);
                var clientsMoisColors = ['#2bb3c0', '#36b58f', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#63e6be', '#74c0fc', '#ced4da'];

                var clientsMoisTotal = clientsMoisData.reduce(function (a, b) { return a + b; }, 0);
                var clientsMoisTotalEl = document.getElementById('ovlClientsMoisTotal');
                if (clientsMoisTotalEl) {
                    clientsMoisTotalEl.textContent = fmt(clientsMoisTotal);
                }

                var clientsMoisCanvas = document.getElementById('ovlClientsMoisDonut');
                if (clientsMoisCanvas && clientsMoisLabels.length) {
                    new Chart(clientsMoisCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: clientsMoisLabels,
                            datasets: [
                                { data: clientsMoisData, backgroundColor: clientsMoisLabels.map(function (_, i) { return clientsMoisColors[i % clientsMoisColors.length]; }), borderWidth: 0 }
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

                var clientsMoisLegend = document.getElementById('ovlClientsMoisLegend');
                if (clientsMoisLegend && clientsMoisLabels.length) {
                    clientsMoisLegend.innerHTML = clientsMoisLabels
                        .map(function (label, i) {
                            var color = clientsMoisColors[i % clientsMoisColors.length];
                            var val = clientsMoisData[i] || 0;
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
