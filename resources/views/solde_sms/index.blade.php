@extends('layout.main')

@section('title', 'Solde SMS')
@section('page_title', 'Solde SMS')

@section('content')
<div class="container-fluid">
  @if($error)
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
    </div>
  @endif

  <div class="row">
    <div class="col-lg-4 col-md-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ isset($balance['sms_disponibles']) ? number_format($balance['sms_disponibles'], 0, ',', ' ') : '—' }}</h3>
          <p>SMS disponibles</p>
        </div>
        <div class="icon">
          <i class="fas fa-comment-dots"></i>
        </div>
      </div>
    </div>

    @if(!empty($balance['wallet_balance']))
    <div class="col-lg-4 col-md-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ number_format((float) $balance['wallet_balance'], 0, ',', ' ') }}</h3>
          <p>Portefeuille ({{ $balance['wallet_currency'] ?? 'XOF' }})</p>
        </div>
        <div class="icon">
          <i class="fas fa-wallet"></i>
        </div>
      </div>
    </div>
    @endif
  </div>

  <div class="row">
    <div class="col-lg-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-sms mr-2"></i>Détails du compte HSMS</h3>
          <div class="card-tools">
            <a href="{{ route('solde-sms.index') }}" class="btn btn-sm btn-primary">
              <i class="fas fa-sync-alt mr-1"></i> Actualiser
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <tbody>
              <tr>
                <th style="width: 40%">Application</th>
                <td>{{ $balance['application'] ?? '—' }}</td>
              </tr>
              <tr>
                <th>SMS disponibles</th>
                <td>
                  @if(isset($balance['sms_disponibles']))
                    <span class="badge badge-info">{{ number_format($balance['sms_disponibles'], 0, ',', ' ') }}</span>
                  @else
                    —
                  @endif
                </td>
              </tr>
              @if(!empty($balance['wallet_balance']))
              <tr>
                <th>Solde portefeuille</th>
                <td>{{ number_format((float) $balance['wallet_balance'], 2, ',', ' ') }} {{ $balance['wallet_currency'] ?? 'XOF' }}</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
