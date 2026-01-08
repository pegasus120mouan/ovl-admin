
@extends('layout.main')

@section('title', 'Engins')
@section('page_title', 'Engins')

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
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterEngin"><i class="fas fa-plus"></i> Enregistrer un engin</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-hover">
            <thead class="thead-dark">
              <tr>
                <th>Type</th>
                <th>Marque</th>
                <th>Année</th>
                <th>Plaque</th>
                <th>Chassis</th>
                <th>Couleur</th>
                <th>Date ajout</th>
                <th>Statut</th>
                <th>Livreur</th>
              </tr>
            </thead>
            <tbody>
              @forelse($engins as $engin)
              <tr>
                <td>
                  @php($typeEnginLabel = strtolower((string) ($engin->type_engin ?? '')))
                  @if($typeEnginLabel === 'moto')
                    <img src="{{ asset('img/icones/bike.png') }}" alt="Moto" style="height:32px; width:auto;">
                  @elseif($typeEnginLabel === 'voiture')
                    <img src="{{ asset('img/icones/car.png') }}" alt="Voiture" style="height:32px; width:auto;">
                  @else
                    -
                  @endif
                </td>
                <td>{{ $engin->marque }}</td>
                <td>{{ $engin->annee_fabrication }}</td>
                <td>{{ $engin->plaque_immatriculation }}</td>
                <td>
                  <a href="{{ route('engins.show', $engin) }}" class="text-primary">{{ $engin->numero_chassis }}</a>
                </td>
                <td>{{ $engin->couleur }}</td>
                <td>{{ $engin->date_ajout }}</td>
                <td>
                  @php($statutEnginLabel = strtolower((string) ($engin->statut ?? '')))
                  @if($statutEnginLabel === 'en utilisation' || $statutEnginLabel === 'en utilisation ')
                    <span class="badge badge-success">En Utilisation</span>
                  @elseif($statutEnginLabel === 'en panne' || $statutEnginLabel === 'en panne ')
                    <span class="badge badge-danger">En Panne</span>
                  @elseif($statutEnginLabel !== '')
                    <span class="badge badge-secondary">{{ $engin->statut }}</span>
                  @else
                    <span class="badge badge-secondary">-</span>
                  @endif
                </td>
                <td>
                  @if($engin->utilisateur)
                    {{ $engin->utilisateur->nom }} {{ $engin->utilisateur->prenoms }}
                    @if($engin->utilisateur->contact)
                      <br><small class="text-muted">{{ $engin->utilisateur->contact }}</small>
                    @endif
                  @else
                    -
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center">Aucun engin enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $engins->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
