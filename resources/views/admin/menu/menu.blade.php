<?php
/** @var \CoasterCommerce\Core\Menu\AdminItem[] $items */
?>
<div class="closearrow">
    <i class="fas fa-chevron-left"></i>
</div>
<div class="sidebar bg-dark">
    <p class="logo">
        <i class="fas fa-shopping-cart"></i> &nbsp; Coaster Ecommerce
    </p>
    <ul class="list-unstyled">
        @foreach($items as $k => $item)
            {!! $item->render($k) !!}
        @endforeach
    </ul>
</div>

