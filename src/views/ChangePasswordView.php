<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer Mot de Passe - Gestion Bulletins de Soins</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 1.6em;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 0.9em;
        }
        .header-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-cancel {
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .password-requirements {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 0.85em;
            border-left: 4px solid #ffc107;
        }
        .user-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .user-info p {
            margin: 5px 0;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">🔒</div>
            <h1>Changer Mot de Passe</h1>
            <p>Gestion Bulletins de Soins</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success">
                ✓ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="user-info">
            <p><strong>👤 Utilisateur :</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?></p>
            <p><strong>📛 Nom :</strong> <?= htmlspecialchars($_SESSION['nom_complet'] ?? 'N/A') ?></p>
        </div>

        <form method="post" action="index.php?controller=auth&action=updatePassword">
            <div class="form-group">
                <label for="current_password">🔑 Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required placeholder="Entrez votre mot de passe actuel">
            </div>
            <div class="form-group">
                <label for="new_password">🆕 Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" required placeholder="Entrez le nouveau mot de passe">
            </div>
            <div class="form-group">
                <label for="confirm_password">✓ Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirmez le nouveau mot de passe">
            </div>
            <button type="submit" class="btn-submit">💾 Modifier le Mot de Passe</button>
            <a href="index.php?controller=dashboard&action=index" class="btn-cancel">← Retour Tableau de Bord</a>
        </form>

        <div class="password-requirements">
            <strong>📋 Critères de sécurité :</strong><br>
            • Minimum 6 caractères<br>
            • Les deux nouveaux mots de passe doivent correspondre
        </div>
    </div>
</body>
</html>