<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Interventions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h1>Liste des Types d'Intervention</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($interventions as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= htmlspecialchars($i['libelle']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>