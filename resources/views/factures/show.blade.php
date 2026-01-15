@extends('layout.main')

@section('title', 'Facture')
@section('page_title', 'Facture')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="mb-1">{{ $facture->numero }}</h5>
              <div>Client: {{ $facture->client->nom ?? 'N/A' }} {{ $facture->client->prenoms ?? '' }}</div>
              <div>Contact: {{ $facture->client->contact ?? '' }}</div>
              <div>Date: {{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : 'N/A' }}</div>
              <div>
                Periode:
                {{ $facture->date_debut ? \Carbon\Carbon::parse($facture->date_debut)->format('d/m/Y') : '' }}
                @if($facture->date_fin)
                  - {{ \Carbon\Carbon::parse($facture->date_fin)->format('d/m/Y') }}
                @endif
              </div>
            </div>
            <div>
              <a class="btn btn-success" href="{{ route('factures.print', $facture->id) }}" target="_blank"><i class="fas fa-print"></i> Imprimer</a>
              <a class="btn btn-light" href="{{ route('factures.index') }}">Retour</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <form method="POST" action="{{ route('factures.lignes.store', $facture->id) }}">
            @csrf
            <div class="row">
              <div class="col-md-2">
                <div class="form-group">
                  <label>Quantite</label>
                  <input type="number" name="quantite" min="1" value="1" class="form-control" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Designation</label>
                  <input type="text" name="designation" class="form-control" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Prix unitaire</label>
                  <input type="number" name="prix_unitaire" min="0" value="0" class="form-control" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Statut</label>
                  <input type="text" name="statut" value="Livré" class="form-control">
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter une ligne</button>
          </form>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Quantite</th>
                <th>Designation</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($facture->lignes as $ligne)
                <tr>
                  <td>{{ $ligne->quantite }}</td>
                  <td>{{ $ligne->designation }}</td>
                  <td>{{ number_format($ligne->prix_unitaire ?? 0, 0, ',', ' ') }}</td>
                  <td>{{ number_format($ligne->prix_total ?? 0, 0, ',', ' ') }}</td>
                  <td>{{ $ligne->statut }}</td>
                  <td>
                    <form action="{{ route('factures.lignes.destroy', ['facture' => $facture->id, 'ligne' => $ligne->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette ligne ?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="text-right font-weight-bold">
            Total: {{ number_format($facture->total_ttc ?? 0, 0, ',', ' ') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
