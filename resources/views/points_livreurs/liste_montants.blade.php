@extends('layout.main')

@section('title', 'Liste des montants')
@section('page_title', 'Liste des montants')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $statsMois['recus'] ?? 0 }}</h3>
          <p>Colis recus (mois en cours)</p>
        </div>
        <div class="icon"><i class="fas fa-shopping-bag"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $statsMois['livrees'] ?? 0 }}</h3>
          <p>Colis livres (mois en cours)</p>
        </div>
        <div class="icon"><i class="fas fa-chart-bar"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $statsMois['non_livrees'] ?? 0 }}</h3>
          <p>Colis non livres (mois en cours)</p>
        </div>
        <div class="icon"><i class="fas fa-user-plus"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $statsMois['retours'] ?? 0 }}</h3>
          <p>Colis retour (mois en cours)</p>
        </div>
        <div class="icon"><i class="fas fa-chart-pie"></i></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('points-livreurs.liste-montants') }}">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Date debut</label>
                  <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Date fin</label>
                  <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Livreur</label>
                  <select class="form-control" name="livreur_id">
                    <option value="">Tous</option>
                    @foreach($livreurs as $livreur)
                      <option value="{{ $livreur->id }}" {{ (string)$livreurId === (string)$livreur->id ? 'selected' : '' }}>
                        {{ $livreur->nom }} {{ $livreur->prenoms }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Par page</label>
                  <select class="form-control" name="per_page">
                    @foreach([20, 50, 100, 200] as $pp)
                      <option value="{{ $pp }}" {{ request('per_page', 50) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="{{ route('points-livreurs.liste-montants') }}" class="btn btn-light">Reinitialiser</a>
          </form>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Livreur</th>
                <th>Montant</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                <tr>
                  <td>{{ $row->nom ?? 'N/A' }} {{ $row->prenoms ?? '' }}</td>
                  <td>{{ number_format($row->montant ?? 0, 0, ',', ' ') }}</td>
                  <td>{{ $row->jour ? \Carbon\Carbon::parse($row->jour)->format('Y-m-d') : 'N/A' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center">Aucune donnee</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          {{ $rows->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
