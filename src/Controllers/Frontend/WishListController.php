<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use CoasterCommerce\Core\Events\FrontendInit;
use CoasterCommerce\Core\Mailables\OrderMailable;
use CoasterCommerce\Core\Mailables\WishListMailable;
use CoasterCommerce\Core\Menu\FrontendCrumb;
use CoasterCommerce\Core\Menu\FrontendItem;
use CoasterCommerce\Core\Model\Customer\WishList as WishListModel;
use CoasterCommerce\Core\Session\Cart;
use CoasterCommerce\Core\Session\WishList;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory;

class WishListController extends AbstractController
{

    use ValidatesInput;

    /**
     * @var WishList
     */
    protected $_wishList;

    /**
     * WistListController constructor.
     * @param Cart $cart
     * @param Factory $view
     * @param Redirector $urlRedirect
     * @param Session $session
     * @param WishList $wishList
     */
    public function __construct(Cart $cart, Factory $view, Redirector $urlRedirect, Session $session, WishList $wishList)
    {
        $this->_wishList = $wishList;
        parent::__construct($cart, $view, $urlRedirect, $session);
    }

    /**
     * Sets breadcrumb, metas and active menu item
     * @param string $title
     * @param string $desc
     * @param string $keywords
     */
    protected function _setCustomerMeta($title, $desc = null, $keywords = null)
    {
        $this->_setCustomerMenuActiveItem($title);
        $customerCrumb = new FrontendCrumb('Account', route('coaster-commerce.frontend.customer.account'));
        event(new FrontendInit(
            app('coaster-commerce.url-resolver')->setCustomPage([$customerCrumb, new FrontendCrumb($title, null)], $title, $desc, $keywords)
        ));
    }

    /**
     * @param string $name
     */
    protected function _setCustomerMenuActiveItem($name)
    {
        /** @var FrontendItem $item */
        if ($item = app('coaster-commerce.customer-menu')->getByName($name)) {
            $item->setActive();
        }
    }

    /**
     * @param WishListModel\Item $item
     * @return array
     */
    protected function _itemMessageVars($item)
    {
        if ($item->variation_id) {
            $variation = [];
            foreach ($item->variation->variationArray() as $attribute => $value) {
                $variation[] = $attribute . ': ' . $value;
            }
            $variationText = ' (' . implode(', ', $variation) . ')';
        } else {
            $variationText = '';
        }
        return [
            'list_name' => $this->_wishList->name,
            'item_full_name' => $item->product->name . $variationText,
            'item_name' => $item->product->name
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToList(Request $request)
    {
        try {
            if ($variationId = $request->post('variation_id')) {
                $item = $this->_wishList->addProductVariation($variationId);
            } else {
                $item = $this->_wishList->addProduct($request->post('product_id'));
            }
            $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_item_added', $this->_itemMessageVars($item)));
        } catch (\Exception $e) {
            $this->_flashAlert('danger', __($e->getMessage()));
        }
        return $this->_wishList->id ?
            $this->_redirect('customer.wishlist.view', ['id' => $this->_wishList->id]) :
            $this->_redirect('customer.wishlist.lists'); // in case of adding issue and no wish list created
    }

    /**
     * @param int $itemId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromList($itemId)
    {
        $deleted = false;
        $wishList = null;
        if ($item = WishListModel\Item::find($itemId)) {
            /** @var WishListModel\Item $item */
            if ($this->_canEdit($wishList = $item->wishList)) {
                try {
                    $deleted = $item->delete();
                    $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_item_removed', $this->_itemMessageVars($item)));
                } catch (\Exception $e) {
                    $this->_flashAlert('danger', $e->getMessage());
                }
            }
        }
        if (!$deleted) {
            $this->_flashAlert('danger', __('coaster-commerce::frontend.wishlist_item_remove_fail'));
        }
        return $wishList ?
            $this->_redirect('customer.wishlist.view', ['id' => $wishList->id]) :
            $this->_redirect('customer.wishlist.lists'); // in case of not finding valid wish list
    }

    /**
     * @return View
     */
    public function allLists()
    {
        if ($this->_cart->getCustomerId()) {
            // load a customer lists
            $wishLists = WishListModel::where('customer_id', $this->_cart->getCustomerId())->get();
        } else {
            // load guest list from session
            $wishLists = collect($this->_wishList->exists ? [$this->_wishList] : []);
        }
        $this->_setCustomerMeta('My Lists');
        return $this->_view('customer.account.wishlist.lists', ['wishLists' => $wishLists, 'currentList' => $this->_wishList]);
    }

