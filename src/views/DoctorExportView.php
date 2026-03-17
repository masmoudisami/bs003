<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Médecins</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        .no-print { display: none; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>📊 Liste Complète des Médecins</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Spécialité</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Date Création</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doctors as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['nom']) ?></td>
                <td><?= htmlspecialchars($d['specialite']) ?></td>
                <td><?= htmlspecialchars($d['adresse']) ?></td>
                <td><?= htmlspecialchars($d['telephone']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= $d['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>