@extends('layout.main')

@section('title', 'Gain du ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))
@section('page_title', 'Gain du ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))

@section('content')
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
      <div>
        <h4 class="mb-1 font-weight-bold">
          Gain du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
          @if($transfere)
            <span class="badge badge-secondary ml-2">Transféré</span>
          @endif
        </h4>
        <p class="text-muted mb-0">Totaux combinés de tous les livreurs pour cette journée</p>
      </div>
      <div class="d-flex flex-wrap" style="gap: 8px;">
        @if($transfere)
          <button type="button" class="btn btn-secondary" disabled title="Déjà transféré">
            <i class="fas fa-exchange-alt mr-1"></i> Transférer
          </button>
        @else
          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalTransfererDetail">
            <i class="fas fa-exchange-alt mr-1"></i> Transférer
          </button>
        @endif
        <a href="{{ route('points-livreurs.gain-journalier', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" class="btn btn-light border">
          <i class="fas fa-arrow-left mr-1"></i> Retour
        </a>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm h-100" style="border-left: 4px solid #17a2b8;">
        <div class="card-body">
          <div class="text-info font-weight-bold mb-1">Recette du jour</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($totalRecette, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Somme des recettes de tous les livreurs</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100" style="border-left: 4px solid #ffc107;">
        <div class="card-body">
          <div class="text-warning font-weight-bold mb-1">Dépense du jour</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($totalDepense, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Somme des dépenses de tous les livreurs</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100" style="border-left: 4px solid #28a745;">
        <div class="card-body">
          <div class="text-success font-weight-bold mb-1">Gain du jour</div>
          <h3 class="font-weight-bold mb-2 text-success">{{ number_format($totalGain, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Recette − Dépense</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100" style="border-left: 4px solid #dc3545;">
        <div class="card-body">
          <div class="text-danger font-weight-bold mb-1">Livreurs</div>
          <h3 class="font-weight-bold mb-2">{{ $nombreLivreurs }}</h3>
          <small class="text-muted">Livreurs combinés sur cette journée</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-motorcycle mr-1"></i> Détail par livreur</h3>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Livreur</th>
            <th>Recette</th>
            <th>Dépense</th>
            <th>Gain</th>
          </tr>
        </thead>
        <tbody>
          @forelse($points as $point)
            @php
              $gain = $point->gain_jour ?? ((int) $point->recette - (int) $point->depense);
            @endphp
            <tr>
              <td>{{ trim(($point->livreur->nom ?? 'N/A') . ' ' . ($point->livreur->prenoms ?? '')) }}</td>
              <td>{{ number_format((int) $point->recette, 0, ',', ' ') }} XOF</td>
              <td>{{ number_format((int) $point->depense, 0, ',', ' ') }} XOF</td>
              <td><span class="text-success font-weight-bold">{{ number_format((int) $gain, 0, ',', ' ') }} XOF</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center">Aucun point livreur pour cette date</td>
            </tr>
          @endforelse
        </tbody>
        @if($points->isNotEmpty())
          <tfoot>
            <tr class="font-weight-bold bg-light">
              <td>Total</td>
              <td>{{ number_format($totalRecette, 0, ',', ' ') }} XOF</td>
              <td>{{ number_format($totalDepense, 0, ',', ' ') }} XOF</td>
              <td><span class="text-success">{{ number_format($totalGain, 0, ',', ' ') }} XOF</span></td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>

@if(!$transfere)
  <div class="modal fade" id="modalTransfererDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>Marquer comme transféré</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <form action="{{ route('points-livreurs.gain-journalier.transferer') }}" method="POST">
          @csrf
          <input type="hidden" name="date" value="{{ $date }}">
          <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
          <input type="hidden" name="date_fin" value="{{ $dateFin }}">
          <div class="modal-body">
            <p class="mb-2">Marquer le <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong> comme transféré ?</p>
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
@endsection
