@extends('layout.main')

@section('title', 'Prix')
@section('page_title', 'Prix')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $totalPrix ?? 0 }}</h3>
          <p>Total prix</p>
        </div>
        <div class="icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-9 col-6">
      <div class="card">
        <div class="card-body">
          <strong>Commune de récupération :</strong> {{ $zoneOrigine->nom_zone ?? ($commune->nom_commune ?? 'N/A') }}
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterPrix"><i class="fas fa-plus"></i> Ajouter un prix</a>
      <a href="{{ route('communes.prix.print', $commune) }}?zone_id={{ $selectedZoneId ?? $commune->commune_id }}" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> Imprimer prix</a>
      <a href="{{ route('communes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <form action="{{ route('communes.prix.index', $commune) }}" method="GET">
        <div class="input-group">
          <select class="form-control" name="zone_id" onchange="this.form.submit()">
            @foreach($zones as $z)
              <option value="{{ $z->zone_id }}" {{ (int)($selectedZoneId ?? 0) === (int)$z->zone_id ? 'selected' : '' }}>{{ $z->nom_zone }}</option>
            @endforeach
          </select>
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="submit">Filtrer</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Commune de récupération</th>
                <th>Commune de destination</th>
                <th>Coût</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($prixRows as $row)
              <tr>
                <td>{{ $zoneOrigine->nom_zone ?? ($commune->nom_commune ?? 'N/A') }}</td>
                <td>{{ $row->commune->nom_commune ?? 'N/A' }}</td>
                <td>{{ number_format($row->prix, 0, ',', ' ') }}</td>
                <td>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierPrix{{ $row->prix_id }}"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('communes.prix.destroy', [$commune, $row]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce prix ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center">Aucun prix enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $prixRows->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterPrix" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un prix</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.prix.store', $commune) }}" method="POST">
        @csrf
        <input type="hidden" name="zone_id" value="{{ $selectedZoneId ?? $commune->commune_id }}">
        <div class="modal-body">
          <div class="form-group">
            <label>Commune de destination</label>
            <select class="form-control" name="commune_id" required>
              @foreach($destinations as $d)
              <option value="{{ $d->commune_id }}">{{ $d->nom_commune }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Prix</label>
            <select class="form-control" name="cout_livraison_id" required>
              @foreach($coutLivraisons as $c)
              <option value="{{ $c->id }}">{{ number_format($c->cout_livraison, 0, ',', ' ') }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($prixRows as $row)
<div class="modal fade" id="modalModifierPrix{{ $row->prix_id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le prix</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('communes.prix.update', [$commune, $row]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Zone</label>
            <select class="form-control" name="zone_id">
              <option value="">-- Garder --</option>
              @foreach($zones as $z)
              <option value="{{ $z->zone_id }}" {{ (int)$row->zone_id === (int)$z->zone_id ? 'selected' : '' }}>{{ $z->nom_zone }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Commune de destination</label>
            <select class="form-control" name="commune_id" required>
              @foreach($destinations as $d)
              <option value="{{ $d->commune_id }}" {{ (int)$row->commune_id === (int)$d->commune_id ? 'selected' : '' }}>{{ $d->nom_commune }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Prix</label>
            @php
              $selectedCout = $coutLivraisons->firstWhere('cout_livraison', $row->prix);
              $selectedCoutId = $selectedCout->id ?? null;
            @endphp
            <select class="form-control" name="cout_livraison_id" required>
              @foreach($coutLivraisons as $c)
              <option value="{{ $c->id }}" {{ (int)$selectedCoutId === (int)$c->id ? 'selected' : '' }}>{{ number_format($c->cout_livraison, 0, ',', ' ') }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endsection
