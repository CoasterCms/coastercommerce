<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Redirect;
use CoasterCommerce\Core\Model\Product\Attribute\OptionSource;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RedirectController extends AbstractController
{
    use ValidatesInput;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Catalogue')->setActive();
    }

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Redirects');
        return $this->_view('redirect.list');
    }

    /**
     * @return View
     */
    public function add()
    {
        $this->_setTitle('New Redirect');
        return $this->_view('redirect.edit', [
            'redirect' => new Redirect,
            'productOptions' => ['' => '-- Not Set --'] + (new OptionSource\Product)->optionsData(),
            'categoryOptions' => ['' => '-- Not Set --'] + (new OptionSource\Category)->optionsData()
        ]);
    }

    /**
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $this->_setTitle('Edit Redirect');
        if (!$redirect = Redirect::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_view('redirect.edit', [
            'redirect' => $redirect,
            'productOptions' => ['' => '-- Not Set --'] + (new OptionSource\Product)->optionsData(),
            'categoryOptions' => ['' => '-- Not Set --'] + (new OptionSource\Category)->optionsData()
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $id)
    {
        if ($id) {
            if (!$redirect = Redirect::find($id)) {
                return $this->_notFoundView();
            }
        } else {
            $redirect = new Redirect();
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['url'] as $attribute) {
            $rules['attributes.' . $attribute] = 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        // save inputData to redirect model
        $redirect
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new Redirect)->getTable()), null)))
            ->save();
        // redirect based on save action
        $this->_flashAlert('success', 'Redirect "' . $redirect->url . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('redirect.edit', ['id' => $redirect->id]) :
            $this->_redirectRoute('redirect.list');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        if ($redirect = Redirect::find($id)) {
            if ($redirect->delete()) {
                $this->_flashAlert('success', 'Redirect  "' . $redirect->url . '" deleted!');
            }
        }
        return $this->_redirectRoute('redirect.list');
    }

}
