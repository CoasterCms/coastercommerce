<?php
/**
 * @var float $value
 * @var bool $discount
 * @var string $style
 * @var string $class
 * @var string $field
 * @var int $fieldCols
 * @var int $valueCols
 */
$discount = $discount ?? false;
$showZero = $showZero ?? false;
$style = $style ?? '';
$class = $class ?? '';
$fieldCols = $fieldCols ?? 1;
$valueCols = $valueCols ?? 1;
use CoasterCommerce\Core\Currency\Format;
?>

<tr{!! $style ? ' style="' . $style . '"' : '' !!}{!! $class ? ' class="' . $class . '"' : '' !!}>
    <td colspan="{{ $fieldCols }}">{{ $field }}:</td>
    <td colspan="{{ $valueCols }}" class="text-right">
        {{ $discount ? '-' : '' }}{!! (new Format($value))->showZero($showZero) !!}
    </td>
</tr>