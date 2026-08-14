@extends('layout.main')

@section('title', 'Montant des Clients')
@section('page_title', 'Montant des Clients')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $boutiques->total() }}</h3>
          <p>Total Boutiques</p>
        </div>
        <div class="icon"><i class="fas fa-store"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $boutiquesActives ?? 0 }}</h3>
          <p>Boutiques Actives</p>
        </div>
        <div class="icon"><i class="fas fa-check-circle"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $boutiquesInactives ?? 0 }}</h3>
          <p>Boutiques Inactives</p>
        </div>
        <div class="icon"><i class="fas fa-times-circle"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ number_format($totalMontantMois ?? 0, 0, ',', ' ') }}</h3>
          <p>Montant du mois</p>
        </div>
        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-store"></i> Liste des boutiques</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Boutique</th>
                <th>Colis (mois)</th>
                <th>Montant (mois)</th>
                <th>Montant à payer</th>
                <th>Payé</th>
                <th>Reste à payer</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($boutiques as $boutique)
                <tr>
                  <td>
                    @if($boutique->statut && ($boutique->a_client ?? false))
                      <a href="{{ route('points-clients.situation-financiere', $boutique) }}">
                        {{ $boutique->nom }}
                      </a>
                    @else
                      {{ $boutique->nom }}
                    @endif
                  </td>
                  <td>{{ number_format($boutique->nb_colis_mois ?? 0, 0, ',', ' ') }}</td>
                  <td class="font-weight-bold text-success">{{ number_format($boutique->montant_mois ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold">{{ number_format($boutique->montant_a_payer ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold text-success">{{ number_format($boutique->montant_paye ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold {{ ($boutique->reste_a_payer ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ number_format($boutique->reste_a_payer ?? 0, 0, ',', ' ') }} XOF
                  </td>
                  <td>
                    @if($boutique->statut)
                      <span class="badge badge-success">Actif</span>
                    @else
                      <span class="badge badge-danger">Inactif</span>
                    @endif
                  </td>
                  <td>
                    @if($boutique->statut && ($boutique->a_client ?? false))
                      <a href="{{ route('points-clients.situation-financiere', $boutique) }}" class="btn btn-sm btn-info" title="Voir la situation financière">
                        <i class="fas fa-eye"></i> Voir montants
                      </a>
                    @else
                      <button type="button" class="btn btn-sm btn-secondary" disabled title="Boutique inactive ou sans client">
                        <i class="fas fa-eye"></i> Voir montants
                      </button>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center">Aucune boutique trouvée</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $boutiques->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
