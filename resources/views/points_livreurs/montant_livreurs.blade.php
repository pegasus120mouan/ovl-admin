@extends('layout.main')

@section('title', 'Montant des Livreurs')
@section('page_title', 'Montant des Livreurs')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $livreurs->total() }}</h3>
          <p>Total Livreurs</p>
        </div>
        <div class="icon">
          <i class="fas fa-motorcycle"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $livreursActifs ?? 0 }}</h3>
          <p>Livreurs Actifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $livreursInactifs ?? 0 }}</h3>
          <p>Livreurs Inactifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-times"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ number_format($totalMontantMois ?? 0, 0, ',', ' ') }}</h3>
          <p>Montant du mois</p>
        </div>
        <div class="icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-motorcycle"></i> Liste des livreurs</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Nom et prénoms</th>
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
              @forelse($livreurs as $livreur)
                <tr>
                  <td>
                    @if($livreur->statut_compte)
                      <a href="{{ route('points-livreurs.situation-financiere', $livreur) }}">
                        {{ trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? '')) }}
                      </a>
                    @else
                      {{ trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? '')) }}
                    @endif
                  </td>
                  <td>{{ number_format($livreur->nb_colis_mois ?? 0, 0, ',', ' ') }}</td>
                  <td class="font-weight-bold text-success">{{ number_format($livreur->montant_mois ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold">{{ number_format($livreur->montant_a_payer ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold text-success">{{ number_format($livreur->montant_paye ?? 0, 0, ',', ' ') }} XOF</td>
                  <td class="font-weight-bold {{ ($livreur->reste_a_payer ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ number_format($livreur->reste_a_payer ?? 0, 0, ',', ' ') }} XOF
                  </td>
                  <td>
                    @if($livreur->statut_compte)
                      <span class="badge badge-success">Actif</span>
                    @else
                      <span class="badge badge-danger">Inactif</span>
                    @endif
                  </td>
                  <td>
                    @if($livreur->statut_compte)
                      <a href="{{ route('points-livreurs.situation-financiere', $livreur) }}" class="btn btn-sm btn-info" title="Voir la situation financière">
                        <i class="fas fa-eye"></i> Voir montants
                      </a>
                    @else
                      <button type="button" class="btn btn-sm btn-secondary" disabled title="Livreur inactif">
                        <i class="fas fa-eye"></i> Voir montants
                      </button>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center">Aucun livreur trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $livreurs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
