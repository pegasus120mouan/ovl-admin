@extends('layout.main')

@section('title', 'Montant des commerciaux')
@section('page_title', 'Montant des commerciaux')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card card-outline card-success">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-cog"></i> Paramétrer la commission</h3>
        </div>
        <form action="{{ route('montant-commerciaux.taux') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            <p class="text-muted mb-3">
              Un seul taux pour tous les commerciaux, appliqué au <strong>coût de livraison</strong> de chaque colis livré.
            </p>
            <div class="form-row align-items-end">
              <div class="form-group col-md-3 mb-0">
                <label for="taux">Taux de commission (%)</label>
                <input type="number" class="form-control" id="taux" name="taux" value="{{ old('taux', $regle->taux ?? 5) }}" min="0" max="100" step="0.01" required>
              </div>
              <div class="form-group col-md-3 mb-0">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save"></i> Enregistrer le taux
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-handshake"></i> Liste des commerciaux</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Commercial</th>
                <th>Montant dû</th>
                <th>Montant payé</th>
                <th>Reste à payer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($commerciaux as $commercial)
                @php
                  $ficheUrl = route('montant-commerciaux.show', $commercial->code_commercial ?: $commercial);
                @endphp
                <tr>
                  <td>
                    <a href="{{ $ficheUrl }}" class="font-weight-bold">
                      {{ trim(($commercial->nom ?? '').' '.($commercial->prenoms ?? '')) }}
                    </a>
                    <div class="text-muted small">
                      {{ $commercial->code_commercial ?: $commercial->defaultCommercialCode() }}
                    </div>
                  </td>
                  <td class="text-success font-weight-bold">
                    {{ number_format($commercial->montant_du ?? 0, 0, ',', ' ') }} FCFA
                  </td>
                  <td class="text-success font-weight-bold">
                    {{ number_format($commercial->montant_paye ?? 0, 0, ',', ' ') }} FCFA
                  </td>
                  <td>
                    @if (($commercial->reste_a_payer ?? 0) <= 0)
                      <span class="text-success"><i class="fas fa-check-circle"></i> Soldé</span>
                    @else
                      <span class="text-warning font-weight-bold">
                        {{ number_format($commercial->reste_a_payer, 0, ',', ' ') }} FCFA
                      </span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ $ficheUrl }}" class="btn btn-sm btn-outline-primary" title="Bordereaux">
                      <i class="fas fa-file-invoice"></i> Bordereaux
                    </a>
                    <a href="{{ $ficheUrl }}" class="btn btn-sm btn-outline-secondary" title="Voir la fiche">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4">Aucun commercial trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $commerciaux->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
