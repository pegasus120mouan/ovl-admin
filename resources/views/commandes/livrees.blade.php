@extends('layout.main')

@section('title', 'Commandes Livrées')

@section('content')
<div class="container-fluid">
        <!-- Main row -->
          <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-success">
                <h3 class="card-title text-white"><i class="fas fa-check-circle"></i> Commandes Livrées</h3>
                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card-body table-responsive p-0">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th style="width: 350px;">Communes</th>
                      <th>Coût Global</th>
                      <th>Livraison</th>
                      <th>Côut réel</th>
                      <th>Boutique</th>
                      <th>Livreur</th>
                      <th>Statut</th>
                      <th>Date réception</th>
                      <th>Date livraison</th>
                      <th>Actions</th>
                    </tr>
                  </thead>

                  <tbody>
                    @forelse($commandes as $commande)
                    <tr>
                      <td>{{ $commande->communes }}</td>
                      <td>{{ number_format($commande->cout_global, 0, ',', ' ') }} </td>
                      <td>{{ number_format($commande->cout_livraison, 0, ',', ' ') }} </td>
                      <td>{{ number_format($commande->cout_reel, 0, ',', ' ') }}</td>
                      <td>{{ $commande->client->boutique->nom ?? 'N/A' }}</td>
                      <td>
                        @if($commande->livreur)
                          {{ $commande->livreur->nom }} {{ $commande->livreur->prenoms }}
                        @else
                          <span class="badge badge-warning">Pas de livreur attribué</span>
                        @endif
                      </td>
                      <td><span class="badge badge-success">{{ $commande->statut }}</span></td>
                      <td>{{ $commande->date_reception ? $commande->date_reception->format('d-m-Y') : 'N/A' }}</td>
                      <td>{{ $commande->date_livraison ? $commande->date_livraison->format('d-m-Y') : 'N/A' }}</td>
                      <td>
                        <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetailsCommande{{ $commande->id }}"><i class="fas fa-eye"></i></a>
                        <form action="{{ route('commandes.destroy', $commande) }}" method="POST" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="10" class="text-center">Aucune commande livrée trouvée</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="card-footer bg-secondary">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-white small">
                    Affichage de {{ $commandes->firstItem() ?? 0 }} à {{ $commandes->lastItem() ?? 0 }} sur {{ $commandes->total() }} entrées
                  </div>
                  <div class="d-flex align-items-center">
                    @if ($commandes->onFirstPage())
                      <button class="btn btn-primary btn-sm mr-1" disabled><i class="fas fa-chevron-left"></i></button>
                    @else
                      <a href="{{ $commandes->previousPageUrl() }}" class="btn btn-primary btn-sm mr-1"><i class="fas fa-chevron-left"></i></a>
                    @endif
                    <span class="text-white mx-2">{{ $commandes->currentPage() }}/{{ $commandes->lastPage() }}</span>
                    @if ($commandes->hasMorePages())
                      <a href="{{ $commandes->nextPageUrl() }}" class="btn btn-primary btn-sm mr-3"><i class="fas fa-chevron-right"></i></a>
                    @else
                      <button class="btn btn-primary btn-sm mr-3" disabled><i class="fas fa-chevron-right"></i></button>
                    @endif
                    <form action="{{ route('commandes.livrees') }}" method="GET" class="d-flex align-items-center">
                      <span class="text-white mr-2">Afficher :</span>
                      <select name="per_page" class="form-control form-control-sm" style="width: 70px;">
                        <option value="15" {{ request('per_page', 20) == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                      </select>
                      <button type="submit" class="btn btn-primary btn-sm ml-2">Valider</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

@foreach($commandes as $commande)
<!-- Modal Détails Commande -->
<div class="modal fade" id="modalDetailsCommande{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-gradient-success">
        <h5 class="modal-title text-white font-weight-bold">Détails de la commande #{{ $commande->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #007bff !important;">
              <small class="text-muted d-block mb-1">Commune</small>
              <p class="font-weight-bold mb-0 text-dark">{{ $commande->communes }}</p>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #28a745 !important;">
              <small class="text-muted d-block mb-1">Boutique</small>
              <p class="font-weight-bold mb-0"><span class="badge badge-success p-2">{{ $commande->client->boutique->nom ?? 'N/A' }}</span></p>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #ffc107 !important;">
              <small class="text-muted d-block mb-1">Coût Global</small>
              <p class="font-weight-bold mb-0 text-primary">{{ number_format($commande->cout_global, 0, ',', ' ') }} FCFA</p>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #17a2b8 !important;">
              <small class="text-muted d-block mb-1">Coût Livraison</small>
              <p class="font-weight-bold mb-0 text-info">{{ number_format($commande->cout_livraison, 0, ',', ' ') }} FCFA</p>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #6c757d !important;">
              <small class="text-muted d-block mb-1">Coût Réel</small>
              <p class="font-weight-bold mb-0 text-dark">{{ number_format($commande->cout_reel, 0, ',', ' ') }} FCFA</p>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #28a745 !important;">
              <small class="text-muted d-block mb-1">Statut</small>
              <p class="mb-0"><span class="badge badge-success p-2">{{ $commande->statut }}</span></p>
            </div>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-warning btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalChangerStatut{{ $commande->id }}"><i class="fas fa-sync"></i> Changer Statut</button>
          </div>
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-success btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalChangerClient{{ $commande->id }}"><i class="fas fa-user"></i> Changer Client</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Changer Statut -->
<div class="modal fade" id="modalChangerStatut{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Changer le statut</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nouveau statut</label>
            <select class="form-control" name="statut" required>
              <option value="Non Livré" {{ $commande->statut == 'Non Livré' ? 'selected' : '' }}>Non Livré</option>
              <option value="Livré" {{ $commande->statut == 'Livré' ? 'selected' : '' }}>Livré</option>
              <option value="Retour" {{ $commande->statut == 'Retour' ? 'selected' : '' }}>Retour</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Changer Client -->
<div class="modal fade" id="modalChangerClient{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title text-white">Changer le client</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nouveau client</label>
            <select class="form-control" name="utilisateur_id" required>
              @foreach($boutiques as $boutique)
                @foreach($boutique->utilisateurs as $utilisateur)
                  <option value="{{ $utilisateur->id }}" {{ $commande->utilisateur_id == $utilisateur->id ? 'selected' : '' }}>{{ $boutique->nom }}</option>
                @endforeach
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@endsection
