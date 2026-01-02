@extends('layout.main')

@section('title', 'Modifier Commande')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Modifier Commande #{{ $commande->id }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('commandes.index') }}">Commandes</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
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
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Modifier la commande</h3>
                        </div>

                        <form action="{{ route('commandes.update', $commande) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="utilisateur_id">Client <span class="text-danger">*</span></label>
                                            <select name="utilisateur_id" id="utilisateur_id" class="form-control" required>
                                                <option value="">-- Sélectionner un client --</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('utilisateur_id', $commande->utilisateur_id) == $client->id ? 'selected' : '' }}>
                                                        {{ $client->nom }} {{ $client->prenoms }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="livreur_id">Livreur <span class="text-danger">*</span></label>
                                            <select name="livreur_id" id="livreur_id" class="form-control" required>
                                                <option value="">-- Sélectionner un livreur --</option>
                                                @foreach($livreurs as $livreur)
                                                    <option value="{{ $livreur->id }}" {{ old('livreur_id', $commande->livreur_id) == $livreur->id ? 'selected' : '' }}>
                                                        {{ $livreur->nom }} {{ $livreur->prenoms }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="communes">Commune / Destination <span class="text-danger">*</span></label>
                                            <input type="text" name="communes" id="communes" class="form-control" value="{{ old('communes', $commande->communes) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="statut">Statut <span class="text-danger">*</span></label>
                                            <select name="statut" id="statut" class="form-control" required>
                                                <option value="Non Livré" {{ old('statut', $commande->statut) == 'Non Livré' ? 'selected' : '' }}>Non Livré</option>
                                                <option value="Livré" {{ old('statut', $commande->statut) == 'Livré' ? 'selected' : '' }}>Livré</option>
                                                <option value="Retour" {{ old('statut', $commande->statut) == 'Retour' ? 'selected' : '' }}>Retour</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_global">Coût Global (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" name="cout_global" id="cout_global" class="form-control" value="{{ old('cout_global', $commande->cout_global) }}" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_livraison">Coût Livraison (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" name="cout_livraison" id="cout_livraison" class="form-control" value="{{ old('cout_livraison', $commande->cout_livraison) }}" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_reel">Coût Réel (FCFA)</label>
                                            <input type="number" id="cout_reel" class="form-control" value="{{ $commande->cout_reel }}" readonly disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_reception">Date de réception <span class="text-danger">*</span></label>
                                            <input type="date" name="date_reception" id="date_reception" class="form-control" value="{{ old('date_reception', $commande->date_reception ? $commande->date_reception->format('Y-m-d') : '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_livraison">Date de livraison</label>
                                            <input type="date" name="date_livraison" id="date_livraison" class="form-control" value="{{ old('date_livraison', $commande->date_livraison ? $commande->date_livraison->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_retour">Date de retour</label>
                                            <input type="date" name="date_retour" id="date_retour" class="form-control" value="{{ old('date_retour', $commande->date_retour ? $commande->date_retour->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Mettre à jour
                                </button>
                                <a href="{{ request('redirect_to') ?: route('commandes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coutGlobal = document.getElementById('cout_global');
        const coutLivraison = document.getElementById('cout_livraison');
        const coutReel = document.getElementById('cout_reel');

        function calculerCoutReel() {
            const global = parseInt(coutGlobal.value) || 0;
            const livraison = parseInt(coutLivraison.value) || 0;
            coutReel.value = global - livraison;
        }

        coutGlobal.addEventListener('input', calculerCoutReel);
        coutLivraison.addEventListener('input', calculerCoutReel);
    });
</script>
@endsection
