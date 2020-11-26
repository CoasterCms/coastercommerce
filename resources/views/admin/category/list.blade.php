<?php
/**
 * @var CoasterCommerce\Core\Renderer\Admin\CategoryList $listRenderer
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Categories</h1>
                    <span class="col-sm-6 text-right">
                        <button id="expandAll" class="btn btn-info mr-3">
                            <i class="fa fa-plus-square"></i> Expand all
                        </button>
                        <button id="collapseAll"  class="btn btn-info mr-3">
                            <i class="fa fa-minus-square"></i>  Collapse all
                        </button>
                        <a href="{{ route('coaster-commerce.admin.category.add') }}" class="btn btn-success">
                            <i class="fa fa-folder"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <div class="mb-4">
                    <p>
                        Disabled categories are shown in red.<br />
                        Anchor categories include products assigned to sub categories as well as directly assigned ones.
                    </p>
                </div>

                <div id="categoryList" class="nested-sortable-group list-group nested-sortable">
                    {!! $listRenderer->rootItems() !!}
                </div>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>

        $(document).ready(function () {

            let nestedSortables = [].slice.call(document.querySelectorAll('.nested-sortable'));

            for (let i = 0; i < nestedSortables.length; i++) {
                new Sortable(nestedSortables[i], {
                    group: 'categories',
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.2,
                    onEnd: function (event) {
                        saveCategoryLocation(event, resetLocation);
                    }
                });
            }

            function resetLocation(event) {
                let previousParent = $(event.from);
                if (event.oldIndex === 0) {
                    $(event.item).prependTo(previousParent);
                } else {
                    $(event.item).insertAfter(previousParent.children().eq(
                        event.oldIndex - (event.to === event.from && event.oldIndex > event.newIndex ? 0 : 1)
                    ));
                }
            }

            function saveCategoryLocation(event, failureCallback) {
                let positions = [];
                $(event.from).children().each(function () {
                    positions.push($(this).data('id'));
                });
                $.post('{{ route('coaster-commerce.admin.category.move') }}', {
                    id: event.item.dataset.id,
                    parentId: event.to.parentElement.dataset.id ? event.to.parentElement.dataset.id : 0,
                    positions: positions
                }).fail(function (r) {
                    commerceAlert('danger', r.responseJSON.error);
                    failureCallback(event);
                });
            }

            let categoryList = $('#categoryList');

            $('#expandAll').click(function () {
                categoryList.find('.cat-icon').removeClass('fa-folder').addClass('fa-folder-open');
                categoryList.find('.list-group').css('display', 'block');
            });

            $('#collapseAll').click(function () {
                categoryList.find('.cat-icon').addClass('fa-folder').removeClass('fa-folder-open');
                categoryList.find('.list-group').css('display', 'none');
            });

            $('.tree-node').click(function (e) {
                if (e.target.tagName !== 'A') {
                    let catIcon = $(this).find('.cat-icon');
                    let subCats = $(this).parent().find('> .list-group');
                    if (subCats.css('display') === 'none') {
                        catIcon.removeClass('fa-folder').addClass('fa-folder-open');
                        subCats.css('display', 'block');
                    } else {
                        catIcon.addClass('fa-folder').removeClass('fa-folder-open');
                        subCats.css('display', 'none');
                    }
                }
            });

            $('.cat-delete').click(function (e) {
                e.preventDefault();
                $.get(e.target.href, function () {
                    e.target.closest('.tree-node').remove();
                    commerceAlert('success', 'Category deleted');
                })
            });

        });

    </script>
@append