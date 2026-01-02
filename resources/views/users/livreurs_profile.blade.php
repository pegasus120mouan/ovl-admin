@extends('layout.main')

@section('title', 'Livreurs')
@section('page_title', $livreur->nom . ' ' . $livreur->prenoms)

@section('content')

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $livreursTotal ?? 0 }}</h3>
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
    <div class="col-md-3">
      <div class="card card-primary card-outline">
        <div class="card-body box-profile">
          <div class="text-center">
            <img class="profile-user-img img-fluid img-circle"
                 id="livreurAvatarPreview"
                 src="{{ $avatarUrl }}"
                 alt="Photo livreur"
                 style="cursor: pointer;">
          </div>

          <form id="avatarForm" action="{{ route('users.livreurs.update', $livreur) }}" method="POST" enctype="multipart/form-data" class="mt-2">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ route('users.livreurs.show', $livreur) }}">
            <input type="file" name="avatar" id="livreurAvatarInput" accept="image/*" class="d-none">

            <div id="avatarActions" class="mt-2" style="display: none;">
              <button type="submit" class="btn btn-primary btn-block">Mettre à jour la photo</button>
              <button type="button" id="cancelAvatarBtn" class="btn btn-default btn-block">Annuler</button>
            </div>
          </form>

          <h3 class="profile-username text-center">{{ $livreur->nom }} {{ $livreur->prenoms }}</h3>
          <p class="text-muted text-center">{{ $livreur->contact }}</p>

          <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
              <b>Login</b>
              <a class="float-right">{{ $livreur->login }}</a>
            </li>
            <li class="list-group-item">
              <b>Statut</b>
              <a class="float-right">{{ $livreur->statut_compte ? 'Actif' : 'Inactif' }}</a>
            </li>
            <li class="list-group-item">
              <b>Code PIN</b>
              <a class="float-right">{{ $livreur->code_pin ?? '-' }}</a>
            </li>
          </ul>

          <a href="{{ route('users.livreurs') }}" class="btn btn-primary btn-block"><b>Retour</b></a>
        </div>
      </div>
    </div>

    <div class="col-md-9">
      <div class="card">
        <div class="card-header p-2">
          <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Changer le nom</a></li>
            <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Contact / Statut</a></li>
            <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">Changer le mot de passe</a></li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div class="tab-pane" id="activity">
              <form action="{{ route('users.livreurs.update', $livreur) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.livreurs.show', $livreur) }}">

                <div class="form-group">
                  <label for="nom">Nom</label>
                  <input type="text" name="nom" id="nom" class="form-control" value="{{ $livreur->nom }}" required>
                </div>

                <div class="form-group">
                  <label for="prenoms">Prénoms</label>
                  <input type="text" name="prenoms" id="prenoms" class="form-control" value="{{ $livreur->prenoms }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Valider</button>
              </form>
            </div>

            <div class="active tab-pane" id="settings">
              <form action="{{ route('users.livreurs.update', $livreur) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.livreurs.show', $livreur) }}">

                <div class="form-group">
                  <label for="contact">Contact</label>
                  <input type="text" name="contact" id="contact" class="form-control" value="{{ $livreur->contact }}" required>
                </div>

                <div class="form-group">
                  <label for="login">Login</label>
                  <input type="text" name="login" id="login" class="form-control" value="{{ $livreur->login }}" required>
                </div>

                <div class="form-group">
                  <label for="statut_compte">Statut</label>
                  <select name="statut_compte" id="statut_compte" class="form-control">
                    <option value="1" {{ $livreur->statut_compte ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ !$livreur->statut_compte ? 'selected' : '' }}>Inactif</option>
                  </select>
                </div>

                <button type="submit" class="btn btn-primary">Modifier</button>
              </form>

              <hr>
            </div>

            <div class="tab-pane" id="password">
              <form action="{{ route('users.livreurs.update', $livreur) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.livreurs.show', $livreur) }}">

                <div class="form-group">
                  <label for="new_password">Nouveau mot de passe</label>
                  <div class="input-group">
                    <input type="password" name="password" id="new_password" class="form-control" placeholder="Saisir un nouveau mot de passe">
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword" aria-label="Afficher / masquer le mot de passe">
                        <i class="fas fa-eye" id="iconNewPassword"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="confirm_password">Confirmer le mot de passe</label>
                  <div class="input-group">
                    <input type="password" name="password_confirmation" id="confirm_password" class="form-control" placeholder="Confirmer le mot de passe">
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword" aria-label="Afficher / masquer la confirmation">
                        <i class="fas fa-eye" id="iconConfirmPassword"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var preview = document.getElementById('livreurAvatarPreview');
    var input = document.getElementById('livreurAvatarInput');
    var actions = document.getElementById('avatarActions');
    var cancelBtn = document.getElementById('cancelAvatarBtn');

    if (!preview || !input || !actions || !cancelBtn) return;

    var originalSrc = preview.getAttribute('src');

    preview.addEventListener('click', function () {
      input.click();
    });

    input.addEventListener('change', function () {
      if (!input.files || !input.files[0]) {
        actions.style.display = 'none';
        preview.setAttribute('src', originalSrc);
        return;
      }

      var file = input.files[0];
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
        actions.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });

    cancelBtn.addEventListener('click', function () {
      input.value = '';
      actions.style.display = 'none';
      preview.setAttribute('src', originalSrc);
    });

    var newPassword = document.getElementById('new_password');
    var toggleNewPassword = document.getElementById('toggleNewPassword');
    var iconNewPassword = document.getElementById('iconNewPassword');

    if (newPassword && toggleNewPassword && iconNewPassword) {
      toggleNewPassword.addEventListener('click', function () {
        var isPassword = newPassword.getAttribute('type') === 'password';
        newPassword.setAttribute('type', isPassword ? 'text' : 'password');
        iconNewPassword.classList.toggle('fa-eye', !isPassword);
        iconNewPassword.classList.toggle('fa-eye-slash', isPassword);
      });
    }

    var confirmPassword = document.getElementById('confirm_password');
    var toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    var iconConfirmPassword = document.getElementById('iconConfirmPassword');

    if (confirmPassword && toggleConfirmPassword && iconConfirmPassword) {
      toggleConfirmPassword.addEventListener('click', function () {
        var isPassword = confirmPassword.getAttribute('type') === 'password';
        confirmPassword.setAttribute('type', isPassword ? 'text' : 'password');
        iconConfirmPassword.classList.toggle('fa-eye', !isPassword);
        iconConfirmPassword.classList.toggle('fa-eye-slash', isPassword);
      });
    }
  });
</script>

@endsection
