<?php
/**
 * @var CoasterCommerce\Core\Model\Category $rootCategory
 * @var CoasterCommerce\Core\Renderer\Admin\CategoryList $listRenderer
 */
?>

<div class="list-group-item" data-id="{{ $rootCategory->id }}">
    <div class="tree-node {{ $rootCategory->enabled ? '' : 'text-danger' }}">
        <div class="row">
            <div class="col-sm-8">
                <i class="fa fa-folder cat-icon"></i>&nbsp;
                @if ($rootCategory->anchor)
                <i class="fa fa-anchor"></i>&nbsp;
                @endif
                {{ $rootCategory->name }}
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('coaster-commerce.admin.category.edit', ['id' => $rootCategory->id]) }}" class="mr-4">Edit</a>
                <a href="{{ route('coaster-commerce.admin.category.delete.post', ['id' => $rootCategory->id]) }}" class="cat-delete confirm" data-confirm="your wish to delete {{ $rootCategory->name }}">Delete</a>
            </div>
        </div>
    </div>
    <div class="list-group nested-sortable" style="display: none;">
        {!! $listRenderer->category($rootCategory) !!}
    </div>
</div>