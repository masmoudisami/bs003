<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Médecins</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; }
        form { margin-bottom: 20px; }
        input, textarea { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        .actions { width: 350px; white-space: nowrap; }
        .actions a { margin-right: 12px; text-decoration: none; display: inline-block; }
        .actions a:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 10px; text-decoration: none; color: #333; }
        .btn { padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-export { background: #17a2b8; }
        .btn-export:hover { background: #138496; }
        .top-nav { background: #333; padding: 10px 20px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.5em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
        .tabs { margin-bottom: 20px; border-bottom: 2px solid #ddd; }
        .tabs a { display: inline-block; padding: 10px 20px; text-decoration: none; color: #333; border: 1px solid #ddd; border-bottom: none; border-radius: 5px 5px 0 0; margin-right: 5px; }
        .tabs a.active { background: #007bff; color: #fff; border-color: #007bff; }
        .tabs a:hover { background: #e9ecef; }
        .tabs a.active:hover { background: #007bff; }
        .doctor-card { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #007bff; }
        .doctor-card h3 { margin: 0 0 10px 0; color: #333; }
        .doctor-card p { margin: 5px 0; color: #666; }
        .info-section { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .action-view { color: #6f42c1; font-weight: 500; }
        .action-view:hover { color: #5a32a3; }
        .action-edit { color: #007bff; font-weight: 500; }
        .action-edit:hover { color: #0056b3; }
        .action-delete { color: #dc3545; font-weight: 500; }
        .action-delete:hover { color: #c82333; }
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
            <a href="index.php?controller=doctor&action=index" class="<?= (!isset($_GET['action']) || $_GET['action'] === 'index') ? 'active' : '' ?>">📋 Liste des Médecins</a>
            <a href="index.php?controller=doctor&action=create" class="<?= (isset($_GET['action']) && $_GET['action'] === 'create') ? 'active' : '' ?>">➕ Nouveau Médecin</a>
        </div>
        
        <?php if (isset($doctor) && $doctor && isset($_GET['action']) && $_GET['action'] === 'viewData'): ?>
            <!-- Affichage des données complètes d'un médecin -->
            <div class="doctor-card">
                <h2>📊 Fiche Complète du Médecin</h2>
                <h3><?= htmlspecialchars($doctor['nom']) ?></h3>
                <p><strong>Spécialité :</strong> <?= htmlspecialchars($doctor['specialite']) ?></p>
                <p><strong>Adresse :</strong> <?= htmlspecialchars($doctor['adresse']) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($doctor['telephone']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($doctor['email']) ?></p>
                <p><strong>Date Création :</strong> <?= $doctor['created_at'] ?></p>
                <p><strong>Nombre de Bulletins :</strong> <?= $doctor['slip_count'] ?? 0 ?></p>
            </div>
            
            <div class="info-section">
                <h3>📈 Statistiques</h3>
                <p><strong>Nombre de bulletins associés :</strong> <?= $doctor['slip_count'] ?? 0 ?></p>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="index.php?controller=doctor&action=index" class="btn">📋 Retour Liste</a>
                <button onclick="window.print()" class="btn btn-export">🖨️ Imprimer / PDF</button>
            </div>
        <?php elseif (isset($doctor) && $doctor): ?>
            <!-- Formulaire de modification -->
            <h1>✏️ Modifier Médecin</h1>
            <form method="post" action="index.php?controller=doctor&action=update">
                <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
                <label>Nom <span style="color: #dc3545;">*</span></label>
                <input type="text" name="nom" value="<?= htmlspecialchars($doctor['nom']) ?>" required>
                <label>Spécialité <span style="color: #dc3545;">*</span></label>
                <input type="text" name="specialite" value="<?= htmlspecialchars($doctor['specialite']) ?>" required>
                <label>Adresse</label>
                <textarea name="adresse" rows="3"><?= htmlspecialchars($doctor['adresse']) ?></textarea>
                <label>Téléphone</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($doctor['telephone']) ?>">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($doctor['email']) ?>">
                <button type="submit">💾 Enregistrer</button>
            </form>
            <a href="index.php?controller=doctor&action=index" class="btn">📋 Retour Liste</a>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'create'): ?>
            <!-- Formulaire de création -->
            <h1>➕ Nouveau Médecin</h1>
            <form method="post" action="index.php?controller=doctor&action=store">
                <label>Nom <span style="color: #dc3545;">*</span></label>
                <input type="text" name="nom" required>
                <label>Spécialité <span style="color: #dc3545;">*</span></label>
                <input type="text" name="specialite" required>
                <label>Adresse</label>
                <textarea name="adresse" rows="3"></textarea>
                <label>Téléphone</label>
                <input type="text" name="telephone">
                <label>Email</label>
                <input type="email" name="email">
                <button type="submit">💾 Enregistrer</button>
            </form>
        <?php else: ?>
            <!-- Liste des médecins -->
            <div style="margin-bottom: 20px;">
                <a href="index.php?controller=doctor&action=exportCsv" class="btn btn-export">📊 Export CSV</a>
                <a href="index.php?controller=doctor&action=exportPdf" class="btn btn-export" onclick="window.print(); return false;">🖨️ Export PDF</a>
            </div>
            <h1>📋 Liste des Médecins</h1>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 250px;">Nom</th>
                        <th style="width: 200px;">Spécialité</th>
                        <th style="width: 150px;">Téléphone</th>
                        <th style="width: 350px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>
                            <td><?= htmlspecialchars($d['nom']) ?></td>
                            <td><?= htmlspecialchars($d['specialite']) ?></td>
                            <td><?= htmlspecialchars($d['telephone']) ?></td>
                            <td class="actions">
                                <a href="index.php?controller=doctor&action=viewData&id=<?= $d['id'] ?>" class="action-view">📊 Voir Fiche</a>
                                <a href="index.php?controller=doctor&action=edit&id=<?= $d['id'] ?>" class="action-edit">✏️ Modifier</a>
                                <a href="index.php?controller=doctor&action=delete&id=<?= $d['id'] ?>" class="action-delete" onclick="return confirm('Supprimer ce médecin ?')">🗑️ Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">Aucun médecin enregistré</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>