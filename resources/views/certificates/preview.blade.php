<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Great+Vibes&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;600;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #e5e7eb;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }

        .no-print {
            margin: 20px 0;
        }

        /* 
         * ROOT CAUSE FIX: The base container must match the exact pixel dimensions of the background image (2481x3508)
         * We then scale it down ONLY for the screen viewer, keeping native measurements mathematically perfect.
         */
        
        .certificate-wrapper {
            /* Allocates space for the scaled down 0.32 certificate */
            width: 794px;
            height: 1123px;
            position: relative;
            background: white;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            margin: 0 auto 40px auto;
        }

        #certificate {
            position: absolute;
            top: 0;
            left: 0;
            width: 2481px;
            height: 3508px;
            background-image: url('{{ asset('images/certificates/sankalp-certificate.png') }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            transform: scale(0.32003); /* 794 / 2481 */
            transform-origin: top left;
        }

        /* 1. Certificate Number (Native Pixels) */
        .dynamic-cert-no {
            position: absolute;
            top: 813px; /* ✅ adjusted */
            left: 560px;
            font-size: 46px;
            font-weight: 700;
            color: #1f3c5b;
            line-height: 1;
        }

        /* 2. Generated Date (Native Pixels) */
        .dynamic-date {
            position: absolute;
            top: 813px; /* ✅ EXACT SAME top */
            right: 250px;
            font-size: 46px;
            font-weight: 700;
            color: #1f3c5b;
            line-height: 1;
        }
        /* 3. Recipient Name (Native Pixels) */
        .recipient-name-overlay {
            position: absolute;
            top: 1032px; /* ✅ corrected based on gap center */
            left: 50%;
            transform: translateX(-50%);
            width: 1800px; /* prevent overflow */
            text-align: center;
        }

        .recipient-name-overlay h2 {
            font-family: 'Cinzel', serif;
            font-size: 75px; /* reduced to avoid overlap */
            color: #1f3c5b;
            font-weight: 550;
            margin: 0;
            letter-spacing: 2px;
            line-height: 1.2;
        }

        /* 4. Batch Dates (Native Pixels) */
        .batch-dates-overlay {
            position: absolute;
            top: 1362px;
            left: 1540px;
            font-size: 46px;
            font-weight: 700;
            color: #1f3c5b;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
        }

        /* Print Settings (Strict Fixed Size) */
        @media print {
            body { 
                background: white; 
                margin: 0;
                padding: 0;
                display: block;
            }
            .no-print { display: none !important; }
            .certificate-wrapper {
                box-shadow: none;
                margin: 0;
                width: 2481px;
                height: 3508px;
            }
            #certificate { 
                transform: scale(1) !important;
                width: 2481px !important;
                height: 3508px !important;
                position: absolute;
                top: 0;
                left: 0;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            @page {
                size: 2481px 3508px;
                margin: 0;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-size: 15px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .btn-primary { background: #00bde0; color: white; }
        .btn-primary:hover { background: #0099b8; transform: translateY(-1px); }
        .btn-outline { background: white; border: 1px solid #d1d5db; color: #4b5563; }
        .btn-outline:hover { background: #f9fafb; }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div id="certificate">
            <!-- 1. Certificate No -->
            <div class="dynamic-cert-no">
                {{ $certificate->certificate_no }}
            </div>
            
            <!-- 2. Generated Date -->
            <div class="dynamic-date">
                {{ $certificate->generated_date ? $certificate->generated_date->format('l, F d, Y') : '-' }}
            </div>

            <!-- 3. Recipient Name -->
            <div class="recipient-name-overlay">
                <h2>{{ $certificate->recipient_name }}</h2>
            </div>

            <!-- 4. Batch Dates -->
            <div class="batch-dates-overlay">
                {{ $certificate->start_date ? $certificate->start_date->format('F, Y') : 'N/A' }} to {{ $certificate->end_date ? $certificate->end_date->format('F, Y') : 'N/A' }}
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            @if(request()->has('print'))
                setTimeout(() => { window.print(); }, 800);
            @endif
        }
    </script>
</body>
</html>
