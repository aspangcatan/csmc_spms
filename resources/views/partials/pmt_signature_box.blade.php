{{--
    The CSMC PMT box on the print forms, holding the signature of every PMT member who approved.
    Params: $signatures (from PmtApprovalService::signaturesFor), $record (IPCR/SPCR, for the date once fully approved).
--}}
@php
    $slots = max(8, count($signatures));
    $approvedDate = $record->status === 'PMT Approved' && count($signatures)
        ? $signatures->last()->signed_at->format('F j, Y')
        : '';
@endphp
<style>
    /* Transparent like the old stamp image, so the form underneath stays readable through the box. */
    .pmt-box { width: 210px; border: 2px solid #000; background: transparent; font-family: Calibri, Arial, sans-serif; pointer-events: none; }
    .pmt-box-title { border-bottom: 2px solid #000; text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 1px; padding: 2px 0; }
    .pmt-box-signatures { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 3px double #000; }
    .pmt-box-slot { height: 34px; padding: 1px 2px; text-align: center; overflow: hidden; }
    .pmt-box-slot img { display: block; margin: 0 auto; max-width: 100%; height: 24px; object-fit: contain; }
    .pmt-box-slot span { display: block; font-size: 6px; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pmt-box-date-value { height: 16px; border-bottom: 1px solid #000; text-align: center; font-size: 9px; font-weight: bold; padding-top: 2px; }
    .pmt-box-date-label { text-align: center; font-size: 9px; padding: 1px 0; }
</style>
<div class="pmt-box">
    <div class="pmt-box-title">CSMC PMT</div>
    <div class="pmt-box-signatures">
        @for ($i = 0; $i < $slots; $i++)
            <div class="pmt-box-slot">
                @if (isset($signatures[$i]))
                    @if ($signatures[$i]->signature)
                        <img src="{{ $signatures[$i]->signature }}" alt="Signature of {{ $signatures[$i]->name }}">
                    @endif
                    <span title="Signed {{ $signatures[$i]->signed_at->format('M j, Y g:i A') }}">{{ $signatures[$i]->name }}</span>
                @endif
            </div>
        @endfor
    </div>
    <div class="pmt-box-date-value">{{ $approvedDate }}</div>
    <div class="pmt-box-date-label">DATE</div>
</div>
