@extends('layout.main')

@section('title', 'Contrats')
@section('page_title', 'Contrats')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $colisRecusMois ?? 0 }}</h3>
          <p>Colis reçus (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $colisLivresMois ?? 0 }}</h3>
          <p>Colis livrés (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-bar"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $colisNonLivresMois ?? 0 }}</h3>
          <p>Colis non livrés (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-user-plus"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $colisRetoursMois ?? 0 }}</h3>
          <p>Colis retour (mois en cours)</p>
        </div>
        <div class="icon">
          <i class="fas fa-chart-pie"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterContrat">
        Enregistrer un contrat
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead class="thead-dark">
              <tr>
                <th>Avatar</th>
                <th>Numéro chassis</th>
                <th>Plaque d'immatriculation</th>
                <th>Statut</th>
                <th>Vignette Debut</th>
                <th>Vignette Fin</th>
                <th>Nombre de jour restants</th>
                <th>Assurance Debut</th>
                <th>Assurance Fin</th>
                <th>Nombre de jour restants</th>
              </tr>
            </thead>
            <tbody>
              @forelse($contrats as $contrat)
                @php
                  $engin = $contrat->engin;
                  $livreur = $engin?->utilisateur;

                  $localFallbackAvatarUrl = asset('dist/img/user2-160x160.jpg');
                  $defaultLivreurAvatarUrl = $localFallbackAvatarUrl;
                  $livreurPhotoUrl = $localFallbackAvatarUrl;

                  if ($livreur) {
                    $avatarKey = $livreur->avatar ?? null;
                    if (!$avatarKey || $avatarKey === 'default.jpg') {
                      $avatarKey = 'livreurs/livreur.png';
                    } elseif (!str_contains($avatarKey, '/')) {
                      $avatarKey = 'livreurs/' . $avatarKey;
                    }

                    try {
                      $disk = \Illuminate\Support\Facades\Storage::disk('s3');
                      try {
                        $defaultLivreurAvatarUrl = $disk->temporaryUrl('livreurs/livreur.png', now()->addMinutes(30));
                      } catch (\Exception $e) {
                        $defaultLivreurAvatarUrl = $disk->url('livreurs/livreur.png');
                      }

                      try {
                        $livreurPhotoUrl = $disk->temporaryUrl($avatarKey, now()->addMinutes(30));
                      } catch (\Exception $e) {
                        $livreurPhotoUrl = $disk->url($avatarKey);
                      }
                    } catch (\Throwable $e) {
                      $livreurPhotoUrl = $localFallbackAvatarUrl;
                    }
                  }

                  $statut = strtolower((string) ($engin?->statut ?? ''));

                  $vRest = $contrat->vignette_jours_restants;
                  $aRest = $contrat->assurance_jours_restants;

                  $vClass = 'bg-success';
                  if ($vRest === null) {
                    $vClass = 'bg-secondary';
                  } elseif ($vRest <= 0) {
                    $vClass = 'bg-danger';
                  } elseif ($vRest < 122) {
                    $vClass = 'bg-danger';
                  } elseif ($vRest < 244) {
                    $vClass = 'bg-warning';
                  }

                  $aClass = 'bg-success';
                  if ($aRest === null) {
                    $aClass = 'bg-secondary';
                  } elseif ($aRest <= 0) {
                    $aClass = 'bg-danger';
                  } elseif ($aRest < 122) {
                    $aClass = 'bg-danger';
                  } elseif ($aRest < 244) {
                    $aClass = 'bg-warning';
                  }
                @endphp

                <tr>
                  <td>
                    <img src="{{ $livreurPhotoUrl }}" alt="" class="img-circle" style="width: 34px; height: 34px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ $defaultLivreurAvatarUrl }}';">
                  </td>
                  <td>
                    @if($engin)
                      <a href="{{ route('engins.show', $engin) }}" class="text-primary">{{ $engin->numero_chassis }}</a>
                    @else
                      -
                    @endif
                  </td>
                  <td>{{ $engin?->plaque_immatriculation ?? '-' }}</td>
                  <td>
                    @if($statut === 'en utilisation' || $statut === 'en utilisation ')
                      <span class="badge badge-success">En Utilisation</span>
                    @elseif($statut === 'en panne' || $statut === 'en panne ')
                      <span class="badge badge-danger">En Panne</span>
                    @elseif(($engin?->statut ?? '') !== '')
                      <span class="badge badge-secondary">{{ $engin->statut }}</span>
                    @else
                      <span class="badge badge-secondary">-</span>
                    @endif
                  </td>
                  <td>{{ optional($contrat->vignette_date_debut)->format('d-m-y') ?? '-' }}</td>
                  <td>{{ optional($contrat->vignette_date_fin)->format('d-m-y') ?? '-' }}</td>
                  <td class="text-white {{ $vClass }}">
                    {{ $vRest === null ? '-' : max(0, (int) $vRest) . ' jours' }}
                  </td>
                  <td>{{ optional($contrat->assurance_date_debut)->format('d-m-y') ?? '-' }}</td>
                  <td>{{ optional($contrat->assurance_date_fin)->format('d-m-y') ?? '-' }}</td>
                  <td class="text-white {{ $aClass }}">
                    {{ $aRest === null ? '-' : max(0, (int) $aRest) . ' jours' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center">Aucun contrat trouvé</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $contrats->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterContrat" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enregistrer un contrat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form action="{{ route('engins.contrats_engins.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Date de Début vignette</label>
            <input type="date" class="form-control" name="vignette_date_debut" required>
          </div>
          <div class="form-group">
            <label>Date de Fin vignette</label>
            <input type="date" class="form-control" name="vignette_date_fin" required>
          </div>
          <div class="form-group">
            <label>Date de Début Assurance</label>
            <input type="date" class="form-control" name="assurance_date_debut" required>
          </div>
          <div class="form-group">
            <label>Date de Fin Assurance</label>
            <input type="date" class="form-control" name="assurance_date_fin" required>
          </div>
          <div class="form-group">
            <label>Plaque d'immatriculation</label>
            <select class="form-control" name="id_engin" required>
              <option value="" disabled selected>Choisir</option>
              @foreach(($engins ?? []) as $engin)
                @php
                  $enginLabel = trim((string) ($engin->plaque_immatriculation ?? ''));
                  if ($enginLabel === '') {
                    $enginLabel = (string) ($engin->numero_chassis ?? '');
                  }
                @endphp
                <option value="{{ $engin->engin_id }}">{{ $enginLabel }}</option>
              @endforeach
            </select>
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
