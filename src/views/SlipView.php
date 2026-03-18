<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($slip) && $slip ? 'Bulletin #' . $slip['numero_bulletin'] : 'Gestion Bulletins' ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin: 0 0 15px 0; font-size: 1.5em; }
        h2 { color: #555; margin: 20px 0 10px 0; font-size: 1.2em; }
        h3 { color: #333; margin: 15px 0 10px 0; font-size: 1.1em; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        form { margin-bottom: 20px; }
        label { display: block; margin-top: 12px; font-weight: bold; color: #333; }
        input, select, textarea { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; font-size: 1em; border: 2px solid #ddd; border-radius: 5px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #007bff; }
        button { padding: 12px 20px; background: #28a745; color: #fff; border: none; cursor: pointer; margin-top: 15px; border-radius: 5px; font-size: 1em; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #333; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #e9ecef; }
        .actions a { margin-right: 12px; text-decoration: none; color: #007bff; }
        .actions a:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #333; padding: 8px 15px; background: #f8f9fa; border-radius: 5px; }
        .back:hover { background: #e9ecef; }
        .line-item { background: #f8f9fa; padding: 15px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; border-left: 4px solid #007bff; }
        .total-box { font-size: 1.3em; font-weight: bold; text-align: right; margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #e7f3ff 0%, #d4edda 100%); border-radius: 5px; border: 2px solid #007bff; }
        .bulletin-number { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #fff; padding: 15px 30px; border-radius: 5px; display: inline-block; margin-bottom: 20px; font-size: 1.3em; font-weight: bold; box-shadow: 0 3px 10px rgba(0,123,255,0.3); }
        .bulletin-input { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
        .bulletin-input input { width: 250px; font-size: 1.2em; font-weight: bold; }
        .financial-info { background: linear-gradient(135deg, #e7f3ff 0%, #f8f9fa 100%); padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #007bff; }
        .financial-info label { display: inline-block; width: 200px; font-weight: bold; color: #333; }
        .financial-info input { width: 150px; display: inline-block; }
        .financial-info input[readonly] { background: #d4edda; border-color: #28a745; font-weight: bold; color: #155724; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .required { color: #dc3545; }
        .info-box { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 5px; margin: 15px 0; font-size: 0.95em; border-left: 4px solid #17a2b8; }
        .btn-remove { background: #dc3545; margin-top: 10px; padding: 8px 15px; font-size: 0.9em; }
        .btn-remove:hover { background: #c82333; }
        .btn-add { background: #17a2b8; padding: 10px 18px; }
        .btn-add:hover { background: #138496; }
        .btn-submit { background: linear-gradient(135deg, #28a745 0%, #218838 100%); font-size: 1.2em; padding: 15px 40px; box-shadow: 0 3px 10px rgba(40,167,69,0.3); }
        .btn-submit:hover { background: linear-gradient(135deg, #218838 0%, #1e7e34 100%); }
        .file-preview { font-size: 0.9em; color: #666; margin-top: 5px; }
        .attachment-link { color: #17a2b8; text-decoration: none; font-weight: bold; }
        .attachment-link:hover { text-decoration: underline; }
        
        /* ============================================ */
        /* HEADER / NAVIGATION */
        /* ============================================ */
        .top-nav { 
            background: #333; 
            padding: 15px 25px; 
            margin: -20px -20px 25px -20px; 
            border-radius: 5px 5px 0 0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .top-nav h1 { 
            color: #fff; 
            border: none; 
            margin: 0; 
            font-size: 1.4em;
            white-space: nowrap;
        }
        .top-nav .nav-links { 
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .top-nav .nav-links a { 
            color: #fff; 
            text-decoration: none; 
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.3s;
        }
        .top-nav .nav-links a:hover { 
            background: rgba(255,255,255,0.1);
            color: #17a2b8;
        }
        .user-menu { 
            display: flex; 
            align-items: center; 
            gap: 10px;
            white-space: nowrap;
        }
        .user-menu span {
            color: #fff;
            font-weight: 500;
        }
        .user-menu a { 
            transition: opacity 0.3s;
            padding: 6px 12px;
            border-radius: 3px;
            text-decoration: none;
            color: #fff;
            font-size: 0.9em;
            white-space: nowrap;
        }
        .user-menu a:hover { 
            opacity: 0.8;
        }
        
        @media (max-width: 1024px) {
            .top-nav {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            .top-nav h1 {
                margin-bottom: 10px;
            }
            .nav-links {
                justify-content: center;
                margin-bottom: 15px;
            }
            .user-menu {
                justify-content: center;
            }
        }
        
        /* ============================================ */
        /* STYLES POUR IMPRESSION (COMPACT) */
        /* ============================================ */
        .print-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .print-header h1 { margin: 0; font-size: 1.5em; }
        .print-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .print-info-box { background: #f8f9fa; padding: 12px; border-radius: 5px; font-size: 0.9em; border: 1px solid #ddd; }
        .print-info-box h3 { margin: 0 0 8px 0; font-size: 1em; color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .print-info-box p { margin: 4px 0; font-size: 0.9em; }
        .print-info-box strong { color: #555; }
        .print-financial { background: #e7f3ff; padding: 12px; border-radius: 5px; font-size: 0.9em; border: 1px solid #007bff; }
        .print-financial h3 { margin: 0 0 8px 0; font-size: 1em; color: #333; }
        .print-financial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .print-financial p { margin: 3px 0; font-size: 0.9em; }
        .print-comment { background: #fff3cd; padding: 12px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; font-size: 0.9em; }
        .print-comment h3 { margin: 0 0 5px 0; font-size: 0.95em; color: #856404; }
        .print-comment p { margin: 0; color: #856404; white-space: pre-wrap; font-size: 0.9em; }
        .print-table th, .print-table td { padding: 6px 8px; font-size: 0.85em; }
        
        @media print { 
            .no-print { display: none !important; } 
            body { background: #fff; margin: 0; font-size: 0.8em; } 
            .container { box-shadow: none; max-width: 100%; padding: 15px; }
            .print-header { border-bottom: 2px solid #000; }
            .print-info-box, .print-financial, .print-comment { background: #fff; border: 1px solid #ddd; }
            .bulletin-number { background: #333; color: #fff; padding: 10px 20px; font-size: 1.1em; }
            h1 { font-size: 1.3em; }
            table { font-size: 0.8em; }
            .print-grid { gap: 10px; }
            @page { margin: 0.8cm; size: A4; }
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
                <span>👤 <?= htmlspecialchars($_SESSION['nom_complet'] ?? $_SESSION['username'] ?? 'Utilisateur') ?></span>
                <a href="index.php?controller=auth&action=changePassword" style="background: #6f42c1;">🔒 Changer MDP</a>
                <a href="index.php?controller=auth&action=logout" style="background: #dc3545;">🚪 Déconnexion</a>
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
            <!-- ============================================ -->
            <!-- VERSION IMPRESSION / CONSULTATION BULLETIN -->
            <!-- ============================================ -->
            
            <div class="print-header">
                <div class="bulletin-number">N° <?= htmlspecialchars($slip['numero_bulletin']) ?></div>
                <h1>📄 Bulletin de Soins</h1>
            </div>
            
            <div class="print-grid">
                <div class="print-info-box">
                    <h3>📋 Informations Générales</h3>
                    <p><strong>Patient :</strong> <?= htmlspecialchars($slip['patient_nom']) ?></p>
                    <p><strong>Médecin :</strong> <?= htmlspecialchars($slip['doctor_nom']) ?></p>
                    <p><strong>Date Soins :</strong> <?= date('d/m/Y', strtotime($slip['date_soins'])) ?></p>
                    <p><strong>Remboursement :</strong> <?= !empty($slip['date_remboursement']) ? date('d/m/Y', strtotime($slip['date_remboursement'])) : '-' ?></p>
                </div>
                
                <div class="print-financial">
                    <h3>💰 Financier</h3>
                    <div class="print-financial-grid">
                        <p><strong>Déboursé :</strong> <?= number_format((float)$slip['montant_debourse'], 3, ',', ' ') ?> TND</p>
                        <p><strong>Remboursé :</strong> <?= number_format((float)$slip['montant_rembourse'], 3, ',', ' ') ?> TND</p>
                        <p><strong>Total :</strong> <?= number_format((float)$slip['total'], 3, ',', ' ') ?> TND</p>
                        <?php $diff = (float)$slip['montant_debourse'] - (float)$slip['montant_rembourse']; ?>
                        <p><strong>Différence :</strong> <span style="color: <?= $diff >= 0 ? '#28a745' : '#dc3545' ?>"><?= number_format($diff, 3, ',', ' ') ?> TND</span></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($slip['commentaire'])): ?>
            <div class="print-comment">
                <h3>💬 Commentaire</h3>
                <p><?= nl2br(htmlspecialchars($slip['commentaire'])) ?></p>
            </div>
            <?php endif; ?>

            <h3 style="margin: 15px 0 10px 0; font-size: 1.1em;">📝 Détail des Interventions</h3>
            <table class="print-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Intervention</th>
                        <th style="width: 20%;">Montant</th>
                        <th style="width: 30%;">Document</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['libelle']) ?></td>
                        <td><?= number_format((float)$l['montant'], 3, ',', ' ') ?></td>
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

            <div class="no-print" style="margin-top: 30px; text-align: center;">
                <button onclick="window.print()" style="background: #17a2b8; padding: 15px 35px; font-size: 1.1em;">🖨️ Imprimer / PDF</button>
                <a href="index.php?controller=dashboard&action=index" class="back" style="display: inline-block; margin-left: 20px;">← Retour Tableau de Bord</a>
            </div>

        <?php else: ?>
            <!-- ============================================ -->
            <!-- VERSION FORMULAIRE (CRÉATION / MODIFICATION) -->
            <!-- ============================================ -->
            <h1><?= isset($slip) && $slip ? '✏️ Modifier Bulletin' : '➕ Nouveau Bulletin' ?></h1>
            
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Date des soins <span class="required">*</span></label>
                        <input type="date" name="date_soins" value="<?= $slip['date_soins'] ?? date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label>Date de remboursement</label>
                        <input type="date" name="date_remboursement" value="<?= $slip['date_remboursement'] ?? '' ?>">
                    </div>
                </div>

                <div class="financial-info">
                    <h3 style="margin: 0 0 15px 0; font-size: 1.1em; color: #007bff;">💰 Informations Financières</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>Montant BS Déboursé (TND) <span class="required">*</span></label>
                            <input type="text" name="montant_debourse" id="montant_debourse" value="<?= $slip['montant_debourse'] ?? '0.000' ?>" readonly>
                            <p style="margin: 5px 0 0 0; color: #28a745; font-size: 0.9em;">✓ Auto-calculé depuis les lignes d'intervention</p>
                        </div>
                        <div>
                            <label>Montant BS Remboursé (TND)</label>
                            <input type="text" name="montant_rembourse" id="montant_rembourse" value="<?= $slip['montant_rembourse'] ?? '0.000' ?>" oninput="calcBalance()">
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>Solde :</strong> <span id="solde-display">0.000</span> TND <span id="solde-message"></span>
                    </div>
                </div>

                <label>Commentaire</label>
                <textarea name="commentaire" rows="4" placeholder="Commentaire optionnel sur le bulletin..."><?= htmlspecialchars($slip['commentaire'] ?? '') ?></textarea>

                <h3>📝 Lignes d'intervention</h3>
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
            <h2>📋 Liste des Bulletins</h2>
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
