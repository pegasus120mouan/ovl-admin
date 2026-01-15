@extends('layout.main')

@section('title', 'Période de paie')
@section('page_title', 'Période de paie')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <h5 class="mb-1">{{ $periode->libelle }}</h5>
              <div>
                {{ $periode->date_debut ? \Carbon\Carbon::parse($periode->date_debut)->format('d/m/Y') : '' }}
                -
                {{ $periode->date_fin ? \Carbon\Carbon::parse($periode->date_fin)->format('d/m/Y') : '' }}
              </div>
              <div class="mt-1">
                Statut: <b>{{ $periode->statut }}</b>
              </div>
            </div>
            <div class="col-md-4 text-right">
              <form method="POST" action="{{ route('paies.periodes.destroy', $periode) }}" class="d-inline" onsubmit="return confirm('Supprimer cette période ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer</button>
              </form>
              <a href="{{ route('paies.periodes.index') }}" class="btn btn-default">Retour</a>
            </div>
          </div>

          <hr>

          <form method="POST" action="{{ route('paies.periodes.generer-fiches', $periode) }}">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Livreur (optionnel)</label>
                  <select class="form-control" name="livreur_id">
                    <option value="">Tous les livreurs</option>
                    @foreach($livreurs as $livreur)
                      <option value="{{ $livreur->id }}">{{ $livreur->nom ?? '' }} {{ $livreur->prenoms ?? '' }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Uniquement actifs</label>
                  <select class="form-control" name="uniquement_actifs">
                    <option value="1" selected>Oui</option>
                    <option value="0">Non</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sync"></i> Générer fiches</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Livreur</th>
                <th>Salaire base</th>
                <th>Ajustements</th>
                <th>Net à payer</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($fiches as $fiche)
                <tr>
                  <td>{{ $fiche->livreur->nom ?? 'N/A' }} {{ $fiche->livreur->prenoms ?? '' }}</td>
                  <td>{{ number_format($fiche->salaire_base ?? 0, 0, ',', ' ') }}</td>
                  <td>{{ number_format($fiche->total_ajustements ?? 0, 0, ',', ' ') }}</td>
                  <td><b>{{ number_format($fiche->net_a_payer ?? 0, 0, ',', ' ') }}</b></td>
                  <td>{{ $fiche->statut }}</td>
                  <td>
                    <a class="btn btn-sm btn-primary" href="{{ route('paies.fiches.show', $fiche) }}"><i class="fas fa-eye"></i></a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Aucune fiche</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $fiches->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
