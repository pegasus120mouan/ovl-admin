@extends('layout.main')

@section('title', 'Factures')
@section('page_title', 'Facturation')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $stats['total'] ?? 0 }}</h3>
          <p>Total Factures</p>
        </div>
        <div class="icon">
          <i class="fas fa-file-invoice"></i>
        </div>
        <a href="{{ route('factures.index') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $stats['brouillon'] ?? 0 }}</h3>
          <p>Brouillons</p>
        </div>
        <div class="icon">
          <i class="fas fa-pen"></i>
        </div>
        <a href="{{ route('factures.index') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-primary">
        <div class="inner">
          <h3>{{ $stats['valide'] ?? 0 }}</h3>
          <p>Validées</p>
        </div>
        <div class="icon">
          <i class="fas fa-check"></i>
        </div>
        <a href="{{ route('factures.index') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $stats['paye'] ?? 0 }}</h3>
          <p>Payées</p>
        </div>
        <div class="icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
        <a href="{{ route('factures.payees') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-secondary">
        <div class="inner">
          <h3>{{ number_format(($stats['montant_paye'] ?? 0), 0, ',', ' ') }}</h3>
          <p>Montant payé (FCFA)</p>
        </div>
        <div class="icon">
          <i class="fas fa-coins"></i>
        </div>
        <a href="{{ route('factures.payees') }}" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        @if(!request()->routeIs('factures.payees'))
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
        @endif
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
                    <a class="btn btn-sm btn-info" href="{{ route('factures.download', $facture->id) }}"><i class="fas fa-download"></i></a>
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
