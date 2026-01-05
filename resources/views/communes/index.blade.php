
@extends('layout.main')

@section('title', 'Communes')
@section('page_title', 'Communes')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $totalCommunes ?? 0 }}</h3>
          <p>Total communes</p>
        </div>
        <div class="icon">
          <i class="fas fa-map-marked-alt"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterCommune"><i class="fas fa-plus"></i> Ajouter une commune</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Commune</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($communes as $commune)
              <tr>
                <td>
                  <a href="{{ route('communes.prix.index', $commune) }}">{{ $commune->nom_commune }}</a>
                </td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierCommune{{ $commune->commune_id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('communes.destroy', $commune) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette commune ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="2" class="text-center">Aucune commune enregistrée</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $communes->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterCommune" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une commune</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nom commune</label>
            <input type="text" class="form-control" name="nom_commune" required>
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

@foreach($communes as $commune)
<div class="modal fade" id="modalModifierCommune{{ $commune->commune_id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier la commune</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.update', $commune) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom commune</label>
            <input type="text" class="form-control" name="nom_commune" value="{{ $commune->nom_commune }}" required>
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
