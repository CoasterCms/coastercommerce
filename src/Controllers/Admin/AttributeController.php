<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Events\AdminAttributeSave;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttributeController extends AbstractController
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
        $this->_setTitle('Attributes');
        return $this->_view('attribute.list', [
            'columnConf' => Attribute::getDataTableColumnsConf()
        ]);
    }

    /**
     * @param string $attributeId
     * @return View
     */
    public function edit($attributeId)
    {
        if (!$attributeId) {
            $attribute = new Attribute();
        } elseif (!$attribute = Attribute::with(['eav', 'meta'])->find($attributeId)) {
            return $this->_notFoundView();
        }
        $this->_setTitle($attributeId ? 'Editing ' . $attribute->name : 'New Attribute');
        return $this->_view('attribute.edit', [
            'attribute' => $attribute,
            'groupItem' => Attribute\GroupItem::where('attribute_id', $attribute->id)->first() ?: new Attribute\GroupItem(),
            'groups' =>  ['' => ' -- None -- '] + Attribute\Group::orderBy('position')->get()->pluck('name', 'id')->toArray(),
            'dataTypes' => app(Attribute\EavTypes::class)->selectOptions() + ['virtual' => 'virtual'],
            'inputTypes' => app(Attribute\FrontendTypes::class)->selectOptions()
        ]);
    }

    /**
     * @return View
     */
    public function add()
    {
        return $this->edit(0);
    }

    /**
     * @param Request $request
     * @param int $attributeId
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $attributeId)
    {
        // load or create product and attributes
        if ($attributeId) {
            if (!$attribute = Attribute::with(['eav', 'meta'])->find($attributeId)) {
                return $this->_notFoundView();
            }
        } else {
            $attribute = new Attribute();
            $attribute->type = 'eav';
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        $requiredFields = ['name'];
        $editableFields = ['name', 'admin_filter', 'admin_column', 'admin_validation', 'search_weight'];
        if (!$attribute->isSystem()) {
            $requiredFields = array_merge($requiredFields, ['code', 'frontend', 'datatype']);
            $editableFields = array_merge($editableFields, ['code', 'frontend']);
        }
        foreach ($requiredFields as $fieldName) {
            $rules['attributes.' . $fieldName] = 'required';
            $niceNames['attributes.' . $fieldName] = strtolower($fieldName);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        // save inputData to attribute model
        foreach (['admin_filter', 'admin_column', 'search_weight'] as $niceNames) {
            $inputData[$niceNames] = $inputData[$niceNames] ?: 0; // convert columns which can't be null to 0
        }
        $attribute
            ->forceFill(array_intersect_key($inputData, array_fill_keys($editableFields, null)));
        // set model based on frontend class
        if (!$attribute->isSystem()) {
            $attribute->setAttribute('model', app(Attribute\FrontendTypes::class)->defaultModel($attribute));
        }
        $attribute->save();
        // save non attribute model data (ie. eav,meta)
        event(new AdminAttributeSave($attribute, $inputData));
        // redirect based on save action
        $this->_flashAlert('success', 'Attribute "' . $attribute->name . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('attribute.edit', ['id' => $attribute->id]) :
            $this->_redirectRoute('attribute.list');
    }

    /**
     * @param int $attributeId
     * @return RedirectResponse
     */
    public function delete($attributeId)
    {
        if ($attribute = Attribute::find($attributeId)) {
            /** @var Attribute $attribute */
            if ($attribute->isSystem()) {
                $this->_flashAlert('danger', 'Attribute "' . $attribute->name . '"  is a protected system attribute, if you seriously want to delete this then edit the database!');
            } elseif ($attribute->delete()) {
                $this->_flashAlert('success', 'Attribute "' . $attribute->name . '" deleted!');
            }
        }
        return $this->_redirectRoute('attribute.list');
    }

}
