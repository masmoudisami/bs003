<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données Médecins</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #6f42c1; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        .back { display: inline-block; margin-bottom: 10px; text-decoration: none; color: #333; }
        .btn { padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px; }
        .top-nav { background: #333; padding: 10px 20px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.5em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
        .stats { margin-bottom: 20px; display: flex; gap: 20px; flex-wrap: wrap; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; flex: 1; min-width: 200px; text-align: center; border-left: 4px solid #6f42c1; }
        .stat-box h3 { margin: 0; color: #333; font-size: 2em; }
        .stat-box p { margin: 5px 0 0 0; color: #666; font-weight: bold; }
        @media print { .no-print { display: none; } body { background: #fff; } .container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-nav no-print">
            <h1>🏥 Gestion Bulletins de Soins</h1>
            <div class="nav-links">
                <a href="index.php?controller=dashboard&action=index">📊 Tableau de Bord</a>
                <a href="index.php?controller=patient&action=index">👥 Patients</a>
                <a href="index.php?controller=doctor&action=index">👨‍⚕️ Médecins</a>
                <a href="index.php?controller=intervention&action=index">💉 Interventions</a>
            </div>
        </div>
        
        <a href="index.php?controller=doctor&action=index" class="back">← Retour Médecins</a>
        
        <h1>📊 Données Complètes des Médecins</h1>
        
        <div class="stats no-print">
            <div class="stat-box">
                <h3><?= count($doctors ?? []) ?></h3>
                <p>Total Médecins</p>
            </div>
            <div class="stat-box">
                <h3><?= count(array_unique(array_column($doctors ?? [], 'specialite'))) ?></h3>
                <p>Spécialités Différentes</p>
            </div>
        </div>
        
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
                <?php if (!empty($doctors)): ?>
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
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">Aucun médecin enregistré</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="no-print" style="margin-top: 20px;">
            <button onclick="window.print()" class="btn">🖨️ Imprimer / PDF</button>
        </div>
    </div>
</body>
</html>