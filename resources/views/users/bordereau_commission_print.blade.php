<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>{{ $bordereau->numero }}</title>
  <style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f4f4f4; }
    .muted { color: #666; }
  </style>
</head>
<body>
  <h1>Bordereau {{ $bordereau->numero }}</h1>
  <p class="muted">
    {{ trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')) }}
    — {{ $commercial->code_commercial ?: $commercial->defaultCommercialCode() }}
  </p>
  <p>
    Période : {{ $bordereau->date_debut->format('d/m/Y') }} — {{ $bordereau->date_fin->format('d/m/Y') }}<br>
    Généré le : {{ $bordereau->genere_le->format('d/m/Y H:i') }}<br>
    Taux : {{ $regle ? rtrim(rtrim(number_format((float) $regle->taux, 2, ',', ' '), '0'), ',').' %' : '—' }}
  </p>

  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Client</th>
        <th>Boutique</th>
        <th>N° colis</th>
        <th>Coût livraison</th>
        <th>Commission</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($bordereau->commandes as $commande)
        <tr>
          <td>{{ optional($commande->date_livraison)->format('d/m/Y') }}</td>
          <td>{{ trim(($commande->client->nom ?? '').' '.($commande->client->prenoms ?? '')) }}</td>
          <td>{{ $commande->client->boutique->nom ?? '—' }}</td>
          <td>{{ $commande->reference_externe ?: '#'.$commande->id }}</td>
          <td>{{ number_format((int) $commande->cout_livraison, 0, ',', ' ') }} FCFA</td>
          <td>{{ number_format((int) $commande->pivot->montant, 0, ',', ' ') }} FCFA</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <p>
    Colis : <strong>{{ $bordereau->nb_colis }}</strong><br>
    Base livraison : <strong>{{ number_format($bordereau->base_livraison, 0, ',', ' ') }} FCFA</strong><br>
    Montant : <strong>{{ number_format($bordereau->montant, 0, ',', ' ') }} FCFA</strong><br>
    Payé : <strong>{{ number_format($bordereau->montantPaye(), 0, ',', ' ') }} FCFA</strong><br>
    Reste : <strong>{{ number_format($bordereau->resteAPayer(), 0, ',', ' ') }} FCFA</strong>
  </p>
</body>
</html>
