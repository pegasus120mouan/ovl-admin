@extends('layout.main')

@section('title', 'Points Clients')
@section('page_title', 'Montants clients jour par jour')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>CFA</h3>
          <p>Montant Global de {{ $moisLabel }}</p>
          <h4 class="mt-2 mb-0">{{ number_format((int) $montantGlobalMois, 0, ',', ' ') }}</h4>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>CFA</h3>
          <p>Montant clients de {{ $moisLabel }}</p>
          <h4 class="mt-2 mb-0">{{ number_format((int) $montantClientsMois, 0, ',', ' ') }}</h4>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>CFA</h3>
          <p>Gain {{ $moisLabel }}</p>
          <h4 class="mt-2 mb-0">{{ number_format((int) $gainMois, 0, ',', ' ') }}</h4>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ (int) $nbColisLivresMois }}</h3>
          <p>Nbre de colis livrés en {{ $moisLabel }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-secondary px-4 py-2 mr-2" style="border-radius: 12px;" data-toggle="modal" data-target="#modalPointsClients2Dates">Points clients en 2 Date</a>
      <a href="#" class="btn btn-primary px-4 py-2" style="border-radius: 12px;">Statistiques par mois</a>
    </div>
  </div>

  <div class="modal fade" id="modalPointsClients2Dates" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <form method="GET" action="{{ route('points-clients.print') }}" target="_blank">
            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <label class="font-weight-bold mb-0">Nom boutique</label>
              </div>
              <div class="col-md-8">
                <select class="form-control" name="client_id" required>
                  @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) $clientId === (string) $client->id)>
                      {{ $client->boutique->nom ?? trim(($client->nom ?? '') . ' ' . ($client->prenoms ?? '')) }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <label class="font-weight-bold mb-0">Date début</label>
              </div>
              <div class="col-md-8">
                <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}" required>
              </div>
            </div>

            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <label class="font-weight-bold mb-0">Date Fin</label>
              </div>
              <div class="col-md-8">
                <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}" required>
              </div>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-warning px-4" style="border-radius: 8px;">Imprimer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ route('points-clients.index') }}" class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Date début</label>
                <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Date fin</label>
                <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Client</label>
                <select class="form-control" name="client_id">
                  <option value="">Tous les clients</option>
                  @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) $clientId === (string) $client->id)>
                      {{ $client->boutique->nom ?? trim(($client->nom ?? '') . ' ' . ($client->prenoms ?? '')) }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <div class="form-group mb-0 w-100">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Résultat</h3>
          <div class="card-tools">
            <span class="badge badge-info">Total: {{ number_format($totalGlobal, 0, ',', ' ') }} CFA</span>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Client</th>
                <th>Date de Livraison</th>
                <th>Montant à Verser</th>
              </tr>
            </thead>
            <tbody>
              @php
                $rowsByDay = $rows->groupBy('jour');
              @endphp

              @forelse($rowsByDay as $jour => $items)
                <tr class="bg-light">
                  <td><strong>Total du jour ({{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }})</strong></td>
                  <td><strong>{{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}</strong></td>
                  <td><strong>{{ number_format((int) ($totauxParJour[$jour]['montant_reel'] ?? 0), 0, ',', ' ') }}</strong></td>
                </tr>

                @foreach($items as $row)
                  <tr>
                    <td>
                      {{ $row->client->boutique->nom ?? trim(($row->client->nom ?? '') . ' ' . ($row->client->prenoms ?? '')) }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}</td>
                    <td>{{ number_format((int) $row->montant_reel, 0, ',', ' ') }}</td>
                  </tr>
                @endforeach
              @empty
                <tr>
                  <td colspan="3" class="text-center">Aucune donnée trouvée</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
