<?php
/** @var \Collective\Html\FormBuilder $formBuilder */
?>{!! $pb->section('head') !!}

<div class="container">
    {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
    <div class="row">
        <div class="col-sm-3 order-sm-1 order-2">
            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}
        </div>
        <div class="col-sm-9 content-column order-sm-2 order-1">
            <div class="row">
                <div class="col-sm-12">
                    <h1>New List</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    {!! $formBuilder->open(['class' => 'form-horizontal']) !!}

                    <div class="form-group row">
                        <label for="name" class="col-sm-2 col-form-label">List Name</label>
                        <div class="col-sm-10">
                            {!! $formBuilder->text('name', null, ['class' => 'form-control', 'id' => 'name']) !!}
                            <small class="form-text text-danger">{{ $errors->first('name') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10">
                            {!! $formBuilder->submit('Create List', ['class' => 'btn btn-default', 'style' => 'margin-top: 15px']) !!}
                        </div>
                    </div>

                    {!! $formBuilder->close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

{!! $pb->section('footer') !!}
