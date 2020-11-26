<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Events\AdminPromotionSave;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute\OptionSource\Category as CategoryOptionSource;
use CoasterCommerce\Core\Model\Product\Attribute\OptionSource\Product as ProductOptionSource;
use CoasterCommerce\Core\Model\Product\SearchIndex\Price;
use CoasterCommerce\Core\Model\Promotion;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PromotionController extends AbstractController
{
    use ValidatesInput;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Promotions')->setActive();
    }

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Promotions');
        return $this->_view('promotion.list', [

        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function add(Request $request)
    {
        $type = $request->get('type') ;
        $this->_setTitle('New Promotion');
        if (!in_array($type, ['item', 'cart'])) {
            return $this->_view('promotion.add');
        } else {
            $promotion =  new Promotion;
            $promotion->enabled = 1;
            $promotion->type = $type;
            $promotion->apply_to_subtotal = 1;
            return $this->_view('promotion.edit', [
                'promotion' => $promotion,
                'customers' => Customer::pluck('email', 'id')->toArray(),
                'groups' => Customer\Group::pluck('name', 'id')->toArray(),
                'categories' => (new CategoryOptionSource())->optionsData(),
                'products' => (new ProductOptionSource)->optionsData(),
            ]);
        }
    }

    /**
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $this->_setTitle('Edit Promotion');
        if (!$promotion = Promotion::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_view('promotion.edit', [
            'promotion' => $promotion,
            'customers' => Customer::pluck('email', 'id')->toArray(),
            'groups' => Customer\Group::pluck('name', 'id')->toArray(),
            'categories' => (new CategoryOptionSource())->optionsData(),
            'products' => (new ProductOptionSource)->optionsData(),
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
            /** @var Promotion $promotion */
            if (!$promotion = Promotion::find($id)) {
                return $this->_notFoundView();
            }
            // get current affected product ids
            $affectedProductIdsCurrent = ($promotion->type == 'item' && $promotion->isActive()) ? $promotion->affectedProductIds() : [];
        } else {
            $promotion = new Promotion();
            $affectedProductIdsCurrent = [];
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['name', 'discount_type', 'discount_amount', 'priority'] as $attribute) {
            $rules['attributes.' . $attribute] = ($attribute == 'name' ? 'unique:cc_promotions,'.$attribute.','.$id.'|' : '') . 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $rules['attributes.discount_amount'] .= '|numeric|min:0';
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        $inputData['is_last'] = !$inputData['is_last'];
        // save inputData to promotion model
        $promotion
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new Promotion())->getTable()), null)))
            ->save();
        // save non promotion model data (ie. customer/catalogue rules)
        event(new AdminPromotionSave($promotion, $inputData));
        // reload promotion (which should reload relations) & reindex prices for all product changes
        $promotion = Promotion::find($promotion->id);
        $updatedAffectedProductIds = ($promotion->type == 'item' && $promotion->isActive()) ? $promotion->affectedProductIds() : [];
        if ($affectedProductIds = array_unique(array_merge($affectedProductIdsCurrent, $updatedAffectedProductIds))) {
            $products = Product::with(['variations', 'categories'])->whereIn('id', $affectedProductIds)->get(['id', 'price']);
            (new Price())->reindexAll($products, false);
        }
        // redirect based on save action
        $this->_flashAlert('success', 'Promotion "' . $promotion->name . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('promotion.edit', ['id' => $promotion->id]) :
            $this->_redirectRoute('promotion.list');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        if ($promotion = Promotion::find($id)) {
            if ($promotion->delete()) {
                $this->_flashAlert('success', 'Promotion "' . $promotion->name . '" deleted!');
            }
        }
        return $this->_redirectRoute('promotion.list');
    }


}
