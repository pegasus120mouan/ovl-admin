<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Point des versements à effectuer</title>
    <style>
        body {
            font-family: Helvetica, Arial, DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .logo-cell {
            width: 220px;
            vertical-align: top;
        }
        .logo {
            width: 200px;
            height: auto;
        }
        .company-name {
            font-size: 25px;
            font-weight: bold;
            text-align: center;
            color: #000;
            margin-bottom: 4px;
        }
        .company-details {
            font-size: 10px;
            line-height: 1.6;
            text-align: center;
            color: #000;
        }
        .title-box {
            border: 1px solid #000;
            text-align: center;
            padding: 10px;
            margin: 12px 0 18px 0;
            font-weight: bold;
            font-size: 20px;
        }
        .info-section {
            margin: 12px 0 18px 0;
            font-size: 14px;
        }
        .info-row {
            margin: 6px 0;
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
            font-size: 14px;
        }
        .data-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #000;
            font-size: 14px;
        }
        .total-row td {
            background-color: #b9dfe9;
            font-weight: bold;
            border: 1px solid #000;
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
                    <img src="{{ public_path('img/icones/home.png') }}" style="width: 10px; height: 10px;">   Sarl au Capital de 1 000 000 CFA<br>
                    <img src="{{ public_path('img/icones/finance.png') }}" style="width: 10px; height: 10px;">   Cocody Riviera Golf en face de l'Ambassade des USA<br>
                    <img src="{{ public_path('img/icones/telephone.png') }}" style="width: 10px; height: 10px;">   Tel: +225 0787703000 - +2250584528385<br>
                    <img src="{{ public_path('img/icones/email.png') }}" style="width: 10px; height: 10px;">   Email: finance@ovl-delivery.online<br>
                    <img src="{{ public_path('img/icones/internet.png') }}" style="width: 10px; height: 10px;">   ovl-delivery.online<br>
                    <img src="{{ public_path('img/icones/whatsapp.png') }}" style="width: 10px; height: 10px;">   +2250584528385
                </div>
            </td>
        </tr>
    </table>

    <div class="title-box">Point des versements à effectuer</div>

    <div class="info-section">
        <div class="info-row"><strong>Coursier:</strong> {{ ($livreur->nom ?? 'N/A') . ' ' . ($livreur->prenoms ?? '') }}</div>
        <div class="info-row">
            <strong>Du :</strong> {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}
            &nbsp;&nbsp;&nbsp;
            <strong>Au :</strong> {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Montant Global</th>
                <th>Dépenses</th>
                <th>Montant à déposer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ number_format($row['montant_global'] ?? 0, 0, ',', ' ') }}</td>
                <td>{{ number_format($row['depense'] ?? 0, 0, ',', ' ') }}</td>
                <td>{{ number_format($row['montant_a_deposer'] ?? 0, 0, ',', ' ') }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>{{ number_format($totalDepot ?? 0, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
