@extends('layout.main')

@section('title', 'Bordereaux — '.trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')))
@section('page_title', trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')))

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 8px;">
    <div>
      <h4 class="mb-0">{{ trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')) }}</h4>
      <small class="text-muted">{{ $commercial->code_commercial ?: $commercial->defaultCommercialCode() }}</small>
    </div>
    <a href="{{ route('montant-commerciaux.index') }}" class="btn btn-light border">
      <i class="fas fa-arrow-left mr-1"></i> Retour
    </a>
  </div>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card h-100" style="background: #fde8e8; border: none;">
        <div class="card-body">
          <div class="text-danger font-weight-bold mb-1">Montant dû</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($stats['montant_du'], 0, ',', ' ') }} FCFA</h3>
          <small class="text-muted">Total des commissions mois par mois</small>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100" style="background: #e8f5e9; border: none;">
        <div class="card-body">
          <div class="text-success font-weight-bold mb-1">Montant payé</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($stats['montant_paye'], 0, ',', ' ') }} FCFA</h3>
          <small class="text-muted">Paiements enregistrés sur les bordereaux</small>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100" style="background: #fff8e1; border: none;">
        <div class="card-body">
          <div class="text-warning font-weight-bold mb-1">Reste à payer</div>
          <h3 class="font-weight-bold mb-2">{{ number_format($stats['reste_a_payer'], 0, ',', ' ') }} FCFA</h3>
          <small class="text-muted">Montant dû − montant payé (bordereaux)</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="background: #fde8e8; border-bottom: none; gap: 8px;">
      <h5 class="mb-0"><i class="fas fa-file-invoice mr-2"></i> Gestion bordereaux</h5>
      <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalGenererBordereau">
        <i class="fas fa-plus mr-1"></i> Générer un bordereau
      </button>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>N° bordereau</th>
            <th>Généré le</th>
            <th>Période</th>
            <th>Colis</th>
            <th>Base</th>
            <th>Montant</th>
            <th>Montant payé</th>
            <th>Reste à payer</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($bordereaux as $bordereau)
            <tr>
              <td class="font-weight-bold">{{ $bordereau->numero }}</td>
              <td>{{ $bordereau->genere_le->format('d/m/Y') }}</td>
              <td>{{ $bordereau->date_debut->format('d/m/Y') }} — {{ $bordereau->date_fin->format('d/m/Y') }}</td>
              <td>{{ $bordereau->nb_colis }}</td>
              <td>{{ number_format($bordereau->base_livraison, 0, ',', ' ') }} FCFA</td>
              <td>{{ number_format($bordereau->montant, 0, ',', ' ') }} FCFA</td>
              <td class="text-success">{{ number_format($bordereau->montantPaye(), 0, ',', ' ') }} FCFA</td>
              <td>
                @if ($bordereau->resteAPayer() <= 0)
                  <span class="text-success"><i class="fas fa-check-circle"></i> Soldé</span>
                @else
                  <span class="text-warning font-weight-bold">{{ number_format($bordereau->resteAPayer(), 0, ',', ' ') }} FCFA</span>
                @endif
              </td>
              <td>
                <a href="{{ route('montant-commerciaux.bordereaux.print', [$commercial, $bordereau]) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Imprimer">
                  <i class="fas fa-print"></i>
                </a>
                @if ($bordereau->resteAPayer() > 0)
                  <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalPayerBordereau{{ $bordereau->id }}">
                    Payer
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">Aucun bordereau généré pour ce commercial</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header" style="background: #fde8e8; border-bottom: none;">
      <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i> Montant dû mois par mois ({{ $montantsMensuels->count() }})</h5>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Mois</th>
            <th>Colis livrés</th>
            <th>Base livraison</th>
            <th>Montant dû</th>
            <th>Montant payé</th>
            <th>Reste à payer</th>
            <th>Objectif</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($montantsMensuels as $ligne)
            <tr>
              <td class="font-weight-bold">
                {{ $ligne['periode'] === 'sans-date' ? 'Sans date' : \Carbon\Carbon::parse($ligne['periode'])->format('m/Y') }}
              </td>
              <td>{{ $ligne['colis'] }}</td>
              <td>{{ number_format($ligne['base'], 0, ',', ' ') }} FCFA</td>
              <td class="font-weight-bold">{{ number_format($ligne['montant_du'], 0, ',', ' ') }} FCFA</td>
              <td class="text-success">{{ number_format($ligne['montant_paye'], 0, ',', ' ') }} FCFA</td>
              <td>
                @if ($ligne['reste'] <= 0)
                  <span class="text-success"><i class="fas fa-check-circle"></i> Soldé</span>
                @else
                  <span class="text-warning font-weight-bold">{{ number_format($ligne['reste'], 0, ',', ' ') }} FCFA</span>
                @endif
              </td>
              <td>
                @if ($ligne['objectif'] === null)
                  <span class="text-muted">—</span>
                @else
                  {{ $ligne['objectif'] }} colis
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Aucun montant dû</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header" style="background: #e8f5e9; border-bottom: none;">
      <h5 class="mb-0"><i class="fas fa-check-circle mr-2"></i> Paiements et avances ({{ $paiements->count() }})</h5>
      <small class="text-muted">Paiements sur bordereau ou avances directes</small>
    </div>
    <div class="card-body table-responsive p-0">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Bordereau</th>
            <th>Mode</th>
            <th>Statut</th>
            <th>Montant</th>
            <th>Reçu</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($paiements as $paiement)
            <tr>
              <td>{{ optional($paiement->date_paiement)->format('d/m/Y') ?? '—' }}</td>
              <td>{{ $paiement->bordereau->numero ?? 'Avance' }}</td>
              <td>{{ $paiement->mode ?: '—' }}</td>
              <td><span class="badge badge-success">{{ $paiement->statut ?: 'Validé' }}</span></td>
              <td class="font-weight-bold">{{ number_format((int) $paiement->montant, 0, ',', ' ') }} FCFA</td>
              <td>{{ $paiement->recu ?: '—' }}</td>
              <td>
                <form action="{{ route('montant-commerciaux.paiements.destroy', [$commercial, $paiement]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Annuler ce paiement ?');">
                    Annuler
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Aucun paiement enregistré</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalGenererBordereau" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="{{ route('montant-commerciaux.bordereaux.store', $commercial) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Générer un bordereau</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Les colis livrés de la période, pas encore rattachés à un bordereau, seront inclus.</p>
          <div class="form-group">
            <label>Date début</label>
            <input type="date" name="date_debut" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required>
          </div>
          <div class="form-group mb-0">
            <label>Date fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ now()->toDateString() }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Générer</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach ($bordereaux as $bordereau)
  @if ($bordereau->resteAPayer() > 0)
    <div class="modal fade" id="modalPayerBordereau{{ $bordereau->id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <form action="{{ route('montant-commerciaux.bordereaux.paiement', [$commercial, $bordereau]) }}" method="POST">
            @csrf
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Payer {{ $bordereau->numero }}</h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <p class="mb-3">Reste à payer : <strong>{{ number_format($bordereau->resteAPayer(), 0, ',', ' ') }} FCFA</strong></p>
              <div class="form-group">
                <label>Mode de paiement</label>
                <select name="mode" class="form-control" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Orange Money">Orange Money</option>
                  <option value="MTN Mobile Money">MTN Mobile Money</option>
                  <option value="Moov Money">Moov Money</option>
                  <option value="Wave">Wave</option>
                  <option value="Espèces">Espèces</option>
                  <option value="Virement bancaire">Virement bancaire</option>
                </select>
              </div>
              <div class="form-group mb-0">
                <label>N° reçu (optionnel)</label>
                <input type="text" name="recu" class="form-control" maxlength="80">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-success">Valider le paiement</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endforeach
@endsection