    /**
     * @return View
     */
    public function newList()
    {
        $this->_setCustomerMeta('Create New List');
        $this->_setCustomerMenuActiveItem('My Lists');
        return $this->_view('customer.account.wishlist.new');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function saveNewList(Request $request)
    {
        $this->validate($request->all(), ['name' => 'required']);
        WishListModel::where('customer_id', $this->_cart->getCustomerId())->update(['selected' => 0]);
        $wishList = (new WishListModel())->forceFill([
            'name' => $request->post('name'),
            'customer_id' => $this->_cart->getCustomerId(),
            'selected' => 1
        ]);
        $wishList->save();
        $this->_wishList->setWishListId($wishList->id);
        return $this->_redirect('customer.wishlist.view', ['id' => $wishList->id]);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @return View|RedirectResponse
     */
    public function viewList(Request $request, $listId)
    {
        $wishList = WishListModel::find($listId) ?: new WishListModel();
        // check if accessing through public share link
        $isPublic = ($request->get('share_key') == $wishList->share_key) && $wishList->share_key;
        // check edit permissions
        $canEdit = $this->_canEdit($wishList);
        // redirect invalid list or if no access
        if (!$wishList->exists || (!$canEdit && !$isPublic)) {
            return $this->_redirect('customer.wishlist.lists');
        }
        $this->_setCustomerMeta('My List - ' . $wishList->name);
        $this->_setCustomerMenuActiveItem('My Lists');
        return $this->_view('customer.account.wishlist.view', ['wishList' => $wishList, 'canEdit' => $canEdit]);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @return RedirectResponse
     */
    public function renameList(Request $request, $listId)
    {
        $wishList = WishListModel::find($listId) ?: new WishListModel();
        // check edit permissions
        if ($this->_canEdit($wishList) && $request->post('name')) {
            $wishList->name = $request->post('name');
            $wishList->save();
            $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_renamed'));
        }
        return $this->_redirect('customer.wishlist.view', ['id' => $wishList->id ?: 0]);
    }

    /**
     * @param int $listId
     * @return RedirectResponse
     */
    public function clearList($listId)
    {
        $wishList = WishListModel::find($listId) ?: new WishListModel();
        // check edit permissions
        if ($this->_canEdit($wishList)) {
            $wishList->items()->delete();
            $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_cleared'));
        }
        return $this->_redirect('customer.wishlist.view', ['id' => $wishList->id ?: 0]);
    }

    /**
     * @param int $listId
     * @return RedirectResponse
     */
    public function deleteList($listId)
    {
        $wishList = WishListModel::find($listId) ?: new WishListModel();
        // check edit permissions
        if ($this->_canEdit($wishList)) {
            try {
                $wishList->delete();
                $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_deleted'));
            } catch (\Exception $e) {
                $this->_flashAlert('danger', $e->getMessage());
            }
        }
        return $this->_redirect('customer.wishlist.lists');
    }

    /**
     * @param Request $request
     * @param int $listId
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function shareList(Request $request, $listId)
    {
        $wishList = WishListModel::find($listId) ?: new WishListModel();
        // check edit permissions
        if ($this->_canEdit($wishList)) {
            $requestData = $request->post();
            $requestData['emails'] = explode("\n", str_replace("\r", "", $requestData['emails']));
            try {
                $this->validate(
                    $requestData,
                    ['emails.*' => 'required|email', 'name' => 'required']
                );
            } catch (ValidationException $e) {
                foreach ($e->validator->errors()->getMessages() as $field => $fieldErrors) {
                    if (stripos($field, 'emails') !== false) continue; // better errors below for email field
                    $this->_flashAlert('danger', $fieldErrors[0]);
                }
                $errorKeys = str_replace('emails.',  '', $e->validator->errors()->keys());
                foreach ($errorKeys as $errorKey) {
                    $this->_flashAlert('danger', 'Invalid email - ' . $requestData['emails'][(int) $errorKey]);
                }
                throw $e;
            }
            Mail::send(new WishListMailable($wishList, $requestData));
            $this->_flashAlert('success', __('coaster-commerce::frontend.wishlist_shared'));
        }
        return $this->_redirect('customer.wishlist.view', ['id' => $wishList->id ?: 0]);
    }

    /**
     * @param WishListModel $wishList
     * @return bool
     */
    protected function _canEdit(WishListModel $wishList)
    {
        if ($wishList) {
            // checkout is own customers list or attached to session for edit permission
            return ($this->_cart->getCustomerId() == $wishList->customer_id && $wishList->customer_id) || ($this->_wishList->id == $wishList->id);
        }
        return false;
    }

}