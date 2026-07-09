@extends('layout.main')

@section('title', 'Points Livreurs')
@section('page_title', 'Points des Livreurs')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ number_format($totalRecette, 0, ',', ' ') }}</h3>
          <p>Recette Totale</p>
        </div>
        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ number_format($totalGain, 0, ',', ' ') }}</h3>
          <p>Gain Total</p>
        </div>
        <div class="icon"><i class="fas fa-chart-line"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format($totalDepense, 0, ',', ' ') }}</h3>
          <p>Depenses Totales</p>
        </div>
        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $nombreLivreurs }}</h3>
          <p>Livreurs Actifs</p>
        </div>
        <div class="icon"><i class="fas fa-motorcycle"></i></div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterPoint"><i class="fas fa-edit"></i> Enregistrer un point</a>
      <a href="#" class="btn btn-danger"><i class="fas fa-file-export"></i> Exporter un point</a>
      <form action="{{ route('points-livreurs.sync-recettes') }}" method="POST" class="d-inline">
        @csrf
        <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="form-control d-inline-block" style="width: 170px; vertical-align: middle;">
        <button type="submit" class="btn btn-success" style="vertical-align: middle;"><i class="fas fa-sync-alt"></i> Sync recettes</button>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Livreur</th>
                <th>Recette</th>
                <th>Depense</th>
                <th>Gain</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pointsLivreurs as $point)
              <tr>
                <td>{{ $point->livreur->nom ?? 'N/A' }} {{ $point->livreur->prenoms ?? '' }}</td>
                <td>{{ number_format($point->recette, 0, ',', ' ') }}</td>
                <td>{{ number_format($point->depense, 0, ',', ' ') }}</td>
                <td><span class="text-success font-weight-bold">{{ number_format($point->gain_jour, 0, ',', ' ') }}</span></td>
                <td>{{ $point->date_commande ? \Carbon\Carbon::parse($point->date_commande)->format('d/m/Y') : 'N/A' }}</td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifier{{ $point->id }}"><i class="fas fa-edit"></i></a>
                  <button type="button"
                          class="btn btn-sm btn-danger"
                          data-toggle="modal"
                          data-target="#modalConfirmDeletePoint"
                          data-action="{{ route('points-livreurs.destroy', $point->id) }}"
                          data-livreur="{{ trim(($point->livreur->nom ?? 'N/A') . ' ' . ($point->livreur->prenoms ?? '')) }}"
                          data-date="{{ $point->date_commande ? \Carbon\Carbon::parse($point->date_commande)->format('d/m/Y') : 'N/A' }}"
                          data-recette="{{ number_format($point->recette, 0, ',', ' ') }}"
                          data-gain="{{ number_format($point->gain_jour, 0, ',', ' ') }}">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">Aucun point livreur trouve</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $pointsLivreurs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter Point -->
<div class="modal fade" id="modalAjouterPoint" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enregistrer un point</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('points-livreurs.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Prenom Livreur</label>
            <select class="form-control" name="utilisateur_id" required>
              @foreach($livreurs as $livreur)
              <option value="{{ $livreur->id }}">{{ $livreur->nom }} {{ $livreur->prenoms }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Recettes du jour</label>
            <input type="number" class="form-control" name="recette" placeholder="Recette" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Depenses du jour</label>
            <input type="number" class="form-control" name="depense" placeholder="Depenses du jour">
          </div>
          <input type="hidden" name="date_commande" value="{{ date('Y-m-d') }}">
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-primary">Enregister</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($pointsLivreurs as $point)
<!-- Modal Modifier Point -->
<div class="modal fade" id="modalModifier{{ $point->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le point</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('points-livreurs.update', $point->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Prenom Livreur</label>
            <input type="text" class="form-control" value="{{ $point->livreur->nom ?? '' }} {{ $point->livreur->prenoms ?? '' }}" disabled>
            <input type="hidden" name="utilisateur_id" value="{{ $point->utilisateur_id }}">
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Recettes du jour</label>
            <input type="number" class="form-control" name="recette" value="{{ $point->recette }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Depenses du jour</label>
            <input type="number" class="form-control" name="depense" value="{{ $point->depense }}">
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date</label>
            <input type="date" class="form-control" name="date_commande" value="{{ $point->date_commande ? \Carbon\Carbon::parse($point->date_commande)->format('Y-m-d') : '' }}" required>
          </div>
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-warning">Modifier</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<!-- Modal Confirmation Suppression Point -->
<div class="modal fade" id="modalConfirmDeletePoint" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmation de suppression
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <span class="fa-stack fa-2x">
            <i class="fas fa-circle fa-stack-2x text-danger"></i>
            <i class="fas fa-trash fa-stack-1x fa-inverse"></i>
          </span>
        </div>
        <h5 class="mb-2">Êtes-vous sûr de vouloir supprimer ce point ?</h5>
        <p class="text-muted mb-1">
          <strong id="deletePointLivreur"></strong>
        </p>
        <p class="text-muted mb-0">
          Date : <strong id="deletePointDate"></strong>
          &nbsp;|&nbsp;
          Recette : <strong id="deletePointRecette"></strong> FCFA
          &nbsp;|&nbsp;
          Gain : <strong id="deletePointGain"></strong> FCFA
        </p>
        <div class="alert alert-warning mt-3 mb-0 text-left">
          <i class="fas fa-info-circle mr-1"></i>
          <small>Cette action est irréversible. Le point sera définitivement supprimé.</small>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Annuler
        </button>
        <form id="deletePointForm" method="POST" action="#" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger px-4">
            <i class="fas fa-trash mr-1"></i>Supprimer
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
  $('#modalConfirmDeletePoint').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);

    $('#deletePointForm').attr('action', button.attr('data-action'));
    $('#deletePointLivreur').text(button.attr('data-livreur'));
    $('#deletePointDate').text(button.attr('data-date'));
    $('#deletePointRecette').text(button.attr('data-recette'));
    $('#deletePointGain').text(button.attr('data-gain'));
  });
});
</script>
@endpush
