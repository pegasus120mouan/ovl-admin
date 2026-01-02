@extends('layout.main')

@section('title', 'Détails Commande')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Commande #{{ $commande->id }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('commandes.index') }}">Commandes</a></li>
                        <li class="breadcrumb-item active">Détails</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Statut -->
                    <div class="card">
                        <div class="card-body text-center">
                            @if($commande->statut == 'Livré')
                                <span class="badge badge-success" style="font-size: 1.5rem; padding: 15px 30px;">
                                    <i class="fas fa-check-circle"></i> {{ $commande->statut }}
                                </span>
                            @elseif($commande->statut == 'Non Livré')
                                <span class="badge badge-warning" style="font-size: 1.5rem; padding: 15px 30px;">
                                    <i class="fas fa-clock"></i> {{ $commande->statut }}
                                </span>
                            @else
                                <span class="badge badge-danger" style="font-size: 1.5rem; padding: 15px 30px;">
                                    <i class="fas fa-undo"></i> {{ $commande->statut }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Informations -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Informations de la commande</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>ID Commande:</th>
                                            <td>#{{ $commande->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Client:</th>
                                            <td>{{ $commande->client->nom ?? 'N/A' }} {{ $commande->client->prenoms ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Boutique:</th>
                                            <td>{{ $commande->client->boutique->nom ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Livreur:</th>
                                            <td>{{ $commande->livreur->nom ?? 'N/A' }} {{ $commande->livreur->prenoms ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Destination:</th>
                                            <td><strong>{{ $commande->communes }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Date réception:</th>
                                            <td>{{ $commande->date_reception ? $commande->date_reception->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date livraison:</th>
                                            <td>{{ $commande->date_livraison ? $commande->date_livraison->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date retour:</th>
                                            <td>{{ $commande->date_retour ? $commande->date_retour->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coûts -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Détails financiers</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Coût Global</span>
                                            <span class="info-box-number">{{ number_format($commande->cout_global, 0, ',', ' ') }} F</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Coût Livraison</span>
                                            <span class="info-box-number">{{ number_format($commande->cout_livraison, 0, ',', ' ') }} F</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Coût Réel</span>
                                            <span class="info-box-number">{{ number_format($commande->cout_reel, 0, ',', ' ') }} F</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body text-center">
                            <a href="{{ route('commandes.edit', $commande) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            @if($commande->statut == 'Non Livré')
                                <form action="{{ route('commandes.marquer-livre', $commande) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Marquer Livré
                                    </button>
                                </form>
                                <form action="{{ route('commandes.marquer-retour', $commande) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Marquer Retour
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('commandes.index') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
