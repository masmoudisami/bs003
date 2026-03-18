<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($slip) && $slip ? 'Bulletin #' . $slip['numero_bulletin'] : 'Gestion Bulletins' ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        form { margin-bottom: 20px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select, textarea { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: #fff; border: none; cursor: pointer; margin-top: 10px; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007bff; }
        .actions a:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 10px; text-decoration: none; color: #333; }
        .line-item { background: #f9f9f9; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .total-box { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px; }
        .bulletin-number { background: #007bff; color: #fff; padding: 15px 25px; border-radius: 5px; display: inline-block; margin-bottom: 20px; font-size: 1.2em; font-weight: bold; }
        .bulletin-input { background: #fff3cd; border: 2px solid #ffc107; padding: 15px 25px; border-radius: 5px; margin-bottom: 20px; }
        .bulletin-input input { width: 200px; font-size: 1.2em; font-weight: bold; }
        .financial-info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .financial-info label { display: inline-block; width: 200px; }
        .financial-info input { width: 150px; display: inline-block; }
        .financial-info input[readonly] { background: #d4edda; border-color: #28a745; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .required { color: #dc3545; }
        .info-box { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; font-size: 0.9em; }
        .btn-remove { background: #dc3545; margin-top: 10px; }
        .btn-remove:hover { background: #c82333; }
        .btn-add { background: #17a2b8; }
        .btn-add:hover { background: #138496; }
        .btn-submit { background: #28a745; font-size: 1.1em; padding: 15px 30px; }
        .btn-submit:hover { background: #218838; }
        .file-preview { font-size: 0.9em; color: #666; margin-top: 5px; }
        .attachment-link { color: #17a2b8; text-decoration: none; }
        .attachment-link:hover { text-decoration: underline; }
        .top-nav { background: #333; padding: 10px 20px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.5em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 15px; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'print' && $slip): ?>
            <div class="bulletin-number">N° Bulletin : <?= htmlspecialchars($slip['numero_bulletin']) ?></div>
            <h1>Bulletin de Soins #<?= htmlspecialchars($slip['numero_bulletin']) ?></h1>
            <p><strong>Patient :</strong> <?= htmlspecialchars($slip['patient_nom']) ?></p>
            <p><strong>Médecin :</strong> <?= htmlspecialchars($slip['doctor_nom']) ?></p>
            <p><strong>Date Soins :</strong> <?= $slip['date_soins'] ?></p>
            <p><strong>Date Remboursement :</strong> <?= $slip['date_remboursement'] ?? 'Non renseignée' ?></p>
            <p><strong>Commentaire :</strong> <?= nl2br(htmlspecialchars($slip['commentaire'])) ?></p>
            <div class="financial-info">
                <p><strong>Montant BS Déboursé :</strong> <?= number_format((float)$slip['montant_debourse'], 3, '.', ' ') ?> TND</p>
                <p><strong>Montant BS Remboursé :</strong> <?= number_format((float)$slip['montant_rembourse'], 3, '.', ' ') ?> TND</p>
                <p><strong>Total Interventions :</strong> <?= number_format((float)$slip['total'], 3, '.', ' ') ?> TND</p>
                <?php $diff = (float)$slip['montant_debourse'] - (float)$slip['montant_rembourse']; ?>
                <p><strong>Différence :</strong> <span style="color: <?= $diff >= 0 ? '#28a745' : '#dc3545' ?>"><?= number_format($diff, 3, '.', ' ') ?> TND</span></p>
            </div>
            <table>
                <thead><tr><th>Intervention</th><th>Montant (TND)</th><th>Document</th></tr></thead>
                <tbody>
                    <?php foreach ($lines as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['libelle']) ?></td>
                        <td><?= number_format((float)$l['montant'], 3, '.', ' ') ?></td>
                        <td>
                            <?php if ($l['fichier_path']): ?>
                                <?php
                                $file_ext = strtolower(pathinfo($l['fichier_path'], PATHINFO_EXTENSION));
                                $file_url = 'index.php?controller=slip&action=viewFile&file=' . urlencode($l['fichier_path']);
                                $icon = ($file_ext === 'pdf') ? '📄' : '🖼️';
                                ?>
                                <a href="<?= $file_url ?>" target="_blank" class="attachment-link">
                                    <?= $icon ?> <?= htmlspecialchars($l['fichier_path']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #666;">Non</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="no-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
        <?php else: ?>
            <h1><?= isset($slip) && $slip ? 'Modifier Bulletin' : 'Nouveau Bulletin' ?></h1>
            
            <?php if (isset($slip) && $slip): ?>
                <div class="bulletin-number">N° Bulletin : <?= htmlspecialchars($slip['numero_bulletin']) ?></div>
            <?php endif; ?>
            
            <form method="post" action="index.php?controller=slip&action=store" enctype="multipart/form-data">
                <?php if (isset($slip) && $slip): ?>
                    <input type="hidden" name="id" value="<?= $slip['id'] ?>">
                    <input type="hidden" name="numero_bulletin" value="<?= htmlspecialchars($slip['numero_bulletin']) ?>">
                <?php endif; ?>

                <?php if (!isset($slip) || !$slip): ?>
                    <div class="bulletin-input">
                        <label>N° Bulletin <span class="required">*</span></label>
                        <input type="text" name="numero_bulletin" id="numero_bulletin" value="<?= htmlspecialchars($_POST['numero_bulletin'] ?? '') ?>" placeholder="Ex: BS-2024-001" required>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">Entrez un numéro unique (ex: BS-2024-001, 2024/001, etc.)</p>
                    </div>
                <?php endif; ?>
                
                <label>Patient <span class="required">*</span></label>
                <select name="patient_id" required>
                    <option value="">-- Sélectionner un patient --</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (isset($slip) && $slip['patient_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Médecin <span class="required">*</span></label>
                <select name="doctor_id" required>
                    <option value="">-- Sélectionner un médecin --</option>
                    <?php foreach ($doctors as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (isset($slip) && $slip['doctor_id'] == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nom']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Date des soins <span class="required">*</span></label>
                <input type="date" name="date_soins" value="<?= $slip['date_soins'] ?? date('Y-m-d') ?>" required>

                <label>Date de remboursement</label>
                <input type="date" name="date_remboursement" value="<?= $slip['date_remboursement'] ?? '' ?>">

                <div class="financial-info">
                    <label>Montant BS Déboursé (TND) <span class="required">*</span></label>
                    <input type="text" name="montant_debourse" id="montant_debourse" value="<?= $slip['montant_debourse'] ?? '0.000' ?>" readonly>
                    <p style="margin: 5px 0 0 0; color: #28a745; font-size: 0.9em;">✓ Auto-calculé depuis les lignes d'intervention</p>
                    
                    <label style="margin-top: 15px;">Montant BS Remboursé (TND)</label>
                    <input type="text" name="montant_rembourse" id="montant_rembourse" value="<?= $slip['montant_rembourse'] ?? '0.000' ?>" oninput="calcBalance()">
                    
                    <div class="info-box">
                        <strong>Solde :</strong> <span id="solde-display">0.000</span> TND 
                        <span id="solde-message"></span>
                    </div>
                </div>

                <label>Commentaire</label>
                <textarea name="commentaire" rows="4" placeholder="Commentaire optionnel..."><?= htmlspecialchars($slip['commentaire'] ?? '') ?></textarea>

                <h3>Lignes d'intervention</h3>
                <div id="lines-container">
                    <?php 
                    $lines_to_show = $lines ?? [];
                    if (empty($lines_to_show)) {
                        $lines_to_show = [['intervention_type_id' => '', 'montant' => '', 'fichier_path' => '']];
                    }
                    foreach ($lines_to_show as $idx => $l): 
                    ?>
                    <div class="line-item">
                        <label>Type d'intervention <span class="required">*</span></label>
                        <select name="lines[<?= $idx ?>][intervention_type_id]" class="intervention-select" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($interventions as $i): ?>
                                <option value="<?= $i['id'] ?>" <?= (isset($l['intervention_type_id']) && $l['intervention_type_id'] == $i['id']) ? 'selected' : '' ?>><?= htmlspecialchars($i['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Montant (TND) <span class="required">*</span></label>
                        <input type="text" name="lines[<?= $idx ?>][montant]" class="amount" value="<?= $l['montant'] ?? '' ?>" oninput="calcTotal()" required>
                        <label>Document</label>
                        <input type="file" name="lines[<?= $idx ?>][fichier]" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if (!empty($l['fichier_path'])): ?>
                            <p class="file-preview">
                                Fichier actuel : 
                                <a href="index.php?controller=slip&action=viewFile&file=<?= urlencode($l['fichier_path']) ?>" target="_blank" class="attachment-link">
                                    <?= htmlspecialchars($l['fichier_path']) ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        <?php if (!isset($slip) || !$slip): ?>
                            <button type="button" class="btn-remove no-print" onclick="removeLine(this)">🗑️ Supprimer ligne</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn-add no-print" onclick="addLine()">➕ Ajouter une ligne</button>

                <div class="total-box">
                    <div>Total Interventions : <span id="total-display">0.000</span> TND</div>
                    <div style="font-size: 0.9em; color: #666; margin-top: 5px;">= Montant BS Déboursé (auto-rempli)</div>
                </div>
                <input type="hidden" name="total" id="total-input" value="0">

                <button type="submit" class="btn-submit no-print">💾 Enregistrer le Bulletin</button>
            </form>

            <?php if (!isset($slip) || !$slip): ?>
            <h2>Liste des Bulletins</h2>
            <table>
                <thead><tr><th>N° Bulletin</th><th>Patient</th><th>Total</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($slips)): ?>
                        <?php foreach ($slips as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['numero_bulletin']) ?></td>
                            <td><?= htmlspecialchars($s['patient_nom']) ?></td>
                            <td><?= number_format((float)$s['total'], 3, '.', ' ') ?></td>
                            <td class="actions">
                                <a href="index.php?controller=slip&action=edit&id=<?= $s['id'] ?>">✏️ Modifier</a>
                                <a href="index.php?controller=slip&action=print&id=<?= $s['id'] ?>" target="_blank">🖨️ Imprimer</a>
                                <a href="index.php?controller=slip&action=delete&id=<?= $s['id'] ?>" onclick="return confirm('Supprimer ce bulletin ?')">🗑️ Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px;">Aucun bulletin enregistré</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        function calcTotal() {
            let total = 0;
            document.querySelectorAll('.amount').forEach(input => {
                let val = parseFloat(input.value.replace(',', '.'));
                if (!isNaN(val)) total += val;
            });
            
            document.getElementById('total-display').innerText = total.toFixed(3);
            document.getElementById('total-input').value = total.toFixed(3);
            document.getElementById('montant_debourse').value = total.toFixed(3);
            
            calcBalance();
        }
        
        function calcBalance() {
            let debourse = parseFloat(document.getElementById('montant_debourse').value.replace(',', '.')) || 0;
            let rembourse = parseFloat(document.getElementById('montant_rembourse').value.replace(',', '.')) || 0;
            let solde = debourse - rembourse;
            
            document.getElementById('solde-display').innerText = solde.toFixed(3);
            
            let message = '';
            if (solde > 0) {
                message = ' (Non remboursé)';
                document.getElementById('solde-message').style.color = '#dc3545';
            } else if (solde < 0) {
                message = ' (Trop perçu)';
                document.getElementById('solde-message').style.color = '#ffc107';
            } else {
                message = ' (Complet)';
                document.getElementById('solde-message').style.color = '#28a745';
            }
            document.getElementById('solde-message').innerText = message;
        }
        
        function addLine() {
            let container = document.getElementById('lines-container');
            let idx = container.children.length;
            let div = document.createElement('div');
            div.className = 'line-item';
            div.innerHTML = '<label>Type d\'intervention <span class="required">*</span></label><select name="lines['+idx+'][intervention_type_id]" class="intervention-select" required><option value="">-- Sélectionner --</option><?php foreach ($interventions as $i): ?><option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['libelle']) ?></option><?php endforeach; ?></select><label>Montant (TND) <span class="required">*</span></label><input type="text" name="lines['+idx+'][montant]" class="amount" oninput="calcTotal()" required><label>Document</label><input type="file" name="lines['+idx+'][fichier]" accept=".pdf,.jpg,.jpeg,.png"><button type="button" class="btn-remove no-print" onclick="removeLine(this)">🗑️ Supprimer ligne</button>';
            container.appendChild(div);
        }
        
        function removeLine(button) {
            let container = document.getElementById('lines-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
                calcTotal();
            } else {
                alert('Au moins une ligne d\'intervention est requise');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            calcTotal();
            calcBalance();
        });
    </script>
</body>
</html>