<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $documentName }}</title>
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
            <h1>{{ $documentName }} #{{ $document->document_number }}</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $customerName }},</p>
            
            <p>Veuillez trouver ci-joint votre {{ mb_strtolower($documentName) }} n° <strong>{{ $document->document_number }}</strong>.</p>
            
            @if($document->document_type == 'invoice')
                <p>Le montant total de cette facture est de <strong>{{ number_format($document->total_amount, 2, ',', ' ') }} €</strong>.</p>
                
                @if($document->payment_status == 'pending')
                    <p>Cette facture est actuellement en attente de paiement.</p>
                @elseif($document->payment_status == 'paid')
                    <p>Cette facture a été payée le {{ date('d/m/Y', strtotime($document->payment_date)) }}.</p>
                @endif
            @endif
            
            <p>Si vous avez des questions concernant ce document, n'hésitez pas à nous contacter.</p>
            
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