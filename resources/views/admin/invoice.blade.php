<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 10px;
            font-size: 13px;
            line-height: 1.5;
        }

        .invoice-header {
            border-bottom: 2px solid #00bde0;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .address-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .address-table td {
            padding: 0;
            vertical-align: top;
        }

        .academy-title, .student-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 6px 0;
        }

        .address-text {
            color: #334155;
            line-height: 1.5;
            font-size: 13px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            table-layout: fixed;
        }

        .meta-table td {
            padding: 6px 4px;
            vertical-align: middle;
            font-size: 13px;
            word-wrap: break-word;
        }

        .meta-label {
            font-weight: bold;
            color: #475569;
        }

        .meta-value {
            color: #0f172a;
            font-weight: bold;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
        }

        .items th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            padding: 10px 8px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .items td {
            border: 1px solid #cbd5e1;
            padding: 10px 8px;
            font-size: 12px;
            color: #1e293b;
        }

        .align-right {
            text-align: right;
        }

        .align-center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
            color: #0f172a;
        }

        .footer {
            margin-top: 80px;
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
            border-top: 1px solid #cbd5e1;
            padding-top: 15px;
        }

        .footer-note {
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <div class="invoice-header">
        <h2 class="invoice-title"><?php echo ($isIndia) ? 'TAX' : 'EXPORT' ?> INVOICE</h2>
    </div>

    <table class="address-table">
        <tr>
            <td width="50%">
                <h3 class="academy-title">BAPS Pramukh Academy</h3>
                <div class="address-text">
                    Near BAPS Shastriji Maharaj Hospital,<br>
                    Shastri Yagnapurush Marg, Atladara,<br>
                    Vadodara, Gujarat 390012
                </div>
            </td>
            <td width="50%" style="padding-left: 20px;">
                <h3 class="student-title">{{ $student_name }}</h3>
                <div class="address-text">
                    {{ $address }}<br>
                    <strong>Phone:</strong> {{ $phone }}<br>
                    <strong>Email:</strong> {{ $email }}
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <?php if ($isIndia) : ?>
        <tr>
            <td class="meta-label" width="18%">GSTIN:</td>
            <td class="meta-value" width="32%">24AACTD6200B1ZK</td>
            <td class="meta-label" width="18%">Invoice No:</td>
            <td class="meta-value" width="32%">{{ $invoice_no }}</td>
        </tr>
        <?php else: ?>
        <tr>
            <td class="meta-label" width="18%">LUT No:</td>
            <td class="meta-value" width="32%">{{ isset($slug) && $slug ? $slug : 'N/A' }}</td>
            <td class="meta-label" width="18%">Invoice No:</td>
            <td class="meta-value" width="32%">{{ $invoice_no }}</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="meta-label" width="18%">PAN No.:</td>
            <td class="meta-value" width="32%">AACTD6200B</td>
            <td class="meta-label" width="18%">Payment Date:</td>
            <td class="meta-value" width="32%">{{ $payment_date }}</td>
        </tr>
        <tr>
            <td class="meta-label" width="18%">Order No:</td>
            <td class="meta-value" width="32%">{{ $order_no }}</td>
            <td class="meta-label" width="18%">Order Date:</td>
            <td class="meta-value" width="32%">{{ $order_date }}</td>
        </tr>
        <tr>
            <td class="meta-label" width="18%">Place of Supply:</td>
            <td class="meta-value" colspan="3">{{ $place }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th width="8%" class="align-center">#</th>
                <th width="15%" class="align-center">SAC</th>
                <th width="57%">Course Name</th>
                <th width="20%" class="align-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
            <tr>
                <td class="align-center">{{ $index + 1 }}</td>
                <td class="align-center">{{ $item['sac'] }}</td>
                <td>{{ $item['course'] }}</td>
                <td class="align-right">{{ number_format(floatval($item['amount']), 2, '.', '') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="align-right">Sub Total</td>
                <td class="align-right">{{ number_format(floatval($sub_total), 2, '.', '') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="align-right">ADD CGST (9%)</td>
                <td class="align-right">{{ number_format(floatval($cgst), 2, '.', '') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="align-right">ADD SGST (9%)</td>
                <td class="align-right">{{ number_format(floatval($sgst), 2, '.', '') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="align-right">ADD IGST (18%)</td>
                <td class="align-right">{{ number_format(floatval($igst), 2, '.', '') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="align-right">Final Amount</td>
                <td class="align-right">{{ number_format(floatval($final_amount), 2, '.', '') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="align-right">Final Amount in words</td>
                <td class="align-right" style="font-size: 11px;">{{ $final_amount_word }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="footer-note">Whether tax is payable on reverse charge basis - <strong>NO</strong></div>
        <div style="margin-top: 10px;">
            <div class="footer-note"># This fee receipt is valid subject to cheque realisation or online payment confirmation.</div>
            <div class="footer-note"># Subject to Local Jurisdiction where the receipt is generated.</div>
            <div class="footer-note"># Fees once paid are neither refundable nor transferable.</div>
            <div class="footer-note"># This is a computer-generated receipt and does not require any stamp or signature.</div>
        </div>
    </div>
</body>

</html>