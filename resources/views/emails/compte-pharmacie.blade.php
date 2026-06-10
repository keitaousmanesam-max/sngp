<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants SNGP</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #F3F4F6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 40px 40px 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 14px; }
        .badge { display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; margin-top: 12px; }
        .body { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #1F2937; margin-bottom: 16px; }
        .text { font-size: 14px; color: #6B7280; line-height: 1.6; margin-bottom: 24px; }
        .pharmacie-info { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .pharmacie-info h3 { color: #1E40AF; font-size: 16px; margin: 0 0 12px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #DBEAFE; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6B7280; }
        .info-value { color: #1F2937; font-weight: 600; }
        .credentials { background: #1E3A8A; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .credentials h3 { color: white; font-size: 16px; margin: 0 0 16px; text-align: center; }
        .credential-item { background: rgba(255,255,255,0.1); border-radius: 8px; padding: 14px 16px; margin-bottom: 10px; }
        .credential-item:last-child { margin-bottom: 0; }
        .credential-label { color: rgba(255,255,255,0.7); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .credential-value { color: white; font-size: 16px; font-weight: 700; font-family: monospace; letter-spacing: 1px; }
        .warning { background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .warning p { color: #92400E; font-size: 13px; margin: 0; }
        .warning strong { color: #B45309; }
        .btn { display: block; background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white; text-decoration: none; text-align: center; padding: 16px 32px; border-radius: 10px; font-size: 16px; font-weight: 600; margin-bottom: 24px; }
        .steps { background: #F9FAFB; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .steps h3 { color: #1F2937; font-size: 15px; margin: 0 0 12px; }
        .step { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; font-size: 14px; color: #374151; }
        .step-num { background: #3B82F6; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
        .footer { background: #F9FAFB; padding: 24px 40px; text-align: center; border-top: 1px solid #E5E7EB; }
        .footer p { color: #9CA3AF; font-size: 12px; margin: 4px 0; }
        .footer strong { color: #6B7280; }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>🏥 SNGP</h1>
            <p>Système National de Gestion Pharmaceutique</p>
            <span class="badge">République de Guinée</span>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">Bonjour, {{ $admin->prenom }} {{ $admin->nom }},</p>

            <p class="text">
                Votre pharmacie a été enregistrée avec succès dans le Système National de Gestion Pharmaceutique (SNGP).
                Vous trouverez ci-dessous les informations de votre pharmacie ainsi que vos identifiants de connexion.
            </p>

            <!-- Infos pharmacie -->
            <div class="pharmacie-info">
                <h3>🏥 Informations de votre Pharmacie</h3>
                <div class="info-row">
                    <span class="info-label">Nom</span>
                    <span class="info-value">{{ $pharmacie->nom }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Numéro d'agrément</span>
                    <span class="info-value">{{ $pharmacie->numero_agrement }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Région</span>
                    <span class="info-value">{{ $pharmacie->region }} — {{ $pharmacie->prefecture }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date d'agrément</span>
                    <span class="info-value">{{ $pharmacie->date_agrement->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="info-value" style="color: #10B981;">✅ Active</span>
                </div>
            </div>

            <!-- Credentials -->
            <div class="credentials">
                <h3>🔐 Vos Identifiants de Connexion</h3>
                <div class="credential-item">
                    <div class="credential-label">Adresse Email</div>
                    <div class="credential-value">{{ $admin->email }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Mot de passe temporaire</div>
                    <div class="credential-value">{{ $motDePasse }}</div>
                </div>
            </div>

            <!-- Avertissement -->
            <div class="warning">
                <p>
                    ⚠️ <strong>Important :</strong> Ce mot de passe est temporaire.
                    Vous serez obligé de le changer lors de votre première connexion.
                    Ne partagez jamais vos identifiants avec quiconque.
                </p>
            </div>

            <!-- Bouton connexion -->
            <a href="{{ config('app.url') }}/login" class="btn">
                🚀 Se connecter au SNGP
            </a>

            <!-- Étapes -->
            <div class="steps">
                <h3>📋 Étapes pour commencer</h3>
                <div class="step">
                    <div class="step-num">1</div>
                    <span>Connectez-vous avec votre email et mot de passe temporaire</span>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <span>Changez votre mot de passe lors de la première connexion</span>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <span>Configurez votre pharmacie et ajoutez vos employés</span>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <span>Commencez à gérer vos stocks, ventes et commandes</span>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>SNGP — Système National de Gestion Pharmaceutique</strong></p>
            <p>Ministère de la Santé et de l'Hygiène Publique — République de Guinée</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>

    </div>
</body>
</html>