@extends('layout.main')

@section('title', 'Gestion des commandes')
@section('page_title', 'Liste des commandes')

@section('content')
<div class="container-fluid">
        <style>
          .editable-cell {
            cursor: pointer;
            position: relative;
          }
          .editable-cell:hover {
            background-color: #fff8e1 !important;
            box-shadow: inset 0 0 0 1px #ffc107;
          }
          .editable-cell.is-editing {
            padding: 4px !important;
            background-color: #fff !important;
          }
          .editable-cell.is-saving {
            opacity: 0.6;
            pointer-events: none;
          }
          .editable-cell .form-control,
          .editable-cell .custom-select {
            min-width: 120px;
            font-size: 0.875rem;
          }
        </style>
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{ $statsMois['recus'] ?? 0 }}</h3>

                <p>Colis reçus (mois en cours)</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{ $statsMois['livrees'] ?? 0 }}</h3>

                <p>Colis livrés (mois en cours)</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="{{ route('commandes.livrees') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{ $statsMois['non_livrees'] ?? 0 }}</h3>

                <p>Colis non livrés (mois en cours)</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{ $statsMois['retours'] ?? 0 }}</h3>

                <p>Colis retour (mois en cours)</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
          <div class="row">
                          <div class="card-body">
                <div class="mb-3">
                  <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalEnregistrerCommande"><i class="fas fa-edit"></i> Enregistrer une commande</a>
                  <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modalImprimerPoint"><i class="fas fa-print"></i> Imprimer un point</a>
                  <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#modalRecherche"><i class="fas fa-search"></i> Recherche un point</a>
                  <a href="#" class="btn btn-danger"><i class="fas fa-file-export"></i> Exporter la liste des commandes</a>
                </div>
              </div>
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                

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
              <!-- /.card-header -->

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
                      <th>Date Retour</th>
                      <th>Actions</th>
                    </tr>
                  </thead>

                  <tbody>
                    @forelse($commandes as $commande)
                    <tr data-commande-id="{{ $commande->id }}" data-update-url="{{ route('commandes.update', $commande) }}">
                      <td class="editable-cell" data-field="communes" data-type="text" data-value="{{ $commande->communes }}">{{ $commande->communes }}</td>
                      <td class="editable-cell" data-field="cout_global" data-type="number" data-value="{{ $commande->cout_global }}">{{ number_format($commande->cout_global, 0, ',', ' ') }}</td>
                      <td class="editable-cell" data-field="cout_livraison" data-type="select-cout" data-value="{{ $commande->cout_livraison }}">{{ number_format($commande->cout_livraison, 0, ',', ' ') }}</td>
                      <td class="editable-cell editable-readonly" data-field="cout_reel" data-type="readonly" data-value="{{ $commande->cout_reel }}">{{ number_format($commande->cout_reel, 0, ',', ' ') }}</td>
                      <td class="editable-cell" data-field="utilisateur_id" data-type="select-client" data-value="{{ $commande->utilisateur_id }}">{{ $commande->client->boutique->nom ?? 'N/A' }}</td>
                      <td class="editable-cell" data-field="livreur_id" data-type="select-livreur" data-value="{{ $commande->livreur_id ?? '' }}">
                        @if($commande->livreur)
                          {{ $commande->livreur->nom }} {{ $commande->livreur->prenoms }}
                        @else
                          <span class="badge badge-warning">Pas de livreur attribué</span>
                        @endif
                      </td>
                      <td class="editable-cell" data-field="statut" data-type="select-statut" data-value="{{ $commande->statut }}">
                        @if($commande->statut == 'Livré')
                          <img src="{{ asset('img/icones/ok.png') }}" alt="Livré" title="Livré" style="height:30px; width:auto;">
                        @elseif($commande->statut == 'Non Livré')
                          <img src="{{ asset('img/icones/non_ok.png') }}" alt="Non Livré" title="Non Livré" style="height:30px; width:auto;">
                        @elseif($commande->statut == 'Retour')
                          <img src="{{ asset('img/icones/return.png') }}" alt="Retour" title="Retour" style="height:30px; width:auto;">
                        @else
                          <span class="badge badge-secondary">{{ $commande->statut }}</span>
                        @endif
                      </td>
                      <td class="editable-cell" data-field="date_reception" data-type="date" data-value="{{ $commande->date_reception ? $commande->date_reception->format('Y-m-d') : '' }}">{{ $commande->date_reception ? $commande->date_reception->format('d-m-Y') : 'N/A' }}</td>
                      <td class="editable-cell" data-field="date_livraison" data-type="date" data-value="{{ $commande->date_livraison ? $commande->date_livraison->format('Y-m-d') : '' }}">
                        @if($commande->date_livraison)
                          {{ $commande->date_livraison->format('d-m-Y') }}
                        @else
                          <span class="badge badge-secondary">Pas encore livré</span>
                        @endif
                      </td>
                      <td class="editable-cell" data-field="date_retour" data-type="date" data-value="{{ $commande->date_retour ? $commande->date_retour->format('Y-m-d') : '' }}">{{ $commande->date_retour ? $commande->date_retour->format('d-m-Y') : 'N/A' }}</td>
                      <td>
                        <div class="d-inline-flex align-items-center" style="gap: 6px;">
                          <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetailsCommande{{ $commande->id }}"><i class="fas fa-eye"></i></a>
                          <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalChangerDateLivraison{{ $commande->id }}" title="Changer date livraison"><i class="fas fa-calendar-check"></i></a>
                          <a href="#" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#modalChangerDateRetour{{ $commande->id }}" title="Changer date retour"><i class="fas fa-calendar-times"></i></a>
                          <a href="{{ route('commandes.edit', $commande) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                          <button type="button" class="btn btn-sm btn-danger btn-delete-commande" 
                                  data-toggle="modal" 
                                  data-target="#modalConfirmDeleteCommande"
                                  data-action="{{ route('commandes.destroy', $commande) }}"
                                  data-commande-id="{{ $commande->id }}"
                                  data-commande-commune="{{ $commande->communes }}">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="11" class="text-center">Aucune commande trouvée</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
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
                    <form action="{{ route('commandes.index') }}" method="GET" class="d-flex align-items-center">
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
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->

