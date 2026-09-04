<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 12px;
        }

        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .muted {
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        th,
        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .right {
            text-align: right;
        }

        .total {
            font-size: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="header">
    <p class="title">Susadhya Counselling</p>
    <p class="muted">Invoice {{ $invoice->invoice_number }}</p>
</div>

<p>
    <strong>Client:</strong>
    {{ $invoice->billing_name }}
</p>

<p>
    <strong>Email:</strong>
    {{ $invoice->billing_email ?: 'Not provided' }}
</p>

<p>
    <strong>Issued:</strong>
    {{ $invoice->issued_at?->format('Y-m-d H:i') }}
</p>

<table>
    <thead>
    <tr>
        <th>Description</th>
        <th class="right">Amount</th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>
            {{ $invoice->payment->appointment->counsellingService?->name ?? 'Counselling service' }}
        </td>

        <td class="right">
            {{ $invoice->currency }}
            {{ number_format((float) $invoice->total, 2) }}
        </td>
    </tr>

    <tr>
        <td class="total">Total</td>

        <td class="right total">
            {{ $invoice->currency }}
            {{ number_format((float) $invoice->total, 2) }}
        </td>
    </tr>
    </tbody>
</table>

<p class="muted" style="margin-top: 30px;">
    This document contains financial information only.
    No confidential counselling or clinical information is included.
</p>
</body>
</html>
