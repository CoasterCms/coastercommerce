<?php

namespace CoasterCommerce\Core\Model;

use CoasterCommerce\Core\Model\Customer\AddressCollection;
use CoasterCommerce\Core\Model\Order\Status;
use Illuminate\Auth\Authenticatable;
use CoasterCommerce\Core\Mailables\NewAccountMailable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContact;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailer;
use CoasterCommerce\Core\Mailables\ResetPasswordMailable;
use Illuminate\Support\Facades\Mail;

class Customer extends Model implements AuthenticatableContact, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * @var string
     */
    protected $table = 'cc_customers';

    /**
     * @var array
     */
    protected $dates = ['last_login'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'group_id'
    ];

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        /** @var Mailer $mailer */
        $mailer = app('mailer');
        $mailer->send(new ResetPasswordMailable($this, $token));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Customer\Group::class, 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany(Customer\Meta::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Customer\Address::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submittedOrders()
    {
        return $this->orders()->whereIn('order_status', Status::submittedStatuses());
    }

    /**
     * @return Customer\Address|null
     */
    public function defaultBillingAddress()
    {
        /** @var Customer\Address $customerBilling */
        $customerBilling = $this->addresses->where('default_billing', '=', 1)->first();
        return $customerBilling ?: new Customer\Address();
    }

    /**
     * @return Customer\Address|null
     */
    public function defaultShippingAddress()
    {
        $customerShipping = $this->addresses->where('default_shipping', '=', 1)->first();
        return $customerShipping ?: new Customer\Address();
    }

    /**
     * @return AddressCollection
     */
    public function otherAddresses()
    {
        return $this->addresses->where('default_billing', '!=', 1)->where('default_shipping', '!=', 1);
    }

    /**
     * @param Customer\Address $customerAddress
     * @param bool $default
     */
    public function saveBillingAddress($customerAddress, $default = false)
    {
        $customerAddress = $this->getFromAddressBook($customerAddress);
        $customerAddress->default_billing = $default ? 1 : 0;
        if ($default) {
            $this->_removeDefaultAddress('default_billing', $customerAddress->id);
        }
        $this->addresses()->save($customerAddress);
        $this->load('addresses');
    }

    /**
     * @param Customer\Address $customerAddress
     * @param bool $default
     */
    public function saveShippingAddress($customerAddress, $default = false)
    {
        $customerAddress = $this->getFromAddressBook($customerAddress);
        $customerAddress->default_shipping = $default ? 1 : 0;
        if ($default) {
            $this->_removeDefaultAddress('default_shipping', $customerAddress->id);
        }
        $this->addresses()->save($customerAddress);
        $this->load('addresses');
    }

    /**
     * @param string $column
     * @param int $ignoreAddressId
     */
    protected function _removeDefaultAddress($column, $ignoreAddressId)
    {
        (new Customer\Address)
            ->where('customer_id', $this->id)
            ->where('id', '!=', $ignoreAddressId)
            ->where($column, 1)
            ->update([$column => 0]);
    }

    /**
     * If not existing check against current saved addresses and load if one matches
     * @param Customer\Address $customerAddress
     * @return Customer\Address
     */
    public function getFromAddressBook($customerAddress)
    {
        if (!$customerAddress->id) {
            $uniqueAddresses = [];
            foreach ($this->addresses as $address) {
                /** @var Customer\Address $address */
                $uniqueAddresses[$address->stringValue()] = $address;
            }
            if (array_key_exists($customerAddress->stringValue(), $uniqueAddresses)) {
                return $uniqueAddresses[$customerAddress->stringValue()];
            }
        }
        return $customerAddress;
    }

    /**
     * @param array $emailData
     */
    public function sendNewAccountEmail($emailData = [])
    {
        Mail::send(new NewAccountMailable($this, $emailData));
    }

}
