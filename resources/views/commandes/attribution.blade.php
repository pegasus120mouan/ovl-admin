@extends('layout.main')

@section('title', 'Attribution des commandes')
@section('page_title', 'Attribution des commandes')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show js-auto-dismiss">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtres</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('commandes.attribution') }}" method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut" class="form-control">
                            <option value="Non Livré" {{ request('statut', 'Non Livré') == 'Non Livré' ? 'selected' : '' }}>Non Livré</option>
                            <option value="Livré" {{ request('statut') == 'Livré' ? 'selected' : '' }}>Livré</option>
                            <option value="Retour" {{ request('statut') == 'Retour' ? 'selected' : '' }}>Retour</option>
                            <option value="" {{ request('statut') === '' ? 'selected' : '' }}>Tous</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Boutique</label>
                        <select name="boutique_id" class="form-control">
                            <option value="">Toutes les boutiques</option>
                            @foreach($boutiques as $boutique)
                                <option value="{{ $boutique->id }}" {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                    {{ $boutique->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Livreur</label>
                        <select name="livreur_id" class="form-control">
                            <option value="">Tous les livreurs</option>
                            <option value="non_attribue" {{ request('livreur_id') == 'non_attribue' ? 'selected' : '' }}>Non attribué</option>
                            @foreach($livreurs as $livreur)
                                <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                    {{ $livreur->nom }} {{ $livreur->prenoms }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date réception</label>
                        <input type="date" name="date_reception" class="form-control" value="{{ request('date_reception') }}">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        <a href="{{ route('commandes.attribution') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions en masse -->
    <div class="card card-outline card-warning mb-3" id="actionsEnMasse" style="display: none;">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="font-weight-bold"><span id="selectedCount">0</span> commande(s) sélectionnée(s)</span>
                </div>
                <div class="d-flex" style="gap: 10px;">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAttribuerMasse">
                        <i class="fas fa-user-plus mr-1"></i>Attribuer à un livreur
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalSupprimerMasse">
                        <i class="fas fa-trash mr-1"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des commandes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>Liste des commandes
                <span class="badge badge-info ml-2">{{ $commandes->total() }}</span>
            </h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="selectAll">
                                <label class="custom-control-label" for="selectAll"></label>
                            </div>
                        </th>
                        <th>Communes</th>
                        <th>Coût Global</th>
                        <th>Livraison</th>
                        <th>Coût réel</th>
                        <th>Boutique</th>
                        <th>Livreur</th>
                        <th class="text-center">Statut</th>
                        <th>Date réception</th>
                        <th>Date livraison</th>
                        <th>Date Retour</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commandes as $commande)
                    <tr>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input commande-checkbox" 
                                       id="commande{{ $commande->id }}" 
                                       value="{{ $commande->id }}"
                                       data-commune="{{ $commande->communes }}">
                                <label class="custom-control-label" for="commande{{ $commande->id }}"></label>
                            </div>
                        </td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $commande->communes }}">
                            {{ $commande->communes }}
                        </td>
                        <td>{{ number_format($commande->cout_global, 0, ',', ' ') }}</td>
                        <td>{{ number_format($commande->cout_livraison, 0, ',', ' ') }}</td>
                        <td>{{ number_format($commande->cout_reel, 0, ',', ' ') }}</td>
                        <td>
                            <span class="badge badge-info">{{ $commande->client->boutique->nom ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($commande->livreur)
                                {{ $commande->livreur->nom }} {{ $commande->livreur->prenoms }}
                            @else
                                <span class="badge badge-warning">Non attribué</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($commande->statut == 'Livré')
                                <img src="{{ asset('img/icones/ok.png') }}" alt="Livré" title="Livré" style="height:25px;">
                            @elseif($commande->statut == 'Non Livré')
                                <img src="{{ asset('img/icones/non_ok.png') }}" alt="Non Livré" title="Non Livré" style="height:25px;">
                            @elseif($commande->statut == 'Retour')
                                <img src="{{ asset('img/icones/return.png') }}" alt="Retour" title="Retour" style="height:25px;">
                            @endif
                        </td>
                        <td>{{ $commande->date_reception ? $commande->date_reception->format('d-m-Y') : 'N/A' }}</td>
                        <td>
                            @if($commande->date_livraison)
                                {{ $commande->date_livraison->format('d-m-Y') }}
                            @else
                                <span class="badge badge-secondary">Pas encore livré</span>
                            @endif
                        </td>
                        <td>{{ $commande->date_retour ? $commande->date_retour->format('d-m-Y') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune commande trouvée</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commandes->hasPages())
        <div class="card-footer clearfix">
            <div class="float-left text-muted" style="line-height: 38px;">
                Affichage de <strong>{{ $commandes->firstItem() ?? 0 }}</strong> à <strong>{{ $commandes->lastItem() ?? 0 }}</strong> sur <strong>{{ $commandes->total() }}</strong> entrées
            </div>
            <ul class="pagination pagination-sm m-0 float-right">
                @if($commandes->onFirstPage())
                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $commandes->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a></li>
                @endif

                @php
                    $currentPage = $commandes->currentPage();
                    $lastPage = $commandes->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                @if($start > 1)
                    <li class="page-item"><a class="page-link" href="{{ $commandes->url(1) }}">1</a></li>
                    @if($start > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $commandes->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="{{ $commandes->url($lastPage) }}">{{ $lastPage }}</a></li>
                @endif

                @if($commandes->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $commandes->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a></li>
                @else
                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>

<!-- Modal Attribuer en masse -->
<div class="modal fade" id="modalAttribuerMasse" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>Attribuer les commandes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAttribuerMasse" action="{{ route('commandes.attribuer-masse') }}" method="POST">
                @csrf
                <div id="attribuerCommandeIds"></div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <span class="fa-stack fa-2x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-motorcycle fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <p class="text-center mb-3">
                        Vous allez attribuer <strong id="attribuerCount">0</strong> commande(s) à un livreur.
                    </p>
                    <div class="form-group">
                        <label><strong>Sélectionner un livreur</strong></label>
                        <select name="livreur_id" class="form-control" required>
                            <option value="">-- Choisir un livreur --</option>
                            @foreach($livreurs as $livreur)
                                <option value="{{ $livreur->id }}">{{ $livreur->nom }} {{ $livreur->prenoms }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check mr-1"></i>Attribuer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Supprimer en masse -->
<div class="modal fade" id="modalSupprimerMasse" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmation de suppression
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSupprimerMasse" action="{{ route('commandes.supprimer-masse') }}" method="POST">
                @csrf
                @method('DELETE')
                <div id="supprimerCommandeIds"></div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <span class="fa-stack fa-2x">
                            <i class="fas fa-circle fa-stack-2x text-danger"></i>
                            <i class="fas fa-trash fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">Êtes-vous sûr de vouloir supprimer ces commandes ?</h5>
                    <p class="text-muted mb-0">
                        <strong id="supprimerCount">0</strong> commande(s) seront supprimée(s)
                    </p>
                    <div class="alert alert-warning mt-3 mb-0 text-left">
                        <i class="fas fa-info-circle mr-1"></i>
                        <small>Cette action est irréversible. Toutes les données associées seront définitivement supprimées.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-trash mr-1"></i>Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    var selectedIds = [];

    function updateSelection() {
        selectedIds = [];
        $('.commande-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        var count = selectedIds.length;
        $('#selectedCount').text(count);
        $('#attribuerCount').text(count);
        $('#supprimerCount').text(count);

        if (count > 0) {
            $('#actionsEnMasse').slideDown();
        } else {
            $('#actionsEnMasse').slideUp();
        }

        // Mettre à jour les inputs cachés
        $('#attribuerCommandeIds').html('');
        $('#supprimerCommandeIds').html('');
        selectedIds.forEach(function(id) {
            $('#attribuerCommandeIds').append('<input type="hidden" name="commande_ids[]" value="' + id + '">');
            $('#supprimerCommandeIds').append('<input type="hidden" name="commande_ids[]" value="' + id + '">');
        });
    }

    // Sélectionner tout
    $('#selectAll').on('change', function() {
        $('.commande-checkbox').prop('checked', $(this).is(':checked'));
        updateSelection();
    });

    // Sélection individuelle
    $(document).on('change', '.commande-checkbox', function() {
        var allChecked = $('.commande-checkbox').length === $('.commande-checkbox:checked').length;
        $('#selectAll').prop('checked', allChecked);
        updateSelection();
    });

    // Validation avant soumission
    $('#formAttribuerMasse').on('submit', function(e) {
        if (selectedIds.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une commande.');
            return false;
        }
    });

    $('#formSupprimerMasse').on('submit', function(e) {
        if (selectedIds.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une commande.');
            return false;
        }
    });
});
</script>
@endpush
