<?php

namespace luya\payment\base;

use luya\helpers\url;
use yii\base\Model;
use luya\payment\PaymentException;
use luya\helpers\ArrayHelper;
use luya\payment\Pay;

/**
 * Pay Model.
 *
 * The Pay Model represents the current payment informations and is used
 * to exchange between process.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class PayModel extends Model
{
    public $id;
    public $totalAmount;
    public $orderId;
    public $currency;
    public $successLink;
    public $abortLink;
    public $errorLink;
    public $authToken;
    public $randomKey;
    public $items = [];
    public $isClosed = 0;
    public $closeState;

    public function isClosed()
    {
        return $this->isClosed;
    }
    
    public function isClosedSuccess()
    {
        return $this->isClosed() && $this->closeState == Pay::STATE_SUCCESS;
    }

    public function isClosedError()
    {
        return $this->isClosed() && $this->closeState == Pay::STATE_ERROR;
    }

    public function isClosedAbort()
    {
        return $this->isClosed() && $this->closeState == Pay::STATE_ABORT;
    }

    public function rules()
    {
        return [
            [['orderId', 'currency', 'successLink', 'abortLink', 'errorLink', 'totalAmount'], 'required'],
            [['totalAmount', 'isClosed', 'id', 'closeState'], 'integer'],
            [['authToken', 'randomKey'], 'string'],
        ];
    }

    /**
     * Add item to items array
     *
     * @param string $name
     * @param integer $qty
     * @param integer $amount
     * @param integer $totalAmount
     * @param boolean $isShipping
     * @param boolean $isTax
     */
    public function addItem($name, $qty, $amount, $totalAmount, $isShipping, $isTax)
    {
        $this->items[] = ['name' => $name, 'qty' => $qty, 'amount' => $amount, 'total_amount' => $totalAmount, 'is_shipping' => $isShipping, 'is_tax' => $isTax];
    }

    /**
     * An array with all items which are not shipping or tax
     *
     * @return array With the keys: name, qty, amount, total_amount, is_shipping, is_tax
     */
    public function getProductItems()
    {
        $items = [];
        foreach ($this->items as $i) {
            if (empty($i['is_shipping']) && empty($i['is_tax'])) {
                $items[] = $i;
            }
        }

        return $items;
    }

    /**
     * An array with all tax items
     *
     * @return array With the keys: name, qty, amount, total_amount, is_shipping, is_tax
     */
    public function getTaxItems()
    {
        $items = [];
        foreach ($this->items as $i) {
            if ($i['is_tax']) {
                $items[] = $i;
            }
        }

        return $items;
    }

    /**
     * An array with all shippings items
     *
     * @return array With the keys: name, qty, amount, total_amount, is_shipping, is_tax
     */
    public function getShippingItems()
    {
        $items = [];
        foreach ($this->items as $i) {
            if ($i['is_shipping']) {
                $items[] = $i;
            }
        }
        
        return $items;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getAuthToken()
    {
        if (empty($this->authToken)) {
            throw new PaymentException("The auth token is empty as it wont be stored in the database and is only available during creation or retrieving from action arguments.");
        }

        return $this->authToken;
    }

    public function setAuthToken($token)
    {
        $this->authToken = $token;
    }

    public function getRandomKey()
    {
        return $this->randomKey;
    }

    public function setRandomKey($key)
    {
        $this->randomKey = $key;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getApplicationSuccessLink()
    {
        return $this->successLink;
    }

    public function getApplicationAbortLink()
    {
        return $this->abortLink;
    }

    public function getApplicationErrorLink()
    {
        return $this->errorLink;
    }

    /**
     * Get the Payment Gateway Create link.
     *
     * This method is used to retrieve the link for dispatching to the requested url.
     *
     * @return string
     * @throws PaymentException
     */
    public function getTransactionGatewayCreateLink()
    {
        return Url::toInternal(['/payment/default/create', 'lpToken' => $this->getAuthToken(), 'lpKey' => $this->randomKey, 'time' => microtime(true)], true);
    }
    
    /**
     * Get the Payment Gateway Back link.
     *
     * This method is used to retrieve the link for dispatching to the requested url.
     *
     * @return string
     */
    public function getTransactionGatewayBackLink()
    {
        return Url::toInternal(['/payment/default/back', 'lpToken' => $this->getAuthToken(), 'lpKey' => $this->randomKey, 'time' => microtime(true)], true);
    }
    
    /**
     * Get the Payment Gateway Fail link.
     *
     * This method is used to retrieve the link for dispatching to the requested url.
     *
     * @return string
     */
    public function getTransactionGatewayFailLink()
    {
        return Url::toInternal(['/payment/default/fail', 'lpToken' => $this->getAuthToken(), 'lpKey' => $this->randomKey, 'time' => microtime(true)], true);
    }
    
    /**
     * Get the Payment Gateway Abort link.
     *
     * This method is used to retrieve the link for dispatching to the requested url.
     *
     * @return string
     */
    public function getTransactionGatewayAbortLink()
    {
        return Url::toInternal(['/payment/default/abort', 'lpToken' => $this->getAuthToken(), 'lpKey' => $this->randomKey, 'time' => microtime(true)], true);
    }
    
    /**
     * Get the Payment Gateway Notify link.
     *
     * This method is used to retrieve the link for dispatching to the requested url.
     *
     * @return string
     */
    public function getTransactionGatewayNotifyLink()
    {
        return Url::toInternal(['/payment/default/notify', 'lpToken' => $this->getAuthToken(), 'lpKey' => $this->randomKey, 'time' => time()], true);
    }
}
