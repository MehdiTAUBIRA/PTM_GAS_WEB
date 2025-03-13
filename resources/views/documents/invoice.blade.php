<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $document->document_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            margin-bottom: 5px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info-block {
            max-width: 45%;
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
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total-row {
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
        .payment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #047857;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FACTURE</h1>
            <p>N° {{ $document->document_number }}</p>
        </div>

        <div class="invoice-info">
            <div class="invoice-info-block">
                <div class="info-box">
                    <h2>Émetteur</h2>
                    <p>{{ config('app.name') }}</p>
                    <p>123 Rue Exemple</p>
                    <p>75000 Paris</p>
                    <p>France</p>
                    <p>Tél: 01 23 45 67 89</p>
                    <p>Email: contact@example.com</p>
                </div>
            </div>

            <div class="invoice-info-block">
                <div class="info-box">
                    <h2>Client</h2>
                    <p>{{ $document->customer->customer_name }} {{ $document->customer->customer_firstname }}</p>
                    @if($document->order && $document->order->customerAddress)
                        <p>{{ $document->order->customerAddress->address }}</p>
                        <p>{{ $document->order->customerAddress->postalcode }}</p>
                        <p>{{ $document->order->customerAddress->country }}</p>
                    @endif
                    <p>Tél: {{ $document->customer->customer_phone }}</p>
                    <p>Email: {{ $document->customer->customer_email }}</p>
                </div>
            </div>
        </div>

        <div class="info-box">
            <h2>Informations de la facture</h2>
            <table>
                <tr>
                    <td><strong>Date d'émission:</strong></td>
                    <td>{{ date('d/m/Y', strtotime($document->document_date)) }}</td>
                </tr>
                <tr>
                    <td><strong>N° de commande:</strong></td>
                    <td>{{ $document->order->order_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Statut de paiement:</strong></td>
                    <td>
                        @php
                            $statusClasses = [
                                'paid' => 'status-paid',
                                'pending' => 'status-pending',
                                'cancelled' => 'status-cancelled'
                            ];
                            $statusTexts = [
                                'paid' => 'Payé',
                                'pending' => 'En attente',
                                'cancelled' => 'Annulé'
                            ];
                        @endphp
                        <span class="payment-status {{ $statusClasses[$document->payment_status] ?? '' }}">
                            {{ $statusTexts[$document->payment_status] ?? $document->payment_status }}
                        </span>
                    </td>
                </tr>
                @if($document->payment_status == 'paid')
                <tr>
                    <td><strong>Date de paiement:</strong></td>
                    <td>{{ date('d/m/Y', strtotime($document->payment_date)) }}</td>
                </tr>
                <tr>
                    <td><strong>Méthode de paiement:</strong></td>
                    <td>
                        @php
                            $paymentMethods = [
                                'cash' => 'Espèces',
                                'card' => 'Carte bancaire',
                                'transfer' => 'Virement',
                                'check' => 'Chèque'
                            ];
                        @endphp
                        {{ $paymentMethods[$document->payment_method] ?? $document->payment_method }}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <h2>Détails</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if($document->order && $document->order->orderDetails)
                    @foreach($document->order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->product->prolibcommercial }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ number_format($detail->unit_price, 2, ',', ' ') }} €</td>
                        <td>{{ number_format($detail->quantity * $detail->unit_price, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right">Total</td>
                    <td>{{ number_format($document->total_amount, 2, ',', ' ') }} €</td>
                </tr>
            </tfoot>
        </table>

        @if($document->comments)
        <div class="info-box">
            <h2>Commentaires</h2>
            <p>{{ $document->comments }}</p>
        </div>
        @endif

        <div class="footer">
            <p>{{ config('app.name') }} - Document généré le {{ date('d/m/Y à H:i') }}</p>
            <p>SIRET: 123 456 789 00012 - TVA: FR12 345 678 901</p>
        </div>
    </div>
</body>
</html>