<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de dépôt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info p {
            margin: 5px 0;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box h2 {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .deposit-amount {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>REÇU DE DÉPÔT DE GARANTIE</h1>
            <p>N° {{ $document->document_number }}</p>
        </div>

        <div class="receipt-info">
            <p><strong>Date:</strong> {{ date('d/m/Y', strtotime($document->document_date)) }}</p>
        </div>

        <div class="info-box">
            <h2>Informations client</h2>
            <div class="info-row">
                <span class="info-label">Client:</span> 
                {{ $deposit->customer->customer_name }} {{ $deposit->customer->customer_firstname }}
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span> 
                {{ $deposit->customer->customer_email }}
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone:</span> 
                {{ $deposit->customer->customer_phone }}
            </div>
        </div>

        <div class="info-box">
            <h2>Détails du dépôt</h2>
            <div class="info-row">
                <span class="info-label">Produit:</span> 
                {{ $deposit->product->prolibcommercial }}
            </div>
            <div class="info-row">
                <span class="info-label">Date du dépôt:</span> 
                {{ date('d/m/Y', strtotime($deposit->deposit_date)) }}
            </div>
            <div class="info-row">
                <span class="info-label">Méthode de paiement:</span> 
                @php
                    $paymentMethods = [
                        'cash' => 'Espèces',
                        'card' => 'Carte bancaire',
                        'transfer' => 'Virement',
                        'check' => 'Chèque'
                    ];
                @endphp
                {{ $paymentMethods[$deposit->deposit_payment_method] ?? $deposit->deposit_payment_method }}
            </div>
            @if($deposit->comments)
            <div class="info-row">
                <span class="info-label">Commentaires:</span> 
                {{ $deposit->comments }}
            </div>
            @endif
        </div>

        <div class="deposit-amount">
            Montant du dépôt: {{ number_format($deposit->deposit_amount, 2, ',', ' ') }} €
        </div>

        <div class="footer">
            <p>Ce dépôt de garantie est remboursable lors de la restitution du produit en bon état.</p>
            <p>{{ config('app.name') }} - Document généré le {{ date('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>