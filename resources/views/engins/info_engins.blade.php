@extends('layout.main')

@section('title', "Infos engin")
@section('page_title', "Infos engin")

@section('content')
@php
  $fallbackImageUrl = asset('img/photo1.png');
  $imageFields = ['image_1', 'image_2', 'image_3', 'image_4'];
  $imageUrls = [];

  $disk = null;
  try {
    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
  } catch (\Throwable $e) {
    $disk = null;
  }

  foreach ($imageFields as $field) {
    $filename = (string) ($engin->{$field} ?? '');
    if ($filename === '') {
      $imageUrls[$field] = $fallbackImageUrl;
      continue;
    }

    if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
      $imageUrls[$field] = $filename;
      continue;
    }

    $key = $filename;
    if (!str_contains($key, '/')) {
      $key = 'engins/' . $key;
    }

    if ($disk) {
      try {
        $imageUrls[$field] = $disk->url($key);
        continue;
      } catch (\Throwable $e) {
      }
    }

    if (file_exists(public_path('img/engins/' . $filename))) {
      $imageUrls[$field] = asset('img/engins/' . $filename);
    } else {
      $imageUrls[$field] = $fallbackImageUrl;
    }
  }

  $mainImageUrl = $imageUrls['image_1'] ?? $fallbackImageUrl;
@endphp

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-7">
      <div class="card">
        <div class="card-body text-center">
          <img id="enginMainImage" src="{{ $mainImageUrl }}" alt="Engin" style="max-width: 100%; height: auto;">
          <div class="mt-3">
            @foreach($imageFields as $field)
              <a href="#" class="d-inline-block mr-2" onclick="event.preventDefault(); document.getElementById('enginMainImage').src='{{ $imageUrls[$field] }}';">
                <img src="{{ $imageUrls[$field] }}" alt="{{ $field }}" style="width: 70px; height: 50px; object-fit: cover; border: 1px solid #e9ecef; border-radius: 4px;">
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Modifier les images de l'engin</h3>
        </div>
        <form action="{{ route('engins.images.update', $engin) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="card-body">
            <div class="form-group">
              <input type="file" class="form-control" name="image_1" accept="image/*">
            </div>
            <div class="form-group">
              <input type="file" class="form-control" name="image_2" accept="image/*">
            </div>
            <div class="form-group">
              <input type="file" class="form-control" name="image_3" accept="image/*">
            </div>
            <div class="form-group">
              <input type="file" class="form-control" name="image_4" accept="image/*">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-info">Modifier les images de la moto</button>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-body" style="background: #6c757d; color: #fff;">
          <h4 class="m-0">{{ $engin->numero_chassis }}</h4>
          <div class="mt-1">{{ $engin->statut }}</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informations</h3>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <tbody>
              <tr>
                <th style="width: 40%;">Type</th>
                <td>{{ $engin->type_engin }}</td>
              </tr>
              <tr>
                <th>Marque</th>
                <td>{{ $engin->marque }}</td>
              </tr>
              <tr>
                <th>Année</th>
                <td>{{ $engin->annee_fabrication }}</td>
              </tr>
              <tr>
                <th>Plaque</th>
                <td>{{ $engin->plaque_immatriculation }}</td>
              </tr>
              <tr>
                <th>Couleur</th>
                <td>{{ $engin->couleur }}</td>
              </tr>
              <tr>
                <th>Date ajout</th>
                <td>{{ $engin->date_ajout }}</td>
              </tr>
              <tr>
                <th>Livreur</th>
                <td>
                  @if($engin->utilisateur)
                    {{ $engin->utilisateur->nom }} {{ $engin->utilisateur->prenoms }}
                  @else
                    -
                  @endif
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
