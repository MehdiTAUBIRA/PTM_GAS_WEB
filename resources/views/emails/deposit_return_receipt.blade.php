<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de remboursement de dépôt</title>
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
            <h1>Reçu de remboursement de dépôt</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $customerName }},</p>
            
            <p>Veuillez trouver ci-joint votre reçu de remboursement de dépôt n° <strong>{{ $document->document_number }}</strong>.</p>
            
            <p>Le remboursement du dépôt de garantie a été effectué pour le produit <strong>{{ $deposit->product->prolibcommercial }}</strong> 
               d'un montant de <strong>{{ number_format(abs($document->amount), 2, ',', ' ') }} €</strong>.</p>
            
            <p>Nous vous remercions d'avoir restitué le produit.</p>
            
            <p>Si vous avez des questions concernant ce remboursement, n'hésitez pas à nous contacter.</p>
            
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