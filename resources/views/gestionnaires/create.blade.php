@extends('layout.main')

@section('title', 'Ajouter un gestionnaire')
@section('page_title', 'Ajouter un gestionnaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Nouveau gestionnaire de commandes</h3>
                </div>
                <form action="{{ route('gestionnaires.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
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
                                    <label for="nom"><i class="fas fa-user mr-1"></i>Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                                    @error('nom')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prenoms"><i class="fas fa-user mr-1"></i>Prénoms <span class="text-danger">*</span></label>
                                    <input type="text" name="prenoms" id="prenoms" class="form-control @error('prenoms') is-invalid @enderror" value="{{ old('prenoms') }}" required>
                                    @error('prenoms')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact"><i class="fas fa-phone mr-1"></i>Contact <span class="text-danger">*</span></label>
                                    <input type="text" name="contact" id="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact') }}" required>
                                    @error('contact')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="boutique_id"><i class="fas fa-store mr-1"></i>Boutique <span class="text-danger">*</span></label>
                                    <select name="boutique_id" id="boutique_id" class="form-control @error('boutique_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner une boutique --</option>
                                        @foreach($boutiques as $boutique)
                                            <option value="{{ $boutique->id }}" {{ old('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                                {{ $boutique->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('boutique_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="login"><i class="fas fa-sign-in-alt mr-1"></i>Login <span class="text-danger">*</span></label>
                                    <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" required>
                                    @error('login')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password"><i class="fas fa-lock mr-1"></i>Mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="avatar"><i class="fas fa-camera mr-1"></i>Photo (optionnel)</label>
                            <div class="custom-file">
                                <input type="file" name="avatar" id="avatar" class="custom-file-input @error('avatar') is-invalid @enderror" accept="image/*">
                                <label class="custom-file-label" for="avatar">Choisir une image...</label>
                                @error('avatar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Un code PIN sera généré automatiquement et envoyé par SMS au gestionnaire.
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
document.getElementById('avatar').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Choisir une image...';
    e.target.nextElementSibling.textContent = fileName;
});
</script>
@endsection
