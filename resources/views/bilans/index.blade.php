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
                  <th>Envoyer Message</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pointsClients as $point)
                <tr>
                  <td>
                    <a href="#" class="client-packages-link" 
                       data-client-id="{{ optional($point['client'])->id }}" 
                       data-client-name="{{ optional(optional($point['client'])->boutique)->nom ?? optional($point['client'])->nom ?? 'N/A' }}">
                      {{ optional(optional($point['client'])->boutique)->nom ?? optional($point['client'])->nom ?? 'N/A' }}
                    </a>
                  </td>
                  <td>{{ number_format($point['cout_global'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['cout_livraison'], 0, ',', ' ') }}</td>
                  <td>{{ number_format($point['cout_reel'], 0, ',', ' ') }}</td>
                  <td><span class="text-info">{{ $point['nbre_recu'] }}</span></td>
                  <td><span class="text-success">{{ $point['nbre_livre'] }}</span></td>
                  <td><span class="text-danger">{{ $point['nbre_non_livre'] }}</span></td>
                  <td>
                    @if((optional($point['client'])->contact ?? null))
                      <form action="{{ route('bilans.send-client-sms', $point['client']) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="btn btn-success btn-sm" title="Envoyer SMS">
                          <i class="fas fa-sms"></i> SMS
                        </button>
                      </form>
                    @else
                      <button type="button" class="btn btn-secondary btn-sm" disabled title="Aucun contact">
                        <i class="fas fa-sms"></i> SMS
                      </button>
                    @endif
                  </td>
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

      <!-- Modal pour afficher les colis d'un client -->
      <div class="modal fade" id="clientPackagesModal" tabindex="-1" role="dialog" aria-labelledby="clientPackagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="clientPackagesModalLabel">Colis du client</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="packagesLoader" class="text-center" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Chargement des colis...</p>
              </div>
              <div id="packagesContent">
                <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>Commune</th>
                      <th>Statut</th>
                      <th>Coût Global</th>
                      <th>Coût Livraison</th>
                      <th>Coût Réel</th>
                      <th>Livreur</th>
                      <th>Date Réception</th>
                      <th>Date Livraison</th>
                    </tr>
                  </thead>
                  <tbody id="packagesTableBody">
                  </tbody>
                  <tfoot>
                    <tr class="bg-light font-weight-bold">
                      <td colspan="4" class="text-right">Montant à remettre :</td>
                      <td id="totalMontantRemettre">0</td>
                      <td colspan="3"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
@endsection

 
@section('scripts')
<script>
$(document).ready(function() {
  $('.client-packages-link').on('click', function(e) {
    e.preventDefault();
    
    const clientId = $(this).data('client-id');
    const clientName = $(this).data('client-name');
    const date = '{{ $date }}';
    
    if (!clientId) {
      alert('Client non trouvé');
      return;
    }
    
    $('#clientPackagesModalLabel').text('Colis de ' + clientName);
    $('#packagesLoader').show();
    $('#packagesContent').hide();
    $('#clientPackagesModal').modal('show');
    
    $.ajax({
      url: '/bilans/' + clientId + '/colis',
      method: 'GET',
      data: { date: date },
      success: function(response) {
        $('#packagesLoader').hide();
        $('#packagesContent').show();
        
        if (response.success && response.commandes) {
          const tbody = $('#packagesTableBody');
          tbody.empty();
          
          if (response.commandes.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center">Aucun colis trouvé pour cette date</td></tr>');
            $('#totalMontantRemettre').text('0');
          } else {
            let totalMontant = 0;
            response.commandes.forEach(function(commande) {
              const statutClass = commande.statut === 'Livré' ? 'badge-success' : 
                                 commande.statut === 'Non Livré' ? 'badge-danger' : 'badge-warning';
              
              // Calculer le total du montant à remettre (cout_reel des colis livrés)
              if (commande.statut === 'Livré') {
                totalMontant += parseFloat(commande.cout_reel || 0);
              }
              
              const row = `
                <tr>
                  <td>${commande.commune || '-'}</td>
                  <td><span class="badge ${statutClass}">${commande.statut}</span></td>
                  <td>${formatNumber(commande.cout_global)}</td>
                  <td>${formatNumber(commande.cout_livraison)}</td>
                  <td>${formatNumber(commande.cout_reel)}</td>
                  <td>${commande.livreur || '-'}</td>
                  <td>${formatDate(commande.date_reception)}</td>
                  <td>${commande.date_livraison ? formatDate(commande.date_livraison) : '-'}</td>
                </tr>
              `;
              tbody.append(row);
            });
            
            // Afficher le total
            $('#totalMontantRemettre').text(formatNumber(totalMontant));
          }
        }
      },
      error: function(xhr) {
        $('#packagesLoader').hide();
        $('#packagesContent').show();
        let errorMessage = 'Erreur lors du chargement des colis';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        } else if (xhr.status === 404) {
          errorMessage = 'Client non trouvé';
        } else if (xhr.status === 500) {
          errorMessage = 'Erreur serveur';
        }
        $('#packagesTableBody').html('<tr><td colspan="8" class="text-center text-danger">' + errorMessage + '</td></tr>');
        console.error('Erreur AJAX:', xhr);
      }
    });
  });
  
  function formatNumber(num) {
    if (!num) return '0';
    return new Intl.NumberFormat('fr-FR').format(num);
  }
  
  function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR');
  }
});
</script>
@endsection