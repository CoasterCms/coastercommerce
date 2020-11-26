<?php
/**
 * @var CoasterCommerce\Core\Model\EmailSetting $email
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">

                    <h1 class="card-title col-sm-7 mb-5">
                        Preview {{ $email->label }}
                    </h1>

                    <div class="col-sm-5 mb-5 text-right">
                        <a href="{{ route('coaster-commerce.admin.system.email.edit', ['id' => $email->id]) }}" class="btn btn-info mb-2">
                            <i class="fas fa-arrow-circle-left"></i> &nbsp; Return to edit
                        </a> &nbsp;
                        <a href="{{ route('coaster-commerce.admin.system.email') }}" class="btn btn-success mb-2">
                            <i class="fas fa-arrow-circle-left"></i> &nbsp; Return to email list
                        </a> &nbsp;
                    </div>

                    <div class="col-sm-12">
                        <iframe src="{{ route('coaster-commerce.admin.system.email.preview.frame', ['id' => $email->id]) }}"
                                class="border-0" width="600" height="500"></iframe>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">

                        {!! $formBuilder->open(['url' => route('coaster-commerce.admin.system.email.preview.test', ['id' => $email->id])]) !!}

                        <div class="input-group">
                            <input type="text" name="test_email" class="form-control" id="test_email" placeholder="Recipient Email" value="{{ app('auth')->user()->email }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success">Send Test</button>
                            </div>
                        </div>

                        {!! $formBuilder->close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>