@extends('layout.main')

@section('title', 'Livreurs')
@section('page_title', 'Liste des livreurs')

@section('content')
<div class="container-fluid">
  <!-- Small boxes (Stat box) -->
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $livreurs->total() }}</h3>
          <p>Total Livreurs</p>
        </div>
        <div class="icon">
          <i class="fas fa-users"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $livreursActifs ?? 0 }}</h3>
          <p>Livreurs Actifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-check"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $livreursInactifs ?? 0 }}</h3>
          <p>Livreurs Inactifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-times"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $boutiquesTotal ?? 0 }}</h3>
          <p>Boutiques</p>
        </div>
        <div class="icon">
          <i class="fas fa-store"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterLivreur"><i class="fas fa-user-plus"></i> Enregistrer un livreur</a>
        <a href="#" class="btn btn-danger"><i class="fas fa-file-export"></i> Exporter la liste des livreurs</a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-motorcycle"></i> Livreurs</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Login</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              @forelse($livreurs as $livreur)
                <tr>
                  <td>{{ $livreur->nom }}</td>
                  <td>{{ $livreur->prenoms }}</td>
                  <td>{{ $livreur->contact }}</td>
                  <td>{{ $livreur->login }}</td>
                  <td>
                    @if($livreur->statut_compte)
                      <span class="badge badge-success">Actif</span>
                    @else
                      <span class="badge badge-danger">Inactif</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Aucun livreur trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $livreurs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterLivreur" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Ajouter un livreur</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('users.livreurs.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" required>
          </div>
          <div class="form-group">
            <label>Login</label>
            <input type="text" class="form-control" name="login" required>
          </div>
          <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte">
              <option value="1" selected>Actif</option>
              <option value="0">Inactif</option>
            </select>
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
@endsection
