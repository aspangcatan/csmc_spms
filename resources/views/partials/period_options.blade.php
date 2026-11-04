{{-- <option>s for a rating-period select. Params: $selected (period code), $short (bool, compact labels). --}}
@foreach(\App\Support\Period::codes() as $code)
    <option value="{{ $code }}" {{ (int) ($selected ?? 0) === $code ? 'selected' : '' }}>
        {{ !empty($short) ? \App\Support\Period::shortLabel($code) : \App\Support\Period::label($code) }}
    </option>
@endforeach
