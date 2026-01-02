@extends('layout.main')

@section('title', 'Nouvelle Commande')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Nouvelle Commande</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('commandes.index') }}">Commandes</a></li>
                        <li class="breadcrumb-item active">Nouvelle</li>
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
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Créer une nouvelle commande</h3>
                        </div>

                        <form action="{{ route('commandes.store') }}" method="POST">
                            @csrf
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
                                            <select name="utilisateur_id" id="utilisateur_id" class="form-control @error('utilisateur_id') is-invalid @enderror" required>
                                                <option value="">-- Sélectionner un client --</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('utilisateur_id') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->nom }} {{ $client->prenoms }} ({{ $client->boutique->nom ?? 'Sans boutique' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="livreur_id">Livreur <span class="text-danger">*</span></label>
                                            <select name="livreur_id" id="livreur_id" class="form-control @error('livreur_id') is-invalid @enderror" required>
                                                <option value="">-- Sélectionner un livreur --</option>
                                                @foreach($livreurs as $livreur)
                                                    <option value="{{ $livreur->id }}" {{ old('livreur_id') == $livreur->id ? 'selected' : '' }}>
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
                                            <input type="text" name="communes" id="communes" class="form-control @error('communes') is-invalid @enderror" value="{{ old('communes') }}" placeholder="Ex: Cocody, Plateau..." required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_reception">Date de réception <span class="text-danger">*</span></label>
                                            <input type="date" name="date_reception" id="date_reception" class="form-control @error('date_reception') is-invalid @enderror" value="{{ old('date_reception', date('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_global">Coût Global (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" name="cout_global" id="cout_global" class="form-control @error('cout_global') is-invalid @enderror" value="{{ old('cout_global', 0) }}" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_livraison">Coût Livraison (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" name="cout_livraison" id="cout_livraison" class="form-control @error('cout_livraison') is-invalid @enderror" value="{{ old('cout_livraison', 1500) }}" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cout_reel">Coût Réel (FCFA)</label>
                                            <input type="number" id="cout_reel" class="form-control" value="0" readonly disabled>
                                            <small class="text-muted">Calculé automatiquement</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="{{ route('commandes.index') }}" class="btn btn-secondary">
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
        calculerCoutReel();
    });
</script>
@endsection
