@extends('layout.main')

@section('title', 'Situation financière — ' . $nomComplet)
@section('page_title', 'Situation financière — ' . $nomComplet)

@section('content')
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
      <div>
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
          <span class="badge badge-primary px-3 py-2">{{ $livreur->login }}</span>
          <span class="badge badge-secondary px-3 py-2">{{ $livreur->contact }}</span>
          @if($livreur->statut_compte)
            <span class="badge badge-success px-3 py-2">Actif</span>
          @else
            <span class="badge badge-danger px-3 py-2">Inactif</span>
          @endif
        </div>
      </div>
      <div class="d-flex flex-wrap" style="gap: 8px;">
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalCreerDette">
          <i class="fas fa-file-invoice-dollar mr-1"></i> Créer une dette
        </button>
        <a href="{{ route('points-livreurs.montant-livreurs') }}" class="btn btn-light border">
          <i class="fas fa-arrow-left mr-1"></i> Retour
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h6 class="font-weight-bold mb-3">Filtres</h6>
      <form method="GET" action="{{ route('points-livreurs.situation-financiere', $livreur) }}" class="row align-items-end">
        <div class="col-md-3">
          <label class="font-weight-bold">Date début</label>
          <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}" required>
        </div>
        <div class="col-md-3">
          <label class="font-weight-bold">Date fin</label>
          <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}" required>
        </div>
        <div class="col-md-6">
          <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Filtrer</button>
          <a href="{{ route('points-livreurs.situation-financiere', $livreur) }}" class="btn btn-light border">Réinitialiser</a>
        </div>
      </form>
    </div>
  </div>

  <div class="text-center mb-3">
    <span class="badge badge-primary px-4 py-2" style="font-size: 14px;">
      Colis livrés : {{ number_format($nbColis, 0, ',', ' ') }}
    </span>
  </div>

  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card border-left-danger shadow-sm h-100" style="border-left: 4px solid #dc3545;">
        <div class="card-body">
          <div class="text-danger font-weight-bold mb-1">Montant dû</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($montantDu, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Total des montants à remettre sur la période</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-left-success shadow-sm h-100" style="border-left: 4px solid #28a745;">
        <div class="card-body">
          <div class="text-success font-weight-bold mb-1">Montant payé</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($montantPaye, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Versements enregistrés sur la période</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100" style="border-left: 4px solid #6f42c1;">
        <div class="card-body">
          <div class="font-weight-bold mb-1" style="color: #6f42c1;">Dettes</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($totalDettesReste, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Pertes et prêts restant à rembourser</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-left-warning shadow-sm h-100" style="border-left: 4px solid #ffc107;">
        <div class="card-body">
          <div class="text-warning font-weight-bold mb-1">Reste à payer</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($resteAPayer, 0, ',', ' ') }} XOF</h3>
          <small class="text-muted">Montant dû − montant payé</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
      <h5 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-2"></i> Versement</h5>
      <div class="d-flex align-items-center" style="gap: 8px;">
        <span class="badge badge-light text-info">{{ $versementsJournaliers->total() }} jour(s)</span>
        <button type="button" id="btnSolderSelection" class="btn btn-sm btn-success" disabled>
          <i class="fas fa-check-double mr-1"></i> Solder la sélection
          <span id="solderSelectionCount" class="badge badge-light text-success ml-1" style="display:none;">0</span>
        </button>
      </div>
    </div>
    <form id="formSolderMasse" action="{{ route('points-livreurs.situation-financiere.paiement-masse', $livreur) }}" method="POST">
      @csrf
      <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
      <input type="hidden" name="date_fin" value="{{ $dateFin }}">
      <input type="hidden" name="page" value="{{ $versementsJournaliers->currentPage() }}">
      <div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 40px;">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="selectAllVersements">
                  <label class="custom-control-label" for="selectAllVersements"></label>
                </div>
              </th>
              <th>Date</th>
              <th>Montant à remettre</th>
              <th>Montant payé</th>
              <th>Reste à payer</th>
              <th class="text-center">Statut</th>
              <th class="text-center" style="width: 180px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($versementsJournaliers as $versement)
              <tr>
                <td>
                  @if(($versement['reste_a_payer'] ?? 0) > 0)
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox"
                             class="custom-control-input versement-checkbox"
                             id="versement{{ $loop->index }}"
                             name="dates[]"
                             value="{{ $versement['date'] }}"
                             data-reste="{{ (int) ($versement['reste_a_payer'] ?? 0) }}"
                             data-date="{{ $versement['date'] ? \Carbon\Carbon::parse($versement['date'])->format('d/m/Y') : 'N/A' }}">
                      <label class="custom-control-label" for="versement{{ $loop->index }}"></label>
                    </div>
                  @endif
                </td>
                <td>{{ $versement['date'] ? \Carbon\Carbon::parse($versement['date'])->format('d/m/Y') : 'N/A' }}</td>
                <td class="font-weight-bold">{{ number_format($versement['montant_a_remettre'] ?? 0, 0, ',', ' ') }} XOF</td>
                <td class="text-success font-weight-bold">{{ number_format($versement['montant_verse'] ?? 0, 0, ',', ' ') }} XOF</td>
                <td class="font-weight-bold {{ ($versement['reste_a_payer'] ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">
                  {{ number_format($versement['reste_a_payer'] ?? 0, 0, ',', ' ') }} XOF
                </td>
                <td class="text-center">
                  @if(($versement['statut'] ?? '') === 'Soldé')
                    <span class="badge badge-success px-3 py-2">Soldé</span>
                  @else
                    <span class="badge badge-danger px-3 py-2">Non Soldé</span>
                  @endif
                </td>
                <td class="text-center">
                  @if(($versement['reste_a_payer'] ?? 0) > 0)
                    <button type="button"
                            class="btn btn-sm btn-success mb-1"
                            data-toggle="modal"
                            data-target="#modalPaiementVersement{{ $loop->index }}"
                            title="Enregistrer le versement">
                      <i class="fas fa-check mr-1"></i> Payé
                    </button>
                  @endif
                  @if(($versement['montant_verse'] ?? 0) > 0)
                    <button type="button"
                            class="btn btn-sm btn-danger mb-1"
                            data-toggle="modal"
                            data-target="#modalAnnulerPaiementVersement{{ $loop->index }}"
                            title="Annuler le paiement">
                      <i class="fas fa-undo mr-1"></i> Annuler
                    </button>
                  @endif
                  @if(($versement['reste_a_payer'] ?? 0) <= 0 && ($versement['montant_verse'] ?? 0) <= 0)
                    <span class="text-muted">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">Aucun versement sur cette période</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>
    @if($versementsJournaliers->hasPages())
      <div class="card-footer clearfix">
        <div class="float-left text-muted" style="line-height: 38px;">
          Affichage de <strong>{{ $versementsJournaliers->firstItem() ?? 0 }}</strong> à <strong>{{ $versementsJournaliers->lastItem() ?? 0 }}</strong> sur <strong>{{ $versementsJournaliers->total() }}</strong> entrées
        </div>
        <div class="float-right">
          {{ $versementsJournaliers->onEachSide(1)->links() }}
        </div>
      </div>
    @endif
  </div>

  @if($paiementsDeclares->isNotEmpty())
    @php($nbEnAttente = $paiementsDeclares->where('statut', \App\Models\PaiementLivreur::STATUT_EN_ATTENTE)->count())
    <div class="card mb-4">
      <div class="card-header bg-warning d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
        <h5 class="card-title mb-0"><i class="fas fa-mobile-alt mr-2"></i> Paiements déclarés depuis l'application</h5>
        <span class="badge badge-light">{{ $nbEnAttente }} en attente</span>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Livreur</th>
              <th>Déclaré le</th>
              <th>Jour concerné</th>
              <th>Montant</th>
              <th>Mode</th>
              <th>Référence</th>
              <th class="text-center">Statut</th>
              <th class="text-center" style="width: 200px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($paiementsDeclares as $paiement)
              <tr>
                <td class="font-weight-bold">{{ $nomComplet }}</td>
                <td>{{ $paiement->created_at ? $paiement->created_at->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ $paiement->date_versement ? $paiement->date_versement->format('d/m/Y') : 'N/A' }}</td>
                <td class="font-weight-bold">
                  {{ number_format((int) $paiement->montant, 0, ',', ' ') }} XOF
                  @if($paiement->statut === \App\Models\PaiementLivreur::STATUT_VALIDE && (int) $paiement->montant_valide !== (int) $paiement->montant)
                    <br><small class="text-muted">Validé : {{ number_format((int) $paiement->montant_valide, 0, ',', ' ') }} XOF</small>
                  @endif
                </td>
                <td><span class="badge badge-secondary px-2 py-1">{{ $paiement->mode_paiement }}</span></td>
                <td>{{ $paiement->reference ?: '—' }}</td>
                <td class="text-center">
                  @if($paiement->statut === \App\Models\PaiementLivreur::STATUT_EN_ATTENTE)
                    <span class="badge badge-warning px-3 py-2">En attente</span>
                  @elseif($paiement->statut === \App\Models\PaiementLivreur::STATUT_VALIDE)
                    <span class="badge badge-success px-3 py-2">Validé</span>
                  @else
                    <span class="badge badge-danger px-3 py-2" @if($paiement->motif_rejet) title="{{ $paiement->motif_rejet }}" @endif>Rejeté</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($paiement->statut === \App\Models\PaiementLivreur::STATUT_EN_ATTENTE)
                    <button type="button" class="btn btn-sm btn-success mb-1" data-toggle="modal" data-target="#modalValiderPaiement{{ $paiement->id }}">
                      <i class="fas fa-check mr-1"></i> Valider
                    </button>
                    <button type="button" class="btn btn-sm btn-danger mb-1" data-toggle="modal" data-target="#modalRejeterPaiement{{ $paiement->id }}">
                      <i class="fas fa-times mr-1"></i> Rejeter
                    </button>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
      <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i> Dettes du livreur</h5>
      <span class="badge badge-light text-danger">Reste dû : {{ number_format($totalDettesReste, 0, ',', ' ') }} XOF</span>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Motif</th>
            <th>Montant</th>
            <th>Remboursé</th>
            <th>Reste</th>
            <th>Échéance</th>
            <th class="text-center">Statut</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dettes as $dette)
            <tr>
              <td>{{ $dette->date_dette ? $dette->date_dette->format('d/m/Y') : 'N/A' }}</td>
              <td>
                <span class="badge {{ $dette->type === 'Perte' ? 'badge-danger' : 'badge-info' }} px-2 py-1">{{ $dette->type }}</span>
              </td>
              <td>{{ $dette->motifs ?: '—' }}</td>
              <td class="font-weight-bold">{{ number_format((int) $dette->montant_actuel, 0, ',', ' ') }} XOF</td>
              <td class="text-success font-weight-bold">{{ number_format((int) $dette->montants_payes, 0, ',', ' ') }} XOF</td>
              <td class="font-weight-bold {{ (int) $dette->reste > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format((int) $dette->reste, 0, ',', ' ') }} XOF</td>
              <td>{{ $dette->date_echeance ? $dette->date_echeance->format('d/m/Y') : '—' }}</td>
              <td class="text-center">
                @if((int) $dette->reste > 0)
                  <span class="badge badge-warning px-3 py-2">En cours</span>
                @else
                  <span class="badge badge-success px-3 py-2">Soldée</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">Aucune dette pour ce livreur</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCreerDette" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Créer une dette — {{ $nomComplet }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('points-livreurs.dettes.store', $livreur) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Type</label>
            <select name="type" class="form-control" required>
              <option value="">-- Sélectionner --</option>
              @foreach(\App\Models\Dette::TYPES_LIVREUR as $typeDette)
                <option value="{{ $typeDette }}" {{ old('type') === $typeDette ? 'selected' : '' }}>{{ $typeDette }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Montant (XOF)</label>
            <input type="number" name="montant" class="form-control" min="1" step="1" value="{{ old('montant') }}" required>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="font-weight-bold">Date de la dette</label>
              <input type="date" name="date_dette" class="form-control" value="{{ old('date_dette', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group col-md-6">
              <label class="font-weight-bold">Échéance (optionnel)</label>
              <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance') }}">
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold">Motif</label>
            <textarea name="motifs" class="form-control" rows="2" placeholder="Ex. colis perdu, avance sur salaire...">{{ old('motifs') }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-save mr-1"></i> Enregistrer la dette
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($versementsJournaliers as $versement)
  @if(($versement['reste_a_payer'] ?? 0) > 0)
    <div class="modal fade" id="modalPaiementVersement{{ $loop->index }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-hand-holding-usd mr-2"></i>Enregistrer le versement</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <form action="{{ route('points-livreurs.situation-financiere.paiement', $livreur) }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $versement['date'] }}">
            <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
            <input type="hidden" name="date_fin" value="{{ $dateFin }}">
            <input type="hidden" name="page" value="{{ $versementsJournaliers->currentPage() }}">
            <div class="modal-body">
              <div class="mb-3 p-3 bg-light rounded">
                <p class="mb-1"><strong>Livreur :</strong> {{ $nomComplet }}</p>
                <p class="mb-1"><strong>Date :</strong> {{ $versement['date'] ? \Carbon\Carbon::parse($versement['date'])->format('d/m/Y') : 'N/A' }}</p>
                <p class="mb-1"><strong>Montant à remettre :</strong> {{ number_format($versement['montant_a_remettre'] ?? 0, 0, ',', ' ') }} XOF</p>
                <p class="mb-1"><strong>Montant payé :</strong> {{ number_format($versement['montant_verse'] ?? 0, 0, ',', ' ') }} XOF</p>
                <p class="mb-0"><strong>Reste à payer :</strong> <span class="text-warning font-weight-bold">{{ number_format($versement['reste_a_payer'] ?? 0, 0, ',', ' ') }} XOF</span></p>
              </div>
              <div class="form-group mb-0">
                <label class="font-weight-bold">Montant à payer</label>
                <input type="number"
                       class="form-control"
                       name="montant"
                       min="1"
                       max="{{ (int) ($versement['reste_a_payer'] ?? 0) }}"
                       value="{{ (int) ($versement['reste_a_payer'] ?? 0) }}"
                       required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-success">
                <i class="fas fa-check mr-1"></i> Valider le versement
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if(($versement['montant_verse'] ?? 0) > 0)
    <div class="modal fade" id="modalAnnulerPaiementVersement{{ $loop->index }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="fas fa-undo mr-2"></i>Annuler le paiement</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <form action="{{ route('points-livreurs.situation-financiere.annuler-paiement', $livreur) }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $versement['date'] }}">
            <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
            <input type="hidden" name="date_fin" value="{{ $dateFin }}">
            <input type="hidden" name="page" value="{{ $versementsJournaliers->currentPage() }}">
            <div class="modal-body">
              <p class="mb-2">Confirmez-vous l'annulation du paiement pour le <strong>{{ $versement['date'] ? \Carbon\Carbon::parse($versement['date'])->format('d/m/Y') : 'N/A' }}</strong> ?</p>
              <p class="mb-0 text-muted">Montant payé à annuler : <strong class="text-danger">{{ number_format($versement['montant_verse'] ?? 0, 0, ',', ' ') }} XOF</strong></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Non</button>
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-undo mr-1"></i> Confirmer l'annulation
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endforeach

@foreach($paiementsDeclares as $paiement)
  @if($paiement->statut === \App\Models\PaiementLivreur::STATUT_EN_ATTENTE)
    @php
      $jourPaiement = $paiement->date_versement ? $paiement->date_versement->toDateString() : null;
      $resteJour = $jourPaiement !== null ? ($resteParJour[$jourPaiement] ?? null) : null;
      $montantDeclare = (int) $paiement->montant;
      $montantApplique = $resteJour !== null ? min($montantDeclare, $resteJour) : $montantDeclare;
      $resteApres = $resteJour !== null ? max(0, $resteJour - $montantApplique) : null;
    @endphp

    <div class="modal fade" id="modalValiderPaiement{{ $paiement->id }}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i>Valider le paiement</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer"><span>&times;</span></button>
          </div>
          <form action="{{ route('points-livreurs.paiements-declares.valider', [$livreur, $paiement]) }}" method="POST" class="form-paiement-declare">
            @csrf
            <div class="modal-body">
              <div class="text-center mb-3">
                <div class="text-muted small text-uppercase">Montant déclaré</div>
                <div class="font-weight-bold" style="font-size: 2rem; line-height: 1.2;">{{ number_format($montantDeclare, 0, ',', ' ') }} XOF</div>
                <span class="badge badge-secondary px-3 py-2 mt-1">{{ $paiement->mode_paiement }}</span>
              </div>

              <table class="table table-sm table-borderless mb-3">
                <tbody>
                  <tr>
                    <td class="text-muted" style="width: 45%;">Livreur</td>
                    <td class="font-weight-bold text-right">{{ $nomComplet }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Jour concerné</td>
                    <td class="font-weight-bold text-right">{{ $paiement->date_versement ? $paiement->date_versement->format('d/m/Y') : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Déclaré le</td>
                    <td class="text-right">{{ $paiement->created_at ? $paiement->created_at->format('d/m/Y à H:i') : '—' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Référence</td>
                    <td class="text-right">{{ $paiement->reference ?: '—' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Reste à payer du jour</td>
                    <td class="text-right font-weight-bold text-warning">
                      {{ $resteJour !== null ? number_format($resteJour, 0, ',', ' ') . ' XOF' : '—' }}
                    </td>
                  </tr>
                </tbody>
              </table>

              @if($resteJour !== null && $resteJour <= 0)
                <div class="alert alert-danger mb-0">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  Ce jour est déjà soldé. La validation sera refusée et la déclaration sera rejetée.
                </div>
              @elseif($resteJour !== null && $montantApplique < $montantDeclare)
                <div class="alert alert-warning mb-0">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  Le montant dépasse le reste à payer du jour : seuls
                  <strong>{{ number_format($montantApplique, 0, ',', ' ') }} XOF</strong> seront enregistrés.
                </div>
              @else
                <div class="alert alert-success mb-0">
                  <i class="fas fa-info-circle mr-1"></i>
                  <strong>{{ number_format($montantApplique, 0, ',', ' ') }} XOF</strong> seront ajoutés au versement du jour.
                  @if($resteApres !== null)
                    Reste après validation : <strong>{{ number_format($resteApres, 0, ',', ' ') }} XOF</strong>.
                  @endif
                </div>
              @endif

              <p class="text-muted small mt-3 mb-0">
                <i class="fas fa-shield-alt mr-1"></i>
                Vérifiez la réception de l'argent (Mobile Money) avant de valider. Cette action met à jour le montant payé.
              </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-success">
                <i class="fas fa-check mr-1"></i> Confirmer la validation
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalRejeterPaiement{{ $paiement->id }}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i>Rejeter le paiement</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer"><span>&times;</span></button>
          </div>
          <form action="{{ route('points-livreurs.paiements-declares.rejeter', [$livreur, $paiement]) }}" method="POST" class="form-paiement-declare">
            @csrf
            <div class="modal-body">
              <div class="mb-3 p-3 bg-light rounded">
                <p class="mb-1"><strong>Livreur :</strong> {{ $nomComplet }}</p>
                <p class="mb-1"><strong>Jour concerné :</strong> {{ $paiement->date_versement ? $paiement->date_versement->format('d/m/Y') : 'N/A' }}</p>
                <p class="mb-1"><strong>Montant :</strong> {{ number_format($montantDeclare, 0, ',', ' ') }} XOF ({{ $paiement->mode_paiement }})</p>
                <p class="mb-0"><strong>Référence :</strong> {{ $paiement->reference ?: '—' }}</p>
              </div>
              <div class="form-group mb-0">
                <label class="font-weight-bold" for="motifRejet{{ $paiement->id }}">Motif du rejet <span class="text-muted font-weight-normal">(optionnel)</span></label>
                <textarea id="motifRejet{{ $paiement->id }}"
                          name="motif_rejet"
                          class="form-control"
                          rows="2"
                          maxlength="255"
                          placeholder="Ex. paiement non reçu, référence introuvable..."></textarea>
                <small class="text-muted">Le livreur verra ce motif dans l'application.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Retour</button>
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-times mr-1"></i> Confirmer le rejet
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endforeach

<div class="modal fade" id="modalSolderSelection" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-check-double mr-2"></i>Solder la sélection</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Vous allez solder <strong id="modalSolderNb">0</strong> jour(s) pour un reste total de <strong id="modalSolderTotal">0</strong> XOF.</p>
        <p class="text-muted mb-0" id="modalSolderDates"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
        <button type="button" id="confirmSolderSelection" class="btn btn-success">
          <i class="fas fa-check mr-1"></i> Confirmer
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
  function selectedVersements() {
    return $('.versement-checkbox:checked');
  }

  function refreshSelection() {
    var checked = selectedVersements();
    var count = checked.length;
    var all = $('.versement-checkbox');
    $('#btnSolderSelection').prop('disabled', count === 0);
    $('#solderSelectionCount').toggle(count > 0).text(count);
    $('#selectAllVersements').prop('checked', all.length > 0 && count === all.length);
  }

  $('#selectAllVersements').on('change', function () {
    $('.versement-checkbox').prop('checked', $(this).is(':checked'));
    refreshSelection();
  });

  $(document).on('change', '.versement-checkbox', refreshSelection);

  $('#btnSolderSelection').on('click', function () {
    var checked = selectedVersements();
    if (!checked.length) {
      return;
    }

    var total = 0;
    var dates = [];
    checked.each(function () {
      total += parseInt($(this).data('reste'), 10) || 0;
      dates.push($(this).data('date'));
    });

    $('#modalSolderNb').text(checked.length);
    $('#modalSolderTotal').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
    $('#modalSolderDates').text(dates.join(', '));
    $('#modalSolderSelection').modal('show');
  });

  // Évite les doubles clics sur la validation / le rejet d'un paiement déclaré.
  $('form.form-paiement-declare').on('submit', function () {
    $(this).find('button[type=submit]')
      .prop('disabled', true)
      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Traitement...');
  });

  $('#confirmSolderSelection').on('click', function () {
    $('#formSolderMasse').submit();
  });

  refreshSelection();
});
</script>
@endsection
