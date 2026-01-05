<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Les coûts des livraisons - {{ $zoneOrigine->nom_zone ?? ($commune->nom_commune ?? 'Zone') }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo-cell {
            width: 120px;
            vertical-align: top;
        }
        .logo {
            width: 150px;
            height: auto;
        }
        .company-name {
            font-size: 25px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            text-align: center;
        }
        .company-details {
            font-size: 10px;
            color: #000;
            line-height: 1.6;
            text-align: center;
        }
        .title-box {
            border: 2px solid #000;
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 24px;
            background: #f1c40f;
        }
        .subtitle-box {
            border: 1px solid #000;
            text-align: center;
            padding: 8px;
            margin: 10px 0 20px 0;
            font-size: 16px;
            background: #d9d9d9;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background-color: #bdbdbd;
            color: #000;
            padding: 10px;
            text-align: center;
            border: 1px solid #000;
            font-size: 16px;
        }
        .data-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #000;
            font-size: 16px;
        }
        .data-table td.cost {
            text-align: right;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('img/logo.png') }}" alt="OVL Logo" class="logo">
            </td>
            <td>
                <div class="company-name">OVL DELIVERY SERVICES</div>
                <div class="company-details">
                    <img src="{{ public_path('img/icones/home.png') }}" style="width: 10px; height: 10px;"> Sarl au Capital de 1 000 000 CFA<br>
                    <img src="{{ public_path('img/icones/finance.png') }}" style="width: 10px; height: 10px;"> Cocody Riviera Golf en face de l'Ambassade des USA<br>
                    <img src="{{ public_path('img/icones/telephone.png') }}" style="width: 10px; height: 10px;"> Tel: +225 0787703000 - +2250584528385<br>
                    <img src="{{ public_path('img/icones/email.png') }}" style="width: 10px; height: 10px;"> Email: finance@ovl-delivery.online<br>
                    <img src="{{ public_path('img/icones/internet.png') }}" style="width: 10px; height: 10px;"> ovl-delivery.online<br>
                    <img src="{{ public_path('img/icones/whatsapp.png') }}" style="width: 10px; height: 10px;"> +2250584528385
                </div>
            </td>
        </tr>
    </table>

    <div class="title-box">Les coûts des livraisons</div>

    <div class="subtitle-box">Type de Livraisons : Programmées</div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Commune de récupération</th>
                <th>Commune de destination</th>
                <th>Coût de livraison</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prixRows as $row)
            <tr>
                <td>{{ $zoneOrigine->nom_zone ?? ($commune->nom_commune ?? 'N/A') }}</td>
                <td>{{ $row->commune->nom_commune ?? 'N/A' }}</td>
                <td class="cost">{{ number_format($row->prix ?? 0, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
