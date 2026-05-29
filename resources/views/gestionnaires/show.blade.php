@extends('layout.main')

@section('title', 'Profil gestionnaire')
@section('page_title', 'Profil gestionnaire')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             id="gestionnaireAvatarPreview"
                             src="{{ $avatarUrl }}"
                             alt="Photo gestionnaire"
                             style="cursor: pointer; width: 100px; height: 100px; object-fit: cover;">
                    </div>

                    <form id="avatarForm" action="{{ route('gestionnaires.update', $gestionnaire) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ route('gestionnaires.show', $gestionnaire) }}">
                        <input type="file" name="avatar" id="gestionnaireAvatarInput" accept="image/*" class="d-none">

                        <div id="avatarActions" class="mt-2" style="display: none;">
                            <button type="submit" class="btn btn-primary btn-block">Mettre à jour la photo</button>
                            <button type="button" id="cancelAvatarBtn" class="btn btn-default btn-block">Annuler</button>
                        </div>
                    </form>

                    <h3 class="profile-username text-center mt-3">{{ $gestionnaire->nom }} {{ $gestionnaire->prenoms }}</h3>
                    <p class="text-muted text-center">
                        <i class="fas fa-user-tie mr-1"></i>Gestionnaire de commandes
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-store mr-2"></i>Boutique</b>
                            <span class="float-right">
                                @if($gestionnaire->boutique)
                                    <span class="badge badge-info">{{ $gestionnaire->boutique->nom }}</span>
                                @else
                                    <span class="text-muted">Non assigné</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-phone mr-2"></i>Contact</b>
                            <span class="float-right">{{ $gestionnaire->contact }}</span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-sign-in-alt mr-2"></i>Login</b>
                            <span class="float-right">{{ $gestionnaire->login }}</span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-toggle-on mr-2"></i>Statut</b>
                            <span class="float-right">
                                @if($gestionnaire->statut_compte)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-secondary">Inactif</span>
                                @endif
                            </span>
                        </li>
                    </ul>

                    <form action="{{ route('gestionnaires.regenerate-pin', $gestionnaire) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Êtes-vous sûr de vouloir régénérer le code PIN ?');">
                            <i class="fas fa-key mr-1"></i> Régénérer le code PIN
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modifier les informations</h3>
                </div>
                <form action="{{ route('gestionnaires.update', $gestionnaire) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom"><i class="fas fa-user mr-1"></i>Nom</label>
                                    <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom', $gestionnaire->nom) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prenoms"><i class="fas fa-user mr-1"></i>Prénoms</label>
                                    <input type="text" name="prenoms" id="prenoms" class="form-control" value="{{ old('prenoms', $gestionnaire->prenoms) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact"><i class="fas fa-phone mr-1"></i>Contact</label>
                                    <input type="text" name="contact" id="contact" class="form-control" value="{{ old('contact', $gestionnaire->contact) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="boutique_id"><i class="fas fa-store mr-1"></i>Boutique</label>
                                    <select name="boutique_id" id="boutique_id" class="form-control" required>
                                        <option value="">-- Sélectionner une boutique --</option>
                                        @foreach($boutiques as $boutique)
                                            <option value="{{ $boutique->id }}" {{ old('boutique_id', $gestionnaire->boutique_id) == $boutique->id ? 'selected' : '' }}>
                                                {{ $boutique->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="login"><i class="fas fa-sign-in-alt mr-1"></i>Login</label>
                                    <input type="text" name="login" id="login" class="form-control" value="{{ old('login', $gestionnaire->login) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password"><i class="fas fa-lock mr-1"></i>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                    <input type="password" name="password" id="password" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="statut_compte" name="statut_compte" value="1" {{ old('statut_compte', $gestionnaire->statut_compte) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="statut_compte">Compte actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('gestionnaires.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save mr-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var preview = document.getElementById('gestionnaireAvatarPreview');
    var input = document.getElementById('gestionnaireAvatarInput');
    var actions = document.getElementById('avatarActions');
    var cancelBtn = document.getElementById('cancelAvatarBtn');

    if (!preview || !input || !actions || !cancelBtn) return;

    var originalSrc = preview.src;

    preview.addEventListener('click', function () {
        input.click();
    });

    input.addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
                actions.style.display = 'block';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    cancelBtn.addEventListener('click', function () {
        preview.src = originalSrc;
        input.value = '';
        actions.style.display = 'none';
    });
});
</script>
@endsection
