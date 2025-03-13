<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de dépôt de garantie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reçu de dépôt de garantie</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $customerName }},</p>
            
            <p>Veuillez trouver ci-joint votre reçu de dépôt de garantie n° <strong>{{ $document->document_number }}</strong>.</p>
            
            <p>Le dépôt de garantie a été enregistré pour le produit <strong>{{ $deposit->product->prolibcommercial }}</strong> 
               d'un montant de <strong>{{ number_format($deposit->deposit_amount, 2, ',', ' ') }} €</strong>.</p>
            
            <p>Ce dépôt est remboursable lors de la restitution du produit en bon état.</p>
            
            <p>Si vous avez des questions concernant ce dépôt, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br>
            L'équipe {{ config('app.name') }}</p>
        </div>
        
        <div class="footer">
            <p>Cet e-mail est généré automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>