<?php
/**
 * @var string $key
 * @var \CoasterCommerce\Core\Menu\AdminItem $item
 */
$elId = 'item' . $key;
?>

<li>
    @if ($item->subItems)
    <a href="#{{ $elId }}" role="button" class="dropdown-toggle" aria-expanded="false" aria-controls="{{ $elId }}" data-toggle="collapse">
    @else
    <a href="{{ $item->url }}">
    @endif
        @if ($item->icon)
        <i class="fas fa-{{ $item->icon }}"></i>
        @endif &nbsp; {{ $item->name }}
    </a>
    @if ($subItems = $item->allowedSubItems())
    <div class="collapse {{ $item->active ? 'show' : null }}" id="{{ $elId }}">
        <div class="card card-body">
            <ul class="list-unstyled">
                @foreach($subItems as $subKey => $subItem)
                    {!! $subItem->render($key . '-' . $subKey) !!}
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</li>
