<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Gestion Bulletins de Soins</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 1400px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .filters { margin-bottom: 20px; padding: 15px; background: #eee; border-radius: 5px; }
        .filters input, .filters select, .filters button { margin: 5px; padding: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; cursor: pointer; }
        th:hover { background: #555; }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #e9ecef; }
        .actions a { margin-right: 10px; text-decoration: none; display: inline-block; }
        .actions a:hover { text-decoration: underline; }
        .shortcuts { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .btn { padding: 10px 15px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn:hover { background: #218838; }
        .btn-blue { background: #007bff; }
        .btn-blue:hover { background: #0056b3; }
        .btn-export { background: #17a2b8; }
        .btn-export:hover { background: #138496; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .stats { margin-bottom: 20px; display: flex; gap: 20px; flex-wrap: wrap; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; flex: 1; min-width: 200px; text-align: center; border-left: 4px solid #007bff; }
        .stat-box.debourser { border-left-color: #28a745; }
        .stat-box.rembourse { border-left-color: #17a2b8; }
        .stat-box.difference { border-left-color: #dc3545; }
        .stat-box h3 { margin: 0; color: #333; font-size: 2em; }
        .stat-box p { margin: 5px 0 0 0; color: #666; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
        .zero { color: #6c757d; font-weight: bold; }
        .top-nav { background: #333; padding: 10px 20px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.5em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
        .user-menu { display: flex; align-items: center; margin-left: auto; }
        .user-menu a { transition: opacity 0.3s; }
        .user-menu a:hover { opacity: 0.8; }
        .action-print { color: #17a2b8; font-weight: 500; }
        .action-print:hover { color: #138496; }
        .action-edit { color: #007bff; font-weight: 500; }
        .action-edit:hover { color: #0056b3; }
        .action-delete { color: #dc3545; font-weight: 500; }
        .action-delete:hover { color: #c82333; }
        @media print { .filters, .shortcuts, .actions, .stats, .no-print, .top-nav { display: none; } }
        @media (max-width: 768px) { 
            .shortcuts { flex-direction: column; } 
            .stats { flex-direction: column; } 
            table { font-size: 0.8em; }
            .top-nav { flex-direction: column; gap: 10px; }
            .nav-links { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
            .user-menu { margin-left: 0; justify-content: center; width: 100%; }
        }
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
            <div class="user-menu">
                <span style="color: #fff; margin-right: 15px;">👤 <?= htmlspecialchars($_SESSION['nom_complet'] ?? $_SESSION['username'] ?? 'Utilisateur') ?></span>
                <a href="index.php?controller=auth&action=changePassword" style="background: #6f42c1; padding: 5px 10px; border-radius: 3px; color: #fff; margin-right: 10px; text-decoration: none;">🔒 Changer MDP</a>
                <a href="index.php?controller=auth&action=logout" style="background: #dc3545; padding: 5px 10px; border-radius: 3px; color: #fff; text-decoration: none;">🚪 Déconnexion</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="shortcuts no-print">
            <a href="index.php?controller=slip&action=create" class="btn">📄 Nouveau Bulletin</a>
            <a href="index.php?controller=patient&action=index" class="btn btn-blue">👥 Patients</a>
            <a href="index.php?controller=doctor&action=index" class="btn btn-blue">👨‍⚕️ Médecins</a>
            <a href="index.php?controller=intervention&action=index" class="btn btn-blue">💉 Types Intervention</a>
            <a href="index.php?controller=dashboard&action=exportCsv&<?= http_build_query($_GET) ?>" class="btn btn-export">📊 Export CSV</a>
            <button onclick="window.print()" class="btn btn-export">🖨️ Export PDF</button>
        </div>

        <div class="stats no-print">
            <?php
            $total_debourse = array_sum(array_column($slips ?? [], 'montant_debourse'));
            $total_rembourse = array_sum(array_column($slips ?? [], 'montant_rembourse'));
            $total_difference = $total_debourse - $total_rembourse;
            ?>
            <div class="stat-box debourser">
                <h3><?= number_format($total_debourse, 3, '.', ' ') ?></h3>
                <p>Total Déboursé</p>
            </div>
            <div class="stat-box rembourse">
                <h3><?= number_format($total_rembourse, 3, '.', ' ') ?></h3>
                <p>Total Remboursé</p>
            </div>
            <div class="stat-box difference">
                <h3 class="<?= $total_difference >= 0 ? 'positive' : 'negative' ?>">
                    <?= number_format($total_difference, 3, '.', ' ') ?>
                </h3>
                <p>Total Différence</p>
            </div>
            <div class="stat-box">
                <h3><?= count($slips ?? []) ?></h3>
                <p>Nombre de Bulletins</p>
            </div>
        </div>

        <div class="filters no-print">
            <form method="get">
                <input type="hidden" name="controller" value="dashboard">
                <input type="hidden" name="action" value="index">
                
                <label>Date des soins :</label>
                <input type="date" name="date" value="<?= htmlspecialchars($date ?? '') ?>">
                
                <label>Médecin :</label>
                <select name="doctor_id">
                    <option value="">Tous les médecins</option>
                    <?php foreach ($doctors ?? [] as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (isset($doctor_id) && $doctor_id == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label>Patient :</label>
                <select name="patient_id">
                    <option value="">Tous les patients</option>
                    <?php foreach ($patients ?? [] as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (isset($patient_id) && $patient_id == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label>Recherche :</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="N° Bulletin, Patient, Médecin...">
                
                <button type="submit" class="btn btn-blue">Filtrer</button>
                <a href="index.php?controller=dashboard&action=index" class="btn btn-danger">Réinitialiser</a>
            </form>
        </div>

        <table id="dataTable">
            <thead>
                <tr>
                    <th style="width: 120px;" onclick="sortTable(0)">N° Bulletin</th>
                    <th style="width: 200px;" onclick="sortTable(1)">Patient</th>
                    <th style="width: 200px;" onclick="sortTable(2)">Médecin</th>
                    <th style="width: 120px;" onclick="sortTable(3)">Date Soins</th>
                    <th style="width: 140px;" onclick="sortTable(4)">Date Remboursement</th>
                    <th style="width: 120px;" onclick="sortTable(5)">Déboursé</th>
                    <th style="width: 120px;" onclick="sortTable(6)">Remboursé</th>
                    <th style="width: 120px;" onclick="sortTable(7)">Différence</th>
                    <th class="actions no-print" style="width: 380px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($slips)): ?>
                    <?php foreach ($slips as $s): ?>
                    <?php
                    $difference = (float)$s['montant_debourse'] - (float)$s['montant_rembourse'];
                    $diff_class = $difference > 0 ? 'positive' : ($difference < 0 ? 'negative' : 'zero');
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['numero_bulletin']) ?></strong></td>
                        <td><?= htmlspecialchars($s['patient_nom']) ?></td>
                        <td><?= htmlspecialchars($s['doctor_nom']) ?></td>
                        <td><?= $s['date_soins'] ?></td>
                        <td><?= $s['date_remboursement'] ?? '-' ?></td>
                        <td><?= number_format((float)$s['montant_debourse'], 3, '.', ' ') ?></td>
                        <td><?= number_format((float)$s['montant_rembourse'], 3, '.', ' ') ?></td>
                        <td class="<?= $diff_class ?>"><?= number_format($difference, 3, '.', ' ') ?></td>
                        <td class="actions no-print">
                            <a href="index.php?controller=slip&action=print&id=<?= $s['id'] ?>" target="_blank" class="action-print">🖨️ Imprimer</a>
                            <a href="index.php?controller=slip&action=edit&id=<?= $s['id'] ?>" class="action-edit">✏️ Modifier</a>
                            <a href="index.php?controller=slip&action=delete&id=<?= $s['id'] ?>" class="action-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce bulletin ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px;">
                            <p style="color: #666; font-size: 1.1em;">Aucun bulletin trouvé</p>
                            <a href="index.php?controller=slip&action=create" class="btn">Créer un premier bulletin</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($slips)): ?>
            <tfoot>
                <tr style="background: #333; color: #fff; font-weight: bold;">
                    <td colspan="5">TOTAUX</td>
                    <td><?= number_format($total_debourse, 3, '.', ' ') ?></td>
                    <td><?= number_format($total_rembourse, 3, '.', ' ') ?></td>
                    <td class="<?= $total_difference >= 0 ? 'positive' : 'negative' ?>"><?= number_format($total_difference, 3, '.', ' ') ?></td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <script>
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("dataTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    var xContent = x.innerHTML.toLowerCase().replace(' TND', '').replace(',', '.');
                    var yContent = y.innerHTML.toLowerCase().replace(' TND', '').replace(',', '.');
                    var xNum = parseFloat(xContent);
                    var yNum = parseFloat(yContent);
                    
                    if (!isNaN(xNum) && !isNaN(yNum)) {
                        if (dir == "asc") {
                            if (xNum > yNum) { shouldSwitch = true; break; }
                        } else if (dir == "desc") {
                            if (xNum < yNum) { shouldSwitch = true; break; }
                        }
                    } else {
                        if (dir == "asc") {
                            if (xContent > yContent) { shouldSwitch = true; break; }
                        } else if (dir == "desc") {
                            if (xContent < yContent) { shouldSwitch = true; break; }
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") { dir = "desc"; switching = true; }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row, index) {
                row.style.transition = 'background 0.3s';
                row.addEventListener('mouseenter', function() {
                    this.style.background = '#e9ecef';
                });
                row.addEventListener('mouseleave', function() {
                    if (index % 2 === 0) {
                        this.style.background = '#f9f9f9';
                    } else {
                        this.style.background = '#fff';
                    }
                });
            });
        });
    </script>
</body>
</html>