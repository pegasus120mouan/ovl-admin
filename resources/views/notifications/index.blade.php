@extends('layout.main')

@section('title', 'Notifications')
@section('page_title', 'Notifications')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-4 col-md-6">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-bell mr-2"></i>Enregistrer un numéro</h3>
        </div>
        <form action="{{ route('notifications.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label for="telephone"><i class="fas fa-phone mr-1"></i>Numéro de téléphone</label>
              <input
                type="text"
                name="telephone"
                id="telephone"
                class="form-control @error('telephone') is-invalid @enderror"
                placeholder="Ex: 07 00 00 00 00"
                value="{{ old('telephone') }}"
                required
                autofocus
              >
              @error('telephone')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
              <small class="form-text text-muted">Format local (07XXXXXXXX) ou international (22507XXXXXXXX)</small>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save mr-1"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-8 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list mr-2"></i>Numéros enregistrés</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Numéro de téléphone</th>
                <th>Date d'enregistrement</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($numeros as $numero)
              <tr>
                <td>{{ $numero->id }}</td>
                <td>{{ $numero->telephone }}</td>
                <td>{{ $numero->created_at ? $numero->created_at->format('d/m/Y H:i') : '-' }}</td>
                <td>
                  <form action="{{ route('notifications.destroy', $numero) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce numéro ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center">Aucun numéro enregistré</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
