<?php
/**
 * @var int $deleted
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-12">

                        <h1 class="card-title">
                            Products Deleted
                        </h1>

                        <p>
                            Successfully deleted {{ $deleted }} product(s).
                        </p>

                        <a href="{{ route('coaster-commerce.admin.product.list') }}" class="btn btn-success mt-2">
                            Return to products list
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>