<?php
/**
 * @var CoasterCommerce\Core\Model\Setting[] $settings
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h1 class="card-title mb-4">Store Details</h1>

                {!! $formBuilder->open(['url' => route('coaster-commerce.admin.system.store.update')]) !!}

                @foreach($settings as $setting)
                    {!! (new Attribute($setting->setting, 'text', str_replace('_', ' ', ucwords($setting->setting))))->key()->renderInput($setting->value) !!}
                @endforeach

                <div class="text-right">
                    <button class="btn btn-success">Update</button>
                </div>

                {!! $formBuilder->close() !!}

            </div>
        </div>
    </div>
</div>

