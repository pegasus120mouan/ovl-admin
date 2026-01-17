@extends('layout.main')

@section('title', 'Dettes internes')
@section('page_title', 'Dettes internes')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-4 col-12">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ number_format($totalReste ?? 0, 0, ',', ' ') }}</h3>
          <p>Total reste</p>
        </div>
        <div class="icon"><i class="fas fa-balance-scale"></i></div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAjouterDette"><i class="fas fa-plus"></i> Ajouter une dette</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Remboursable</th>
                <th>Nom</th>
                <th>Montant</th>
                <th>Payé</th>
                <th>Reste</th>
                <th>Date</th>
                <th>Échéance</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($dettes as $dette)
              <tr>
                <td>{{ ($dette->remboursable ?? true) ? 'Oui' : 'Non' }}</td>
                <td>{{ $dette->nom_debiteur }}</td>
                <td>{{ number_format($dette->montant_actuel ?? 0, 0, ',', ' ') }}</td>
                <td>{{ number_format($dette->montants_payes ?? 0, 0, ',', ' ') }}</td>
                <td><span class="font-weight-bold {{ ($dette->reste ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($dette->reste ?? 0, 0, ',', ' ') }}</span></td>
                <td>{{ $dette->date_dette ? \Carbon\Carbon::parse($dette->date_dette)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $dette->date_echeance ? \Carbon\Carbon::parse($dette->date_echeance)->format('d/m/Y') : '-' }}</td>
                <td>
                  @if(($dette->reste ?? 0) > 0)
                    <span class="badge badge-warning">En cours</span>
                  @else
                    <span class="badge badge-success">Soldée</span>
                  @endif
                </td>
                <td>
                  <a href="#" class="btn btn-sm btn-info {{ ($dette->remboursable ?? true) ? '' : 'disabled' }}" data-toggle="modal" data-target="#modalVersement{{ $dette->id }}" {{ ($dette->remboursable ?? true) ? '' : 'aria-disabled=true tabindex=-1' }}><i class="fas fa-coins"></i></a>
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalModifierDette{{ $dette->id }}"><i class="fas fa-edit"></i></a>
                  <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalConfirmDeleteDette" data-action="{{ route('dettes-internes.destroy', $dette->id) }}"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center">Aucune dette</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjouterDette" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une dette interne</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('dettes-internes.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Remboursement</label>
            <select class="form-control" name="remboursable" id="remboursable_add" required>
              <option value="1" selected>Oui (remboursable)</option>
              <option value="0">Non (non remboursable)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Nom (personne/service)</label>
            <input type="text" class="form-control" name="nom_debiteur" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Motifs</label>
            <textarea class="form-control" name="motifs" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Montant</label>
            <input type="number" class="form-control" name="montant_initial" min="0" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date dette</label>
            <input type="date" class="form-control" name="date_dette" id="date_dette_add" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date échéance</label>
            <input type="date" class="form-control" name="date_echeance" id="date_echeance_add">
          </div>
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-primary">Enregister</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($dettes as $dette)
<div class="modal fade" id="modalModifierDette{{ $dette->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier la dette</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('dettes-internes.update', $dette->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Remboursement</label>
            <select class="form-control" name="remboursable" data-remboursable="1" data-dette-id="{{ $dette->id }}" required>
              <option value="1" {{ ($dette->remboursable ?? true) ? 'selected' : '' }}>Oui (remboursable)</option>
              <option value="0" {{ !($dette->remboursable ?? true) ? 'selected' : '' }}>Non (non remboursable)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Nom</label>
            <input type="text" class="form-control" name="nom_debiteur" value="{{ $dette->nom_debiteur }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Motifs</label>
            <textarea class="form-control" name="motifs" rows="2">{{ $dette->motifs ?? '' }}</textarea>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Montant actuel</label>
            <input type="number" class="form-control" name="montant_actuel" min="0" value="{{ (int) ($dette->montant_actuel ?? 0) }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date dette</label>
            <input type="date" class="form-control" name="date_dette" id="date_dette_edit_{{ $dette->id }}" value="{{ $dette->date_dette ? \Carbon\Carbon::parse($dette->date_dette)->format('Y-m-d') : '' }}" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date échéance</label>
            <input type="date" class="form-control" name="date_echeance" id="date_echeance_edit_{{ $dette->id }}" value="{{ $dette->date_echeance ? \Carbon\Carbon::parse($dette->date_echeance)->format('Y-m-d') : '' }}">
          </div>
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-warning">Modifier</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalVersement{{ $dette->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Versement</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="{{ route('dettes-internes.versements.store', $dette->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Montant à verser</label>
            <input type="number" class="form-control" name="montant_versement" min="1" required>
            <small class="text-muted">Reste: {{ number_format($dette->reste ?? 0, 0, ',', ' ') }}</small>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Date versement</label>
            <input type="date" class="form-control" name="date_versement" value="{{ date('Y-m-d') }}">
          </div>

          @if(($dette->versements ?? collect())->count())
          <div class="mt-3">
            <div class="font-weight-bold mb-2">Historique</div>
            <div class="table-responsive p-0">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Montant</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dette->versements as $v)
                  <tr>
                    <td>{{ $v->date_versement ? \Carbon\Carbon::parse($v->date_versement)->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ number_format($v->montant_versement ?? 0, 0, ',', ' ') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @endif
        </div>
        <div class="modal-footer justify-content-start">
          <button type="submit" class="btn btn-info">Enregistrer</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<div class="modal fade" id="modalConfirmDeleteDette" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        Voulez-vous vraiment supprimer cette dette ?
      </div>
      <div class="modal-footer justify-content-start">
        <form id="deleteDetteForm" method="POST" action="#" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
        <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var form = document.getElementById('deleteDetteForm');
    if (!form) {
      return;
    }

    var bindAction = function (btn) {
      if (!btn) {
        return;
      }
      var action = btn.getAttribute('data-action');
      if (action) {
        form.setAttribute('action', action);
      }
    };

    document.querySelectorAll('[data-target="#modalConfirmDeleteDette"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        bindAction(btn);
      });
    });

    if (window.jQuery) {
      window.jQuery('#modalConfirmDeleteDette').on('show.bs.modal', function (event) {
        bindAction(event.relatedTarget);
      });
    }
  })();
</script>

<script>
  (function () {
    var sync = function (remboursableEl, dateDetteEl, dateEcheanceEl) {
      if (!remboursableEl || !dateDetteEl || !dateEcheanceEl) {
        return;
      }

      var apply = function () {
        var isRemboursable = String(remboursableEl.value) === '1';
        if (!isRemboursable) {
          dateEcheanceEl.value = dateDetteEl.value;
          dateEcheanceEl.setAttribute('readonly', 'readonly');
          dateEcheanceEl.setAttribute('disabled', 'disabled');
        } else {
          dateEcheanceEl.removeAttribute('readonly');
          dateEcheanceEl.removeAttribute('disabled');
        }
      };

      remboursableEl.addEventListener('change', apply);
      dateDetteEl.addEventListener('change', function () {
        if (String(remboursableEl.value) === '0') {
          dateEcheanceEl.value = dateDetteEl.value;
        }
      });

      apply();
    };

    sync(
      document.getElementById('remboursable_add'),
      document.getElementById('date_dette_add'),
      document.getElementById('date_echeance_add')
    );

    document.querySelectorAll('select[name="remboursable"][data-dette-id]').forEach(function (remb) {
      var id = remb.getAttribute('data-dette-id');
      sync(
        remb,
        document.getElementById('date_dette_edit_' + id),
        document.getElementById('date_echeance_edit_' + id)
      );
    });
  })();
</script>
@endsection
