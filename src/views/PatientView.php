<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Patients</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        form { margin-bottom: 20px; }
        input { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007bff; }
        .actions a:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 10px; text-decoration: none; color: #333; }
        .btn { padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-export { background: #17a2b8; }
        .btn-export:hover { background: #138496; }
        .btn-green { background: #28a745; }
        .btn-green:hover { background: #218838; }
        .top-nav { background: #333; padding: 10px 20px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.5em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
        .tabs { margin-bottom: 20px; border-bottom: 2px solid #ddd; }
        .tabs a { display: inline-block; padding: 10px 20px; text-decoration: none; color: #333; border: 1px solid #ddd; border-bottom: none; border-radius: 5px 5px 0 0; margin-right: 5px; }
        .tabs a.active { background: #007bff; color: #fff; border-color: #007bff; }
        .tabs a:hover { background: #e9ecef; }
        .tabs a.active:hover { background: #007bff; }
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
        
        <div class="tabs no-print">
            <a href="index.php?controller=patient&action=index" class="<?= (!isset($_GET['action']) || $_GET['action'] === 'index') ? 'active' : '' ?>">📋 Liste des Patients</a>
            <a href="index.php?controller=patient&action=create" class="<?= (isset($_GET['action']) && $_GET['action'] === 'create') ? 'active' : '' ?>">➕ Nouveau Patient</a>
        </div>
        
        <?php if (isset($patient) && $patient): ?>
            <h1>✏️ Modifier Patient</h1>
            <form method="post" action="index.php?controller=patient&action=update">
                <input type="hidden" name="id" value="<?= $patient['id'] ?>">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($patient['nom']) ?>" required>
                <label>Date de Naissance</label>
                <input type="date" name="date_naissance" value="<?= $patient['date_naissance'] ?>" required>
                <button type="submit">💾 Enregistrer</button>
            </form>
            <a href="index.php?controller=patient&action=index" class="btn">📋 Retour Liste</a>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'create'): ?>
            <h1>➕ Nouveau Patient</h1>
            <form method="post" action="index.php?controller=patient&action=store">
                <label>Nom <span style="color: #dc3545;">*</span></label>
                <input type="text" name="nom" required>
                <label>Date de Naissance <span style="color: #dc3545;">*</span></label>
                <input type="date" name="date_naissance" required>
                <button type="submit">💾 Enregistrer</button>
            </form>
        <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="index.php?controller=patient&action=exportCsv" class="btn btn-export">📊 Export CSV</a>
                <a href="index.php?controller=patient&action=exportPdf" class="btn btn-export" onclick="window.print(); return false;">🖨️ Export PDF</a>
            </div>
            <h1>📋 Liste des Patients</h1>
            <table>
                <thead><tr><th>ID</th><th>Nom</th><th>Date Naissance</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['nom']) ?></td>
                            <td><?= $p['date_naissance'] ?></td>
                            <td class="actions">
                                <a href="index.php?controller=patient&action=history&id=<?= $p['id'] ?>">📁 Historique</a>
                                <a href="index.php?controller=patient&action=edit&id=<?= $p['id'] ?>">✏️ Modifier</a>
                                <a href="index.php?controller=patient&action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce patient ?')">🗑️ Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px;">Aucun patient enregistré</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>