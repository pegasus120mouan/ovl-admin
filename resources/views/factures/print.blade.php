<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>{{ $facture->numero }}</title>
  <style>
    body { font-family: Helvetica, Arial, DejaVu Sans, sans-serif; font-size: 12px; color: #111; margin: 0; padding: 20px; }
    .header-table { width: 100%; margin-bottom: 20px; }
    .header-table td { border: none; padding: 0; vertical-align: middle; }
    .logo-cell { width: 120px; vertical-align: top; }
    .logo { width: 150px; height: auto; }
    .company-name { font-size: 25px; font-weight: bold; color: #f39c12; margin-bottom: 5px; text-align: center; }
    .company-details { font-size: 10px; color: #333; line-height: 1.6; text-align: center; }
    .divider { border-top: 1px solid #e5e5e5; margin: 10px 0 14px; }
    .invoice-header { width: 100%; margin-bottom: 16px; }
    .invoice-header td { border: none; padding: 0; vertical-align: top; }
    .invoice-header-right { text-align: right; }
    .title { font-size: 18px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f5f5f5; text-align: left; }
    .right { text-align: right; }
    .muted { color: #666; }
    .total-table { margin-top: 14px; width: 100%; }
    .total-label { border: none; text-align: right; font-size: 18px; font-weight: bold; }
    .total-value { border: none; text-align: right; width: 180px; font-size: 22px; font-weight: bold; }
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
  <div class="divider"></div>
  <table class="invoice-header">
    <tr>
      <td>
        <div class="title">Facture {{ $facture->numero }}</div>
        <div class="muted">Date: {{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '' }}</div>
        <div class="muted">
          Periode:
          {{ $facture->date_debut ? \Carbon\Carbon::parse($facture->date_debut)->format('d/m/Y') : '' }}
          @if($facture->date_fin)
            - {{ \Carbon\Carbon::parse($facture->date_fin)->format('d/m/Y') }}
          @endif
        </div>
      </td>
      <td class="invoice-header-right" style="width: 240px;">
        <div><strong>Client</strong></div>
        <div>{{ $facture->client->nom ?? '' }} {{ $facture->client->prenoms ?? '' }}</div>
        <div>{{ $facture->client->contact ?? '' }}</div>
      </td>
    </tr>
  </table>

  <table>
    <thead>
      <tr>
        <th style="width: 70px;">Quantite</th>
        <th>Designation</th>
        <th style="width: 120px;" class="right">Prix unitaire</th>
        <th style="width: 120px;" class="right">Prix total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($facture->lignes as $ligne)
      <tr>
        <td>{{ $ligne->quantite }}</td>
        <td>{{ $ligne->designation }}</td>
        <td class="right">{{ number_format($ligne->prix_unitaire ?? 0, 0, ',', ' ') }}</td>
        <td class="right">{{ number_format($ligne->prix_total ?? 0, 0, ',', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table class="total-table">
    <tr>
      <td class="total-label">Total</td>
      <td class="total-value">{{ number_format($facture->total_ttc ?? 0, 0, ',', ' ') }}</td>
    </tr>
  </table>
</body>
</html>
