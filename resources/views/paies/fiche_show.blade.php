@extends('layout.main')

@section('title', 'Fiche de paie')
@section('page_title', 'Fiche de paie')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <h5 class="mb-1">{{ $fiche->livreur->nom ?? 'N/A' }} {{ $fiche->livreur->prenoms ?? '' }}</h5>
              <div>
                Période: {{ $fiche->periode->libelle ?? '' }}
              </div>
              <div class="mt-1">
                Statut: <b>{{ $fiche->statut }}</b>
              </div>
            </div>
            <div class="col-md-4 text-right">
              <a href="{{ route('paies.periodes.show', $fiche->periode) }}" class="btn btn-default">Retour</a>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-md-4">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3>{{ number_format($fiche->salaire_base ?? 0, 0, ',', ' ') }}</h3>
                  <p>Salaire base</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3>{{ number_format($fiche->total_ajustements ?? 0, 0, ',', ' ') }}</h3>
                  <p>Ajustements approuvés</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3>{{ number_format($fiche->net_a_payer ?? 0, 0, ',', ' ') }}</h3>
                  <p>Net à payer</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header"><b>Ajouter un ajustement</b></div>
                <div class="card-body">
                  <form method="POST" action="{{ route('paies.fiches.ajustements.store', $fiche) }}">
                    @csrf
                    <div class="form-group">
                      <label>Type</label>
                      <select name="type" class="form-control" required>
                        <option value="bonus">bonus</option>
                        <option value="retenue">retenue</option>
                        <option value="remboursement">remboursement</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Montant (positif ou négatif)</label>
                      <input type="number" name="montant" class="form-control" required>
                    </div>
                    <div class="form-group">
                      <label>Motif</label>
                      <input type="text" name="motif" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card">
                <div class="card-header"><b>Actions</b></div>
                <div class="card-body">
                  <form method="POST" action="{{ route('paies.fiches.valider', $fiche) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success" {{ $fiche->statut !== 'Brouillon' ? 'disabled' : '' }}><i class="fas fa-check"></i> Valider</button>
                  </form>

                  <hr>

                  <form method="POST" action="{{ route('paies.fiches.payer', $fiche) }}">
                    @csrf
                    <div class="form-group">
                      <label>Date paiement</label>
                      <input type="date" name="date_paiement" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                      <label>Montant payé</label>
                      <input type="number" name="montant_paye" class="form-control" value="{{ (int) ($fiche->net_a_payer ?? 0) }}" required>
                    </div>
                    <div class="form-group">
                      <label>Référence (optionnel)</label>
                      <input type="text" name="reference_paiement" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" {{ !in_array($fiche->statut, ['Validé', 'Payé']) ? 'disabled' : '' }}><i class="fas fa-money-bill-wave"></i> Marquer payé</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header"><b>Ajustements</b></div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($fiche->ajustements as $aj)
                        <tr>
                          <td>{{ $aj->type }}</td>
                          <td>{{ number_format($aj->montant ?? 0, 0, ',', ' ') }}</td>
                          <td>{{ $aj->motif }}</td>
                          <td>{{ $aj->statut }}</td>
                          <td>
                            <form method="POST" action="{{ route('paies.ajustements.approuver', $aj) }}" class="d-inline">
                              @csrf
                              @method('PATCH')
                              <button type="submit" class="btn btn-sm btn-success" {{ $fiche->statut !== 'Brouillon' ? 'disabled' : '' }}>Approuver</button>
                            </form>
                            <form method="POST" action="{{ route('paies.ajustements.refuser', $aj) }}" class="d-inline">
                              @csrf
                              @method('PATCH')
                              <button type="submit" class="btn btn-sm btn-danger" {{ $fiche->statut !== 'Brouillon' ? 'disabled' : '' }}>Refuser</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center">Aucun ajustement</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
