<?php
/**
 * @var CoasterCms\Libraries\Builder\ViewClasses\MenuItemDetails $item
 * @var bool $is_last
 */
?>

<li class="nav-item">
    <a class="nav-link {{ $item->active ? 'active' : '' }}" href="{{ $item->url }}">
        {{ $item->name }}
    </a>
</li>
