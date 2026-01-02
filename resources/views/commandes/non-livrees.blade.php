@extends('layout.main')

@section('title', 'Commandes Non Livrées')
@section('page_title', 'Commandes Non Livrées')

@section('content')
<div class="container-fluid">
        <!-- Main row -->
          <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-clock"></i> Commandes Non Livrées</h3>
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
                      <th>Date Retour</th>
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
                      <td><span class="badge badge-danger">{{ $commande->statut }}</span></td>
                      <td>{{ $commande->date_reception ? $commande->date_reception->format('d-m-Y') : 'N/A' }}</td>
                      <td>
                        @if($commande->date_retour)
                          {{ $commande->date_retour->format('d-m-Y') }}
                        @else
                          <span class="badge badge-secondary">Colis pas encore retourné</span>
                        @endif
                      </td>
                      <td>
                        @if($commande->date_retour)
                          <button class="btn btn-sm btn-secondary" disabled><i class="fas fa-undo"></i> Retourné le colis</button>
                        @else
                          <form action="{{ route('commandes.update', $commande) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="statut" value="Retour">
                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Confirmer le retour de ce colis?')"><i class="fas fa-undo"></i> Retourné le colis</button>
                          </form>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="10" class="text-center">Aucune commande non livrée trouvée</td>
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
                    <form action="{{ route('commandes.non-livrees') }}" method="GET" class="d-flex align-items-center">
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

@endsection
