@extends('layout.main')

@section('title', 'Clients')
@section('page_title', $client->nom . ' ' . $client->prenoms)

@section('content')

<div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ $clientsTotal ?? 0 }}</h3>
            <p>Total Clients</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
          <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ $clientsActifs ?? 0 }}</h3>
            <p>Clients Actifs</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-check"></i>
          </div>
          <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ $clientsInactifs ?? 0 }}</h3>
            <p>Clients Inactifs</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-times"></i>
          </div>
          <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ $boutiquesTotal ?? 0 }}</h3>
            <p>Boutiques</p>
          </div>
          <div class="icon">
            <i class="fas fa-store"></i>
          </div>
          <a href="#" class="small-box-footer">Plus d'infos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card card-primary card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              <img class="profile-user-img img-fluid img-circle"
                   id="clientAvatarPreview"
                   src="{{ $avatarUrl }}"
                   alt="Photo client"
                   style="cursor: pointer;">
            </div>

            <form id="avatarForm" action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data" class="mt-2">
              @csrf
              @method('PUT')
              <input type="hidden" name="redirect_to" value="{{ route('clients.show', $client) }}">
              <input type="file" name="avatar" id="clientAvatarInput" accept="image/*" class="d-none">

              <div id="avatarActions" class="mt-2" style="display: none;">
                <button type="submit" class="btn btn-primary btn-block">Mettre à jour la photo</button>
                <button type="button" id="cancelAvatarBtn" class="btn btn-default btn-block">Annuler</button>
              </div>
            </form>

            <h3 class="profile-username text-center">{{ $client->nom }} {{ $client->prenoms }}</h3>

            <p class="text-muted text-center">{{ $client->contact }}</p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>Boutique</b>
                <a class="float-right">
                  {{ $client->boutique->nom ?? 'Aucune' }}
                </a>
              </li>
              <li class="list-group-item">
                <b>Type d'articles</b>
                <a class="float-right">
                  {{ $client->boutique->type_articles ?? 'N/A' }}
                </a>
              </li>
              <li class="list-group-item">
                <b>Gérant</b>
                <a class="float-right">
                  @if($client->boutique && $client->boutique->gerant)
                    {{ $client->boutique->gerant->nom }} {{ $client->boutique->gerant->prenoms }} ({{ $client->boutique->gerant->contact }})
                  @else
                    N/A
                  @endif
                </a>
              </li>
              <li class="list-group-item">
                <b>Statut</b>
                <a class="float-right">
                  @if($client->statut_compte)
                    Actif
                  @else
                    Inactif
                  @endif
                </a>
              </li>
            </ul>

            <a href="{{ route('clients.index') }}" class="btn btn-primary btn-block"><b>Retour</b></a>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->

      </div>
      <!-- /.col -->
      <div class="col-md-9">
        <div class="card">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Changer le nom</a></li>
              <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Changer la boutique</a></li>
              <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Contact / Statut</a></li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane" id="activity">
                <form action="{{ route('clients.update', $client) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="{{ route('clients.show', $client) }}">

                  <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="{{ $client->nom }}" required>
                  </div>

                  <div class="form-group">
                    <label for="prenoms">Prénoms</label>
                    <input type="text" name="prenoms" id="prenoms" class="form-control" value="{{ $client->prenoms }}" required>
                  </div>

                  <button type="submit" class="btn btn-primary">Valider</button>
                </form>
              </div>

              <div class="tab-pane" id="timeline">
                <form action="{{ route('clients.update', $client) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="{{ route('clients.show', $client) }}">

                  <div class="form-group">
                    <label for="boutique_id">Boutique</label>
                    <select name="boutique_id" id="boutique_id" class="form-control">
                      <option value="">Aucune</option>
                      @foreach($boutiques as $boutique)
                        @if(($boutique->utilisateurs_count ?? 0) == 0 || $client->boutique_id == $boutique->id)
                          <option value="{{ $boutique->id }}" {{ $client->boutique_id == $boutique->id ? 'selected' : '' }}>{{ $boutique->nom }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary">Valider</button>
                </form>
              </div>

              <div class="active tab-pane" id="settings">
                <form action="{{ route('clients.update', $client) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="{{ route('clients.show', $client) }}">

                  <div class="form-group">
                    <label for="contact">Contact</label>
                    <input type="text" name="contact" id="contact" class="form-control" value="{{ $client->contact }}" required>
                  </div>

                  <div class="form-group">
                    <label for="statut_compte">Statut</label>
                    <select name="statut_compte" id="statut_compte" class="form-control">
                      <option value="1" {{ $client->statut_compte ? 'selected' : '' }}>Actif</option>
                      <option value="0" {{ !$client->statut_compte ? 'selected' : '' }}>Inactif</option>
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary">Modifier</button>
                </form>

                <hr>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var preview = document.getElementById('clientAvatarPreview');
    var input = document.getElementById('clientAvatarInput');
    var actions = document.getElementById('avatarActions');
    var cancelBtn = document.getElementById('cancelAvatarBtn');

    if (!preview || !input || !actions || !cancelBtn) return;

    var originalSrc = preview.getAttribute('src');

    preview.addEventListener('click', function () {
      input.click();
    });

    input.addEventListener('change', function () {
      if (!input.files || !input.files[0]) {
        actions.style.display = 'none';
        preview.setAttribute('src', originalSrc);
        return;
      }

      var file = input.files[0];
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
        actions.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });

    cancelBtn.addEventListener('click', function () {
      input.value = '';
      actions.style.display = 'none';
      preview.setAttribute('src', originalSrc);
    });
  });
</script>

@endsection
