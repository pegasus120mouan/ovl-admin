@extends('layout.main')

@section('title', 'Bilan du jour')
@section('page_title', 'Bilan du ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))

@section('content')

      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Montant Global</span>
                <span class="info-box-number">
                  {{ number_format($montantLivre, 0, ',', ' ') }}
                  <small>FCFA</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Montant à donner</span>
                <span class="info-box-number">
                  {{ number_format($coutReelLivre, 0, ',', ' ') }}
                  <small>FCFA</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Recette Global</span>
                <span class="info-box-number">
                  {{ number_format($livraisonLivre, 0, ',', ' ') }}
                  <small>FCFA</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Nombre de colis livré aujourd'hui</span>
                <span class="info-box-number">{{ $totalLivrees }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->


        <!-- Points par Clients -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Points par Clients</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Clients</th>
                  <th>Montant Global</th>
                  <th>Gain livraison</th>
                  <th>Versements</th>
                  <th>Nbre de colis Récu</th>
                  <th>Nbre de livré</th>
                  <th>Nbre de colis non Livré</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pointsClients as $point)
                <tr>
                  <td><a href="#">{{ $point['client']->boutique->nom ?? $point['client']->nom }}</a></td>
                  <td>{{ number_format($point['cout_global'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['cout_livraison'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['cout_reel'], 0, ',', ' ') }}</td>
                  <td><span class="text-info">{{ $point['nbre_recu'] }}</span></td>
                  <td><span class="text-success">{{ $point['nbre_livre'] }}</span></td>
                  <td><span class="text-danger">{{ $point['nbre_non_livre'] }}</span></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <!-- Versement -->
        <div class="card card-info">
          <div class="card-header">
            <h3 class="card-title">Versement</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table">
              <thead class="bg-info">
                <tr>
                  <th>Nom du livreur</th>
                  <th>Montant Global</th>
                  <th>Dépenses</th>
                  <th>Montant à remettre</th>
                </tr>
              </thead>
              <tbody>
                @foreach($versements as $versement)
                <tr>
                  <td>{{ $versement['livreur']->nom ?? 'N/A' }} {{ $versement['livreur']->prenoms ?? '' }}</td>
                  <td>{{ number_format($versement['montant_global'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($versement['depenses'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($versement['montant_remettre'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <!-- Point livreur -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Point livreur</h3>
            <div class="card-tools">
              <form action="{{ route('points-livreurs.sync-recettes') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit" class="btn btn-success btn-sm mr-2">
                  <i class="fas fa-sync"></i> Synchroniser les recettes
                </button>
              </form>
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Livreur</th>
                  <th>Recette</th>
                  <th>Dépense</th>
                  <th>Gain</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pointLivreurs as $point)
                <tr>
                  <td>{{ $point['livreur']->nom ?? 'N/A' }} {{ $point['livreur']->prenoms ?? '' }}</td>
                  <td>{{ number_format($point['recette'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['depense'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['gain'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

      </div><!--/. container-fluid -->


@endsection