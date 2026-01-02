@extends('layout.main')

@section('title', 'Boutiques')
@section('page_title', $boutique->nom)

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
                   id="boutiqueLogoPreview"
                   src="{{ $logoUrl }}"
                   alt="Logo boutique"
                   style="cursor: pointer;">
            </div>

            <form id="logoForm" action="{{ route('boutiques.update', $boutique) }}" method="POST" enctype="multipart/form-data" class="mt-2">
              @csrf
              @method('PUT')
              <input type="file" name="logo" id="boutiqueLogoInput" accept="image/*" class="d-none">

              <div id="logoActions" class="mt-2" style="display: none;">
                <button type="submit" class="btn btn-primary btn-block">Mettre à jour le logo</button>
                <button type="button" id="cancelLogoBtn" class="btn btn-default btn-block">Annuler</button>
              </div>
            </form>

            <h3 class="profile-username text-center">{{ $boutique->nom }}</h3>

            <p class="text-muted text-center">{{ $boutique->type_articles ?? 'N/A' }}</p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>Gérant</b>
                <a class="float-right">
                  @if($boutique->gerant)
                    {{ $boutique->gerant->nom }} {{ $boutique->gerant->prenoms }} ({{ $boutique->gerant->contact }})
                  @else
                    N/A
                  @endif
                </a>
              </li>
              <li class="list-group-item">
                <b>Commandes</b> <a class="float-right">{{ $commandesCount ?? 0 }}</a>
              </li>
              <li class="list-group-item">
                <b>Type d'articles</b> <a class="float-right">{{ $boutique->type_articles ?? 'N/A' }}</a>
              </li>
            </ul>

            <a href="{{ route('boutiques.index') }}" class="btn btn-primary btn-block"><b>Retour</b></a>
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
              <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Changer le nom de la boutique</a></li>
              <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Changer le Gérant</a></li>
              <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Types d'articles</a></li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane" id="activity">
                <form action="{{ route('boutiques.update', $boutique) }}" method="POST">
                  @csrf
                  @method('PUT')

                  <div class="form-group">
                    <label for="nom">Nom de la boutique</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="{{ $boutique->nom }}" required>
                  </div>

                  <button type="submit" class="btn btn-primary">Valider</button>
                </form>
              </div>

              <div class="tab-pane" id="timeline">
                <form action="{{ route('boutiques.update', $boutique) }}" method="POST">
                  @csrf
                  @method('PUT')

                  <div class="form-group">
                    <label for="gerant_id">Gérant</label>
                    <select name="gerant_id" id="gerant_id" class="form-control" required>
                      <option value="">-- Sélectionner un utilisateur --</option>
                      @foreach(($clients ?? []) as $client)
                        <option value="{{ $client->id }}" @if($boutique->gerant && $boutique->gerant->id === $client->id) selected @endif>
                          {{ $client->nom }} {{ $client->prenoms }} ({{ $client->contact }})
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary">Valider</button>
                </form>
              </div>

              <div class="active tab-pane" id="settings">
                <form action="{{ route('boutiques.update', $boutique) }}" method="POST">
                  @csrf
                  @method('PUT')

                  <div class="form-group">
                    <label for="type_articles">Types d'articles</label>
                    <input type="text" name="type_articles" id="type_articles" class="form-control" value="{{ $boutique->type_articles ?? '' }}">
                  </div>

                  <button type="submit" class="btn btn-primary">Modifier</button>
                </form>

                <hr>

                <p class="text-muted mb-0">Clique sur le logo (à gauche) pour le changer.</p>
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
    var preview = document.getElementById('boutiqueLogoPreview');
    var input = document.getElementById('boutiqueLogoInput');
    var actions = document.getElementById('logoActions');
    var cancelBtn = document.getElementById('cancelLogoBtn');

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
