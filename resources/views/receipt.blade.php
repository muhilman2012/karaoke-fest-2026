<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt - Statement Coffee</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
      background: #eef1f5;
      color: #000;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 25px 15px;
      -webkit-font-smoothing: antialiased;
    }
    .toolbar {
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .btn {
      background: #0f172a;
      color: #fff;
      border: none;
      padding: 9px 18px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      transition: background 0.2s;
    }
    .btn:hover { background: #334155; }
    .btn-secondary {
      background: #fff;
      color: #0f172a;
      border: 1px solid #cbd5e1;
    }
    .btn-secondary:hover { background: #f8fafc; }

    /* Thermal receipt container */
    .receipt {
      width: 310px; /* 80mm default */
      background: #fff;
      padding: 22px 16px 26px 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      font-size: 12px;
      line-height: 1.4;
      color: #000;
      position: relative;
    }

    .center { text-align: center; }
    .bold { font-weight: 700; }
    .brand-title {
      font-size: 18px;
      font-weight: 900;
      letter-spacing: 2px;
      margin-bottom: 2px;
    }
    .brand-sub {
      font-size: 10px;
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }
    .address {
      font-size: 9.5px;
      line-height: 1.35;
      margin-bottom: 10px;
    }

    .divider-double {
      border-top: 2px double #000;
      margin: 7px 0;
    }
    .divider-dashed {
      border-top: 1px dashed #000;
      margin: 7px 0;
    }
    .divider-solid {
      border-top: 1px solid #000;
      margin: 7px 0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 2px;
    }

    .item-table {
      width: 100%;
      margin: 5px 0;
      font-size: 11.5px;
      border-collapse: collapse;
    }
    .item-table th {
      font-weight: 700;
      text-align: left;
      padding-bottom: 4px;
      border-bottom: 1px dashed #000;
    }
    .item-table th.col-amt, .item-table td.col-amt {
      text-align: right;
    }
    .item-table th.col-qty, .item-table td.col-qty {
      text-align: center;
      width: 25px;
    }
    .item-table td {
      padding: 4px 0 2px 0;
      vertical-align: top;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 2px;
    }
    .grand-total {
      font-size: 14px;
      font-weight: 900;
      margin: 5px 0;
    }

    .payment-section {
      margin-top: 6px;
      font-size: 10.5px;
    }
    .payment-title {
      font-weight: 700;
      margin-bottom: 4px;
      text-align: center;
    }

    .barcode-area {
      margin-top: 12px;
      text-align: center;
    }
    .barcode-svg {
      width: 80%;
      height: 38px;
    }
    .barcode-num {
      font-size: 9.5px;
      letter-spacing: 2px;
      margin-top: 2px;
    }

    .footer-note {
      font-size: 10px;
      text-align: center;
      margin-top: 10px;
      line-height: 1.4;
    }

    /* Khusus saat diprint ke printer kiosk */
    @media print {
      body {
        background: #fff;
        padding: 0;
        margin: 0;
      }
      .toolbar {
        display: none !important;
      }
      .receipt {
        width: 100% !important;
        max-width: 80mm;
        box-shadow: none !important;
        padding: 2mm 1mm;
        margin: 0 auto;
      }
      @page {
        size: 80mm auto;
        margin: 0;
      }
    }
  </style>
</head>
<body>

  <div class="toolbar">
    <button class="btn" onclick="window.print()">
      🖨️ Cetak Struk (Print)
    </button>
    <button class="btn btn-secondary" onclick="toggleWidth()">
      Lebar: <span id="width-label">80mm</span>
    </button>
  </div>

  <div class="receipt" id="receipt">
    <div class="center brand-title">STATEMENT</div>
    <div class="center brand-sub">COFFEE &amp; ART SPACE</div>
    <div class="center address">
      Jl. Tegeh Sari III No.2, Kuta<br>
      Kec. Kuta, Kab. Badung, Bali 80361<br>
      Telp: +62 812-3890-4112
    </div>

    <div class="divider-double"></div>

    <div class="info-row">
      <span>Date: 21/10/2026</span>
      <span>Time: 14:13:25</span>
    </div>
    <div class="info-row">
      <span>Receipt: #STM-261021-084</span>
      <span>Table: 04</span>
    </div>
    <div class="info-row">
      <span>Cashier: Dewa</span>
      <span>Order: Dine In</span>
    </div>
    <div class="info-row">
      <span>Customer: <strong>Nira</strong></span>
      <span>POS: #01</span>
    </div>

    <div class="divider-dashed"></div>

    <table class="item-table">
      <thead>
        <tr>
          <th style="width: 58%;">ITEM</th>
          <th class="col-qty">QTY</th>
          <th class="col-amt">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Kusamba Sea Salt</td>
          <td class="col-qty">1</td>
          <td class="col-amt">63.000</td>
        </tr>
        <tr>
          <td>Ricebowl Chicken Teriyaki</td>
          <td class="col-qty">1</td>
          <td class="col-amt">73.000</td>
        </tr>
        <tr>
          <td>Déjà Vu</td>
          <td class="col-qty">1</td>
          <td class="col-amt">69.000</td>
        </tr>
        <tr>
          <td>Midori Ichigo</td>
          <td class="col-qty">1</td>
          <td class="col-amt">59.000</td>
        </tr>
      </tbody>
    </table>

    <div class="divider-dashed"></div>

    <div class="total-section">
      <div class="total-row">
        <span>Total Items (4 items)</span>
        <span>264.000</span>
      </div>
      <div class="total-row">
        <span>Subtotal</span>
        <span>Rp 264.000</span>
      </div>
      <div class="total-row">
        <span>Service Charge (5%)</span>
        <span>Rp 13.200</span>
      </div>
      <div class="total-row">
        <span>PB1 / Gov. Tax (10%)</span>
        <span>Rp 27.720</span>
      </div>
      <div class="divider-solid"></div>
      <div class="total-row grand-total">
        <span>GRAND TOTAL</span>
        <span>Rp 304.920</span>
      </div>
    </div>

    <div class="divider-dashed"></div>

    <div class="payment-section">
      <div class="payment-title">--- TRANSAKSI EDC BCA ---</div>
      <div class="info-row">
        <span>Merchant:</span>
        <span>STATEMENT COFFEE</span>
      </div>
      <div class="info-row">
        <span>MID / TID:</span>
        <span>00088291 / BCA09412</span>
      </div>
      <div class="info-row">
        <span>Card Type:</span>
        <span>BCA DEBIT CHIP</span>
      </div>
      <div class="info-row">
        <span>Card No:</span>
        <span>5264 49** **** 8821</span>
      </div>
      <div class="info-row">
        <span>Cardholder:</span>
        <span>NIRA</span>
      </div>
      <div class="info-row">
        <span>Batch / Trace:</span>
        <span>000102 / 003921</span>
      </div>
      <div class="info-row">
        <span>Appr Code:</span>
        <span class="bold">781923</span>
      </div>
      <div class="info-row">
        <span>Amount Paid:</span>
        <span class="bold">Rp 304.920</span>
      </div>
      <div class="info-row">
        <span>Kembalian:</span>
        <span>Rp 0</span>
      </div>
      <div class="info-row" style="margin-top: 2px;">
        <span>Status:</span>
        <span class="bold">APPROVED - ONLINE</span>
      </div>
    </div>

    <div class="divider-double"></div>

    <div class="barcode-area">
      <svg class="barcode-svg" viewBox="0 0 200 45">
        <rect x="0" y="0" width="3" height="45" fill="#000"/>
        <rect x="5" y="0" width="2" height="45" fill="#000"/>
        <rect x="10" y="0" width="4" height="45" fill="#000"/>
        <rect x="18" y="0" width="2" height="45" fill="#000"/>
        <rect x="23" y="0" width="5" height="45" fill="#000"/>
        <rect x="32" y="0" width="2" height="45" fill="#000"/>
        <rect x="37" y="0" width="4" height="45" fill="#000"/>
        <rect x="45" y="0" width="2" height="45" fill="#000"/>
        <rect x="50" y="0" width="6" height="45" fill="#000"/>
        <rect x="60" y="0" width="2" height="45" fill="#000"/>
        <rect x="65" y="0" width="5" height="45" fill="#000"/>
        <rect x="74" y="0" width="3" height="45" fill="#000"/>
        <rect x="80" y="0" width="2" height="45" fill="#000"/>
        <rect x="85" y="0" width="6" height="45" fill="#000"/>
        <rect x="94" y="0" width="3" height="45" fill="#000"/>
        <rect x="100" y="0" width="5" height="45" fill="#000"/>
        <rect x="110" y="0" width="2" height="45" fill="#000"/>
        <rect x="115" y="0" width="5" height="45" fill="#000"/>
        <rect x="124" y="0" width="3" height="45" fill="#000"/>
        <rect x="130" y="0" width="5" height="45" fill="#000"/>
        <rect x="140" y="0" width="2" height="45" fill="#000"/>
        <rect x="145" y="0" width="4" height="45" fill="#000"/>
        <rect x="153" y="0" width="6" height="45" fill="#000"/>
        <rect x="162" y="0" width="2" height="45" fill="#000"/>
        <rect x="168" y="0" width="4" height="45" fill="#000"/>
        <rect x="176" y="0" width="5" height="45" fill="#000"/>
        <rect x="185" y="0" width="3" height="45" fill="#000"/>
        <rect x="192" y="0" width="4" height="45" fill="#000"/>
      </svg>
      <div class="barcode-num">STM261021084NIRA</div>
    </div>

    <div class="footer-note">
      WiFi: <strong>Statement_Guest</strong> (stmnt88)<br>
      Follow Instagram: <strong>@statementcoffee</strong><br>
      *** Terima Kasih Atas Kunjungan Anda ***<br>
      <em>Barang yang dibeli tidak dapat ditukar/dikembalikan</em>
    </div>
  </div>

  <script>
    // Auto-print jika membuka dengan URL parameter: ?autoprint=1
    window.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('autoprint') === '1') {
        window.print();
      }
    });

    let is80 = true;
    function toggleWidth() {
      const receipt = document.getElementById('receipt');
      const label = document.getElementById('width-label');
      is80 = !is80;
      if (is80) {
        receipt.style.width = '310px';
        label.textContent = '80mm';
      } else {
        receipt.style.width = '230px';
        label.textContent = '58mm';
      }
    }
  </script>
</body>
</html>