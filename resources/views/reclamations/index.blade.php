@extends('layout.main')

@section('title', 'Réclamations')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Réclamations</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Réclamations</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filtres -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form action="{{ route('reclamations.index') }}" method="GET" class="d-flex align-items-center">
                    <label class="mr-2 mb-0">Statut:</label>
                    <select name="statut" class="form-control form-control-sm mr-2" style="width: 200px;" onchange="this.form.submit()">
                        <option value="en_attente" {{ $statut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="acceptee" {{ $statut == 'acceptee' ? 'selected' : '' }}>Acceptées</option>
                        <option value="refusee" {{ $statut == 'refusee' ? 'selected' : '' }}>Refusées</option>
                        <option value="toutes" {{ $statut == 'toutes' ? 'selected' : '' }}>Toutes</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des réclamations</h3>
            </div>
            <div class="card-body table-responsive p-0">
                @if($reclamations->count() > 0)
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Date réclamation</th>
                                <th>Client / Boutique</th>
                                <th>Commune</th>
                                <th>Date réception</th>
                                <th>Date livraison</th>
                                <th>Livreur</th>
                                <th>Statut commande</th>
                                <th>Type d'erreur</th>
                                <th>Montant actuel</th>
                                <th>Montant réclamé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                                <th class="text-center">Suppression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reclamations as $reclamation)
                                <tr>
                                    <td>{{ $reclamation->created_at ? $reclamation->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        @if($reclamation->client && $reclamation->client->boutique)
                                            <strong>{{ $reclamation->client->boutique->nom }}</strong>
                                        @elseif($reclamation->client)
                                            {{ $reclamation->client->nom }} {{ $reclamation->client->prenoms }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $reclamation->commande->communes ?? '-' }}</td>
                                    <td>{{ $reclamation->commande->date_reception ? \Carbon\Carbon::parse($reclamation->commande->date_reception)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $reclamation->commande->date_livraison ? \Carbon\Carbon::parse($reclamation->commande->date_livraison)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($reclamation->commande->livreur)
                                            {{ $reclamation->commande->livreur->nom }} {{ $reclamation->commande->livreur->prenoms }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($reclamation->commande->statut == 'Livré')
                                            <span class="badge badge-success">{{ $reclamation->commande->statut }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $reclamation->commande->statut ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">{{ $reclamation->type_label }}</span>
                                    </td>
                                    <td><strong>{{ number_format($reclamation->montant_actuel ?? 0, 0, ',', ' ') }} XOF</strong></td>
                                    <td>
                                        @if($reclamation->montant_reclame)
                                            <strong class="text-danger">{{ number_format($reclamation->montant_reclame, 0, ',', ' ') }} XOF</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $reclamation->statut_badge_class }}">{{ $reclamation->statut_label }}</span>
                                    </td>
                                    <td>
                                        @if($reclamation->statut === 'en_attente')
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#traiterModal{{ $reclamation->id }}" title="Traiter">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal{{ $reclamation->id }}" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#supprimerModal{{ $reclamation->id }}" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Traiter -->
                                @if($reclamation->statut === 'en_attente')
                                <div class="modal fade" id="traiterModal{{ $reclamation->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Traiter la réclamation</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('reclamations.traiter', $reclamation->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>Client:</strong> 
                                                        @if($reclamation->client && $reclamation->client->boutique)
                                                            {{ $reclamation->client->boutique->nom }}
                                                        @elseif($reclamation->client)
                                                            {{ $reclamation->client->nom }} {{ $reclamation->client->prenoms }}
                                                        @endif
                                                        <br>
                                                        <strong>Commune:</strong> {{ $reclamation->commande->communes ?? '-' }}<br>
                                                        <strong>Type:</strong> {{ $reclamation->type_label }}<br>
                                                        <strong>Montant actuel:</strong> {{ number_format($reclamation->montant_actuel ?? 0, 0, ',', ' ') }} XOF<br>
                                                        @if($reclamation->montant_reclame)
                                                            <strong>Montant réclamé:</strong> {{ number_format($reclamation->montant_reclame, 0, ',', ' ') }} XOF
                                                        @endif
                                                    </div>

                                                    <hr>
                                                    <h6 class="text-primary"><i class="fas fa-edit"></i> Modifier la commande</h6>
                                                    
                                                    <div class="form-group">
                                                        <label>Commune</label>
                                                        <input type="text" name="communes" class="form-control" value="{{ $reclamation->commande->communes ?? '' }}" placeholder="Nom de la commune">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Montant Global</label>
                                                        <input type="number" name="cout_global" id="cout_global_{{ $reclamation->id }}" class="form-control" value="{{ $reclamation->commande->cout_global ?? 0 }}" placeholder="Montant global" oninput="calculerCoutReel({{ $reclamation->id }})">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Coût Livraison</label>
                                                        <select name="cout_livraison" id="cout_livraison_{{ $reclamation->id }}" class="form-control" onchange="calculerCoutReel({{ $reclamation->id }})">
                                                            @php
                                                                $coutsLivraison = \App\Models\CoutLivraison::orderBy('cout_livraison')->get();
                                                            @endphp
                                                            @foreach($coutsLivraison as $cout)
                                                                <option value="{{ $cout->cout_livraison }}" {{ ($reclamation->commande->cout_livraison ?? 0) == $cout->cout_livraison ? 'selected' : '' }}>
                                                                    {{ number_format($cout->cout_livraison, 0, ',', ' ') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Coût Réel <small class="text-muted">(calculé automatiquement)</small></label>
                                                        <input type="number" name="cout_reel" id="cout_reel_{{ $reclamation->id }}" class="form-control bg-light" value="{{ $reclamation->commande->cout_reel ?? 0 }}" readonly>
                                                    </div>

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Valider</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <!-- Modal Détails -->
                                <div class="modal fade" id="detailModal{{ $reclamation->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info">
                                                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i>Détails de la réclamation</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Client:</strong> 
                                                    @if($reclamation->client && $reclamation->client->boutique)
                                                        {{ $reclamation->client->boutique->nom }}
                                                    @elseif($reclamation->client)
                                                        {{ $reclamation->client->nom }} {{ $reclamation->client->prenoms }}
                                                    @endif
                                                </p>
                                                <p><strong>Commune:</strong> {{ $reclamation->commande->communes ?? '-' }}</p>
                                                <p><strong>Type:</strong> {{ $reclamation->type_label }}</p>
                                                <p><strong>Montant actuel:</strong> {{ number_format($reclamation->montant_actuel ?? 0, 0, ',', ' ') }} XOF</p>
                                                @if($reclamation->montant_reclame)
                                                    <p><strong>Montant réclamé:</strong> {{ number_format($reclamation->montant_reclame, 0, ',', ' ') }} XOF</p>
                                                @endif
                                                <hr>
                                                <p><strong>Statut:</strong> <span class="badge badge-{{ $reclamation->statut_badge_class }}">{{ $reclamation->statut_label }}</span></p>
                                                @if($reclamation->reponse_admin)
                                                    <p><strong>Réponse:</strong> {{ $reclamation->reponse_admin }}</p>
                                                @endif
                                                @if($reclamation->date_traitement)
                                                    <p><strong>Date de traitement:</strong> {{ $reclamation->date_traitement->format('d/m/Y H:i') }}</p>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Modal Supprimer -->
                                <div class="modal fade" id="supprimerModal{{ $reclamation->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger">
                                                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmer la suppression</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('reclamations.supprimer', $reclamation->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                                                        <p>Êtes-vous sûr de vouloir supprimer cette réclamation ?</p>
                                                    </div>
                                                    <div class="p-3 bg-light rounded">
                                                        <p class="mb-1"><strong>Client:</strong> 
                                                            @if($reclamation->client && $reclamation->client->boutique)
                                                                {{ $reclamation->client->boutique->nom }}
                                                            @elseif($reclamation->client)
                                                                {{ $reclamation->client->nom }} {{ $reclamation->client->prenoms }}
                                                            @endif
                                                        </p>
                                                        <p class="mb-1"><strong>Type:</strong> {{ $reclamation->type_label }}</p>
                                                        <p class="mb-1"><strong>Montant actuel:</strong> {{ number_format($reclamation->montant_actuel ?? 0, 0, ',', ' ') }} XOF</p>
                                                        @if($reclamation->montant_reclame)
                                                            <p class="mb-0"><strong>Montant réclamé:</strong> {{ number_format($reclamation->montant_reclame, 0, ',', ' ') }} XOF</p>
                                                        @endif
                                                    </div>
                                                    <div class="alert alert-warning mt-3 mb-0">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Cette action est irréversible.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-trash mr-1"></i>Supprimer
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Aucune réclamation en attente</p>
                    </div>
                @endif
            </div>
            @if($reclamations->hasPages())
                <div class="card-footer">
                    {{ $reclamations->links() }}
                </div>
            @endif
        </div>
    </div>
</section>

<script>
function toggleChampsModification(select, id) {
    var champsModification = document.getElementById('champsModification' + id);
    if (select.value === 'accepter') {
        champsModification.style.display = 'block';
    } else {
        champsModification.style.display = 'none';
    }
}

function calculerCoutReel(id) {
    var coutGlobal = parseInt(document.getElementById('cout_global_' + id).value) || 0;
    var coutLivraison = parseInt(document.getElementById('cout_livraison_' + id).value) || 0;
    var coutReel = coutGlobal - coutLivraison;
    document.getElementById('cout_reel_' + id).value = coutReel;
}
</script>
@endsection
