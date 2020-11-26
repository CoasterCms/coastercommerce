<?php
/**
 * @var \CoasterCommerce\Core\Model\Customer\Address $address
 */
?>

<p>
    @if ($address->exists)
        @php
            $lines = [
                $address->first_name . ' ' .$address->last_name,
                $address->company,
                $address->address_line_1,
                $address->address_line_2,
                $address->town,
                $address->county,
                $address->postcode,
                $address->country(),
                $address->email ? ('Email: ' . $address->email) : null,
                $address->phone ? 'Tel: ' . $address->phone : null,
            ];
        @endphp
        {!! implode('<br />', array_filter(array_map('trim', $lines))) !!}
    @else
        None set
    @endif
</p>
