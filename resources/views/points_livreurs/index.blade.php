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
                  <form action="{{ route('points-livreurs.destroy', $point->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Etes-vous sur de vouloir supprimer?')"><i class="fas fa-trash"></i></button>
                  </form>
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
          <input type="hidden" name="date_commande" value="{{ $point->date_commande }}">
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
@endsection
