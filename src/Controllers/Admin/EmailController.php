<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Events\AdminEmailSave;
use CoasterCommerce\Core\Mailables\AbstractMailable;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\EmailSetting;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class EmailController extends AbstractController
{

    use ValidatesInput;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Settings')->setActive();
    }

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Email List');
        return $this->_view('email.list', [
            'emails' => EmailSetting::all(),
        ]);
    }

    /**
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $this->_setTitle('Edit Email');
        if (!$email = EmailSetting::find($id)) {
            return $this->_notFoundView();
        }

        /** @var AbstractMailable $mailableClass */
        $mailableClass = $email->mailable;
        $mailable = $mailableClass::testInstance();

        return $this->_view('email.edit', [
            'email' => $email,
            'contents' => file_get_contents(view($mailable->getMarkdownView())->getPath())
        ]);
    }

    /**
     * @param int $id
     * @return View
     */
    public function preview($id)
    {
        $this->_setTitle('Preview Email');
        if (!$email = EmailSetting::find($id)) {
            return $this->_notFoundView();
        }

        return $this->_view('email.preview', [
            'email' => $email
        ]);
    }

    /**
     * @param int $id
     * @return Response|View
     */
    public function previewFrame($id)
    {
        $this->_setTitle('Preview Email');
        if (!$email = EmailSetting::find($id)) {
            return $this->_notFoundView();
        }

        /** @var AbstractMailable $mailableClass */
        $mailableClass = $email->mailable;
        $mailable = $mailableClass::testInstance();

        return response($mailable->render());
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function previewTest(Request $request, $id)
    {
        $this->validate($request->post(), ['test_email' => 'email']);

        if (!$email = EmailSetting::find($id)) {
            return $this->_notFoundView();
        }

        /** @var AbstractMailable $mailableClass */
        $mailableClass = $email->mailable;
        $mailableClass::sendTest($request->post('test_email'));

        $this->_flashAlert('success', 'Test email sent to '. $request->post('test_email'));
        return $this->_redirectRoute('system.email.preview', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function updateDefaults(Request $request)
    {
        $attributes = $request->post('attributes');
        (new Setting)->setValue('email_sender_name', $attributes['default_name']);
        (new Setting)->setValue('email_sender_address', $attributes['default_email']);
        return $this->_redirectRoute('system.email');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $id)
    {
        if (!$email = EmailSetting::find($id)) {
            return $this->_notFoundView();
        }

        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['label', 'subject'] as $attribute) {
            $rules['attributes.' . $attribute] = 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        // save inputData to category model
        $email
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new EmailSetting)->getTable()), null)))
            ->save();
        // save non customer group model data
        event(new AdminEmailSave($email, $inputData));
        // redirect based on save action
        $this->_flashAlert('success', 'Email "' . $email->label . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('system.email.edit', ['id' => $email->id]) :
            $this->_redirectRoute('system.email');
    }

}
