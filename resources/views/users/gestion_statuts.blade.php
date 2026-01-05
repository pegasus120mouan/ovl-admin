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
                  <td>
                    @php
                      $role = $utilisateur->role;
                      $roleIcon = $role === 'admin'
                        ? 'admin.png'
                        : ($role === 'clients'
                          ? 'clients.png'
                          : ($role === 'livreur' ? 'livreur.png' : null));
                    @endphp
                    @if($roleIcon)
                      <img src="{{ asset('img/icones/' . $roleIcon) }}" alt="{{ $role }}" title="{{ $role }}" style="width: 40px; height: 40px; object-fit: contain;">
                    @endif
                  </td>
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
          <div class="d-flex justify-content-between align-items-center" style="background: #6c757d; color: #fff; padding: 8px 12px; border-radius: 3px;">
            <div>
              Affichage de {{ $utilisateurs->firstItem() ?? 0 }} à {{ $utilisateurs->lastItem() ?? 0 }} sur {{ $utilisateurs->total() }} entrées
            </div>

            <div class="d-flex align-items-center">
              <a
                href="{{ $utilisateurs->previousPageUrl() ?? '#' }}"
                class="btn btn-primary btn-sm mr-2 {{ $utilisateurs->onFirstPage() ? 'disabled' : '' }}"
                @if($utilisateurs->onFirstPage()) aria-disabled="true" tabindex="-1" @endif
              >
                <i class="fas fa-chevron-left"></i>
              </a>

              <span class="mr-2">
                {{ $utilisateurs->currentPage() }}/{{ $utilisateurs->lastPage() }}
              </span>

              <a
                href="{{ $utilisateurs->nextPageUrl() ?? '#' }}"
                class="btn btn-primary btn-sm mr-3 {{ $utilisateurs->hasMorePages() ? '' : 'disabled' }}"
                @if(!$utilisateurs->hasMorePages()) aria-disabled="true" tabindex="-1" @endif
              >
                <i class="fas fa-chevron-right"></i>
              </a>

              <form method="GET" action="{{ route('users.gestion-statuts') }}" class="form-inline mb-0">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="role" value="{{ request('role') }}">
                <input type="hidden" name="statut" value="{{ request('statut') }}">
                <input type="hidden" name="page" value="1">

                <label class="mr-2 mb-0">Afficher :</label>
                <select name="per_page" class="form-control form-control-sm mr-2" style="width: 80px;">
                  @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                  @endforeach
                </select>

                <button type="submit" class="btn btn-primary btn-sm">Valider</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
