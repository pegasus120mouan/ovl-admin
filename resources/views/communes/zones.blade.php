
@extends('layout.main')

@section('title', 'Zones')
@section('page_title', 'Zones')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $totalZones ?? 0 }}</h3>
          <p>Total zones</p>
        </div>
        <div class="icon">
          <i class="fas fa-map"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterZone"><i class="fas fa-plus"></i> Ajouter une zone</a>
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
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($zones as $zone)
              <tr>
                <td>
                  <a href="{{ route('communes.zones.prix.index', $zone) }}">{{ $zone->nom_zone }}</a>
                </td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierZone{{ $zone->zone_id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('communes.zones.destroy', $zone) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette zone ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="2" class="text-center">Aucune zone enregistrée</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $zones->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterZone" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une zone</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.zones.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nom zone</label>
            <input type="text" class="form-control" name="nom_zone" required>
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

@foreach($zones as $zone)
<div class="modal fade" id="modalModifierZone{{ $zone->zone_id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier la zone</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.zones.update', $zone) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom zone</label>
            <input type="text" class="form-control" name="nom_zone" value="{{ $zone->nom_zone }}" required>
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
