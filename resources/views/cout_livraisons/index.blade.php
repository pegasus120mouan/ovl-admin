@extends('layout.main')

@section('title', 'Coût des livraisons')
@section('page_title', 'Coût des livraisons')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $totalCouts ?? 0 }}</h3>
          <p>Total coûts</p>
        </div>
        <div class="icon">
          <i class="fas fa-list"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ number_format($coutMin ?? 0, 0, ',', ' ') }}</h3>
          <p>Coût minimum</p>
        </div>
        <div class="icon">
          <i class="fas fa-arrow-down"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format($coutMax ?? 0, 0, ',', ' ') }}</h3>
          <p>Coût maximum</p>
        </div>
        <div class="icon">
          <i class="fas fa-arrow-up"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ number_format($coutMoyen ?? 0, 0, ',', ' ') }}</h3>
          <p>Coût moyen</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-line"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterCout"><i class="fas fa-plus"></i> Ajouter un coût</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Coût livraison</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($couts as $cout)
              <tr>
                <td>{{ number_format($cout->cout_livraison, 0, ',', ' ') }}</td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierCout{{ $cout->id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('cout-livraisons.destroy', $cout) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce coût ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="2" class="text-center">Aucun coût enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $couts->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterCout" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un coût</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('cout-livraisons.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Coût livraison</label>
            <input type="number" class="form-control" name="cout_livraison" min="0" required>
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

@foreach($couts as $cout)
<div class="modal fade" id="modalModifierCout{{ $cout->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le coût #{{ $cout->id }}</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('cout-livraisons.update', $cout) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Coût livraison</label>
            <input type="number" class="form-control" name="cout_livraison" min="0" value="{{ $cout->cout_livraison }}" required>
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
