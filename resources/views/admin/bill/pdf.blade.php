<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill - {{ $bill->bill_no }}</title>
    <style>
        @page {
            margin: 15px;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 12px; 
            color: #000; 
            margin: 0; 
            padding: 0;
            line-height: 1.35;
            letter-spacing: 0.1px;
        }
        
        .main-container {
            border: 1px solid #000;
            width: 100%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            text-align: left;
        }
        
        /* Specific Styles */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .bold { font-weight: bold; }
        .no-border { border: none !important; }
        .no-left { border-left: none !important; }
        .no-right { border-right: none !important; }
        .no-top { border-top: none !important; }
        .no-bottom { border-bottom: none !important; }
        
        .company-name {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
            margin: 2px 0;
        }
        
        .tax-invoice-title {
            background-color: #ededed;
            font-size: 18px;
            font-weight: bold;
            height: 40px;
            vertical-align: middle !important;
        }
        
        .section-header {
            padding: 1px 12px !important;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        
        .item-row td {
            height: 16px;
            border-top: none !important;
            border-bottom: none !important;
            font-size: 12.5px;
            padding-top: 1px;
            padding-bottom: 1px;
        }
        
        .totals-bar td {
            font-weight: bold;
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            padding: 6px;
        }
        
        .footer-summary td {
            padding: 3px 6px;
            font-size: 11px;
        }
    </style>
</head>
<body>

@php
    function getIndianCurrency($number) {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        return trim($Rupees) . " Only";
    }

    $is_igst = false;
    $customer_state = strtoupper($bill->customer->state ?? '');
    if ($customer_state != '' && (strpos($customer_state, 'PUNJAB') !== false || strpos($customer_state, 'PB') !== false)) {
        $is_igst = false;
    } else {
        $is_igst = true;
    }
    $half_gst = $bill->igst_amount / 2;
    $raw_total = $bill->taxable_amount + $bill->igst_amount;
    $round_off = $bill->grand_total - $raw_total;
@endphp

<div class="main-container">
    <!-- Header -->
    <table class="no-border">
        <tr>
            <td width="32%" class="no-border" style="font-size: 10px; padding-left: 10px;">
                GSTIN : 03AEKFS3882A1Z6<br>PAN &nbsp;: AEKFS3882A
            </td>
            <td width="36%" class="no-border text-center">
                <div style="font-size: 10px;">UDYAM-PB-12-0125762</div>
                <div class="company-name">SHIVA POLYFAB</div>
                <div style="font-size: 10px; font-weight: bold;">
                    ALL KIND OF BOPP SHOPPING BAGS AND SACKS<br>
                    M.K ROAD,OPP. GODREJ AGROVET,VILL.IKOLAHA, Vill Ikolaha (PB)<br>
                    shivapolyfab@gmail.com
                </div>
            </td>
            <td width="32%" class="no-border text-right" style="font-size: 10px; padding-right: 10px;">
                Tel : 9988110853<br>9988110892
            </td>
        </tr>
    </table>

    <!-- Controls Bar -->
    <table class="no-left no-right">
        <tr>
            <td width="38%" class="tax-invoice-title text-center no-left">TAX-INVOICE</td>
            <td width="32%" style="padding: 0;">
                <table class="no-border">
                    <tr><td class="no-border bold" style="border-bottom: 1px solid #000 !important; padding: 5px;">Invoice No. &nbsp; SP-{{ $bill->bill_no }}</td></tr>
                    <tr><td class="no-border bold" style="padding: 5px;">Invoice Dt. &nbsp; {{ date('d-m-Y', strtotime($bill->bill_date)) }}</td></tr>
                </table>
            </td>
            <td width="30%" style="padding: 0; border-right: none;">
                <table class="no-border">
                    <tr><td class="no-border" style="border-bottom: 1px solid #000 !important; padding: 5px;">Reverse Charge : No</td></tr>
                    <tr><td class="no-border" style="padding: 5px;">Payment Mode : CREDIT</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="text-center no-left">Duplicate Copy</td>
            <td style="padding: 0;">
                <table class="no-border">
                    <tr><td class="no-border" style="border-bottom: 1px solid #000 !important; padding: 5px;">Vehicle No.</td></tr>
                    <tr><td class="no-border" style="padding: 5px;">Transport Name :</td></tr>
                </table>
            </td>
            <td style="padding: 0; border-right: none;">
                <table class="no-border">
                    <tr><td class="no-border" style="border-bottom: 1px solid #000 !important; padding: 5px;">GR No.</td></tr>
                    <tr><td class="no-border" style="padding: 5px;">&nbsp;</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-header">Details of Receiver ( Billed to )</div>

    <!-- Receiver Content -->
    <div style="padding: 2px 12px;">
        <table class="no-border">
            <tr>
                <td width="65px" class="no-border" style="padding: 1px 0;">Name :</td>
                <td class="no-border bold" style="padding: 1px 0;">{{ $bill->customer->name ?? '' }}</td>
            </tr>
            <tr>
                <td width="65px" class="no-border" style="padding: 1px 0;">Address:</td>
                <td class="no-border" style="padding: 1px 0;">
                    {{ $bill->customer->address ?? '' }}<br>
                    @if($bill->customer->city) {{ strtoupper($bill->customer->city) }} , @endif KHANNA
                </td>
            </tr>
            <tr>
                <td width="65px" class="no-border" style="padding: 1px 0;">State:</td>
                <td class="no-border" style="padding: 1px 0;">{{ strtoupper($bill->customer->state ?? '') }} @if($bill->customer->pincode)-{{ substr($bill->customer->pincode, 0, 2) }}@endif</td>
            </tr>
            <tr>
                <td class="no-border" colspan="2" style="padding: 2px 0 0 0;">
                    <table class="no-border">
                        <tr>
                            <td width="65px" class="no-border" style="padding: 1px 0;">GST No.</td>
                            <td width="160px" class="no-border bold" style="padding: 1px 0;">{{ $bill->customer->gst ?? '' }}</td>
                            <td width="65px" class="no-border" style="padding: 1px 0;">P A N:</td>
                            <td class="no-border bold" style="padding: 1px 0;">{{ strlen($bill->customer->gst ?? '') >= 12 ? substr($bill->customer->gst, 2, 10) : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <table class="no-left no-right" style="border-top: 1px solid #000;">
        <thead>
            <tr>
                <th width="4.5%" class="text-center no-left">Sr</th>
                <th width="36.5%" class="text-center">Description of Goods</th>
                <th width="10.5%" class="text-center">HSN</th>
                <th width="4.5%" class="text-center">Pcs</th>
                <th width="10.5%" class="text-center">Qty.</th>
                <th width="5.5%" class="text-center">Unit</th>
                <th width="5.5%" class="text-center">Gst@</th>
                <th width="9%" class="text-center">Rate</th>
                <th width="13% text-center no-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total_qty = 0; @endphp
            @foreach($bill->items as $index => $item)
            @php $total_qty += $item->qty; @endphp
            <tr class="item-row">
                <td class="text-center no-left">{{ $index+1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">
                    @php
                        $hsn = '-';
                        if ($item->gst_percent == 5) $hsn = '56039300';
                        elseif ($item->gst_percent == 18) $hsn = '63059000';
                    @endphp
                    {{ $hsn }}
                </td>
                <td class="text-center"></td>
                <td class="text-right">{{ number_format($item->qty, 3) }}</td>
                <td class="text-center">{{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->gst_percent, 2) }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right no-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
            
            @php $pad = 14 - count($bill->items); @endphp
            @for($i=0; $i<$pad; $i++)
            <tr class="item-row">
                <td class="no-left"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class="no-right"></td>
            </tr>
            @endfor

            <tr class="totals-bar">
                <td colspan="4" class="text-right no-left">Total</td>
                <td class="text-right">{{ number_format($total_qty, 3) }}</td>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right no-right">{{ number_format($bill->taxable_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Summary -->
    <table class="no-border">
        <tr>
            <td width="65%" style="border-right: 1px solid #000; padding: 10px; vertical-align: top;">
                <span class="bold">HDFC BANK LTD, GRAIN MARKET, KHANNA</span><br>
                A/C NO. : 50200060188310<br>
                IFSC : HDFC0002765<br><br>
                <span class="bold" style="font-size: 10px;">Terms & Conditions :</span>
                <div style="font-size: 9px; line-height: 1.3;">
                    1. Our responsibility ceases after the goods are dispatched from our premises.<br>
                    2. Goods once sold are not returnable or exchangeable<br>
                    3. If the bill is not paid within 30 days Interest @18% will be charged from the date of bill.<br>
                    Subject to KHANNA Jurisdiction Only
                </div>
            </td>
            <td width="35%" style="padding: 0;">
                <table class="no-border footer-summary">
                    <tr><td class="no-border" width="55%">Expenses</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;"></td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">Taxable Value</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;">{{ number_format($bill->taxable_amount, 2) }}</td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">C.GST</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;">{{ !$is_igst && $half_gst > 0 ? number_format($half_gst, 2) : '' }}</td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">S.GST</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;">{{ !$is_igst && $half_gst > 0 ? number_format($half_gst, 2) : '' }}</td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">I.GST</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;">{{ $is_igst && $bill->igst_amount > 0 ? number_format($bill->igst_amount, 2) : '' }}</td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">&nbsp;</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;"></td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border">Round Off</td><td class="no-border text-right" style="border-left: 1px solid #000 !important;">{{ $round_off != 0 ? number_format($round_off, 2) : '' }}</td></tr>
                    <tr style="border-top: 1px solid #000;"><td class="no-border bold" style="padding: 8px 6px;">Grand Total</td><td class="no-border text-right bold" style="border-left: 1px solid #000 !important; padding: 8px 6px; font-size: 16px;">{{ number_format($bill->grand_total, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="border-top: 1px solid #000; padding: 8px 15px;">
        <span class="bold">Rs.(Inwords)</span> &nbsp;&nbsp;&nbsp; {{ getIndianCurrency($bill->grand_total) }}
    </div>

    <!-- Signatures -->
    <div style="border-top: 1px solid #000; height: 110px;">
        <div class="text-right bold" style="padding: 10px 15px 0 0;">For SHIVA POLYFAB</div>
        <div style="margin-top: 50px;">
            <table class="no-border">
                <tr>
                    <td class="no-border bold" width="33%" style="padding-left: 15px;">E.& O.E.</td>
                    <td class="no-border bold text-center" width="34%">Checked By</td>
                    <td class="no-border bold text-right" width="33%" style="padding-right: 15px;">Authorised Sign./ Partner</td>
                </tr>
            </table>
        </div>
    </div>
</div>

</body>
</html>
