<!-- Modal confirmation modification inline -->
<div class="modal fade" id="modalConfirmInlineEdit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
          <i class="fas fa-edit mr-2"></i>Confirmer la modification
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Voulez-vous enregistrer cette modification ?</p>
        <table class="table table-sm table-bordered mb-0">
          <tbody>
            <tr>
              <th style="width: 35%">Commande</th>
              <td id="inlineEditCommandeId">—</td>
            </tr>
            <tr>
              <th>Champ</th>
              <td id="inlineEditFieldLabel">—</td>
            </tr>
            <tr>
              <th>Ancienne valeur</th>
              <td id="inlineEditOldValue">—</td>
            </tr>
            <tr>
              <th>Nouvelle valeur</th>
              <td id="inlineEditNewValue" class="font-weight-bold text-primary">—</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnInlineEditCancel">
          <i class="fas fa-times mr-1"></i>Annuler
        </button>
        <button type="button" class="btn btn-warning" id="btnInlineEditConfirm">
          <i class="fas fa-save mr-1"></i>Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
window.commandeInlineOptions = {
  coutsLivraison: @json($coutsLivraison->pluck('cout_livraison')->values()),
  clients: @json($boutiques->flatMap(function ($boutique) {
    return $boutique->utilisateurs->map(function ($utilisateur) use ($boutique) {
      return ['value' => $utilisateur->id, 'label' => $boutique->nom];
    });
  })->values()),
  livreurs: @json($livreurs->map(function ($livreurItem) {
    return ['value' => $livreurItem->id, 'label' => trim(($livreurItem->nom ?? '') . ' ' . ($livreurItem->prenoms ?? ''))];
  })->values()),
  statuts: ['Non Livré', 'Livré', 'Retour'],
  icones: {
    'Livré': @json(asset('img/icones/ok.png')),
    'Non Livré': @json(asset('img/icones/non_ok.png')),
    'Retour': @json(asset('img/icones/return.png'))
  },
  fieldLabels: {
    communes: 'Communes',
    cout_global: 'Coût Global',
    cout_livraison: 'Livraison',
    cout_reel: 'Coût réel',
    utilisateur_id: 'Boutique',
    livreur_id: 'Livreur',
    statut: 'Statut',
    date_reception: 'Date réception',
    date_livraison: 'Date livraison',
    date_retour: 'Date retour'
  }
};