<!-- Modal Enregistrer Commande -->
<div class="modal fade" id="modalEnregistrerCommande" tabindex="-1" role="dialog" aria-labelledby="modalEnregistrerCommandeLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEnregistrerCommandeLabel">Enregistrer une commande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="communes">Communes</label>
            <input type="text" class="form-control" id="communes" name="communes" placeholder="Commune destination" required>
          </div>
          <div class="form-group">
            <label for="cout_global">Côut Global</label>
            <input type="number" class="form-control" id="cout_global" name="cout_global" placeholder="Coût global Colis" required>
          </div>
          <div class="form-group">
            <label for="cout_livraison">Côut Livraison</label>
            <select class="form-control" id="cout_livraison" name="cout_livraison" required>
              @foreach($coutsLivraison as $cout)
                <option value="{{ $cout->cout_livraison }}">{{ $cout->cout_livraison }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="utilisateur_id">Clients</label>
            <select class="form-control" id="utilisateur_id" name="utilisateur_id">
              <option value="">Sélectionner un client</option>
              @foreach($boutiques as $boutique)
                @foreach($boutique->utilisateurs as $utilisateur)
                  <option value="{{ $utilisateur->id }}">{{ $boutique->nom }}</option>
                @endforeach
              @endforeach
            </select>
          </div>
          <input type="hidden" name="date_reception" value="{{ date('Y-m-d') }}">
          <input type="hidden" id="cout_reel" value="0">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
 </div>

@foreach($commandes as $commande)
<!-- Modal Détails Commande -->
<div class="modal fade" id="modalDetailsCommande{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-gradient-primary">
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
            <div class="border rounded p-3 h-100" style="border-left: 4px solid #dc3545 !important;">
              <small class="text-muted d-block mb-1">Statut</small>
              <p class="mb-0">
                @if($commande->statut == 'Livré')
                  <span class="badge badge-success p-2">{{ $commande->statut }}</span>
                @elseif($commande->statut == 'Non Livré')
                  <span class="badge badge-warning p-2">{{ $commande->statut }}</span>
                @elseif($commande->statut == 'Retour')
                  <span class="badge badge-danger p-2">{{ $commande->statut }}</span>
                @else
                  <span class="badge badge-secondary p-2">{{ $commande->statut }}</span>
                @endif
              </p>
            </div>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-primary btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalModifier{{ $commande->id }}"><i class="fas fa-edit"></i> Modifier</button>
          </div>
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-warning btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalChangerStatut{{ $commande->id }}"><i class="fas fa-sync"></i> Changer Statut</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-success btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalChangerClient{{ $commande->id }}"><i class="fas fa-user"></i> Changer Client</button>
          </div>
          <div class="col-md-6 mb-2">
            <button type="button" class="btn btn-info btn-block" data-dismiss="modal" data-toggle="modal" data-target="#modalAttribuerLivreur{{ $commande->id }}"><i class="fas fa-motorcycle"></i> Attribuer Livreur</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Modifier Commande -->
<div class="modal fade" id="modalModifier{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white">Modifier la commande #{{ $commande->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Communes</label>
            <input type="text" class="form-control" name="communes" value="{{ $commande->communes }}" required>
          </div>
          <div class="form-group">
            <label>Coût Global</label>
            <input type="number" class="form-control" id="cout_global_edit_{{ $commande->id }}" name="cout_global" value="{{ $commande->cout_global }}" required>
          </div>
          <div class="form-group">
            <label>Coût Livraison</label>
            <select class="form-control" id="cout_livraison_edit_{{ $commande->id }}" name="cout_livraison" required>
              @foreach($coutsLivraison as $cout)
                <option value="{{ $cout->cout_livraison }}" {{ $commande->cout_livraison == $cout->cout_livraison ? 'selected' : '' }}>{{ $cout->cout_livraison }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Coût Réel</label>
            <input type="number" class="form-control" id="cout_reel_edit_view_{{ $commande->id }}" value="{{ $commande->cout_reel }}" readonly disabled>
          </div>
          <div class="form-group">
            <label>Date Réception</label>
            <input type="date" class="form-control" name="date_reception" value="{{ $commande->date_reception ? $commande->date_reception->format('Y-m-d') : '' }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Changer Date Livraison -->
<div class="modal fade" id="modalChangerDateLivraison{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white">Changer date de livraison #{{ $commande->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
        <div class="modal-body">
          <div class="form-group">
            <label>Date Livraison</label>
            <input type="date" class="form-control" name="date_livraison" value="{{ $commande->date_livraison ? $commande->date_livraison->format('Y-m-d') : '' }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Changer Date Retour -->
<div class="modal fade" id="modalChangerDateRetour{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-secondary">
        <h5 class="modal-title text-white">Changer date retour #{{ $commande->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
        <div class="modal-body">
          <div class="form-group">
            <label>Date Retour</label>
            <input type="date" class="form-control" name="date_retour" value="{{ $commande->date_retour ? $commande->date_retour->format('Y-m-d') : '' }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Attribuer Livreur -->
<div class="modal fade" id="modalAttribuerLivreur{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title text-white">Attribuer un livreur</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.update', $commande) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Sélectionner un livreur</label>
            <select class="form-control" name="livreur_id" required>
              <option value="">Choisir un livreur</option>
              @foreach($livreurs as $livreur)
                <option value="{{ $livreur->id }}" {{ $commande->livreur_id == $livreur->id ? 'selected' : '' }}>{{ $livreur->nom }} {{ $livreur->prenoms }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Attribuer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
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

<!-- Modal Recherche Avancée -->
<div class="modal fade" id="modalRecherche" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-search"></i> Recherche avancée</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.index') }}" method="GET">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Communes</label>
                <input type="text" class="form-control" name="communes" value="{{ request('communes') }}" placeholder="Rechercher par commune">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Statut</label>
                <select class="form-control" name="statut">
                  <option value="">Tous les statuts</option>
                  <option value="Non Livré" {{ request('statut') == 'Non Livré' ? 'selected' : '' }}>Non Livré</option>
                  <option value="Livré" {{ request('statut') == 'Livré' ? 'selected' : '' }}>Livré</option>
                  <option value="Retour" {{ request('statut') == 'Retour' ? 'selected' : '' }}>Retour</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Client (Boutique)</label>
                <select class="form-control" name="boutique_id">
                  <option value="">Tous les clients</option>
                  @foreach($boutiques as $boutique)
                    <option value="{{ $boutique->id }}" {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>{{ $boutique->nom }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Livreur</label>
                <select class="form-control" name="livreur_id">
                  <option value="">Tous les livreurs</option>
                  @foreach($livreurs as $livreur)
                    <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>{{ $livreur->nom }} {{ $livreur->prenoms }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Date réception</label>
                <input type="date" class="form-control" name="date_reception" value="{{ request('date_reception') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Date livraison</label>
                <input type="date" class="form-control" name="date_livraison" value="{{ request('date_livraison') }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Date retour</label>
                <input type="date" class="form-control" name="date_retour" value="{{ request('date_retour') }}">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="{{ route('commandes.index') }}" class="btn btn-secondary">Réinitialiser</a>
          <button type="submit" class="btn btn-warning"><i class="fas fa-search"></i> Rechercher</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Imprimer Point -->
<div class="modal fade" id="modalImprimerPoint" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Imprimer un point</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.print') }}" method="GET" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Client</label>
            <select class="form-control" name="boutique_id" required>
              @foreach($boutiques as $boutique)
                <option value="{{ $boutique->id }}">{{ $boutique->nom }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Sélectionner la date</label>
            <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
          </div>
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-primary">Imprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const coutGlobal = document.getElementById('cout_global');
  const coutLivraison = document.getElementById('cout_livraison');
  const coutReel = document.getElementById('cout_reel');

  function calculerCoutReel() {
    const global = parseInt(coutGlobal.value) || 0;
    const livraison = parseInt(coutLivraison.value) || 0;
    coutReel.value = global - livraison;
  }

  coutGlobal.addEventListener('input', calculerCoutReel);
  coutLivraison.addEventListener('change', calculerCoutReel);

  function calculerCoutReelModifier(id) {
    var g = document.getElementById('cout_global_edit_' + id);
    var l = document.getElementById('cout_livraison_edit_' + id);
    var r = document.getElementById('cout_reel_edit_view_' + id);

    if (!g || !l || !r) return;

    var global = parseInt(g.value || '0', 10);
    var livraison = parseInt(l.value || '0', 10);
    r.value = String(global - livraison);
  }

  if (window.jQuery) {
    $(document).on('shown.bs.modal', '[id^="modalModifier"]', function () {
      var id = this && typeof this.id === 'string' ? this.id : '';
      var m = id.match(/modalModifier(\d+)/);
      if (m && m[1]) {
        calculerCoutReelModifier(m[1]);
      }
    });
  }

  document.addEventListener('input', function (e) {
    if (!e.target || !e.target.id) return;
    var m1 = e.target.id.match(/^cout_global_edit_(\d+)$/);
    if (m1 && m1[1]) {
      calculerCoutReelModifier(m1[1]);
    }
  });

  document.addEventListener('change', function (e) {
    if (!e.target || !e.target.id) return;
    var m1 = e.target.id.match(/^cout_livraison_edit_(\d+)$/);
    if (m1 && m1[1]) {
      calculerCoutReelModifier(m1[1]);
    }
  });
});
</script>

<!-- Modal Confirmation Suppression Commande -->
<div class="modal fade" id="modalConfirmDeleteCommande" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmation de suppression
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <span class="fa-stack fa-2x">
            <i class="fas fa-circle fa-stack-2x text-danger"></i>
            <i class="fas fa-trash fa-stack-1x fa-inverse"></i>
          </span>
        </div>
        <h5 class="mb-2">Êtes-vous sûr de vouloir supprimer cette commande ?</h5>
        <p class="text-muted mb-0">
          Commande <strong id="deleteCommandeId"></strong> - <span id="deleteCommandeCommune"></span>
        </p>
        <div class="alert alert-warning mt-3 mb-0 text-left">
          <i class="fas fa-info-circle mr-1"></i>
          <small>Cette action est irréversible. Toutes les données associées seront définitivement supprimées.</small>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Annuler
        </button>
        <form id="deleteCommandeForm" method="POST" action="#" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger px-4">
            <i class="fas fa-trash mr-1"></i>Supprimer
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
window.commandeInlineOptions = {
  coutsLivraison: @json($coutsLivraison->pluck('cout_livraison')->values()),
  clients: @json($boutiques->flatMap(function ($boutique) {
    return $boutique->utilisateurs->map(function ($utilisateur) use ($boutique) {
      return ['value' => $utilisateur->id, 'label' => $boutique->nom];
    });
  })->values()),
  livreurs: @json($livreurs->map(function ($livreur) {
    return ['value' => $livreur->id, 'label' => trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? ''))];
  })->values()),
  statuts: ['Non Livré', 'Livré', 'Retour'],
  icones: {
    'Livré': @json(asset('img/icones/ok.png')),
    'Non Livré': @json(asset('img/icones/non_ok.png')),
    'Retour': @json(asset('img/icones/return.png'))
  }
};

(function () {
  var activeCell = null;

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('fr-FR');
  }

  function formatDateDisplay(iso) {
    if (!iso) return 'N/A';
    var parts = iso.split('-');
    if (parts.length !== 3) return iso;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
  }

  function renderStatut(statut) {
    var icon = window.commandeInlineOptions.icones[statut];
    if (icon) {
      return '<img src="' + icon + '" alt="' + statut + '" title="' + statut + '" style="height:30px; width:auto;">';
    }
    return '<span class="badge badge-secondary">' + statut + '</span>';
  }

  function renderLivreur(nom) {
    if (nom) {
      return document.createTextNode(nom).textContent;
    }
    return '<span class="badge badge-warning">Pas de livreur attribué</span>';
  }

  function renderDateLivraison(iso) {
    if (!iso) {
      return '<span class="badge badge-secondary">Pas encore livré</span>';
    }
    return formatDateDisplay(iso);
  }

  function updateRowDisplay(row, commande) {
    row.querySelector('[data-field="communes"]').innerHTML = commande.communes || '';
    row.querySelector('[data-field="communes"]').dataset.value = commande.communes || '';

    row.querySelector('[data-field="cout_global"]').innerHTML = formatNumber(commande.cout_global);
    row.querySelector('[data-field="cout_global"]').dataset.value = commande.cout_global;

    row.querySelector('[data-field="cout_livraison"]').innerHTML = formatNumber(commande.cout_livraison);
    row.querySelector('[data-field="cout_livraison"]').dataset.value = commande.cout_livraison;

    row.querySelector('[data-field="cout_reel"]').innerHTML = formatNumber(commande.cout_reel);
    row.querySelector('[data-field="cout_reel"]').dataset.value = commande.cout_reel;

    row.querySelector('[data-field="utilisateur_id"]').innerHTML = commande.boutique_nom || 'N/A';
    row.querySelector('[data-field="utilisateur_id"]').dataset.value = commande.utilisateur_id || '';

    var livreurCell = row.querySelector('[data-field="livreur_id"]');
    livreurCell.innerHTML = renderLivreur(commande.livreur_nom);
    livreurCell.dataset.value = commande.livreur_id || '';

    var statutCell = row.querySelector('[data-field="statut"]');
    statutCell.innerHTML = renderStatut(commande.statut);
    statutCell.dataset.value = commande.statut;

    var dateReceptionCell = row.querySelector('[data-field="date_reception"]');
    dateReceptionCell.innerHTML = formatDateDisplay(commande.date_reception);
    dateReceptionCell.dataset.value = commande.date_reception || '';

    var dateLivraisonCell = row.querySelector('[data-field="date_livraison"]');
    dateLivraisonCell.innerHTML = renderDateLivraison(commande.date_livraison);
    dateLivraisonCell.dataset.value = commande.date_livraison || '';

    var dateRetourCell = row.querySelector('[data-field="date_retour"]');
    dateRetourCell.innerHTML = formatDateDisplay(commande.date_retour);
    dateRetourCell.dataset.value = commande.date_retour || '';
  }

  function buildSelect(options, currentValue, emptyLabel) {
    var select = document.createElement('select');
    select.className = 'form-control form-control-sm';

    if (emptyLabel !== undefined) {
      var emptyOption = document.createElement('option');
      emptyOption.value = '';
      emptyOption.textContent = emptyLabel;
      select.appendChild(emptyOption);
    }

    options.forEach(function (option) {
      var opt = document.createElement('option');
      opt.value = String(option.value ?? option);
      opt.textContent = option.label ?? String(option);
      if (String(currentValue) === String(opt.value)) {
        opt.selected = true;
      }
      select.appendChild(opt);
    });

    return select;
  }

  function buildEditor(cell) {
    var type = cell.dataset.type;
    var value = cell.dataset.value || '';

    if (type === 'text') {
      var textInput = document.createElement('input');
      textInput.type = 'text';
      textInput.className = 'form-control form-control-sm';
      textInput.value = value;
      return textInput;
    }

    if (type === 'number') {
      var numberInput = document.createElement('input');
      numberInput.type = 'number';
      numberInput.className = 'form-control form-control-sm';
      numberInput.value = value;
      return numberInput;
    }

    if (type === 'date') {
      var dateInput = document.createElement('input');
      dateInput.type = 'date';
      dateInput.className = 'form-control form-control-sm';
      dateInput.value = value;
      return dateInput;
    }

    if (type === 'select-cout') {
      return buildSelect(
        window.commandeInlineOptions.coutsLivraison.map(function (cout) {
          return { value: cout, label: cout };
        }),
        value
      );
    }

    if (type === 'select-client') {
      return buildSelect(window.commandeInlineOptions.clients, value);
    }

    if (type === 'select-livreur') {
      return buildSelect(window.commandeInlineOptions.livreurs, value, 'Pas de livreur attribué');
    }

    if (type === 'select-statut') {
      return buildSelect(
        window.commandeInlineOptions.statuts.map(function (statut) {
          return { value: statut, label: statut };
        }),
        value
      );
    }

    return null;
  }

  function cancelEdit(cell) {
    if (!cell || !cell.dataset.originalHtml) return;
    cell.innerHTML = cell.dataset.originalHtml;
    cell.classList.remove('is-editing');
    delete cell.dataset.originalHtml;
    activeCell = null;
  }

  function saveEdit(cell) {
    var row = cell.closest('tr');
    var editor = cell.querySelector('input, select');
    if (!editor || !row) return;

    var field = cell.dataset.field;
    var newValue = editor.value;
    var oldValue = cell.dataset.value || '';

    if (String(newValue) === String(oldValue)) {
      cancelEdit(cell);
      return;
    }

    var payload = {};
    payload[field] = newValue === '' ? null : newValue;

    if (field === 'cout_global' || field === 'cout_livraison') {
      payload.cout_global = field === 'cout_global'
        ? newValue
        : row.querySelector('[data-field="cout_global"]').dataset.value;
      payload.cout_livraison = field === 'cout_livraison'
        ? newValue
        : row.querySelector('[data-field="cout_livraison"]').dataset.value;
    }

    cell.classList.add('is-saving');

    fetch(row.dataset.updateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(Object.assign({ _method: 'PUT' }, payload))
    })
    .then(function (response) {
      return response.json().then(function (data) {
        if (!response.ok) {
          var message = data.message;
          if (data.errors) {
            message = Object.values(data.errors).flat().join('\n');
          }
          throw new Error(message || 'Erreur lors de la mise à jour');
        }
        return data;
      });
    })
    .then(function (data) {
      updateRowDisplay(row, data.commande);
      cell.classList.remove('is-editing', 'is-saving');
      delete cell.dataset.originalHtml;
      activeCell = null;
    })
    .catch(function (error) {
      alert(error.message || 'Impossible de mettre à jour la commande.');
      cancelEdit(cell);
      cell.classList.remove('is-saving');
    });
  }

  function startEdit(cell) {
    if (cell.classList.contains('editable-readonly')) return;
    if (activeCell && activeCell !== cell) {
      cancelEdit(activeCell);
    }

    activeCell = cell;
    cell.dataset.originalHtml = cell.innerHTML;
    cell.classList.add('is-editing');
    cell.innerHTML = '';

    var editor = buildEditor(cell);
    if (!editor) {
      cancelEdit(cell);
      return;
    }

    cell.appendChild(editor);
    editor.focus();

    if (editor.tagName === 'SELECT') {
      editor.addEventListener('change', function () {
        saveEdit(cell);
      });
      editor.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          cancelEdit(cell);
        }
      });
      return;
    }

    editor.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        saveEdit(cell);
      }
      if (event.key === 'Escape') {
        cancelEdit(cell);
      }
    });

    editor.addEventListener('blur', function () {
      setTimeout(function () {
        if (activeCell === cell) {
          saveEdit(cell);
        }
      }, 120);
    });
  }

  document.addEventListener('click', function (event) {
    var cell = event.target.closest('.editable-cell');
    if (!cell || cell.classList.contains('editable-readonly')) return;
    if (event.target.closest('a, button')) return;
    if (cell.classList.contains('is-editing')) return;
    startEdit(cell);
  });
})();

$(function() {
  $('#modalConfirmDeleteCommande').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var action = button.attr('data-action');
    var commandeId = button.attr('data-commande-id');
    var commune = button.attr('data-commande-commune');
    
    $('#deleteCommandeForm').attr('action', action);
    $('#deleteCommandeId').text('#' + commandeId);
    $('#deleteCommandeCommune').text(commune || 'N/A');
  });
});
</script>
@endpush