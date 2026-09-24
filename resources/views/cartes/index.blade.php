@extends('layout.main')

@section('title', 'Gestion des cartes')
@section('page_title', 'Gestion des cartes')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
  .cartes-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem 1.25rem;
    align-items: center;
    margin-bottom: 1.25rem;
  }
  .cartes-toolbar .btn-carte {
    background: transparent;
    border: 0;
    color: #4b5563;
    font-weight: 500;
    padding: .35rem .25rem;
    box-shadow: none;
  }
  .cartes-toolbar .btn-carte i {
    color: #111827;
    margin-right: .35rem;
  }
  .cartes-toolbar .btn-carte:hover,
  .cartes-toolbar .btn-carte.is-active {
    color: #111827;
  }
  .cartes-toolbar .btn-carte.is-active {
    font-weight: 700;
  }
  .cartes-zones {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    margin: 0 0 1.1rem;
  }
  .cartes-zones .btn-zone {
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .85rem;
    line-height: 1.3;
  }
  .cartes-zones .btn-zone:hover,
  .cartes-zones .btn-zone.is-active {
    border-color: #111827;
    color: #111827;
    background: #f8fafc;
  }
  .cartes-zones .btn-zone.is-active {
    font-weight: 700;
  }
  .carte-card {
    border: 0;
    box-shadow: 0 0 0 1px #eef0f3;
    border-radius: .75rem;
    overflow: hidden;
  }
  .carte-card .card-header {
    background: #fff;
    border-bottom: 1px solid #eef0f3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .9rem 1.1rem;
  }
  .carte-card .card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
  }
  .carte-card .card-title i {
    margin-right: .4rem;
    color: #6b7280;
  }
  .carte-traces {
    color: #6b7280;
    font-size: .95rem;
  }
  #carte-map {
    height: min(72vh, 720px);
    min-height: 460px;
    width: 100%;
    background: #f8fafc;
  }
  .leaflet-popup-content {
    margin: 10px 12px;
    font-size: 13px;
  }
  .boutique-marker {
    background: transparent;
    border: 0;
  }
  .boutique-marker-pin {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    background: #f59e0b;
    color: #fff;
    box-shadow: 0 2px 6px rgba(17, 24, 39, .25);
  }
  .boutique-marker-pin i {
    transform: rotate(45deg);
    font-size: 12px;
  }
  .boutique-popup small {
    display: block;
    color: #6b7280;
    margin-top: 2px;
  }
