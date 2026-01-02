<!DOCTYPE html>
<html>
<head>
    <title>Test Commandes</title>
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
</head>
<body>
<div class="container mt-4">
    <h1>Liste des Commandes ({{ $commandes->total() }} total)</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Communes</th>
                <th>Coût Global</th>
                <th>Statut</th>
                <th>Date réception</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commandes as $commande)
            <tr>
                <td>{{ $commande->id }}</td>
                <td>{{ $commande->communes }}</td>
                <td>{{ $commande->cout_global }}</td>
                <td>{{ $commande->statut }}</td>
                <td>{{ $commande->date_reception }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $commandes->links() }}
</div>
</body>
</html>
