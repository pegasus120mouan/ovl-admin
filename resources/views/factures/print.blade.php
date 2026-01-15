<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>{{ $facture->numero }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
    .title { font-size: 18px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f5f5f5; text-align: left; }
    .right { text-align: right; }
    .muted { color: #666; }
  </style>
</head>
<body>
  <div class="header">
    <div>
      <div class="title">Facture {{ $facture->numero }}</div>
      <div class="muted">Date: {{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '' }}</div>
      <div class="muted">
        Periode:
        {{ $facture->date_debut ? \Carbon\Carbon::parse($facture->date_debut)->format('d/m/Y') : '' }}
        @if($facture->date_fin)
          - {{ \Carbon\Carbon::parse($facture->date_fin)->format('d/m/Y') }}
        @endif
      </div>
    </div>
    <div>
      <div><strong>Client</strong></div>
      <div>{{ $facture->client->nom ?? '' }} {{ $facture->client->prenoms ?? '' }}</div>
      <div>{{ $facture->client->contact ?? '' }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 70px;">Quantite</th>
        <th>Designation</th>
        <th style="width: 120px;" class="right">Prix unitaire</th>
        <th style="width: 120px;" class="right">Prix total</th>
        <th style="width: 90px;">Statut</th>
      </tr>
    </thead>
    <tbody>
      @foreach($facture->lignes as $ligne)
      <tr>
        <td>{{ $ligne->quantite }}</td>
        <td>{{ $ligne->designation }}</td>
        <td class="right">{{ number_format($ligne->prix_unitaire ?? 0, 0, ',', ' ') }}</td>
        <td class="right">{{ number_format($ligne->prix_total ?? 0, 0, ',', ' ') }}</td>
        <td>{{ $ligne->statut }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table style="margin-top: 12px; width: 100%;">
    <tr>
      <td class="right" style="border: none;"><strong>Total</strong></td>
      <td class="right" style="border: none; width: 140px;"><strong>{{ number_format($facture->total_ttc ?? 0, 0, ',', ' ') }}</strong></td>
    </tr>
  </table>
</body>
</html>