</style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="cartes-toolbar">
    <button type="button" class="btn btn-carte" data-toggle="modal" data-target="#modalImporterGeojson">
      <i class="fas fa-plus-circle"></i> Importer GeoJSON
    </button>
    <a href="{{ route('cartes.index', array_filter(['vue' => 'regions', 'zone' => $zone])) }}" class="btn btn-carte {{ $vue === 'regions' ? 'is-active' : '' }}">
      <i class="fas fa-plus-circle"></i> Régions
    </a>
    <a href="{{ route('cartes.index', array_filter(['vue' => 'departements', 'zone' => $zone])) }}" class="btn btn-carte {{ $vue === 'departements' ? 'is-active' : '' }}">
      <i class="fas fa-plus-circle"></i> Départements
    </a>
    <button type="button" class="btn btn-carte" data-toggle="modal" data-target="#modalEnregistrerRegion">
      <i class="fas fa-plus-circle"></i> Enregistrer une région
    </button>
    <a href="{{ route('cartes.index', array_filter(['vue' => 'points', 'zone' => $zone])) }}" class="btn btn-carte {{ $vue === 'points' ? 'is-active' : '' }}">
      <i class="fas fa-plus-circle"></i> Localisation des points
    </a>
  </div>

  @if (count($zones))
  <div class="cartes-zones">
    <a href="{{ route('cartes.index', ['vue' => $vue]) }}" class="btn btn-zone {{ $zone === '' ? 'is-active' : '' }}">
      Toutes
    </a>
    @foreach ($zones as $nomZone)
      <a href="{{ route('cartes.index', ['vue' => $vue, 'zone' => $nomZone]) }}" class="btn btn-zone {{ strcasecmp($zone, $nomZone) === 0 ? 'is-active' : '' }}">
        {{ mb_convert_case(mb_strtolower($nomZone), MB_CASE_TITLE, 'UTF-8') }}
      </a>
    @endforeach
  </div>
  @endif

  <div class="card carte-card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-map"></i> {{ $titreCarte }}</h3>
      <span class="carte-traces">
        {{ $traces }} tracé(s)
        @if ($boutiquesCount)
          · {{ $boutiquesCount }} boutique(s)
        @endif
      </span>
    </div>
    <div class="card-body p-0">
      <div id="carte-map"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalImporterGeojson" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('cartes.import') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Importer un GeoJSON</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Le fichier remplace la carte existante du type choisi.</p>
          <div class="form-group">
            <label for="import-type">Type de carte</label>
            <select class="form-control" id="import-type" name="type" required>
              <option value="regions" {{ $vue === 'regions' ? 'selected' : '' }}>Régions</option>
              <option value="departements" {{ $vue === 'departements' ? 'selected' : '' }}>Départements</option>
              <option value="points" {{ $vue === 'points' ? 'selected' : '' }}>Points</option>
            </select>
          </div>
          <div class="form-group">
            <label for="import-nom">Nom (optionnel)</label>
            <input type="text" class="form-control" id="import-nom" name="nom" value="{{ old('nom') }}">
          </div>
          <div class="form-group mb-0">
            <label for="import-geojson">Fichier GeoJSON</label>
            <input type="file" class="form-control-file" id="import-geojson" name="geojson" accept=".geojson,.json,application/geo+json,application/json" required>
            @error('geojson')
              <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Importer et remplacer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalEnregistrerRegion" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('cartes.regions.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enregistrer une région</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Ajoute ou remplace une région à partir d’un GeoJSON. Un fichier avec plusieurs tracés remplace toute la carte des régions.</p>
          <div class="form-group">
            <label for="region-nom">Nom de la région</label>
            <input type="text" class="form-control" id="region-nom" name="nom" value="{{ old('nom') }}" required>
          </div>
          <div class="form-group mb-0">
            <label for="region-geojson">Fichier GeoJSON</label>
            <input type="file" class="form-control-file" id="region-geojson" name="geojson" accept=".geojson,.json,application/geo+json,application/json" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalAjouterPoint" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('cartes.points.store') }}" method="POST" id="formAjouterPoint">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Localiser un point</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="point-nom">Nom</label>
            <input type="text" class="form-control" id="point-nom" name="nom" required>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="point-latitude">Latitude</label>
              <input type="text" class="form-control" id="point-latitude" name="latitude" required>
            </div>
            <div class="form-group col-md-6">
              <label for="point-longitude">Longitude</label>
              <input type="text" class="form-control" id="point-longitude" name="longitude" required>
            </div>
          </div>
          <div class="form-group mb-0">
            <label for="point-description">Description</label>
            <input type="text" class="form-control" id="point-description" name="description">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer le point</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var vue = @json($vue);
  var geojson = @json($carteActive->geojson ?? ['type' => 'FeatureCollection', 'features' => []]);
  var points = @json($points);
  var boutiques = @json($boutiques);
  var colors = ['#c5d4eb', '#f3d4b8', '#d4e8c5', '#e8d4f0', '#f0e8c5', '#d4e8e8', '#f0d4d4', '#d9cfc3', '#c9e4de', '#f7d9c4'];
  var boutiqueIcon = L.divIcon({
    className: 'boutique-marker',
    html: '<span class="boutique-marker-pin"><i class="fas fa-store"></i></span>',
    iconSize: [28, 28],
    iconAnchor: [14, 26],
    popupAnchor: [0, -22]
  });

  var map = L.map('carte-map', {
    zoomControl: true,
    attributionControl: true
  }).setView([7.54, -5.5471], 7);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  function featureName(feature, index) {
    var properties = feature.properties || {};
    var keys = ['nom', 'area_name', 'NomDistric', 'NomRegion', 'NomCommune', 'name', 'NAME', 'Nom', 'NAME_1', 'NAME_2', 'NAME_3', 'shapeName', 'ADM1_FR', 'ADM2_FR'];
    for (var i = 0; i < keys.length; i++) {
      if (properties[keys[i]]) {
        return properties[keys[i]];
      }
    }
    return 'Tracé ' + (index + 1);
  }

  var colorIndex = 0;
  var layer = L.geoJSON(geojson, {
    style: function () {
      var color = colors[colorIndex % colors.length];
      colorIndex += 1;
      return {
        color: '#6b7280',
        weight: 1,
        fillColor: color,
        fillOpacity: 0.7
      };
    },
    pointToLayer: function (feature, latlng) {
      return L.circleMarker(latlng, {
        radius: 7,
        color: '#b91c1c',
        fillColor: '#ef4444',
        fillOpacity: 0.9,
        weight: 2
      });
    },
    onEachFeature: function (feature, layerItem) {
      var index = (geojson.features || []).indexOf(feature);
      layerItem.bindPopup('<strong>' + featureName(feature, index) + '</strong>');
    }
  }).addTo(map);

  points.forEach(function (point) {
    L.marker([point.latitude, point.longitude]).addTo(map)
      .bindPopup('<strong>' + point.nom + '</strong>');
  });

  boutiques.forEach(function (boutique) {
    var details = [boutique.commune, boutique.type_articles].filter(Boolean).join(' · ');
    var html = '<div class="boutique-popup"><strong>' + boutique.nom + '</strong>';
    if (details) {
      html += '<small>' + details + '</small>';
    }
    if (boutique.url) {
      html += '<small><a href="' + boutique.url + '">Voir la boutique</a></small>';
    }
    html += '</div>';
    L.marker([boutique.latitude, boutique.longitude], { icon: boutiqueIcon }).addTo(map)
      .bindPopup(html);
  });

  var bounds = L.latLngBounds([]);
  if (geojson.features && geojson.features.length && layer.getBounds().isValid()) {
    bounds.extend(layer.getBounds());
  }
  points.forEach(function (point) {
    bounds.extend([point.latitude, point.longitude]);
  });
  boutiques.forEach(function (boutique) {
    bounds.extend([boutique.latitude, boutique.longitude]);
  });
  if (bounds.isValid()) {
    map.fitBounds(bounds, { padding: [24, 24] });
  }

  map.on('click', function (event) {
    if (vue !== 'points') {
      return;
    }

    document.getElementById('point-latitude').value = event.latlng.lat.toFixed(7);
    document.getElementById('point-longitude').value = event.latlng.lng.toFixed(7);
    document.getElementById('point-nom').value = '';
    document.getElementById('point-description').value = '';
    $('#modalAjouterPoint').modal('show');
  });

  @if ($errors->has('geojson') || ($errors->has('type') && old('geojson')))
    $('#modalImporterGeojson').modal('show');
  @endif

  @if ($errors->has('nom') && old('geojson'))
    $('#modalEnregistrerRegion').modal('show');
  @endif
});
</script>
@endsection
