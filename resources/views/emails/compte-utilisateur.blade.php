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
        .role-badge { display: inline-block; background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 700; margin-bottom: 24px; }
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

        <div class="header">
            <h1>🏥 SNGP</h1>
            <p>Système National de Gestion Pharmaceutique</p>
            <span class="badge">République de Guinée</span>
        </div>

        <div class="body">

            <p class="greeting">Bonjour, {{ $utilisateur->prenom }} {{ $utilisateur->nom }},</p>

            <p class="text">
                Un compte a été créé pour vous sur le Système National de Gestion Pharmaceutique (SNGP).
                Vous trouverez ci-dessous vos identifiants de connexion.
            </p>

            <div style="text-align:center; margin-bottom:24px;">
                <span class="role-badge">
                    👤 Rôle :
                    @php
                        $roles = [
                            'admin_national'      => 'Administrateur National',
                            'admin_pharmacie'     => 'Administrateur Pharmacie',
                            'pharmacien'          => 'Pharmacien',
                            'assistant_pharmacien'=> 'Assistant Pharmacien',
                            'caissier'            => 'Caissier',
                            'gestionnaire_stock'  => 'Gestionnaire de Stock',
                            'fournisseur'         => 'Fournisseur',
                        ];
                        echo $roles[$role] ?? ucfirst(str_replace('_', ' ', $role));
                    @endphp
                </span>
            </div>

            <div class="credentials">
                <h3>🔐 Vos Identifiants de Connexion</h3>
                <div class="credential-item">
                    <div class="credential-label">Adresse Email</div>
                    <div class="credential-value">{{ $utilisateur->email }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Mot de passe temporaire</div>
                    <div class="credential-value">{{ $motDePasse }}</div>
                </div>
            </div>

            <div class="warning">
                <p>
                    ⚠️ <strong>Important :</strong> Ce mot de passe est temporaire.
                    Vous serez obligé de le changer lors de votre première connexion.
                    Ne partagez jamais vos identifiants avec quiconque.
                </p>
            </div>

            <a href="{{ config('app.url') }}/login" class="btn">
                🚀 Se connecter au SNGP
            </a>

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
                    <span>Commencez à utiliser le système selon votre rôle</span>
                </div>
            </div>

        </div>

        <div class="footer">
            <p><strong>SNGP — Système National de Gestion Pharmaceutique</strong></p>
            <p>Ministère de la Santé et de l'Hygiène Publique — République de Guinée</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>

    </div>
</body>
</html>
