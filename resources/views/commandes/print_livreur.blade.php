<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Point livreur - {{ ($livreur->nom ?? 'Livreur') . ' ' . ($livreur->prenoms ?? '') }}</title>
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
            color: #f39c12;
            margin-bottom: 5px;
            text-align: center;
        }
        .company-details {
            font-size: 10px;
            color: #333;
            line-height: 1.6;
            text-align: center;
        }
        .title-box {
            border: 2px solid #000;
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 20px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-section p {
            margin: 5px 0;
            font-size: 15px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #6b6969;
            color: #fff;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 14px;
        }
        .status-livre {
            color: #28a745;
            font-weight: bold;
        }
        .status-non-livre {
            color: #dc3545;
            font-weight: bold;
        }
        .status-retour {
            color: #ffc107;
            font-weight: bold;
        }
        .total-row {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        .total-row td {
            border: none;
            padding: 10px;
            font-size: 25px;
            color: #fff;
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
                <div class="company-name" style="color: black;">OVL DELIVERY SERVICES</div>
                <div class="company-details" style="color: black;">
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

    <div class="title-box">
        Point livreur (Base: coût global)
    </div>

    <div class="info-section">
        <p><strong>Livreur:</strong> {{ ($livreur->nom ?? 'N/A') . ' ' . ($livreur->prenoms ?? '') }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr style="background-color: #6b6969ff; color: #0c0c0cff;">
                
                <th>Communes</th>
                <th>Coût global</th>
                <th>Date de réception</th>
                <th>Date de livraison</th>
                <th>Statut</th>
                <th>Boutique</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commandes as $commande)
            <tr>
                
                <td>{{ $commande->communes }}</td>
                <td>{{ number_format($commande->cout_global, 0, ',', ' ') }}</td>
                <td>{{ $commande->date_reception ? \Carbon\Carbon::parse($commande->date_reception)->format('d-m-Y') : 'N/A' }}</td>
                <td>{{ $commande->date_livraison ? \Carbon\Carbon::parse($commande->date_livraison)->format('d-m-Y') : 'N/A' }}</td>
                <td class="{{ $commande->statut == 'Livré' ? 'status-livre' : ($commande->statut == 'Non Livré' ? 'status-non-livre' : 'status-retour') }}">
                    {{ $commande->statut }}
                </td>
                <td>
                    {{ $commande->client?->boutique?->nom ?? trim(($commande->client?->nom ?? '') . ' ' . ($commande->client?->prenoms ?? '')) ?: 'N/A' }}
                </td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
                <td colspan="2"><strong>{{ number_format($total, 0, ',', ' ') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
