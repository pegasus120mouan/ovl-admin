@extends('layout.main')

@section('title', 'Gestion des statuts')
@section('page_title', 'Gestion des statuts')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $administrateursTotal ?? 0 }}</h3>
          <p>Total Administrateurs</p>
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
          <h3>{{ $administrateursActifs ?? 0 }}</h3>
          <p>Administrateurs Actifs</p>
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
          <h3>{{ $administrateursInactifs ?? 0 }}</h3>
          <p>Administrateurs Inactifs</p>
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
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Critères de recherche</h3>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('users.gestion-statuts') }}">
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Recherche</label>
                <input
                  type="text"
                  name="q"
                  class="form-control"
                  placeholder="Nom, prénoms, contact, login"
                  value="{{ request('q') }}"
                />
              </div>

              <div class="form-group col-md-4">
                <label>Rôle</label>
                <select name="role" class="form-control">
                  <option value="">Tous les rôles</option>
                  <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrateurs</option>
                  <option value="livreur" {{ request('role') === 'livreur' ? 'selected' : '' }}>Livreurs</option>
                  <option value="clients" {{ request('role') === 'clients' ? 'selected' : '' }}>Clients</option>
                </select>
              </div>

              <div class="form-group col-md-4">
                <label>Statut</label>
                <select name="statut" class="form-control">
                  <option value="">Tous</option>
                  <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actif</option>
                  <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
              </div>
            </div>

            <div class="mt-2">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Rechercher
              </button>
              <a href="{{ route('users.gestion-statuts') }}" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Réinitialiser
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-toggle-on"></i> Utilisateurs</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Rôle</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Login</th>
                <th>Statut compte</th>
              </tr>
            </thead>
            <tbody>
              @forelse($utilisateurs as $utilisateur)
                <tr>
                  <td>{{ $utilisateur->role }}</td>
                  <td>{{ $utilisateur->nom }}</td>
                  <td>{{ $utilisateur->prenoms }}</td>
                  <td>{{ $utilisateur->contact }}</td>
                  <td>{{ $utilisateur->login }}</td>
                  <td>
                    <form action="{{ route('users.toggle-statut', $utilisateur) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-{{ $utilisateur->statut_compte ? 'success' : 'danger' }} btn-sm" style="min-width: 90px;">
                        {{ $utilisateur->statut_compte ? 'Actif' : 'Inactif' }}
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $utilisateurs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
