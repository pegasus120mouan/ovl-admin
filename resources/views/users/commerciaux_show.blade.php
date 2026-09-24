@extends('layout.main')

@section('title', 'Commercial '.$commercial->code_commercial)
@section('page_title', trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')))

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $commerciauxTotal ?? 0 }}</h3>
          <p>Total Commerciaux</p>
        </div>
        <div class="icon"><i class="fas fa-handshake"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $commerciauxActifs ?? 0 }}</h3>
          <p>Commerciaux Actifs</p>
        </div>
        <div class="icon"><i class="fas fa-user-check"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $commerciauxInactifs ?? 0 }}</h3>
          <p>Commerciaux Inactifs</p>
        </div>
        <div class="icon"><i class="fas fa-user-times"></i></div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $boutiquesTotal ?? 0 }}</h3>
          <p>Boutiques</p>
        </div>
        <div class="icon"><i class="fas fa-store"></i></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3">
      <div class="card card-primary card-outline">
        <div class="card-body box-profile">
          <div class="text-center">
            <img class="profile-user-img img-fluid img-circle"
                 id="commercialAvatarPreview"
                 src="{{ $avatarUrl }}"
                 alt="Photo commercial"
                 style="cursor: pointer;">
          </div>

          <form id="avatarForm" action="{{ route('users.commerciaux.update', $commercial) }}" method="POST" enctype="multipart/form-data" class="mt-2">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ route('users.commerciaux.show', $commercial) }}">
            <input type="file" name="avatar" id="commercialAvatarInput" accept="image/*" class="d-none">
            <div id="avatarActions" class="mt-2" style="display: none;">
              <button type="submit" class="btn btn-primary btn-block">Mettre à jour la photo</button>
              <button type="button" id="cancelAvatarBtn" class="btn btn-default btn-block">Annuler</button>
            </div>
          </form>

          <h3 class="profile-username text-center">{{ $commercial->nom }} {{ $commercial->prenoms }}</h3>
          <p class="text-muted text-center">{{ $commercial->contact }}</p>

          <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
              <b>Login</b>
              <a class="float-right">{{ $commercial->login }}</a>
            </li>
            <li class="list-group-item">
              <b>Statut</b>
              <a class="float-right">{{ $commercial->statut_compte ? 'Actif' : 'Inactif' }}</a>
            </li>
            <li class="list-group-item">
              <b>Code</b>
              <a class="float-right">{{ $commercial->code_commercial ?: $commercial->defaultCommercialCode() }}</a>
            </li>
          </ul>

          <a href="{{ route('users.commerciaux') }}" class="btn btn-primary btn-block"><b>Retour</b></a>
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
              <form action="{{ route('users.commerciaux.update', $commercial) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.commerciaux.show', $commercial) }}">
                <div class="form-group">
                  <label for="nom">Nom</label>
                  <input type="text" name="nom" id="nom" class="form-control" value="{{ $commercial->nom }}" required>
                </div>
                <div class="form-group">
                  <label for="prenoms">Prénoms</label>
                  <input type="text" name="prenoms" id="prenoms" class="form-control" value="{{ $commercial->prenoms }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Valider</button>
              </form>
            </div>

            <div class="active tab-pane" id="settings">
              <form action="{{ route('users.commerciaux.update', $commercial) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.commerciaux.show', $commercial) }}">
                <div class="form-group">
                  <label for="contact">Contact</label>
                  <input type="text" name="contact" id="contact" class="form-control" value="{{ $commercial->contact }}" required>
                </div>
                <div class="form-group">
                  <label for="login">Login</label>
                  <input type="text" name="login" id="login" class="form-control" value="{{ $commercial->login }}" required>
                </div>
                <div class="form-group">
                  <label for="code_commercial">Code</label>
                  <input type="text" name="code_commercial" id="code_commercial" class="form-control" value="{{ $commercial->code_commercial }}" maxlength="20" required>
                </div>
                <div class="form-group">
                  <label for="statut_compte">Statut</label>
                  <select name="statut_compte" id="statut_compte" class="form-control">
                    <option value="1" {{ $commercial->statut_compte ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ ! $commercial->statut_compte ? 'selected' : '' }}>Inactif</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">Modifier</button>
              </form>
            </div>

            <div class="tab-pane" id="password">
              <form action="{{ route('users.commerciaux.update', $commercial) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('users.commerciaux.show', $commercial) }}">
                <div class="form-group">
                  <label for="new_password">Nouveau mot de passe</label>
                  <div class="input-group">
                    <input type="password" name="password" id="new_password" class="form-control" placeholder="Saisir un nouveau mot de passe">
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword">
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
                      <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
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
    var preview = document.getElementById('commercialAvatarPreview');
    var input = document.getElementById('commercialAvatarInput');
    var actions = document.getElementById('avatarActions');
    var cancelBtn = document.getElementById('cancelAvatarBtn');
    if (!preview || !input || !actions || !cancelBtn) return;

    var originalSrc = preview.getAttribute('src');
    preview.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
      if (!input.files || !input.files[0]) {
        actions.style.display = 'none';
        preview.setAttribute('src', originalSrc);
        return;
      }
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
        actions.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    });
    cancelBtn.addEventListener('click', function () {
      input.value = '';
      actions.style.display = 'none';
      preview.setAttribute('src', originalSrc);
    });

    function bindToggle(inputId, buttonId, iconId) {
      var field = document.getElementById(inputId);
      var button = document.getElementById(buttonId);
      var icon = document.getElementById(iconId);
      if (!field || !button || !icon) return;
      button.addEventListener('click', function () {
        var isPassword = field.getAttribute('type') === 'password';
        field.setAttribute('type', isPassword ? 'text' : 'password');
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    }
    bindToggle('new_password', 'toggleNewPassword', 'iconNewPassword');
    bindToggle('confirm_password', 'toggleConfirmPassword', 'iconConfirmPassword');
  });
</script>
@endsection
