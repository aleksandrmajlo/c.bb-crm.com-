<style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 56px 20px; background: #f4f4f4; color: #191919; font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px; line-height: 1.55; }
    .receipt-layout { display: flex; align-items: flex-start; justify-content: center; gap: 22px; max-width: 1040px; margin: 0 auto; }
    .receipt { width: 100%; max-width: 650px; padding: 48px 48px 38px; border-radius: 14px; background: #fff; box-shadow: 0 16px 55px rgba(0, 0, 0, .06); }
    .receipt-header { text-align: center; }
    .eyebrow, .action-label { color: #7a7a7a; font-size: 11px; font-weight: bold; letter-spacing: .1em; text-transform: uppercase; }
    .receipt-header h1 { margin: 12px 0 4px; font-size: 25px; line-height: 1.2; }
    .receipt-header p { margin: 0; color: #777; font-size: 13px; }
    .receipt-type { margin-top: 30px; padding: 13px 0; border-top: 2px dashed #d4d4d4; border-bottom: 1px solid #ececec; color: #565656; font-size: 12px; font-weight: bold; letter-spacing: .04em; }
    .receipt-details { padding: 14px 0; }
    .detail-row, .summary-row { display: flex; justify-content: space-between; gap: 20px; padding: 7px 0; }
    .detail-row span { color: #777; }
    .detail-row strong { max-width: 70%; text-align: right; overflow-wrap: anywhere; }
    .receipt-items { padding: 4px 0; }
    .item { width: 100%; border-collapse: collapse; }
    .item td { padding: 12px 0; vertical-align: bottom; }
    .item-detail { color: #888; font-size: 12px; }
    .item-price { width: 35%; text-align: right; white-space: nowrap; }
    .receipt-summary { margin-top: 14px; padding: 16px 0; border-top: 2px dashed #d4d4d4; border-bottom: 2px dashed #d4d4d4; }
    .total { margin-top: 4px; font-size: 17px; font-weight: bold; }
    .receipt-footer { padding-top: 22px; color: #878787; font-size: 11px; }
    .receipt-footer p { margin: 4px 0; }
    .receipt-actions { width: 270px; flex: 0 0 270px; padding: 24px; border-radius: 14px; background: #fff; }
    .receipt-actions p { margin: 8px 0 20px; color: #777; font-size: 13px; }
    .action { display: flex; justify-content: space-between; align-items: center; width: 100%; min-height: 46px; margin: 10px 0 0; padding: 11px 15px; border: 0; border-radius: 9px; cursor: pointer; font: inherit; font-weight: bold; text-decoration: none; }
    .action-print { background: #f1f1f1; color: #222; }
    .action-download { background: #171717; color: #fff; }
    .action:hover { opacity: .84; }
    .action:focus-visible { outline: 3px solid #6a9bd3; outline-offset: 3px; }
    .pdf-document { padding: 0; background: #fff; }
    .pdf-document .receipt { max-width: none; padding: 10px 24px; box-shadow: none; }
    @if(!empty($forPdf))
        .detail-row, .summary-row { display: block; }
        .detail-row strong, .summary-row span:last-child { float: right; }
    @endif
    @media (max-width: 700px) {
        body { padding: 20px 12px; }
        .receipt-layout { flex-direction: column; }
        .receipt { padding: 32px 24px; }
        .receipt-actions { width: 100%; flex-basis: auto; }
        .detail-row { flex-wrap: wrap; }
    }
    @media print {
        body { padding: 0; background: #fff; }
        .receipt-layout { display: block; }
        .receipt { max-width: none; padding: 0; box-shadow: none; }
        .receipt-actions { display: none; }
    }
</style>
