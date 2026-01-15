@extends('layout.main')

@section('title', 'Factures')
@section('page_title', 'Facturation')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('factures.store') }}">
            @csrf
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Client</label>
                  <select class="form-control" name="client_id" required>
                    <option value="">Selectionner</option>
                    @foreach($clients as $client)
                      <option value="{{ $client->id }}">{{ $client->nom ?? '' }} {{ $client->prenoms ?? '' }} ({{ $client->contact ?? '' }})</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Mode</label>
                  <select class="form-control" name="mode">
                    <option value="auto">Automatique</option>
                    <option value="manuel">Manuel</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Date debut</label>
                  <input type="date" name="date_debut" class="form-control">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Date fin</label>
                  <input type="date" name="date_fin" class="form-control">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Date facture</label>
                  <input type="date" name="date_facture" value="{{ date('Y-m-d') }}" class="form-control">
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Generer la facture</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Numero</th>
                <th>Client</th>
                <th>Date</th>
                <th>Periode</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($factures as $facture)
                <tr>
                  <td>{{ $facture->numero }}</td>
                  <td>{{ $facture->client->nom ?? 'N/A' }} {{ $facture->client->prenoms ?? '' }}</td>
                  <td>{{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : 'N/A' }}</td>
                  <td>
                    {{ $facture->date_debut ? \Carbon\Carbon::parse($facture->date_debut)->format('d/m/Y') : '' }}
                    @if($facture->date_fin)
                      - {{ \Carbon\Carbon::parse($facture->date_fin)->format('d/m/Y') }}
                    @endif
                  </td>
                  <td>{{ number_format($facture->total_ttc ?? 0, 0, ',', ' ') }}</td>
                  <td>{{ $facture->statut }}</td>
                  <td>
                    <a class="btn btn-sm btn-primary" href="{{ route('factures.show', $facture->id) }}"><i class="fas fa-eye"></i></a>
                    <a class="btn btn-sm btn-success" href="{{ route('factures.print', $facture->id) }}" target="_blank"><i class="fas fa-print"></i></a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Aucune facture</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $factures->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
