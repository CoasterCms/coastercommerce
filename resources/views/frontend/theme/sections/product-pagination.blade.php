<?php
/**
 * @var \Illuminate\Pagination\LengthAwarePaginator $products
 */
?>

@if ($products && $products->total() > $products->perPage())
    <div>
        <div class="float-right">{!! $products->links() !!}</div>
        <p class="float-right mt-1 mr-4">{{ 'Showing products ' . ($products->perPage() * ($products->currentPage()-1) + 1) . '-' . min($products->perPage() * $products->currentPage(), $products->total()) . ' out of ' . $products->total() }}</p>
        <div class="clearfix"></div>
    </div>
@endif