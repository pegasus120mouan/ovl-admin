@extends('layout.main')

@section('title', 'Tableau de bord')

@section('page_title', 'Dashboard')

@section('content')
    <style>
        .ovl-panel-title {
            background: #3f4f59;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 10px 14px;
            border-radius: 2px 2px 0 0;
            text-transform: uppercase;
            font-size: 14px;
        }
        .ovl-panel-body {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-top: 0;
            padding: 14px;
        }
        .ovl-kpi {
            border: 1px solid rgba(0, 0, 0, 0.07);
            background: #ffffff;
            padding: 14px;
            height: 100%;
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
            border-radius: 2px;
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
            <div class="col-lg-4">
                <div class="ovl-kpi">
                    <div class="ovl-kpi-label">Page Engagement</div>
                    <div class="ovl-kpi-value">2,484</div>
                    <div class="ovl-chart-wrap">
                        <canvas id="ovlPageEngagement"></canvas>
                    </div>
                    <div class="ovl-mini-metric">
                        <div class="ovl-mini-item">
                            <div>Previous period</div>
                            <strong><span class="ovl-delta down">-35%</span></strong>
                        </div>
                        <div class="ovl-mini-item">
                            <div>Previous year</div>
                            <strong><span class="ovl-delta up">23%</span></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="ovl-kpi">
                    <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                        <div class="ovl-kpi-label" style="margin: 0;">Action Types</div>
                        <div class="text-muted" style="font-size: 12px;">
                            <span>Sessions</span>
                            <i class="fas fa-caret-down ml-1"></i>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="ovl-donut-wrap">
                                <canvas id="ovlActionTypes"></canvas>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 6px;">
                                <div style="font-weight: 700;">Ad Group</div>
                                <div class="text-muted" style="font-weight: 700;">Sessions</div>
                            </div>
                            <ul class="ovl-list-compact" id="ovlActionTypesLegend"></ul>
                            <div class="d-flex justify-content-between" style="padding-top: 8px; font-weight: 700;">
                                <div>Total</div>
                                <div id="ovlActionTypesTotal">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Twitter</div>
                <div class="ovl-panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-4">
                            <div class="ovl-tile-green">
                                <div class="ovl-tile-label">Followers Count</div>
                                <div class="ovl-tile-value">2,237</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 mt-3 mt-md-0">
                            <div class="ovl-tile-green">
                                <div class="ovl-tile-label">Mentions Count</div>
                                <div class="ovl-tile-value">191</div>
                            </div>
                            <div class="ovl-kpi mt-3" style="padding: 12px;">
                                <div class="ovl-kpi-label">Retweet Count</div>
                                <div class="d-flex align-items-end justify-content-between" style="gap: 10px;">
                                    <div style="font-size: 20px; font-weight: 800;">2,110</div>
                                    <div class="text-right" style="font-size: 11px; color: #6c757d;">
                                        <div>Previous period <span class="ovl-delta up">55%</span></div>
                                        <div>Previous year <span class="ovl-delta up">136%</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-4 mt-3 mt-md-0">
                            <div class="ovl-kpi">
                                <div class="ovl-kpi-label">Top Retweeted Posts</div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="ovl-donut-wrap" style="height: 150px;">
                                            <canvas id="ovlTwitterPosts"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between" style="font-weight: 700; margin-bottom: 6px;">
                                            <div>Campaign</div>
                                            <div class="text-muted">Comments</div>
                                        </div>
                                        <ul class="ovl-list-compact" id="ovlTwitterLegend"></ul>
                                    </div>
                                </div>
                                <div class="mt-2" style="height: 8px; background: rgba(0,0,0,0.08); border-radius: 20px; overflow: hidden;">
                                    <div style="height: 100%; width: 28%; background: rgba(0,0,0,0.28);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="ovl-panel-title">Instagram</div>
                <div class="ovl-panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-4">
                            <div class="ovl-tile-green">
                                <div class="ovl-tile-label">Followers Count</div>
                                <div class="ovl-tile-value">294</div>
                            </div>
                        </div>
                        <div class="col-lg-9 col-md-8 mt-3 mt-md-0">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="ovl-kpi" style="padding: 12px;">
                                        <div class="ovl-kpi-label">Profile Views</div>
                                        <div class="d-flex align-items-end justify-content-between" style="gap: 10px;">
                                            <div style="font-size: 22px; font-weight: 800;">65</div>
                                            <div style="font-size: 11px; color: #6c757d;">
                                                <div>Previous period <span class="ovl-delta down">-79%</span></div>
                                                <div>Previous year <span class="ovl-delta down">-91%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ovl-kpi mt-3" style="padding: 12px;">
                                        <div class="ovl-kpi-label">Website Clicks</div>
                                        <div class="d-flex align-items-end justify-content-between" style="gap: 10px;">
                                            <div style="font-size: 22px; font-weight: 800;">29</div>
                                            <div style="font-size: 11px; color: #6c757d;">
                                                <div>Previous period <span class="ovl-delta down">-84%</span></div>
                                                <div>Previous year <span class="ovl-delta down">-97%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8 mt-3 mt-lg-0">
                                    <div class="ovl-kpi">
                                        <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                            <div class="ovl-kpi-label" style="margin: 0;">Engagement by Post</div>
                                            <div class="text-muted" style="font-size: 12px;">
                                                <span>Clicks</span>
                                                <i class="fas fa-caret-down ml-1"></i>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="ovl-donut-wrap" style="height: 160px;">
                                                    <canvas id="ovlInstagramEngagement"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between" style="font-weight: 700; margin-bottom: 6px;">
                                                    <div>Campaign</div>
                                                    <div class="text-muted">Clicks</div>
                                                </div>
                                                <ul class="ovl-list-compact" id="ovlInstagramLegend"></ul>
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
    </div>

    <script>
        (function () {
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

            var actionData = [175, 138, 96, 94, 85, 81, 69, 59, 54, 4];
            var actionLabels = [
                'Ipsum eu non risus',
                'Tellus ipsum ipsum',
                'Id suscipit faucibus',
                'Quis aliquet',
                'Augue ipsum',
                'Suscipit nunc proin curabitur',
                'Sed erat adipiscing',
                'Semper',
                'Vestibulum amet suscipit',
                'Autre'
            ];
            var actionColors = ['#3fb98f', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#845ef7', '#63e6be', '#74c0fc', '#ced4da'];
            var actionTotal = actionData.reduce(function (a, b) { return a + b; }, 0);
            var totalEl = document.getElementById('ovlActionTypesTotal');
            if (totalEl) {
                totalEl.textContent = fmt(actionTotal);
            }

            var donut1 = document.getElementById('ovlActionTypes');
            if (donut1) {
                new Chart(donut1.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: actionLabels,
                        datasets: [
                            {
                                data: actionData,
                                backgroundColor: actionColors,
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true }
                        }
                    }
                });
            }

            var legendEl = document.getElementById('ovlActionTypesLegend');
            if (legendEl) {
                legendEl.innerHTML = actionLabels
                    .map(function (label, i) {
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + actionColors[i] + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(actionData[i]) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }

            var twData = [84, 54, 44, 18, 11, 9];
            var twLabels = ['Lorem quis ligula', 'Ipsum cursus', 'Tempor leo non pellentesque', 'Eleifend leo', 'Nunc urna feugiat', 'Cursus metus agitis'];
            var twColors = ['#3fb98f', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7'];

            var twCanvas = document.getElementById('ovlTwitterPosts');
            if (twCanvas) {
                new Chart(twCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: twLabels,
                        datasets: [
                            { data: twData, backgroundColor: twColors, borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            var twLegend = document.getElementById('ovlTwitterLegend');
            if (twLegend) {
                twLegend.innerHTML = twLabels
                    .map(function (label, i) {
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + twColors[i] + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(twData[i]) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }

            var igData = [247, 237, 144, 104, 64, 58, 31];
            var igLabels = ['Consectetur', 'Ut tempor', 'Faucibus tristique', 'Luctus adipiscing', 'Feugiat sit faucibus', 'Nulla suscipit non tellus quis', 'Adipiscing vestibulum'];
            var igColors = ['#3fb98f', '#7b5cd6', '#ff6b6b', '#ffa94d', '#ffd43b', '#4dabf7', '#63e6be'];

            var igCanvas = document.getElementById('ovlInstagramEngagement');
            if (igCanvas) {
                new Chart(igCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: igLabels,
                        datasets: [
                            { data: igData, backgroundColor: igColors, borderWidth: 0 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            var igLegend = document.getElementById('ovlInstagramLegend');
            if (igLegend) {
                igLegend.innerHTML = igLabels
                    .map(function (label, i) {
                        return (
                            '<li>' +
                            '<div><span class="ovl-dot" style="background:' + igColors[i] + '"></span>' + label + '</div>' +
                            '<div style="font-weight:700;">' + fmt(igData[i]) + '</div>' +
                            '</li>'
                        );
                    })
                    .join('');
            }
        })();
    </script>
@endsection
