@extends('layout.main')

@section('title', 'Prix par zone')
@section('page_title', 'Prix par zone')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $totalPrix ?? 0 }}</h3>
          <p>Total prix</p>
        </div>
        <div class="icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-9 col-6">
      <div class="card">
        <div class="card-body">
          <strong>Zone :</strong> {{ $zone->nom_zone ?? 'N/A' }}
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterPrixZone"><i class="fas fa-plus"></i> Définir un prix</a>
      <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modalAssocierCommune"><i class="fas fa-link"></i> Associer une commune</a>
      <a href="{{ route('communes.zones') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Zone</th>
                <th>Commune</th>
                <th>Prix</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($prixRows as $row)
              <tr>
                <td>{{ $zone->nom_zone }}</td>
                <td>{{ $row->commune->nom_commune ?? 'N/A' }}</td>
                <td>{{ number_format($row->prix, 0, ',', ' ') }}</td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierPrixZone{{ $row->prix_id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('communes.zones.prix.destroy', [$zone, $row]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce prix ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center">Aucun prix enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $prixRows->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAssocierCommune" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Associer une commune à la zone</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.zones.prix.attach', $zone) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Commune</label>
            <select class="form-control" name="commune_id" required>
              @foreach($communesDisponibles as $c)
              <option value="{{ $c->commune_id }}">{{ $c->nom_commune }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Associer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterPrixZone" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Définir un prix</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.zones.prix.store', $zone) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Commune</label>
            <select class="form-control" name="commune_id" required>
              @foreach($communesAssociees as $c)
              <option value="{{ $c->commune_id }}">{{ $c->nom_commune }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Prix</label>
            <input type="number" class="form-control" name="prix" min="0" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($prixRows as $row)
<div class="modal fade" id="modalModifierPrixZone{{ $row->prix_id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le prix</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.zones.prix.update', [$zone, $row]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Commune</label>
            <select class="form-control" name="commune_id" required>
              @foreach($communesAssociees as $c)
              <option value="{{ $c->commune_id }}" {{ (int)$row->commune_id === (int)$c->commune_id ? 'selected' : '' }}>{{ $c->nom_commune }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Prix</label>
            <input type="number" class="form-control" name="prix" min="0" value="{{ $row->prix }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endsection
