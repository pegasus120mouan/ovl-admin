@extends('layout.main')

@section('title', 'Commerciaux')
@section('page_title', 'Liste des commerciaux')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $commerciaux->total() }}</h3>
          <p>Total Commerciaux</p>
        </div>
        <div class="icon">
          <i class="fas fa-handshake"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $commerciauxActifs ?? 0 }}</h3>
          <p>Commerciaux Actifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $commerciauxInactifs ?? 0 }}</h3>
          <p>Commerciaux Inactifs</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-times"></i>
        </div>
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
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterCommercial">
          <i class="fas fa-user-plus"></i> Enregistrer un commercial
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-handshake"></i> Commerciaux</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Photo</th>
                <th>Code</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Contact</th>
                <th>Login</th>
                <th>Actions</th>
                <th>Statut compte</th>
              </tr>
            </thead>
            <tbody>
              @forelse($commerciaux as $commercial)
                <tr>
                  <td>
                    @php
                      $avatarKey = $commercial->avatar ?: null;
                      if (!$avatarKey || $avatarKey === 'default.jpg') {
                        $avatarKey = 'utilisateurs/utilisateurs.png';
                      } elseif (!str_contains($avatarKey, '/')) {
                        $avatarKey = 'utilisateurs/' . $avatarKey;
                      }

                      $avatarUrl = asset('img/logo/logo.png');
                      try {
                        $disk = \Illuminate\Support\Facades\Storage::disk('r2');
                        $avatarUrl = method_exists($disk, 'temporaryUrl')
                          ? $disk->temporaryUrl($avatarKey, now()->addMinutes(30))
                          : $disk->url($avatarKey);
                      } catch (\Throwable $e) {
                        try {
                          $avatarUrl = \Illuminate\Support\Facades\Storage::disk('r2')->url($avatarKey);
                        } catch (\Throwable $e) {
                          //
                        }
                      }
                    @endphp
                    <a href="{{ route('users.commerciaux.show', $commercial->code_commercial ?: $commercial) }}">
                      <img src="{{ $avatarUrl }}" alt="Photo {{ $commercial->prenoms }} {{ $commercial->nom }}" class="img-circle" style="width: 40px; height: 40px; object-fit: cover;" />
                    </a>
                  </td>
                  <td>
                    <a href="{{ route('users.commerciaux.show', $commercial->code_commercial ?: $commercial) }}" class="font-weight-bold">
                      {{ $commercial->code_commercial ?: $commercial->defaultCommercialCode() }}
                    </a>
                  </td>
                  <td>{{ $commercial->nom }}</td>
                  <td>{{ $commercial->prenoms }}</td>
                  <td>{{ $commercial->contact }}</td>
                  <td>{{ $commercial->login }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-link p-0 mr-2"
                      title="Modifier"
                      data-toggle="modal"
                      data-target="#modalModifierCommercial"
                      data-commercial-id="{{ $commercial->id }}"
                      data-commercial-nom="{{ $commercial->nom }}"
                      data-commercial-prenoms="{{ $commercial->prenoms }}"
                      data-commercial-contact="{{ $commercial->contact }}"
                      data-commercial-login="{{ $commercial->login }}"
                      data-commercial-code="{{ $commercial->code_commercial }}"
                      data-commercial-statut="{{ (int) $commercial->statut_compte }}"
                    >
                      <i class="fas fa-pen text-primary"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-link p-0"
                      title="Supprimer"
                      data-toggle="modal"
                      data-target="#modalSupprimerCommercial"
                      data-commercial-name="{{ $commercial->nom }} {{ $commercial->prenoms }}"
                      data-delete-url="{{ route('users.commerciaux.destroy', $commercial) }}"
                    >
                      <i class="fas fa-trash text-danger"></i>
                    </button>
                  </td>
                  <td>
                    <form action="{{ route('users.commerciaux.toggle-statut', $commercial) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-{{ $commercial->statut_compte ? 'success' : 'danger' }} btn-sm" style="min-width: 90px;">
                        {{ $commercial->statut_compte ? 'Actif' : 'Inactif' }}
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center">Aucun commercial trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $commerciaux->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterCommercial" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Ajouter un commercial</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('users.commerciaux.store') }}" method="POST">
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
            <label>Code commercial</label>
            <input type="text" class="form-control" name="code_commercial" maxlength="20" placeholder="Généré automatiquement si vide">
            <small class="text-muted">Exemple : COM-049. Laissé vide, un code est créé automatiquement.</small>
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

<div class="modal fade" id="modalSupprimerCommercial" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">Supprimer un commercial</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Vous êtes sur le point de supprimer le commercial :</p>
        <p class="font-weight-bold mb-0" id="supprimerCommercialNom"></p>
        <small class="text-muted">Cette action est irréversible.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <form id="formSupprimerCommercial" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalModifierCommercial" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title text-white"><i class="fas fa-edit"></i> Modifier un commercial</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formModifierCommercial" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" id="modifierCommercialNom" required>
          </div>
          <div class="form-group">
            <label>Prénoms</label>
            <input type="text" class="form-control" name="prenoms" id="modifierCommercialPrenoms" required>
          </div>
          <div class="form-group">
            <label>Contact</label>
            <input type="text" class="form-control" name="contact" id="modifierCommercialContact" required>
          </div>
          <div class="form-group">
            <label>Login</label>
            <input type="text" class="form-control" name="login" id="modifierCommercialLogin" required>
          </div>
          <div class="form-group">
            <label>Code commercial</label>
            <input type="text" class="form-control" name="code_commercial" id="modifierCommercialCode" maxlength="20" required>
          </div>
          <div class="form-group">
            <label>Nouveau mot de passe (optionnel)</label>
            <input type="password" class="form-control" name="password">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut_compte" id="modifierCommercialStatut">
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
  $('#modalSupprimerCommercial').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#supprimerCommercialNom').text(button.data('commercial-name') || '');
    $('#formSupprimerCommercial').attr('action', button.data('delete-url') || '#');
  });

  $('#modalModifierCommercial').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('commercial-id');
    $('#formModifierCommercial').attr('action', '/users/commerciaux/' + id);
    $('#modifierCommercialNom').val(button.data('commercial-nom') || '');
    $('#modifierCommercialPrenoms').val(button.data('commercial-prenoms') || '');
    $('#modifierCommercialContact').val(button.data('commercial-contact') || '');
    $('#modifierCommercialLogin').val(button.data('commercial-login') || '');
    $('#modifierCommercialCode').val(button.data('commercial-code') || '');
    $('#modifierCommercialStatut').val(String(button.data('commercial-statut')));
  });
});
</script>
@endsection
