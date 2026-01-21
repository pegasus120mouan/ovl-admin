@extends('layout.main')

@section('title', 'Boutiques')
@section('page_title', 'Liste des boutiques')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $boutiquesTotal ?? $boutiques->total() }}</h3>
          <p>Total Boutiques</p>
        </div>
        <div class="icon">
          <i class="fas fa-store"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $clientsTotal ?? 0 }}</h3>
          <p>Clients (total)</p>
        </div>
        <div class="icon">
          <i class="fas fa-users"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $boutiquesAvecLogo ?? 0 }}</h3>
          <p>Avec logo</p>
        </div>
        <div class="icon">
          <i class="fas fa-image"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $boutiquesAvecTypeArticles ?? 0 }}</h3>
          <p>Type articles défini</p>
        </div>
        <div class="icon">
          <i class="fas fa-tags"></i>
        </div>
        <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="mb-3">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterBoutique"><i class="fas fa-plus"></i> Ajouter une boutique</a>
        <a href="#" class="btn btn-danger"><i class="fas fa-file-export"></i> Exporter la liste des boutiques</a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-store"></i> Boutiques</h3>
          <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
              <input type="text" name="table_search" class="form-control float-right" placeholder="Rechercher">
              <div class="input-group-append">
                <button type="submit" class="btn btn-default">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Nom</th>
                <th>Type articles</th>
                <th>Gérant</th>
                <th>Statut</th>
                <th style="width: 140px;">Actions</th>
                <th>Changer le statut</th>
              </tr>
            </thead>
            <tbody>
              @forelse($boutiques as $boutique)
              <tr>
                <td style="width: 70px;">
                  @php
                    $logoKey = $boutique->logo ?: 'boutiques/default_boutiques.png';
                    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
                    try {
                      $logoUrl = $disk->temporaryUrl($logoKey, now()->addMinutes(30));
                    } catch (\Exception $e) {
                      $logoUrl = $disk->url($logoKey);
                    }
                  @endphp
                  <a href="{{ route('boutiques.show', $boutique) }}">
                    <img src="{{ $logoUrl }}" alt="Logo" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;" />
                  </a>
                </td>
                <td>{{ $boutique->nom }}</td>
                <td>{{ $boutique->type_articles ?? 'N/A' }}</td>
                <td>
                  @if($boutique->gerant)
                    {{ $boutique->gerant->nom }} {{ $boutique->gerant->prenoms }}
                  @else
                    N/A
                  @endif
                </td>
                <td>
                  @if($boutique->statut)
                    <span class="badge badge-success">Actif</span>
                  @else
                    <span class="badge badge-danger">Inactif</span>
                  @endif
                </td>
                <td style="white-space: nowrap;">
                  <div class="d-inline-flex align-items-center">
                    <a href="{{ route('boutiques.show', $boutique) }}" class="btn btn-sm btn-info" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn btn-sm btn-warning ml-1" title="Modifier" data-toggle="modal" data-target="#modalModifier{{ $boutique->id }}"><i class="fas fa-edit"></i></a>
                    <button
                      type="button"
                      class="btn btn-sm btn-danger ml-1"
                      title="Supprimer"
                      data-toggle="modal"
                      data-target="#modalSupprimerBoutique"
                      data-boutique-name="{{ $boutique->nom }}"
                      data-delete-url="{{ route('boutiques.destroy', $boutique) }}"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <form action="{{ route('boutiques.toggle-statut', $boutique) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <div class="custom-control custom-switch switch-lg">
                      <input type="checkbox" class="custom-control-input" id="toggleBoutiqueStatut{{ $boutique->id }}" {{ $boutique->statut ? 'checked' : '' }} onchange="this.form.submit()">
                      <label class="custom-control-label" for="toggleBoutiqueStatut{{ $boutique->id }}"></label>
                    </div>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">Aucune boutique trouvée</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              Affichage de {{ $boutiques->firstItem() ?? 0 }} à {{ $boutiques->lastItem() ?? 0 }} sur {{ $boutiques->total() }} boutiques
            </div>
            <div class="d-flex align-items-center">
              {{ $boutiques->links() }}
              <form action="{{ route('boutiques.index') }}" method="GET" class="d-flex align-items-center ml-3">
                <span class="mr-2">Afficher :</span>
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

<div class="modal fade" id="modalAjouterBoutique" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-store"></i> Ajouter une boutique</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('boutiques.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" required>
          </div>
          <div class="form-group">
            <label>Type articles</label>
            <input type="text" class="form-control" name="type_articles" placeholder="Ex: vêtements, chaussures...">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut">
              <option value="1" selected>Actif</option>
              <option value="0">Inactif</option>
            </select>
          </div>
          <div class="form-group">
            <label>Logo</label>
            <input type="file" class="form-control" name="logo" accept="image/*">
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

<div class="modal fade" id="modalSupprimerBoutique" tabindex="-1" role="dialog" aria-labelledby="modalSupprimerBoutiqueLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white" id="modalSupprimerBoutiqueLabel">Supprimer une boutique</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Vous êtes sur le point de supprimer la boutique :</p>
        <p class="font-weight-bold mb-0" id="supprimerBoutiqueNom"></p>
        <small class="text-muted">Cette action est irréversible.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <form id="formSupprimerBoutique" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modalId = '#modalSupprimerBoutique';

    function fillDeleteModal(relatedTarget) {
      if (!relatedTarget) return;

      var boutiqueName = relatedTarget.getAttribute('data-boutique-name') || '';
      var deleteUrl = relatedTarget.getAttribute('data-delete-url') || '';

      var nameEl = document.getElementById('supprimerBoutiqueNom');
      if (nameEl) nameEl.textContent = boutiqueName;

      var form = document.getElementById('formSupprimerBoutique');
      if (form && deleteUrl) form.setAttribute('action', deleteUrl);
    }

    if (window.jQuery && window.jQuery(modalId).on) {
      window.jQuery(modalId).on('show.bs.modal', function (event) {
        fillDeleteModal(event.relatedTarget);
      });
      return;
    }

    var modal = document.getElementById('modalSupprimerBoutique');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
      fillDeleteModal(event.relatedTarget);
    });
  });
</script>

<style>
  .switch-lg {
    transform: scale(1.4);
    transform-origin: left center;
  }
</style>

@foreach($boutiques as $boutique)
<div class="modal fade" id="modalModifier{{ $boutique->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Modifier la boutique</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('boutiques.update', $boutique) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="nom" value="{{ $boutique->nom }}" required>
          </div>
          <div class="form-group">
            <label>Type articles</label>
            <input type="text" class="form-control" name="type_articles" value="{{ $boutique->type_articles ?? '' }}">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select class="form-control" name="statut">
              <option value="1" {{ $boutique->statut ? 'selected' : '' }}>Actif</option>
              <option value="0" {{ !$boutique->statut ? 'selected' : '' }}>Inactif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Modifier</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endsection
