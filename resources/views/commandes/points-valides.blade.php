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
                                <th>Date de livraison</th>
                                <th>Boutique</th>
                                <th>Nombre de colis</th>
                                <th class="text-right">Montant</th>
                                <th class="text-center">Statut validation</th>
                                <th class="text-center">Statut paiement</th>
                                <th>Date de validation</th>
                                <th>Actions</th>
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
                                <td>{{ $point->nombre_colis }} colis</td>
                                <td class="text-right">
                                    <strong class="text-success">{{ number_format($point->montant_total, 0, ',', ' ') }} XOF</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check mr-1"></i>Validé par le client
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($point->paiement_effectue)
                                        <span class="badge badge-success p-2">
                                            <i class="fas fa-money-bill-wave mr-1"></i>Paiement effectué
                                        </span>
                                        @if($point->operateur_paiement)
                                            <br><small class="text-muted">{{ $point->operateur_paiement }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning p-2">
                                            <i class="fas fa-clock mr-1"></i>Paiement non effectué
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $point->date_validation_point ? \Carbon\Carbon::parse($point->date_validation_point)->format('d-m-Y à H:i') : 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    @if(!$point->paiement_effectue)
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalPaiement{{ $loop->index }}">
                                            <i class="fas fa-credit-card mr-1"></i>Effectuer le paiement
                                        </button>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Payé</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun point validé pour le moment</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pointsValides->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ $pointsValides->firstItem() ?? 0 }} à {{ $pointsValides->lastItem() ?? 0 }} sur {{ $pointsValides->total() }} entrées
                        </div>
                        <div>
                            {{ $pointsValides->links() }}
                        </div>
                    </div>
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
@endif
@endforeach
@endsection
