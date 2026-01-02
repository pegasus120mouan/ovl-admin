@extends('layout.main')

@section('title', 'Administrateurs')
@section('page_title', 'Liste des administrateurs')

@section('content')
<div class="container-fluid">
  <!-- Small boxes (Stat box) -->
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $administrateurs->total() }}</h3>
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
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterAdministrateur"><i class="fas fa-user-plus"></i> Enregistrer un administrateur</a>
        <a href="#" class="btn btn-danger"><i class="fas fa-file-export"></i> Exporter la liste des administrateurs</a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-user-shield"></i> Administrateurs</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Login</th>
                <th>Actions</th>
                <th>Statut compte</th>
              </tr>
            </thead>
            <tbody>
              @forelse($administrateurs as $admin)
                <tr>
                  <td>
                    @php
                      $avatarKey = $admin->avatar ?: null;
                      if (!$avatarKey || $avatarKey === 'default.jpg') {
                        $avatarKey = 'administrateurs/admins.png';
                      } elseif (!str_contains($avatarKey, '/')) {
                        $avatarKey = 'administrateurs/' . $avatarKey;
                      }
                      $disk = \Illuminate\Support\Facades\Storage::disk('s3');

                      try {
                        $avatarUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
                      } catch (\Exception $e) {
                        $avatarUrl = $disk->url($avatarKey);
                      }
                    @endphp
                    <a href="{{ route('users.administrateurs.show', $admin) }}">
                      <img src="{{ $avatarUrl }}" alt="Avatar" class="img-circle" style="width: 40px; height: 40px; object-fit: cover;" />
                    </a>
                  </td>
                  <td>{{ $admin->nom }}</td>
                  <td>{{ $admin->prenoms }}</td>
                  <td>{{ $admin->contact }}</td>
                  <td>{{ $admin->login }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-link p-0 mr-2"
                      title="Modifier"
                      data-toggle="modal"
                      data-target="#modalModifierAdministrateur"
                      data-admin-id="{{ $admin->id }}"
                      data-admin-nom="{{ $admin->nom }}"
                      data-admin-prenoms="{{ $admin->prenoms }}"
                      data-admin-contact="{{ $admin->contact }}"
                      data-admin-login="{{ $admin->login }}"
                      data-admin-statut="{{ (int) $admin->statut_compte }}"
                    >
                      <i class="fas fa-pen text-primary"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-link p-0"
                      title="Supprimer"
                      data-toggle="modal"
                      data-target="#modalSupprimerAdministrateur"
                      data-admin-name="{{ $admin->nom }} {{ $admin->prenoms }}"
                      data-delete-url="{{ route('users.administrateurs.destroy', $admin) }}"
                    >
                      <i class="fas fa-trash text-danger"></i>
                    </button>
                  </td>
                  <td>
                    <form action="{{ route('users.administrateurs.toggle-statut', $admin) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-{{ $admin->statut_compte ? 'success' : 'danger' }} btn-sm" style="min-width: 90px;">
                        {{ $admin->statut_compte ? 'Actif' : 'Inactif' }}
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Aucun administrateur trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $administrateurs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterAdministrateur" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Ajouter un administrateur</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('users.administrateurs.store') }}" method="POST">
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

<div class="modal fade" id="modalSupprimerAdministrateur" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">Supprimer un administrateur</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Vous êtes sur le point de supprimer l'administrateur :</p>
        <p class="font-weight-bold mb-0" id="supprimerAdministrateurNom"></p>
        <small class="text-muted">Cette action est irréversible.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <form id="formSupprimerAdministrateur" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalModifierAdministrateur" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title text-white"><i class="fas fa-edit"></i> Modifier un administrateur</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formModifierAdministrateur" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" id="modifierAdminNom" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" id="modifierAdminPrenoms" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" id="modifierAdminContact" required>
          </div>
          <div class="form-group">
            <label>Login</label>
            <input type="text" class="form-control" name="login" id="modifierAdminLogin" required>
          </div>
          <div class="form-group">
            <label>Nouveau mot de passe (optionnel)</label>
            <input type="password" class="form-control" name="password">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte" id="modifierAdminStatut">
              <option value="1">Actif</option>
              <option value="0">Inactif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    function bindDeleteModal() {
      var modalId = '#modalSupprimerAdministrateur';

      function fillDeleteModal(relatedTarget) {
        if (!relatedTarget) return;
        var adminName = relatedTarget.getAttribute('data-admin-name') || '';
        var deleteUrl = relatedTarget.getAttribute('data-delete-url') || '';

        var nameEl = document.getElementById('supprimerAdministrateurNom');
        if (nameEl) nameEl.textContent = adminName;

        var form = document.getElementById('formSupprimerAdministrateur');
        if (form && deleteUrl) form.setAttribute('action', deleteUrl);
      }

      if (window.jQuery && window.jQuery(modalId).on) {
        window.jQuery(modalId).on('show.bs.modal', function (event) {
          fillDeleteModal(event.relatedTarget);
        });
        return;
      }

      var modal = document.getElementById('modalSupprimerAdministrateur');
      if (!modal) return;
      modal.addEventListener('show.bs.modal', function (event) {
        fillDeleteModal(event.relatedTarget);
      });
    }

    function bindEditModal() {
      var modalId = '#modalModifierAdministrateur';

      function fillEditModal(relatedTarget) {
        if (!relatedTarget) return;

        var id = relatedTarget.getAttribute('data-admin-id');
        var nom = relatedTarget.getAttribute('data-admin-nom') || '';
        var prenoms = relatedTarget.getAttribute('data-admin-prenoms') || '';
        var contact = relatedTarget.getAttribute('data-admin-contact') || '';
        var login = relatedTarget.getAttribute('data-admin-login') || '';
        var statut = relatedTarget.getAttribute('data-admin-statut') || '1';

        var form = document.getElementById('formModifierAdministrateur');
        if (form && id) {
          var url = '{{ route('users.administrateurs.update', ':id') }}'.replace(':id', id);
          form.setAttribute('action', url);
        }

        var nomEl = document.getElementById('modifierAdminNom');
        if (nomEl) nomEl.value = nom;

        var prenomsEl = document.getElementById('modifierAdminPrenoms');
        if (prenomsEl) prenomsEl.value = prenoms;

        var contactEl = document.getElementById('modifierAdminContact');
        if (contactEl) contactEl.value = contact;

        var loginEl = document.getElementById('modifierAdminLogin');
        if (loginEl) loginEl.value = login;

        var statutEl = document.getElementById('modifierAdminStatut');
        if (statutEl) statutEl.value = statut;
      }

      if (window.jQuery && window.jQuery(modalId).on) {
        window.jQuery(modalId).on('show.bs.modal', function (event) {
          fillEditModal(event.relatedTarget);
        });
        return;
      }

      var modal = document.getElementById('modalModifierAdministrateur');
      if (!modal) return;
      modal.addEventListener('show.bs.modal', function (event) {
        fillEditModal(event.relatedTarget);
      });
    }

    bindDeleteModal();
    bindEditModal();
  });
</script>
@endsection
