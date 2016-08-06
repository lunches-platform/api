<?php

namespace Lunches\Validator;

use Lunches\Model\Order;

/**
 * Class OrderValidator.
 */
class OrderValidator
{
    private $errors = [];

    /**
     * @param Order $order
     * @return bool
     */
    public function isValid(Order $order)
    {
        $this->resetErrors();
        
        if ($order->getPrice() <= 0) {
            $this->addError('price', 'Price must be positive');
        }
        
        if (count($order->getLineItems()) === 0)  {
            $this->addError('lineItems', 'Specify at least one line item');
        }

        foreach ($order->getLineItems() as $lineItem) {

            if (!$lineItem->getSize()) {
                $this->addError('lineItems', 'One of LineItem does not have "size" field');
            }
        }

        return $this->hasErrors();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $field
     * @param string $message
     */
    private function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    private function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * @return bool
     */
    private function hasErrors()
    {
        return 0 === count($this->errors);
    }
}
