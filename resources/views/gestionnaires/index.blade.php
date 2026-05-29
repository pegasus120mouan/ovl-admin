@extends('layout.main')

@section('title', 'Gestionnaires de commandes')
@section('page_title', 'Gestionnaires de commandes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $gestionnairesTotal }}</h3>
                    <p>Total Gestionnaires</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $gestionnairesActifs }}</h3>
                    <p>Gestionnaires Actifs</p>
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
                    <h3>{{ $gestionnairesInactifs }}</h3>
                    <p>Gestionnaires Inactifs</p>
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
                    <h3>{{ $boutiquesTotal }}</h3>
                    <p>Boutiques</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
                <a href="{{ route('boutiques.index') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAjoutGestionnaire">
                <i class="fas fa-plus mr-1"></i> Ajouter un gestionnaire
            </button>
        </div>
    </div>

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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tie mr-2"></i>Liste des gestionnaires</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 60px;">Photo</th>
                        <th>Nom</th>
                        <th>Prénoms</th>
                        <th>Contact</th>
                        <th>Boutique</th>
                        <th>Login</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                        <th class="text-center" style="width: 120px;">Statut compte</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gestionnaires as $gestionnaire)
                    <tr>
                        @php
                            $localFallbackAvatarUrl = asset('dist/img/user2-160x160.jpg');
                            $defaultPhotoKey = 'gestionnaires/gestionnaire.png';
                            $defaultGestionnaireAvatarUrl = $localFallbackAvatarUrl;

                            try {
                                $disk = \Illuminate\Support\Facades\Storage::disk('r2');
                                try {
                                    $defaultGestionnaireAvatarUrl = $disk->temporaryUrl($defaultPhotoKey, now()->addMinutes(30));
                                } catch (\Exception $e) {
                                    $defaultGestionnaireAvatarUrl = $disk->url($defaultPhotoKey);
                                }
                            } catch (\Throwable $e) {
                                $defaultGestionnaireAvatarUrl = $localFallbackAvatarUrl;
                            }

                            $gestionnairePhotoUrl = $defaultGestionnaireAvatarUrl;

                            $photoKey = $gestionnaire->avatar ?? null;
                            if (!$photoKey || $photoKey === 'default.jpg') {
                                $photoKey = $defaultPhotoKey;
                            } elseif (!str_contains($photoKey, '/')) {
                                $photoKey = 'gestionnaires/' . $photoKey;
                            }

                            if ($photoKey) {
                                try {
                                    $disk = \Illuminate\Support\Facades\Storage::disk('r2');
                                    try {
                                        $gestionnairePhotoUrl = $disk->temporaryUrl($photoKey, now()->addMinutes(30));
                                    } catch (\Exception $e) {
                                        $gestionnairePhotoUrl = $disk->url($photoKey);
                                    }
                                } catch (\Throwable $e) {
                                    $gestionnairePhotoUrl = $defaultGestionnaireAvatarUrl;
                                }
                            }
                        @endphp
                        <td>
                            <a href="{{ route('gestionnaires.show', $gestionnaire) }}" style="display: inline-block;">
                                <img src="{{ $gestionnairePhotoUrl }}" alt="Photo" class="img-circle" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ $defaultGestionnaireAvatarUrl }}';">
                            </a>
                        </td>
                        <td>{{ $gestionnaire->nom }}</td>
                        <td>{{ $gestionnaire->prenoms }}</td>
                        <td>{{ $gestionnaire->contact }}</td>
                        <td>
                            @if($gestionnaire->boutique)
                                <span class="badge badge-info">{{ $gestionnaire->boutique->nom }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $gestionnaire->login }}</td>
                        <td class="text-center">
                            <a href="{{ route('gestionnaires.show', $gestionnaire) }}" class="btn btn-sm btn-primary" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('gestionnaires.destroy', $gestionnaire) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce gestionnaire ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('gestionnaires.toggle-status', $gestionnaire) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                @if($gestionnaire->statut_compte)
                                    <button type="submit" class="btn btn-sm btn-success" title="Cliquez pour désactiver">
                                        <i class="fas fa-check mr-1"></i>Actif
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Cliquez pour activer">
                                        <i class="fas fa-times mr-1"></i>Inactif
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun gestionnaire enregistré</p>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAjoutGestionnaire">
                                <i class="fas fa-plus mr-1"></i> Ajouter un gestionnaire
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Gestionnaire -->
<div class="modal fade" id="modalAjoutGestionnaire" tabindex="-1" role="dialog" aria-labelledby="modalAjoutGestionnaireLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalAjoutGestionnaireLabel">
                    <i class="fas fa-user-plus mr-2"></i>Nouveau gestionnaire de commandes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('gestionnaires.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nom"><i class="fas fa-user mr-1"></i>Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prenoms"><i class="fas fa-user mr-1"></i>Prénoms <span class="text-danger">*</span></label>
                                <input type="text" name="prenoms" id="prenoms" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact"><i class="fas fa-phone mr-1"></i>Contact <span class="text-danger">*</span></label>
                                <input type="text" name="contact" id="contact" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="boutique_id"><i class="fas fa-store mr-1"></i>Boutique <span class="text-danger">*</span></label>
                                <select name="boutique_id" id="boutique_id" class="form-control" required>
                                    <option value="">-- Sélectionner une boutique --</option>
                                    @php
                                        $boutiques = \App\Models\Boutique::orderBy('nom')->get();
                                    @endphp
                                    @foreach($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}">{{ $boutique->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="login"><i class="fas fa-sign-in-alt mr-1"></i>Login <span class="text-danger">*</span></label>
                                <input type="text" name="login" id="login" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock mr-1"></i>Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="avatar"><i class="fas fa-camera mr-1"></i>Photo (optionnel)</label>
                        <div class="custom-file">
                            <input type="file" name="avatar" id="avatar" class="custom-file-input" accept="image/*">
                            <label class="custom-file-label" for="avatar">Choisir une image...</label>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        Un code PIN sera généré automatiquement et envoyé par SMS au gestionnaire.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Enregistrer
                    </button>
                </div>
            </form>
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
