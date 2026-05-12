@extends('layout.main')

@section('title', 'Points validés par les clients')
@section('page_title', 'Points validés par les clients')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle mr-2 text-success"></i>Liste des points validés</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Date livraison</th>
                                <th style="width: 120px;">Boutique</th>
                                <th style="width: 90px;" class="text-center">Colis</th>
                                <th style="width: 110px;" class="text-right">Montant</th>
                                <th style="width: 130px;" class="text-center">Validation</th>
                                <th style="width: 140px;" class="text-center">Paiement</th>
                                <th style="width: 130px;">Date validation</th>
                                <th style="width: 140px;" class="text-center">Actions</th>
                                <th style="width: 80px;" class="text-center">Suppression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pointsValides as $point)
                            <tr>
                                <td>
                                    <strong>{{ $point->date_livraison ? \Carbon\Carbon::parse($point->date_livraison)->format('d-m-Y') : 'N/A' }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info p-2">{{ $point->boutique_nom }}</span>
                                </td>
                                <td class="text-center">{{ $point->nombre_colis }}</td>
                                <td class="text-right">
                                    <strong class="text-success">{{ number_format($point->montant_total, 0, ',', ' ') }} XOF</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check mr-1"></i>Validé
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($point->paiement_effectue)
                                        <span class="badge badge-success p-2">
                                            <i class="fas fa-check mr-1"></i>Payé
                                        </span>
                                        @if($point->operateur_paiement)
                                            <br><small class="text-muted">{{ $point->operateur_paiement }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning p-2">
                                            <i class="fas fa-clock mr-1"></i>En attente
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $point->date_validation_point ? \Carbon\Carbon::parse($point->date_validation_point)->format('d-m-Y H:i') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if(!$point->paiement_effectue)
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalPaiement{{ $loop->index }}" title="Effectuer le paiement">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Payé</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$point->paiement_effectue)
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalSupprimer{{ $loop->index }}" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-secondary" disabled title="Impossible de supprimer un point déjà payé">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun point validé pour le moment</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pointsValides->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left text-muted" style="line-height: 38px;">
                        Affichage de <strong>{{ $pointsValides->firstItem() ?? 0 }}</strong> à <strong>{{ $pointsValides->lastItem() ?? 0 }}</strong> sur <strong>{{ $pointsValides->total() }}</strong> entrées
                    </div>
                    <ul class="pagination pagination-sm m-0 float-right">
                        {{-- Bouton Précédent --}}
                        @if($pointsValides->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $pointsValides->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                            </li>
                        @endif

                        {{-- Numéros de pages --}}
                        @php
                            $currentPage = $pointsValides->currentPage();
                            $lastPage = $pointsValides->lastPage();
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $pointsValides->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $pointsValides->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $pointsValides->url($lastPage) }}">{{ $lastPage }}</a>
                            </li>
                        @endif

                        {{-- Bouton Suivant --}}
                        @if($pointsValides->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $pointsValides->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@foreach($pointsValides as $point)
@if(!$point->paiement_effectue)
<div class="modal fade" id="modalPaiement{{ $loop->index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-credit-card mr-2"></i>Effectuer le paiement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('commandes.effectuer-paiement') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <p class="mb-1"><strong>Boutique:</strong> {{ $point->boutique_nom }}</p>
                        <p class="mb-1"><strong>Date de livraison:</strong> {{ $point->date_livraison ? \Carbon\Carbon::parse($point->date_livraison)->format('d-m-Y') : 'N/A' }}</p>
                        <p class="mb-0"><strong>Montant à payer:</strong> <span class="text-success font-weight-bold">{{ number_format($point->montant_total, 0, ',', ' ') }} XOF</span></p>
                    </div>
                    <input type="hidden" name="date_livraison" value="{{ $point->date_livraison }}">
                    <input type="hidden" name="utilisateur_id" value="{{ $point->utilisateur_id }}">
                    <div class="form-group">
                        <label><strong>Choisir l'opérateur de paiement</strong></label>
                        <select name="operateur" class="form-control" required>
                            <option value="">-- Sélectionner un opérateur --</option>
                            <option value="Orange Money">Orange Money</option>
                            <option value="MTN Mobile Money">MTN Mobile Money</option>
                            <option value="Moov Money">Moov Money</option>
                            <option value="Wave">Wave</option>
                            <option value="Espèces">Espèces</option>
                            <option value="Virement bancaire">Virement bancaire</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Valider le paiement
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSupprimer{{ $loop->index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmer la suppression</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('commandes.supprimer-point-valide') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <p>Êtes-vous sûr de vouloir supprimer ce point validé ?</p>
                    </div>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-1"><strong>Boutique:</strong> {{ $point->boutique_nom }}</p>
                        <p class="mb-1"><strong>Date de livraison:</strong> {{ $point->date_livraison ? \Carbon\Carbon::parse($point->date_livraison)->format('d-m-Y') : 'N/A' }}</p>
                        <p class="mb-1"><strong>Nombre de colis:</strong> {{ $point->nombre_colis }} colis</p>
                        <p class="mb-0"><strong>Montant:</strong> <span class="text-success font-weight-bold">{{ number_format($point->montant_total, 0, ',', ' ') }} XOF</span></p>
                    </div>
                    <input type="hidden" name="date_livraison" value="{{ $point->date_livraison }}">
                    <input type="hidden" name="utilisateur_id" value="{{ $point->utilisateur_id }}">
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Cette action annulera la validation du point. Les commandes concernées devront être revalidées par le client.
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
@endif
@endforeach
@endsection
