@extends('layout.main')

@section('title', 'Intégration')
@section('page_title', 'Intégration')

@section('content')
<div class="container-fluid">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {{ session('error') }}
    </div>
  @endif

  @if(session('generated_credentials'))
    @php $creds = session('generated_credentials'); @endphp
    <div class="alert alert-warning">
      <h5><i class="icon fas fa-exclamation-triangle"></i> Identifiants à transmettre au client</h5>
      <p class="mb-2">Copiez ces informations et donnez-les au client pour les saisir dans son application (ex. Geststock). Le <strong>token</strong> ne sera plus affiché en clair.</p>
      <div class="table-responsive">
        <table class="table table-sm table-bordered bg-white mb-0">
          <tr>
            <th style="width: 140px">Identifiant</th>
            <td><code id="cred-identifiant">{{ $creds['identifiant'] }}</code></td>
          </tr>
          <tr>
            <th>Token</th>
            <td><code id="cred-token">{{ $creds['token'] }}</code></td>
          </tr>
          <tr>
            <th>URL API</th>
            <td><code id="cred-url">{{ $creds['url'] }}</code></td>
          </tr>
        </table>
      </div>
    </div>
  @endif

  <div class="row">
    <div class="col-lg-4">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-plug mr-2"></i>Nouvelle intégration</h3>
        </div>
        <form action="{{ route('integrations.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label for="nom">Nom de l'application</label>
              <input
                type="text"
                name="nom"
                id="nom"
                class="form-control @error('nom') is-invalid @enderror"
                placeholder="Ex: Geststock Uniko"
                value="{{ old('nom') }}"
                required
              >
              @error('nom')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="utilisateur_id">Client OVL</label>
              <select
                name="utilisateur_id"
                id="utilisateur_id"
                class="form-control @error('utilisateur_id') is-invalid @enderror"
                required
              >
                <option value="">Sélectionner un client</option>
                @foreach($clients as $client)
                  <option value="{{ $client->id }}" {{ (string) old('utilisateur_id') === (string) $client->id ? 'selected' : '' }}>
                    {{ $client->nom }} {{ $client->prenoms }}
                    @if($client->boutique)
                      — {{ $client->boutique->nom }}
                    @endif
                  </option>
                @endforeach
              </select>
              @error('utilisateur_id')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
              <small class="form-text text-muted">Les commandes reçues via l'API seront rattachées à ce client.</small>
            </div>

            <div class="form-group mb-0">
              <label>URL API à transmettre</label>
              <input type="text" class="form-control" value="{{ $apiUrl }}" readonly>
              <small class="form-text text-muted">Identifiant et token seront générés automatiquement à l'enregistrement.</small>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-key mr-1"></i> Générer les accès
            </button>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Utilisation côté client</h3>
        </div>
        <div class="card-body">
          <p class="mb-2">Le client envoie un <code>POST</code> vers l'URL avec :</p>
          <ul class="mb-2 pl-3">
            <li>Header <code>X-Integration-Id</code> = identifiant</li>
            <li>Header <code>X-Integration-Token</code> (ou Bearer) = token</li>
          </ul>
          <p class="mb-1"><strong>Corps JSON :</strong></p>
          <pre class="bg-light p-2 mb-0" style="font-size: 12px">{
  "communes": "Cocody",
  "cout_global": 18000,
  "cout_livraison": 1500,
  "date_reception": "2026-09-14",
  "reference_externe": "CMD-xxx"
}</pre>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list mr-2"></i>Intégrations enregistrées</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Application</th>
                <th>Client</th>
                <th>Identifiant</th>
                <th>Token</th>
                <th>URL</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($integrations as $integration)
                <tr>
                  <td>{{ $integration->id }}</td>
                  <td>{{ $integration->nom }}</td>
                  <td>
                    {{ $integration->client?->nom }} {{ $integration->client?->prenoms }}
                    @if($integration->client?->boutique)
                      <br><small class="text-muted">{{ $integration->client->boutique->nom }}</small>
                    @endif
                  </td>
                  <td><code>{{ $integration->identifiant }}</code></td>
                  <td>
                    <code>••••{{ $integration->token_hint }}</code>
                  </td>
                  <td>
                    <small><code>{{ $integration->apiUrl() }}</code></small>
                  </td>
                  <td>
                    @if($integration->actif)
                      <span class="badge badge-success">Actif</span>
                    @else
                      <span class="badge badge-secondary">Inactif</span>
                    @endif
                  </td>
                  <td class="text-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-info"
                      data-toggle="modal"
                      data-target="#editIntegration{{ $integration->id }}"
                      title="Modifier"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <form
                      action="{{ route('integrations.regenerate', $integration) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Régénérer le token ? L\'ancien ne fonctionnera plus.');"
                    >
                      @csrf
                      <button type="submit" class="btn btn-sm btn-warning" title="Régénérer le token">
                        <i class="fas fa-sync-alt"></i>
                      </button>
                    </form>
                    <form
                      action="{{ route('integrations.destroy', $integration) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Supprimer cette intégration ?');"
                    >
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">Aucune intégration enregistrée</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @foreach($integrations as $integration)
        <div class="modal fade" id="editIntegration{{ $integration->id }}" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form action="{{ route('integrations.update', $integration) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title">Modifier l'intégration</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label>Nom de l'application</label>
                    <input type="text" name="nom" class="form-control" value="{{ $integration->nom }}" required>
                  </div>
                  <div class="form-group">
                    <label>Client OVL</label>
                    <select name="utilisateur_id" class="form-control" required>
                      @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ (int) $integration->utilisateur_id === (int) $client->id ? 'selected' : '' }}>
                          {{ $client->nom }} {{ $client->prenoms }}
                          @if($client->boutique)
                            — {{ $client->boutique->nom }}
                          @endif
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group mb-0">
                    <div class="custom-control custom-switch">
                      <input type="hidden" name="actif" value="0">
                      <input
                        type="checkbox"
                        class="custom-control-input"
                        id="actif{{ $integration->id }}"
                        name="actif"
                        value="1"
                        {{ $integration->actif ? 'checked' : '' }}
                      >
                      <label class="custom-control-label" for="actif{{ $integration->id }}">Actif</label>
                    </div>
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
      @endforeach
    </div>
  </div>
</div>
@endsection
