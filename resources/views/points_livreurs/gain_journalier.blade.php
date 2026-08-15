@extends('layout.main')

@section('title', 'Gain Journalier')
@section('page_title', 'Gain Journalier')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ number_format($totalRecette, 0, ',', ' ') }}</h3>
          <p>Recette (période)</p>
        </div>
        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format($totalDepense, 0, ',', ' ') }}</h3>
          <p>Dépense (période)</p>
        </div>
        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ number_format($totalGain, 0, ',', ' ') }}</h3>
          <p>Gain (période)</p>
        </div>
        <div class="icon"><i class="fas fa-chart-line"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $nombreJours }}</h3>
          <p>Jours</p>
        </div>
        <div class="icon"><i class="fas fa-calendar-day"></i></div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h6 class="font-weight-bold mb-3">Filtres</h6>
      <form method="GET" action="{{ route('points-livreurs.gain-journalier') }}" class="row align-items-end">
        <div class="col-md-3">
          <label class="font-weight-bold">Date début</label>
          <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}" required>
        </div>
        <div class="col-md-3">
          <label class="font-weight-bold">Date fin</label>
          <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}" required>
        </div>
        <div class="col-md-6">
          <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Filtrer</button>
          <a href="{{ route('points-livreurs.gain-journalier') }}" class="btn btn-light border">Réinitialiser</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-coins mr-1"></i> Gains par jour</h3>
      <span class="float-right text-muted">Cliquez sur une date pour voir le détail</span>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Date</th>
            <th>Livreurs</th>
            <th>Recette</th>
            <th>Dépense</th>
            <th>Gain</th>
            <th class="text-center">Statut</th>
            <th class="text-center" style="width: 220px;">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($gainsJournaliers as $jour)
            @php
              $dateJour = $jour->jour instanceof \Carbon\Carbon
                ? $jour->jour->toDateString()
                : \Carbon\Carbon::parse($jour->jour)->toDateString();
            @endphp
            <tr>
              <td>
                <a href="{{ route('points-livreurs.gain-journalier.detail', ['date' => $dateJour, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" class="font-weight-bold">
                  {{ \Carbon\Carbon::parse($dateJour)->format('d/m/Y') }}
                </a>
              </td>
              <td>{{ (int) $jour->nb_livreurs }}</td>
              <td>{{ number_format((int) $jour->recette, 0, ',', ' ') }} XOF</td>
              <td>{{ number_format((int) $jour->depense, 0, ',', ' ') }} XOF</td>
              <td>
                <span class="text-success font-weight-bold">{{ number_format((int) $jour->gain, 0, ',', ' ') }} XOF</span>
              </td>
              <td class="text-center">
                @if(!empty($jour->transfere))
                  <span class="badge badge-secondary px-3 py-2">Transféré</span>
                @else
                  <span class="badge badge-warning px-3 py-2">Non transféré</span>
                @endif
              </td>
              <td class="text-center text-nowrap">
                <a href="{{ route('points-livreurs.gain-journalier.detail', ['date' => $dateJour, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" class="btn btn-sm btn-info" title="Voir le gain du jour">
                  <i class="fas fa-eye"></i> Voir
                </a>
                @if(!empty($jour->transfere))
                  <button type="button" class="btn btn-sm btn-secondary" disabled title="Déjà transféré">
                    <i class="fas fa-exchange-alt"></i> Transférer
                  </button>
                @else
                  <button type="button"
                          class="btn btn-sm btn-success"
                          data-toggle="modal"
                          data-target="#modalTransferer{{ $loop->index }}"
                          title="Transférer le gain du jour">
                    <i class="fas fa-exchange-alt"></i> Transférer
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">Aucun gain journalier trouvé sur cette période</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $gainsJournaliers->links() }}
    </div>
  </div>
</div>

@foreach($gainsJournaliers as $jour)
  @php
    $dateJour = $jour->jour instanceof \Carbon\Carbon
      ? $jour->jour->toDateString()
      : \Carbon\Carbon::parse($jour->jour)->toDateString();
  @endphp
  @if(empty($jour->transfere))
    <div class="modal fade" id="modalTransferer{{ $loop->index }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>Marquer comme transféré</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <form action="{{ route('points-livreurs.gain-journalier.transferer') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $dateJour }}">
            <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
            <input type="hidden" name="date_fin" value="{{ $dateFin }}">
            <input type="hidden" name="page" value="{{ $gainsJournaliers->currentPage() }}">
            <div class="modal-body">
              <p class="mb-2">Marquer le <strong>{{ \Carbon\Carbon::parse($dateJour)->format('d/m/Y') }}</strong> comme transféré ?</p>
              <p class="mb-0 text-muted">Cette action sert uniquement au suivi. Elle ne modifie ni la recette, ni la dépense, ni le gain.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Non</button>
              <button type="submit" class="btn btn-success">
                <i class="fas fa-exchange-alt mr-1"></i> Confirmer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endforeach
@endsection