(function () {
  var activeCell = null;
  var pendingSave = null;

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('fr-FR');
  }

  function formatDateDisplay(iso) {
    if (!iso) return 'N/A';
    var parts = iso.split('-');
    if (parts.length !== 3) return iso;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
  }

  function renderStatut(statut) {
    var icon = window.commandeInlineOptions.icones[statut];
    if (icon) {
      return '<img src="' + icon + '" alt="' + statut + '" title="' + statut + '" style="height:30px; width:auto;">';
    }
    return '<span class="badge badge-secondary">' + statut + '</span>';
  }

  function renderLivreur(nom) {
    if (nom) {
      return document.createTextNode(nom).textContent;
    }
    return '<span class="badge badge-warning">Pas de livreur attribué</span>';
  }

  function renderDateLivraison(iso) {
    if (!iso) {
      return '<span class="badge badge-secondary">Pas encore livré</span>';
    }
    return formatDateDisplay(iso);
  }

  function getOptionLabel(type, value) {
    if (value === null || value === undefined || value === '') {
      return '—';
    }

    if (type === 'select-cout') {
      return formatNumber(value);
    }

    if (type === 'select-client') {
      var client = window.commandeInlineOptions.clients.find(function (item) {
        return String(item.value) === String(value);
      });
      return client ? client.label : String(value);
    }

    if (type === 'select-livreur') {
      if (!value) return 'Pas de livreur attribué';
      var livreur = window.commandeInlineOptions.livreurs.find(function (item) {
        return String(item.value) === String(value);
      });
      return livreur ? livreur.label : String(value);
    }

    if (type === 'select-statut') {
      return String(value);
    }

    if (type === 'number') {
      return formatNumber(value);
    }

    if (type === 'date') {
      return formatDateDisplay(value);
    }

    return String(value);
  }

  function updateRowDisplay(row, commande) {
    var communesCell = row.querySelector('[data-field="communes"]');
    if (communesCell) {
      communesCell.innerHTML = commande.communes || '';
      communesCell.dataset.value = commande.communes || '';
      communesCell.title = commande.communes || '';
    }

    row.querySelector('[data-field="cout_global"]').innerHTML = formatNumber(commande.cout_global);
    row.querySelector('[data-field="cout_global"]').dataset.value = commande.cout_global;

    row.querySelector('[data-field="cout_livraison"]').innerHTML = formatNumber(commande.cout_livraison);
    row.querySelector('[data-field="cout_livraison"]').dataset.value = commande.cout_livraison;

    row.querySelector('[data-field="cout_reel"]').innerHTML = formatNumber(commande.cout_reel);
    row.querySelector('[data-field="cout_reel"]').dataset.value = commande.cout_reel;

    row.querySelector('[data-field="utilisateur_id"]').innerHTML = commande.boutique_nom || 'N/A';
    row.querySelector('[data-field="utilisateur_id"]').dataset.value = commande.utilisateur_id || '';

    var livreurCell = row.querySelector('[data-field="livreur_id"]');
    livreurCell.innerHTML = renderLivreur(commande.livreur_nom);
    livreurCell.dataset.value = commande.livreur_id || '';

    var statutCell = row.querySelector('[data-field="statut"]');
    statutCell.innerHTML = renderStatut(commande.statut);
    statutCell.dataset.value = commande.statut;

    var dateReceptionCell = row.querySelector('[data-field="date_reception"]');
    dateReceptionCell.innerHTML = formatDateDisplay(commande.date_reception);
    dateReceptionCell.dataset.value = commande.date_reception || '';

    var dateLivraisonCell = row.querySelector('[data-field="date_livraison"]');
    dateLivraisonCell.innerHTML = renderDateLivraison(commande.date_livraison);
    dateLivraisonCell.dataset.value = commande.date_livraison || '';

    var dateRetourCell = row.querySelector('[data-field="date_retour"]');
    dateRetourCell.innerHTML = formatDateDisplay(commande.date_retour);
    dateRetourCell.dataset.value = commande.date_retour || '';
  }

  function buildSelect(options, currentValue, emptyLabel) {
    var select = document.createElement('select');
    select.className = 'form-control form-control-sm';

    if (emptyLabel !== undefined) {
      var emptyOption = document.createElement('option');
      emptyOption.value = '';
      emptyOption.textContent = emptyLabel;
      select.appendChild(emptyOption);
    }

    options.forEach(function (option) {
      var opt = document.createElement('option');
      opt.value = String(option.value ?? option);
      opt.textContent = option.label ?? String(option);
      if (String(currentValue) === String(opt.value)) {
        opt.selected = true;
      }
      select.appendChild(opt);
    });

    return select;
  }

  function buildEditor(cell) {
    var type = cell.dataset.type;
    var value = cell.dataset.value || '';

    if (type === 'text') {
      var textInput = document.createElement('input');
      textInput.type = 'text';
      textInput.className = 'form-control form-control-sm';
      textInput.value = value;
      return textInput;
    }

    if (type === 'number') {
      var numberInput = document.createElement('input');
      numberInput.type = 'number';
      numberInput.className = 'form-control form-control-sm';
      numberInput.value = value;
      return numberInput;
    }

    if (type === 'date') {
      var dateInput = document.createElement('input');
      dateInput.type = 'date';
      dateInput.className = 'form-control form-control-sm';
      dateInput.value = value;
      return dateInput;
    }

    if (type === 'select-cout') {
      return buildSelect(
        window.commandeInlineOptions.coutsLivraison.map(function (cout) {
          return { value: cout, label: cout };
        }),
        value
      );
    }

    if (type === 'select-client') {
      return buildSelect(window.commandeInlineOptions.clients, value);
    }

    if (type === 'select-livreur') {
      return buildSelect(window.commandeInlineOptions.livreurs, value, 'Pas de livreur attribué');
    }

    if (type === 'select-statut') {
      return buildSelect(
        window.commandeInlineOptions.statuts.map(function (statut) {
          return { value: statut, label: statut };
        }),
        value
      );
    }

    return null;
  }

  function cancelEdit(cell) {
    if (!cell || !cell.dataset.originalHtml) return;
    cell.innerHTML = cell.dataset.originalHtml;
    cell.classList.remove('is-editing', 'is-saving');
    delete cell.dataset.originalHtml;
    activeCell = null;
    pendingSave = null;
  }

  function buildPayload(cell, row, field, newValue) {
    var payload = {};
    payload[field] = newValue === '' ? null : newValue;

    if (field === 'cout_global' || field === 'cout_livraison') {
      payload.cout_global = field === 'cout_global'
        ? newValue
        : row.querySelector('[data-field="cout_global"]').dataset.value;
      payload.cout_livraison = field === 'cout_livraison'
        ? newValue
        : row.querySelector('[data-field="cout_livraison"]').dataset.value;
    }

    return payload;
  }

  function executeSaveEdit() {
    if (!pendingSave) return;

    var cell = pendingSave.cell;
    var row = pendingSave.row;
    var payload = pendingSave.payload;

    cell.classList.add('is-saving');

    fetch(row.dataset.updateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(Object.assign({ _method: 'PUT' }, payload))
    })
    .then(function (response) {
      return response.json().then(function (data) {
        if (!response.ok) {
          var message = data.message;
          if (data.errors) {
            message = Object.values(data.errors).flat().join('\n');
          }
          throw new Error(message || 'Erreur lors de la mise à jour');
        }
        return data;
      });
    })
    .then(function (data) {
      updateRowDisplay(row, data.commande);
      cell.classList.remove('is-editing', 'is-saving');
      delete cell.dataset.originalHtml;
      activeCell = null;
      pendingSave = null;
      $('#modalConfirmInlineEdit').modal('hide');
    })
    .catch(function (error) {
      alert(error.message || 'Impossible de mettre à jour la commande.');
      cancelEdit(cell);
      cell.classList.remove('is-saving');
      $('#modalConfirmInlineEdit').modal('hide');
    });
  }

  function confirmSaveEdit(cell) {
    var row = cell.closest('tr');
    var editor = cell.querySelector('input, select');
    if (!editor || !row) return;

    var field = cell.dataset.field;
    var type = cell.dataset.type;
    var newValue = editor.value;
    var oldValue = cell.dataset.value || '';

    if (String(newValue) === String(oldValue)) {
      cancelEdit(cell);
      return;
    }

    pendingSave = {
      cell: cell,
      row: row,
      payload: buildPayload(cell, row, field, newValue),
      fieldLabel: window.commandeInlineOptions.fieldLabels[field] || field,
      oldDisplay: getOptionLabel(type, oldValue),
      newDisplay: getOptionLabel(type, newValue)
    };

    document.getElementById('inlineEditCommandeId').textContent = '#' + (row.dataset.commandeId || '—');
    document.getElementById('inlineEditFieldLabel').textContent = pendingSave.fieldLabel;
    document.getElementById('inlineEditOldValue').textContent = pendingSave.oldDisplay;
    document.getElementById('inlineEditNewValue').textContent = pendingSave.newDisplay;

    $('#modalConfirmInlineEdit').modal('show');
  }

  function startEdit(cell) {
    if (cell.classList.contains('editable-readonly')) return;
    if (activeCell && activeCell !== cell) {
      cancelEdit(activeCell);
    }

    activeCell = cell;
    cell.dataset.originalHtml = cell.innerHTML;
    cell.classList.add('is-editing');
    cell.innerHTML = '';

    var editor = buildEditor(cell);
    if (!editor) {
      cancelEdit(cell);
      return;
    }

    cell.appendChild(editor);
    editor.focus();

    if (editor.tagName === 'SELECT') {
      editor.addEventListener('change', function () {
        confirmSaveEdit(cell);
      });
      editor.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          cancelEdit(cell);
        }
      });
      return;
    }

    editor.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        confirmSaveEdit(cell);
      }
      if (event.key === 'Escape') {
        cancelEdit(cell);
      }
    });

    editor.addEventListener('blur', function () {
      setTimeout(function () {
        if (activeCell === cell && !$('#modalConfirmInlineEdit').hasClass('show')) {
          confirmSaveEdit(cell);
        }
      }, 120);
    });
  }

  document.addEventListener('click', function (event) {
    var cell = event.target.closest('.editable-cell');
    if (!cell || cell.classList.contains('editable-readonly')) return;
    if (event.target.closest('a, button')) return;
    if (cell.classList.contains('is-editing')) return;
    startEdit(cell);
  });

  document.getElementById('btnInlineEditConfirm').addEventListener('click', executeSaveEdit);

  document.getElementById('btnInlineEditCancel').addEventListener('click', function () {
    if (pendingSave && pendingSave.cell) {
      cancelEdit(pendingSave.cell);
    }
    pendingSave = null;
  });

  $('#modalConfirmInlineEdit').on('hidden.bs.modal', function () {
    if (pendingSave && pendingSave.cell && pendingSave.cell.classList.contains('is-saving')) {
      return;
    }
    if (pendingSave && pendingSave.cell && pendingSave.cell.classList.contains('is-editing')) {
      cancelEdit(pendingSave.cell);
    }
    pendingSave = null;
  });
})();
</script>
@endpush
