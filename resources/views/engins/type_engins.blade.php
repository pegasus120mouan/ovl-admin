
@extends('layout.main')

@section('title', "Types d'engins")
@section('page_title', "Types d'engins")

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $colisRecusMois ?? 0 }}</h3>
          <p>Colis reçus (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $colisLivresMois ?? 0 }}</h3>
          <p>Colis livrés (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-bar"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $colisNonLivresMois ?? 0 }}</h3>
          <p>Colis non livrés (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-plus"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $colisRetoursMois ?? 0 }}</h3>
          <p>Colis retour (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-pie"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterTypeEngin"><i class="fas fa-plus"></i> Ajouter un type</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($types as $type)
              <tr>
                <td>{{ $type->id }}</td>
                <td>{{ $type->type }}</td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierTypeEngin{{ $type->id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('engins.type_engins.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce type ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">Aucun type enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $types->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterTypeEngin" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un type</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('engins.type_engins.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Type</label>
            <input type="text" class="form-control" name="type" required>
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

@foreach($types as $type)
<div class="modal fade" id="modalModifierTypeEngin{{ $type->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le type #{{ $type->id }}</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('engins.type_engins.update', $type) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Type</label>
            <input type="text" class="form-control" name="type" value="{{ $type->type }}" required>
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
