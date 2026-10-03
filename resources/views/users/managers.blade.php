@extends('layout.main')

@section('title', 'Managers')
@section('page_title', 'Liste des managers')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $managers->total() }}</h3>
          <p>Total Managers</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-tag"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $managersActifs ?? 0 }}</h3>
          <p>Managers Actifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $managersInactifs ?? 0 }}</h3>
          <p>Managers Inactifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-times"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $commerciauxTotal ?? 0 }}</h3>
          <p>Commerciaux</p>
        </div>
        <div class="icon">
          <i class="fas fa-handshake"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterManager">
          <i class="fas fa-user-plus"></i> Enregistrer un manager
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-user-tag"></i> Managers</h3>
          <div class="card-tools">
            <small class="text-muted">Les managers se connectent sur l'espace CRM (ovl-crm).</small>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Login</th>
                <th>Actions</th>
                <th>Statut compte</th>
              </tr>
            </thead>
            <tbody>
              @forelse($managers as $manager)
                <tr>
                  <td>{{ $manager->nom }}</td>
                  <td>{{ $manager->prenoms }}</td>
                  <td>{{ $manager->contact }}</td>
                  <td>{{ $manager->login }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-link p-0 mr-2"
                      title="Modifier"
                      data-toggle="modal"
                      data-target="#modalModifierManager"
                      data-update-url="{{ route('users.managers.update', $manager) }}"
                      data-manager-nom="{{ $manager->nom }}"
                      data-manager-prenoms="{{ $manager->prenoms }}"
                      data-manager-contact="{{ $manager->contact }}"
                      data-manager-login="{{ $manager->login }}"
                      data-manager-statut="{{ (int) $manager->statut_compte }}"
                    >
                      <i class="fas fa-pen text-primary"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-link p-0"
                      title="Supprimer"
                      data-toggle="modal"
                      data-target="#modalSupprimerManager"
                      data-manager-name="{{ $manager->nom }} {{ $manager->prenoms }}"
                      data-delete-url="{{ route('users.managers.destroy', $manager) }}"
                    >
                      <i class="fas fa-trash text-danger"></i>
                    </button>
                  </td>
                  <td>
                    <form action="{{ route('users.managers.toggle-statut', $manager) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-{{ $manager->statut_compte ? 'success' : 'danger' }} btn-sm" style="min-width: 90px;">
                        {{ $manager->statut_compte ? 'Actif' : 'Inactif' }}
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Aucun manager trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $managers->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterManager" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Ajouter un manager</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('users.managers.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" value="{{ old('nom') }}" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" value="{{ old('prenoms') }}" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" value="{{ old('contact') }}" maxlength="15" required>
          </div>
          <div class="form-group">
            <label>Login</label>
            <input type="text" class="form-control" name="login" value="{{ old('login') }}" required>
          </div>
          <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" class="form-control" name="password" minlength="4" required>
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

<div class="modal fade" id="modalSupprimerManager" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">Supprimer un manager</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Vous êtes sur le point de supprimer le manager :</p>
        <p class="font-weight-bold mb-0" id="supprimerManagerNom"></p>
        <small class="text-muted">Cette action est irréversible.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <form id="formSupprimerManager" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalModifierManager" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title text-white"><i class="fas fa-edit"></i> Modifier un manager</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formModifierManager" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" id="modifierManagerNom" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" id="modifierManagerPrenoms" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" id="modifierManagerContact" maxlength="15" required>
          </div>
          <div class="form-group">
            <label>Login</label>
            <input type="text" class="form-control" name="login" id="modifierManagerLogin" required>
          </div>
          <div class="form-group">
            <label>Nouveau mot de passe (optionnel)</label>
            <input type="password" class="form-control" name="password" minlength="4">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte" id="modifierManagerStatut">
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#modalSupprimerManager').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#supprimerManagerNom').text(button.data('manager-name') || '');
    $('#formSupprimerManager').attr('action', button.data('delete-url') || '#');
  });

  $('#modalModifierManager').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#formModifierManager').attr('action', button.data('update-url') || '#');
    $('#modifierManagerNom').val(button.data('manager-nom') || '');
    $('#modifierManagerPrenoms').val(button.data('manager-prenoms') || '');
    $('#modifierManagerContact').val(button.data('manager-contact') || '');
    $('#modifierManagerLogin').val(button.data('manager-login') || '');
    $('#modifierManagerStatut').val(String(button.data('manager-statut')));
  });
});
</script>
@endsection
