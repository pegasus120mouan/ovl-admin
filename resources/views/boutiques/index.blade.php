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
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center">Aucune boutique trouvée</td>
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
@endsection
