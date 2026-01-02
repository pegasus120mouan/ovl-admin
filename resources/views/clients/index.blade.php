@extends('layout.main')

@section('title', 'Liste des clients')
@section('page_title', 'Liste des clients')

@section('content')
<div class="container-fluid">
  <!-- Small boxes (Stat box) -->
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $clients->total() }}</h3>
          <p>Total Clients</p>
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
          <h3>{{ $clientsActifs ?? 0 }}</h3>
          <p>Clients Actifs</p>
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
          <h3>{{ $clientsInactifs ?? 0 }}</h3>
          <p>Clients Inactifs</p>
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
          <h3>{{ $boutiques->count() }}</h3>
          <p>Boutiques</p>
        </div>
        <div class="icon">
          <i class="fas fa-store"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <!-- Boutons d'action -->
  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterClient"><i class="fas fa-user-plus"></i> Ajouter un client</a>
        <a href="#" class="btn btn-success"><i class="fas fa-file-export"></i> Exporter la liste</a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-users"></i> Clients</h3>
          <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
              <input type="text" name="table_search" class="form-control float-right" placeholder="Rechercher">
              <div class="input-group-append">
                <button type="submit" class="btn btn-default">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Boutique</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($clients as $client)
              <tr>
                <td>
                  @php
                    $avatarKey = $client->avatar ?: null;
                    if (!$avatarKey || $avatarKey === 'default.jpg') {
                      $avatarKey = 'utilisateurs/utilisateurs.png';
                    } elseif (!str_contains($avatarKey, '/')) {
                      $avatarKey = 'utilisateurs/' . $avatarKey;
                    }
                    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
                    try {
                      $logoUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
                    } catch (\Exception $e) {
                      $logoUrl = $disk->url($avatarKey);
                    }
                  @endphp
                  <a href="{{ route('clients.show', $client) }}">
                    <img src="{{ $logoUrl }}" alt="Logo" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;" />
                  </a>
                </td>
                <td>{{ $client->nom }}</td>
                <td>{{ $client->prenoms }}</td>
                <td>{{ $client->contact }}</td>
                <td>
                  @if($client->boutique)
                    <span class="badge badge-info">{{ $client->boutique->nom }}</span>
                  @else
                    <span class="badge badge-secondary">Aucune</span>
                  @endif
                </td>
                <td>
                  @if($client->statut_compte)
                    <span class="badge badge-success">Actif</span>
                  @else
                    <span class="badge badge-danger">Inactif</span>
                  @endif
                </td>
                <td>
                  <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetails{{ $client->id }}"><i class="fas fa-eye"></i></a>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifier{{ $client->id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('clients.resend-sms', $client) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary" title="Renvoyer SMS"><i class="fas fa-sms"></i></button>
                  </form>
                  <button
                    type="button"
                    class="btn btn-sm btn-danger"
                    title="Supprimer"
                    data-toggle="modal"
                    data-target="#modalSupprimerClient"
                    data-client-name="{{ $client->nom }} {{ $client->prenoms }}"
                    data-delete-url="{{ route('clients.destroy', $client) }}"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">Aucun client trouvé</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              Affichage de {{ $clients->firstItem() ?? 0 }} à {{ $clients->lastItem() ?? 0 }} sur {{ $clients->total() }} clients
            </div>
            <div>
              {{ $clients->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSupprimerClient" tabindex="-1" role="dialog" aria-labelledby="modalSupprimerClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white" id="modalSupprimerClientLabel">Supprimer un client</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Vous êtes sur le point de supprimer le client :</p>
        <p class="font-weight-bold mb-0" id="supprimerClientNom"></p>
        <small class="text-muted">Cette action est irréversible.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <form id="formSupprimerClient" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modalId = '#modalSupprimerClient';

    function fillDeleteModal(relatedTarget) {
      if (!relatedTarget) return;

      var clientName = relatedTarget.getAttribute('data-client-name') || '';
      var deleteUrl = relatedTarget.getAttribute('data-delete-url') || '';

      var nameEl = document.getElementById('supprimerClientNom');
      if (nameEl) nameEl.textContent = clientName;

      var form = document.getElementById('formSupprimerClient');
      if (form && deleteUrl) form.setAttribute('action', deleteUrl);
    }

    if (window.jQuery && window.jQuery(modalId).on) {
      window.jQuery(modalId).on('show.bs.modal', function (event) {
        fillDeleteModal(event.relatedTarget);
      });
      return;
    }

    var modal = document.getElementById('modalSupprimerClient');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
      fillDeleteModal(event.relatedTarget);
    });
  });
</script>

@foreach($clients as $client)
<!-- Modal Détails Client -->
<div class="modal fade" id="modalDetails{{ $client->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title text-white">Détails du client</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tr>
            <th>Nom</th>
            <td>{{ $client->nom }}</td>
          </tr>
          <tr>
            <th>Prénoms</th>
            <td>{{ $client->prenoms }}</td>
          </tr>
          <tr>
            <th>Contact</th>
            <td>{{ $client->contact }}</td>
          </tr>
          <tr>
            <th>Login</th>
            <td>{{ $client->login }}</td>
          </tr>
          <tr>
            <th>Boutique</th>
            <td>{{ $client->boutique->nom ?? 'Aucune' }}</td>
          </tr>
          <tr>
            <th>Statut</th>
            <td>
              @if($client->statut_compte)
                <span class="badge badge-success">Actif</span>
              @else
                <span class="badge badge-danger">Inactif</span>
              @endif
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Modifier Client -->
<div class="modal fade" id="modalModifier{{ $client->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Modifier le client</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" value="{{ $client->nom }}" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" value="{{ $client->prenoms }}" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" value="{{ $client->contact }}" required>
          </div>
          <div class="form-group">
            <label>Boutique</label>
            <select class="form-control" name="boutique_id">
              <option value="">Aucune</option>
              @foreach($boutiques as $boutique)
                @if(($boutique->utilisateurs_count ?? 0) == 0 || $client->boutique_id == $boutique->id)
                  <option value="{{ $boutique->id }}" {{ $client->boutique_id == $boutique->id ? 'selected' : '' }}>{{ $boutique->nom }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte">
              <option value="1" {{ $client->statut_compte ? 'selected' : '' }}>Actif</option>
              <option value="0" {{ !$client->statut_compte ? 'selected' : '' }}>Inactif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Modifier</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<!-- Modal Ajouter Client -->
<div class="modal fade" id="modalAjouterClient" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Ajouter un client</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('clients.store') }}" method="POST">
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
            <label>Boutique</label>
            <select class="form-control" name="boutique_id">
              <option value="">Aucune</option>
              @foreach($boutiquesLibres as $boutique)
                <option value="{{ $boutique->id }}">{{ $boutique->nom }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte">
              <option value="1">Actif</option>
              <option value="0">Inactif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
