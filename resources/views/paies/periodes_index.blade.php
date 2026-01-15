@extends('layout.main')

@section('title', 'Paie livreurs')
@section('page_title', 'Paie livreurs')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $statsPeriodes['total'] ?? 0 }}</h3>
          <p>Total périodes</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format(($statsPeriodes['montant_total_paye'] ?? 0), 0, ',', ' ') }}</h3>
          <p>Total payé</p>
        </div>
        <div class="icon">
          <i class="ion ion-person-add"></i>
        </div>
        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-primary">
        <div class="inner">
          <h3>{{ $statsPeriodes['en_cours'] ?? 0 }}</h3>
          <p>Périodes en cours</p>
        </div>
        <div class="icon">
          <i class="ion ion-stats-bars"></i>
        </div>
        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $statsPeriodes['paye'] ?? 0 }}</h3>
          <p>Périodes payées</p>
        </div>
        <div class="icon">
          <i class="ion ion-pie-graph"></i>
        </div>
        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('paies.periodes.store') }}">
            @csrf
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Date début</label>
                  <input type="date" name="date_debut" class="form-control" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Date fin</label>
                  <input type="date" name="date_fin" class="form-control" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Libellé (optionnel)</label>
                  <input type="text" name="libelle" class="form-control" placeholder="Paie quinzaine...">
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Créer la période</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Libellé</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($periodes as $periode)
                <tr>
                  <td>{{ $periode->libelle }}</td>
                  <td>{{ $periode->date_debut ? \Carbon\Carbon::parse($periode->date_debut)->format('d/m/Y') : '' }}</td>
                  <td>{{ $periode->date_fin ? \Carbon\Carbon::parse($periode->date_fin)->format('d/m/Y') : '' }}</td>
                  <td>{{ $periode->statut }}</td>
                  <td>
                    <a class="btn btn-sm btn-primary" href="{{ route('paies.periodes.show', $periode) }}"><i class="fas fa-eye"></i></a>
                    <form method="POST" action="{{ route('paies.periodes.destroy', $periode) }}" class="d-inline" onsubmit="return confirm('Supprimer cette période ?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Aucune période</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $periodes->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
