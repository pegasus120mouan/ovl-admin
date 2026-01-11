@extends('layout.main')

@section('title', 'Livreurs')
@section('page_title', 'Commandes - ' . $livreur->nom . ' ' . $livreur->prenoms)

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ number_format($montantGlobal ?? 0, 0, ',', ' ') }} <sup style="font-size: 20px">CFA</sup></h3>
          <p>Montant Global de {{ $monthLabel ?? '' }}</p>
        </div>
        <div class="icon">
          <i class="fas fa-coins"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ number_format($montantClients ?? 0, 0, ',', ' ') }} <sup style="font-size: 20px">CFA</sup></h3>
          <p>Montant clients de {{ $monthLabel ?? '' }}</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-friends"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format($gain ?? 0, 0, ',', ' ') }} <sup style="font-size: 20px">CFA</sup></h3>
          <p>Gain {{ $monthLabel ?? '' }}</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-line"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $nbreColisLivres ?? 0 }}</h3>
          <p>Nbre de colis livrés en {{ $monthLabel ?? '' }}</p>
        </div>
        <div class="icon">
          <i class="fas fa-box"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalEnregistrerCommande"><i class="fas fa-plus"></i> Enregistrer une commande</button>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalImprimerPoint"><i class="fas fa-print"></i> Imprimer un point</button>
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalDepotEntreDates"><i class="fas fa-sync"></i> Dépôt à effectuer entre date</button>
        <a href="{{ route('bilans.hier') }}" class="btn btn-warning"><i class="fas fa-calendar-day"></i> Point d'hier</a>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-box"></i>
            Commandes du livreur
            <strong>{{ $livreur->login }}</strong>
          </h3>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th style="width: 220px;">Communes</th>
                <th>Coût Global</th>
                <th>Livraison</th>
                <th>Côut réel</th>
                <th>Boutique</th>
                <th>livreur</th>
                <th>Statut</th>
                <th>Date réception</th>
                <th>Date livraison</th>
                <th>Date Retour</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              @forelse($commandes as $commande)
                <tr>
                  <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $commande->communes }}">{{ $commande->communes }}</td>
                  <td>{{ number_format($commande->cout_global, 0, ',', ' ') }}</td>
                  <td>{{ number_format($commande->cout_livraison, 0, ',', ' ') }}</td>
                  <td>{{ number_format($commande->cout_reel, 0, ',', ' ') }}</td>
                  <td>{{ $commande->client->boutique->nom ?? 'N/A' }}</td>
                  <td>{{ $commande->livreur ? ($commande->livreur->nom . ' ' . $commande->livreur->prenoms) : 'N/A' }}</td>
                  <td>
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
                  <td>{{ $commande->date_reception ? $commande->date_reception->format('d-m-Y') : 'N/A' }}</td>
                  <td>
                    @if($commande->date_livraison)
                      {{ $commande->date_livraison->format('d-m-Y') }}
                    @else
                      <span class="badge badge-secondary">Pas encore livré</span>
                    @endif
                  </td>
                  <td>{{ $commande->date_retour ? $commande->date_retour->format('d-m-Y') : 'N/A' }}</td>
                  <td>
                    <div class="d-inline-flex align-items-center" style="gap: 6px;">
                      <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierCommande{{ $commande->id }}" title="Modifier">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalChangerDateLivraison{{ $commande->id }}" title="Changer date livraison">
                        <i class="fas fa-calendar-check"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#modalChangerDateRetour{{ $commande->id }}" title="Changer date retour">
                        <i class="fas fa-calendar-times"></i>
                      </button>
                      <form action="{{ route('commandes.destroy', $commande) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')"><i class="fas fa-trash"></i></button>
                      </form>
                    </div>
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalAttribuerLivreur{{ $commande->id }}" title="Changer le livreur">
                      <i class="fas fa-motorcycle"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-info" style="background-color:#6dc6d6; border-color:#6dc6d6;" data-toggle="modal" data-target="#modalChangerStatut{{ $commande->id }}" title="Changer le statut">
                      <i class="fas fa-sync"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="13" class="text-center">Aucune commande trouvée pour ce livreur</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @foreach($commandes as $commande)
          <!-- Modal Modifier Commande -->
          <div class="modal fade" id="modalModifierCommande{{ $commande->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Modifier Commande #{{ $commande->id }}</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>

                <form action="{{ route('commandes.update', $commande) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Client <span class="text-danger">*</span></label>
                          <select name="utilisateur_id" class="form-control" required>
                            <option value="">-- Sélectionner un client --</option>
                            @foreach($boutiques as $boutique)
                              @foreach($boutique->utilisateurs as $utilisateur)
                                <option value="{{ $utilisateur->id }}" {{ (int) $commande->utilisateur_id === (int) $utilisateur->id ? 'selected' : '' }}>
                                  {{ $boutique->nom }}
                                </option>
                              @endforeach
                            @endforeach
                          </select>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Livreur <span class="text-danger">*</span></label>
                          <select name="livreur_id" class="form-control" required>
                            <option value="">-- Sélectionner un livreur --</option>
                            @foreach($livreurs as $livreurOption)
                              <option value="{{ $livreurOption->id }}" {{ (int) $commande->livreur_id === (int) $livreurOption->id ? 'selected' : '' }}>
                                {{ $livreurOption->nom }} {{ $livreurOption->prenoms }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Commune / Destination <span class="text-danger">*</span></label>
                          <input type="text" name="communes" class="form-control" value="{{ $commande->communes }}" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Statut <span class="text-danger">*</span></label>
                          <select name="statut" class="form-control" required>
                            <option value="Non Livré" {{ $commande->statut == 'Non Livré' ? 'selected' : '' }}>Non Livré</option>
                            <option value="Livré" {{ $commande->statut == 'Livré' ? 'selected' : '' }}>Livré</option>
                            <option value="Retour" {{ $commande->statut == 'Retour' ? 'selected' : '' }}>Retour</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Coût Global (FCFA) <span class="text-danger">*</span></label>
                          <input type="number" name="cout_global" id="cout_global_edit_{{ $commande->id }}" class="form-control" value="{{ $commande->cout_global }}" min="0" required>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Coût Livraison (FCFA) <span class="text-danger">*</span></label>
                          <input type="number" name="cout_livraison" id="cout_livraison_edit_{{ $commande->id }}" class="form-control" value="{{ $commande->cout_livraison }}" min="0" required>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Coût Réel (FCFA)</label>
                          <input type="number" id="cout_reel_edit_view_{{ $commande->id }}" class="form-control" value="{{ $commande->cout_reel }}" readonly disabled>
                          <input type="hidden" name="cout_reel" id="cout_reel_edit_{{ $commande->id }}" value="{{ $commande->cout_reel }}">
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Date de réception <span class="text-danger">*</span></label>
                          <input type="date" name="date_reception" class="form-control" value="{{ $commande->date_reception ? $commande->date_reception->format('Y-m-d') : '' }}" required>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Mettre à jour</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
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
                  <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                  <div class="modal-body">
                    <div class="form-group">
                      <label>Date de livraison</label>
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
                  <h5 class="modal-title text-white">Changer date de retour #{{ $commande->id }}</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form action="{{ route('commandes.update', $commande) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                  <div class="modal-body">
                    <div class="form-group">
                      <label>Date de retour</label>
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
                        @foreach($livreurs as $livreurOption)
                          <option value="{{ $livreurOption->id }}" {{ $commande->livreur_id == $livreurOption->id ? 'selected' : '' }}>
                            {{ $livreurOption->nom }} {{ $livreurOption->prenoms }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
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
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                    <button type="submit" class="btn btn-warning">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endforeach

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
              <form action="{{ route('users.livreurs.commandes', $livreur) }}" method="GET" class="d-flex align-items-center">
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
          <input type="hidden" name="livreur_id" value="{{ $livreur->id }}">
          <input type="hidden" name="date_reception" value="{{ date('Y-m-d') }}">
          <input type="hidden" name="cout_reel" id="cout_reel" value="">
          <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Imprimer Point -->
<div class="modal fade" id="modalImprimerPoint" tabindex="-1" role="dialog" aria-labelledby="modalImprimerPointLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalImprimerPointLabel">Imprimer un point {{ now()->format('d-m-Y') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('commandes.print') }}" method="GET" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label for="print_livreur_id">Livreur</label>
            <input type="text" class="form-control" id="print_livreur_id" value="{{ $livreur->nom }} {{ $livreur->prenoms }}" readonly>
            <input type="hidden" name="livreur_id" value="{{ $livreur->id }}">
          </div>
          <div class="form-group">
            <label for="print_date">Date</label>
            <input type="date" class="form-control" id="print_date" name="date" value="{{ now()->format('Y-m-d') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Imprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Depot entre 2 dates -->
<div class="modal fade" id="modalDepotEntreDates" tabindex="-1" role="dialog" aria-labelledby="modalDepotEntreDatesLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDepotEntreDatesLabel">Voir un point</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('points-livreurs.print-depot') }}" method="GET" target="_blank">
        <div class="modal-body">
          <input type="hidden" name="utilisateur_id" value="{{ $livreur->id }}">
          <div class="form-group">
            <label class="font-weight-bold" for="depot_date_debut">Date Début</label>
            <input type="date" class="form-control" id="depot_date_debut" name="date_debut" value="{{ now()->format('Y-m-d') }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold" for="depot_date_fin">Date Fin</label>
            <input type="date" class="form-control" id="depot_date_fin" name="date_fin" value="{{ now()->format('Y-m-d') }}" required>
          </div>
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-danger">Imprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    function computeCoutReelEdit(id) {
      var coutGlobalEl = document.getElementById('cout_global_edit_' + id);
      var coutLivraisonEl = document.getElementById('cout_livraison_edit_' + id);
      var coutReelHiddenEl = document.getElementById('cout_reel_edit_' + id);
      var coutReelViewEl = document.getElementById('cout_reel_edit_view_' + id);

      if (!coutGlobalEl || !coutLivraisonEl || !coutReelHiddenEl || !coutReelViewEl) return;

      var coutGlobal = parseInt(coutGlobalEl.value || '0', 10);
      var coutLivraison = parseInt(coutLivraisonEl.value || '0', 10);
      var coutReel = Math.max(0, coutGlobal - coutLivraison);

      coutReelHiddenEl.value = String(coutReel);
      coutReelViewEl.value = String(coutReel);
    }

    function computeCoutReel() {
      var coutGlobalEl = document.getElementById('cout_global');
      var coutLivraisonEl = document.getElementById('cout_livraison');
      var coutReelEl = document.getElementById('cout_reel');

      if (!coutGlobalEl || !coutLivraisonEl || !coutReelEl) return;

      var coutGlobal = parseInt(coutGlobalEl.value || '0', 10);
      var coutLivraison = parseInt(coutLivraisonEl.value || '0', 10);
      var coutReel = coutGlobal - coutLivraison;
      coutReelEl.value = String(Math.max(0, coutReel));
    }

    document.addEventListener('input', function (e) {
      if (e.target && (e.target.id === 'cout_global' || e.target.id === 'cout_livraison')) {
        computeCoutReel();
      }
    });

    document.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'cout_livraison') {
        computeCoutReel();
      }
    });

    document.addEventListener('shown.bs.modal', function (e) {
      if (e.target && e.target.id === 'modalEnregistrerCommande') {
        computeCoutReel();
      }

      var id = e.target && typeof e.target.id === 'string' ? e.target.id : '';
      if (id.startsWith('modalModifierCommande')) {
        var parts = id.match(/modalModifierCommande(\d+)/);
        if (parts && parts[1]) {
          computeCoutReelEdit(parts[1]);
        }
      }
    });

    document.addEventListener('input', function (e) {
      if (!e.target || !e.target.id) return;
      var m1 = e.target.id.match(/^cout_global_edit_(\d+)$/);
      var m2 = e.target.id.match(/^cout_livraison_edit_(\d+)$/);
      var id = (m1 && m1[1]) || (m2 && m2[1]);
      if (id) {
        computeCoutReelEdit(id);
      }
    });
  })();
</script>
@endsection
