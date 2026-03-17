<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique Patient - <?= htmlspecialchars($patient['nom']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; background: #f4f4f4; font-size: 0.85em; }
        .container { max-width: 1400px; margin: 0 auto; background: #fff; padding: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 8px; margin: 0 0 15px 0; font-size: 1.3em; }
        h2 { color: #555; margin: 15px 0 10px 0; font-size: 1.1em; }
        h3 { margin: 8px 0; font-size: 1em; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85em; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #333; color: #fff; font-weight: normal; }
        tr:nth-child(even) { background: #f9f9f9; }
        .back { display: inline-block; margin-bottom: 10px; text-decoration: none; color: #333; font-size: 0.9em; }
        .btn { padding: 8px 12px; background: #17a2b8; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px; font-size: 0.85em; }
        .btn:hover { background: #138496; }
        .btn-green { background: #28a745; }
        .btn-green:hover { background: #218838; }
        .patient-info { background: #e7f3ff; padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #007bff; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
        .patient-info p { margin: 0; font-size: 0.9em; }
        .patient-info strong { color: #333; }
        .slip-section { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; page-break-inside: avoid; }
        .slip-header { background: #007bff; color: #fff; padding: 8px 12px; font-weight: bold; font-size: 0.9em; display: flex; justify-content: space-between; align-items: center; }
        .slip-body { padding: 10px; }
        .slip-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .slip-info { background: #f8f9fa; padding: 8px; border-radius: 4px; font-size: 0.85em; }
        .slip-info p { margin: 3px 0; }
        .slip-info strong { color: #555; }
        .financial-summary { background: #e7f3ff; padding: 8px; border-radius: 4px; font-size: 0.85em; }
        .financial-summary p { margin: 3px 0; }
        .financial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
        .zero { color: #6c757d; font-weight: bold; }
        .attachment { color: #17a2b8; font-weight: bold; font-size: 0.85em; }
        .attachment a { color: #17a2b8; text-decoration: none; }
        .attachment a:hover { text-decoration: underline; }
        .no-attachment { color: #6c757d; font-size: 0.85em; }
        .comment-box { background: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 8px; border-left: 3px solid #ffc107; font-size: 0.85em; }
        .comment-box strong { color: #856404; }
        .comment-box p { margin: 3px 0; color: #856404; white-space: pre-wrap; }
        .totals-row { background: #333 !important; color: #fff; font-weight: bold; }
        .file-icon { margin-right: 3px; }
        .pdf-icon { color: #dc3545; }
        .jpg-icon { color: #28a745; }
        .top-nav { background: #333; padding: 8px 15px; margin: -15px -15px 15px -15px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .top-nav h1 { color: #fff; border: none; margin: 0; font-size: 1.2em; }
        .top-nav .nav-links a { color: #fff; text-decoration: none; margin-left: 12px; font-size: 0.9em; }
        .top-nav .nav-links a:hover { color: #17a2b8; }
        .compact-table th, .compact-table td { padding: 4px 6px; font-size: 0.8em; }
        .no-print-btn { margin-bottom: 10px; }
        .attachment-text { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: middle; }
        
        @media print { 
            .no-print { display: none !important; } 
            body { background: #fff; margin: 0; font-size: 0.8em; } 
            .container { box-shadow: none; max-width: 100%; padding: 10px; }
            .slip-section { page-break-inside: avoid; border: 1px solid #ccc; }
            .slip-header { background: #ddd !important; color: #000 !important; border-bottom: 1px solid #999; }
            .patient-info, .slip-info, .financial-summary, .comment-box { background: #fff; border: 1px solid #ddd; }
            a { text-decoration: none; color: #333; }
            .attachment a { color: #333; text-decoration: none; }
            .attachment-text { max-width: 120px; }
            @page { margin: 0.8cm; size: A4; }
        }
        @media (max-width: 768px) { 
            .slip-grid, .financial-grid { grid-template-columns: 1fr; }
            .patient-info { flex-direction: column; gap: 8px; }
            table { font-size: 0.75em; }
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
        </div>

        <div class="no-print no-print-btn">
            <a href="index.php?controller=patient&action=index" class="back">← Retour Patients</a>
        </div>
        
        <div class="patient-info">
            <div>
                <h2 style="margin: 0 0 5px 0;">📋 Informations Patient</h2>
                <p><strong>Nom :</strong> <?= htmlspecialchars($patient['nom']) ?></p>
                <p><strong>Date Naissance :</strong> <?= $patient['date_naissance'] ?></p>
            </div>
            <div style="margin-left: auto; text-align: right;">
                <p><strong>Nombre de Bulletins :</strong> <?= count($slips) ?></p>
                <div class="no-print" style="margin-top: 8px;">
                    <a href="index.php?controller=patient&action=historyExportCsv&id=<?= $patient['id'] ?>" class="btn">📊 Export CSV</a>
                    <button onclick="window.print()" class="btn btn-green">🖨️ PDF</button>
                </div>
            </div>
        </div>

        <h1>📁 Historique des Bulletins de Soins</h1>
        
        <?php if (empty($slips)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p style="font-size: 1.1em;">Aucun bulletin trouvé pour ce patient</p>
            </div>
        <?php else: ?>
            <?php 
            $grand_total_debourse = 0;
            $grand_total_rembourse = 0;
            ?>
            
            <?php foreach ($slips as $s): ?>
                <?php
                $difference = (float)$s['montant_debourse'] - (float)$s['montant_rembourse'];
                $diff_class = $difference > 0 ? 'positive' : ($difference < 0 ? 'negative' : 'zero');
                $grand_total_debourse += (float)$s['montant_debourse'];
                $grand_total_rembourse += (float)$s['montant_rembourse'];
                ?>
                
                <div class="slip-section">
                    <div class="slip-header">
                        <span>📄 <?= htmlspecialchars($s['numero_bulletin']) ?></span>
                        <span style="font-weight: normal; font-size: 0.85em;"><?= $s['date_soins'] ?></span>
                    </div>
                    <div class="slip-body">
                        <div class="slip-grid">
                            <div class="slip-info">
                                <p><strong>Médecin :</strong> <?= htmlspecialchars($s['doctor_nom']) ?></p>
                                <p><strong>Date Soins :</strong> <?= $s['date_soins'] ?></p>
                                <p><strong>Remboursement :</strong> <?= $s['date_remboursement'] ?? '-' ?></p>
                            </div>
                            <div class="financial-summary">
                                <div class="financial-grid">
                                    <p><strong>Déboursé :</strong> <?= number_format((float)$s['montant_debourse'], 3, '.', ' ') ?></p>
                                    <p><strong>Remboursé :</strong> <?= number_format((float)$s['montant_rembourse'], 3, '.', ' ') ?></p>
                                    <p><strong>Total :</strong> <?= number_format((float)$s['total'], 3, '.', ' ') ?></p>
                                    <p><strong>Différence :</strong> <span class="<?= $diff_class ?>"><?= number_format($difference, 3, '.', ' ') ?></span></p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($s['lines'])): ?>
                            <table class="compact-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Intervention</th>
                                        <th style="width: 20%;">Montant</th>
                                        <th style="width: 30%;">Document</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($s['lines'] as $line): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($line['libelle']) ?></td>
                                        <td><?= number_format((float)$line['montant'], 3, '.', ' ') ?></td>
                                        <td>
                                            <?php if ($line['fichier_path']): ?>
                                                <?php
                                                $file_ext = strtolower(pathinfo($line['fichier_path'], PATHINFO_EXTENSION));
                                                $icon_class = ($file_ext === 'pdf') ? 'pdf-icon' : 'jpg-icon';
                                                $icon = ($file_ext === 'pdf') ? '📄' : '🖼️';
                                                ?>
                                                <span class="attachment">
                                                    <span class="file-icon <?= $icon_class ?>"><?= $icon ?></span>
                                                    <span class="attachment-text"><?= htmlspecialchars($line['fichier_path']) ?></span>
                                                </span>
                                            <?php else: ?>
                                                <span class="no-attachment">✗ Non</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: #666; font-style: italic; font-size: 0.85em; margin: 8px 0;">Aucune ligne d'intervention</p>
                        <?php endif; ?>

                        <?php if (!empty($s['commentaire'])): ?>
                            <div class="comment-box">
                                <strong>💬 Commentaire :</strong> <?= nl2br(htmlspecialchars($s['commentaire'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="slip-section totals-row" style="border: 2px solid #333;">
                <div class="slip-body" style="padding: 10px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; color: #fff;">📊 TOTAUX GÉNÉRAUX</h3>
                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <p style="margin: 0;"><strong>Déboursé :</strong> <?= number_format($grand_total_debourse, 3, '.', ' ') ?></p>
                            <p style="margin: 0;"><strong>Remboursé :</strong> <?= number_format($grand_total_rembourse, 3, '.', ' ') ?></p>
                            <?php $grand_difference = $grand_total_debourse - $grand_total_rembourse; ?>
                            <p style="margin: 0;"><strong>Différence :</strong> <span class="<?= $grand_difference >= 0 ? 'positive' : 'negative' ?>"><?= number_format($grand_difference, 3, '.', ' ') ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>