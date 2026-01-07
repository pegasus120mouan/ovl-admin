<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Point des versements à effectuer (Clients)</title>
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
            padding: 14px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    @php
        $boutiqueNom = $client->boutique->nom ?? trim(($client->nom ?? '') . ' ' . ($client->prenoms ?? ''));
    @endphp

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

    <div class="title-box">Point des montants à verser</div>

    <div class="info-section">
        <div class="info-row"><strong>Boutique:</strong> {{ $boutiqueNom }}</div>
        <div class="info-row">
            <strong>Du :</strong> {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}
            &nbsp;&nbsp;&nbsp;
            <strong>Au :</strong> {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 32%;">Boutique</th>
                <th style="width: 34%;">Date de livraison</th>
                <th style="width: 34%;">Montant à verser</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td>{{ $boutiqueNom }}</td>
                <td>{{ $row->jour ? \Carbon\Carbon::parse($row->jour)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ number_format((int) ($row->montant_a_verser ?? 0), 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">Aucune donnée</td>
            </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="1">Total</td>
                <td colspan="2">{{ number_format((int) ($totalMontant ?? 0), 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
