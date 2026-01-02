<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>OVL Admin | Connexion</title>

  <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">
  <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

  <style>
    .auth-split {
      min-height: 100vh;
      display: flex;
    }
    .auth-left {
      flex: 1;
      background: #ffffff;
      display: none;
    }
    .auth-right {
      flex: 1;
      background: #799659ff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }
    .auth-right::after {
      content: "";
      position: absolute;
      right: -120px;
      bottom: -120px;
      width: 360px;
      height: 360px;
      border: 2px solid rgba(255,255,255,.35);
      border-radius: 50%;
    }
    .auth-right::before {
      content: "";
      position: absolute;
      right: -60px;
      bottom: -60px;
      width: 240px;
      height: 240px;
      border: 2px solid rgba(255,255,255,.35);
      border-radius: 50%;
    }
    .auth-illustration {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px;
    }
    .auth-illustration img {
      max-width: 520px;
      width: 100%;
      height: auto;
    }
    .auth-card {
      width: 100%;
      max-width: 420px;
      border-radius: 14px;
      overflow: hidden;
      z-index: 1;
    }
    .auth-card .card-body {
      padding: 28px;
    }
    .auth-pill {
      border-radius: 999px;
    }
    .auth-input {
      border-radius: 999px;
      padding-left: 44px;
      height: 46px;
    }
    .auth-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #8b96a5;
    }
    .auth-input-wrap {
      position: relative;
    }
    @media (min-width: 992px) {
      .auth-left { display: flex; }
    }
  </style>
</head>
<body class="hold-transition">

<div class="auth-split">
  <div class="auth-left">
    <div class="auth-illustration">
      <img src="{{ asset('img/logo.png') }}" alt="Illustration">
    </div>
  </div>

  <div class="auth-right">
    <div class="card auth-card shadow">
      <div class="card-body">
        <div class="text-center mb-3">
          <img src="{{ asset('img/logo.png') }}" alt="OVL" class="img-circle elevation-2" style="width: 56px; height: 56px; object-fit: cover;">
        </div>
        <h3 class="font-weight-bold mb-1">Bienvenue sur OVL Admin</h3>
        <p class="text-muted mb-4">Connectez-vous pour continuer</p>

      @if(session('success'))
        <div class="alert alert-success" role="alert">
          <i class="fas fa-check-circle mr-1"></i>
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger" role="alert">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <div class="auth-input-wrap mb-3">
          <i class="fas fa-user auth-icon"></i>
          <input type="text" name="login" class="form-control auth-input @error('login') is-invalid @enderror" placeholder="Login" value="{{ old('login') }}" required autofocus>
        </div>

        <div class="auth-input-wrap mb-3">
          <i class="fas fa-lock auth-icon"></i>
          <input type="password" name="password" class="form-control auth-input @error('password') is-invalid @enderror" placeholder="Mot de passe" required>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3" style="gap: 12px;">
          <div class="icheck-primary mb-0">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember" class="mb-0">
              Se souvenir
            </label>
          </div>
          <a href="#" class="text-white-50" style="font-size: 13px;">Mot de passe oublié</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block auth-pill" style="height: 46px;">Login</button>
      </form>

        <div class="mt-4 text-center text-white-50" style="font-size: 12px;">
          © {{ date('Y') }} OVL Delivery
        </div>

      </div>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>
</html>
