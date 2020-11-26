<?php
/**
 * @var CoasterCommerce\Core\Model\EmailSetting $emails
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Renderer\Admin\Attribute;
$defaultSender = [
    'name' => Setting::getValue('email_sender_name'),
    'email' => Setting::getValue('email_sender_address')
];
function addEALabel(&$value, $key) {
    $value = $key . ': ' . $value;
}
?>

<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h1 class="card-title mb-4">Sender Details</h1>

                {!! $formBuilder->open(['url' => route('coaster-commerce.admin.system.email.defaults')]) !!}

                {!! (new Attribute('default_email', 'text', 'From Email Address'))->renderInput($defaultSender['email']) !!}
                {!! (new Attribute('default_name', 'text', 'From Name'))->renderInput($defaultSender['name']) !!}

                <div class="text-right">
                    <button class="btn btn-success">Update</button>
                </div>

                {!! $formBuilder->close() !!}

            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h1 class="card-title mb-4">Emails</h1>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Enabled</th>
                            <th>Label</th>
                            <th>Subject</th>
                            <th>From</th>
                            <th>Additional Recipients</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emails as $email)
                            @php
                                $from = array_filter(['Email' => $email->from_email ?: $defaultSender['email'], 'Name' => $defaultSender['name']]);
                                $to = array_filter(['To' => $email->to, 'Cc' => $email->cc, 'Bcc' => $email->bcc]);
                                array_walk($to, 'addEALabel');
                                array_walk($from, 'addEALabel');
                            @endphp
                            <tr>
                                <td>{{ $email->id }}</td>
                                <td>{{ $email->enabled ? 'Yes' : 'No' }}</td>
                                <td>{{ $email->label }}</td>
                                <td>{{ $email->subject }}</td>
                                <td>{!! implode('<br />', $from) !!}</td>
                                <td>{!! implode('<br />', $to) !!}</td>
                                <td><a href="{{ route('coaster-commerce.admin.system.email.edit', ['id' => $email->id]) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

