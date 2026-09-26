@php
  $estLivreur = $dette && $dette->livreur_id;
@endphp
<div class="js-debiteur">
  <div class="form-group">
    <label class="font-weight-bold">Débiteur</label>
    <select class="form-control js-debiteur-type" name="debiteur_type" required>
      <option value="livreur" {{ $estLivreur ? 'selected' : '' }}>Un livreur</option>
      <option value="autre" {{ $dette && !$estLivreur ? 'selected' : '' }}>Autre personne / service</option>
    </select>
  </div>
  <div class="js-debiteur-livreur">
    <div class="form-group">
      <label class="font-weight-bold">Livreur</label>
      <select class="form-control" name="livreur_id">
        <option value="">-- Sélectionner un livreur --</option>
        @foreach($livreurs as $livreur)
          <option value="{{ $livreur->id }}" {{ $dette && (int) $dette->livreur_id === (int) $livreur->id ? 'selected' : '' }}>
            {{ trim($livreur->nom . ' ' . $livreur->prenoms) }}{{ $livreur->statut_compte ? '' : ' (inactif)' }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label class="font-weight-bold">Type de dette</label>
      <select class="form-control" name="type_dette">
        @foreach(\App\Models\Dette::TYPES_LIVREUR as $typeDette)
          <option value="{{ $typeDette }}" {{ $dette && $dette->type === $typeDette ? 'selected' : '' }}>{{ $typeDette }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="form-group js-debiteur-autre">
    <label class="font-weight-bold">Nom (personne/service)</label>
    <input type="text" class="form-control" name="nom_debiteur" value="{{ $dette && !$estLivreur ? $dette->nom_debiteur : '' }}">
  </div>
</div>
