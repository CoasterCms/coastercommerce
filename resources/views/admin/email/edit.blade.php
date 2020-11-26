<?php
/**
 * @var CoasterCommerce\Core\Model\EmailSetting $email
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.system.email.save', ['id' => $email->id]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            Edit {{ $email->label }}
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            <a href="{{ route('coaster-commerce.admin.system.email.preview', ['id' => $email->id]) }}" class="btn btn-info mb-2">
                                <i class="fas fa-eye"></i> &nbsp; Preview
                            </a> &nbsp;
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to email list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-12">

                            {!! (new Attribute('enabled', 'switch', 'Enabled'))->renderInput($email->enabled) !!}
                            {!! (new Attribute('label', 'text', 'Label'))->renderInput($email->label) !!}
                            {!! (new Attribute('subject', 'text', 'Subject'))->renderInput($email->subject) !!}
                            {!! (new Attribute('from_email', 'text', 'From Email Address'))->renderInput($email->from_email) !!}
                            {!! (new Attribute('from_name', 'text', 'From Name'))->renderInput($email->from_name) !!}
                            {!! (new Attribute('to', 'text', 'Additional To Recipient(s)'))->renderInput($email->to) !!}
                            {!! (new Attribute('cc', 'text', 'Additional Cc Recipient(s)'))->renderInput($email->cc) !!}
                            {!! (new Attribute('bcc', 'text', 'Additional Bcc Recipient(s)'))->renderInput($email->bcc) !!}

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="attributes_contents">
                                    Content
                                </label>
                                <div class="col-sm-9">
                                    <textarea id="attributes_contents" name="attributes[contents]" class="form-control" rows="20">{{ trim($contents) }}</textarea>
                                </div>
                            </div>

                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>