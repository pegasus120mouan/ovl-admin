@php
    $nomsMois = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];
    $periode = preg_match('/^\d{4}-\d{2}$/', (string) ($mois ?? '')) === 1 ? $mois : now()->format('Y-m');
    $anneeChoisie = (int) substr($periode, 0, 4);
    $moisChoisi = (int) substr($periode, 5, 2);
    $annees = range(now()->year - 2, now()->year + 2);
    if (! in_array($anneeChoisie, $annees, true)) {
        $annees[] = $anneeChoisie;
        sort($annees);
    }
    $taille = $taille ?? '';
@endphp
<select name="mois_num" class="form-control {{ $taille }} mr-2" aria-label="Mois" @if (! empty($auto)) style="width: auto;" @endif required>
    @foreach ($nomsMois as $numero => $nom)
        <option value="{{ $numero }}" @selected($numero === $moisChoisi)>{{ $nom }}</option>
    @endforeach
</select>
<select name="annee" class="form-control {{ $taille }} mr-2" aria-label="Année" style="width: 96px;" required>
    @foreach ($annees as $annee)
        <option value="{{ $annee }}" @selected($annee === $anneeChoisie)>{{ $annee }}</option>
    @endforeach
</select>
