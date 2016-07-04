<?php

namespace Lunches\Model;

use Lunches\Validator\OrderValidator;
use Doctrine\ORM\EntityManager;

class OrderFactory
{
    /** @var ProductRepository  */
    protected $productRepo;
    
    /** @var OrderRepository  */
    protected $orderRepo;

    /** @var MenuRepository  */
    protected $menuRepo;

    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->orderRepo = $entityManager->getRepository('Lunches\Model\Order');
        $this->productRepo = $entityManager->getRepository('Lunches\Model\Product');
        $this->menuRepo = $entityManager->getRepository('Lunches\Model\Menu');
    }

    /**
     * @param array $data
     * @return Order
     */
    public function createNewFromArray(array $data)
    {
        $order = new Order();
        $order->setCreatedAt(new \DateTime());

        if (count($data) === 0) {
            return $order;
        }

        $order->setNumber($this->orderRepo->generateOrderNumber());
        if (array_key_exists('customer', $data)) {
            $order->setCustomer($data['customer']);
        }

        if (array_key_exists('items', $data)) {
            $lineItems = $this->createLineItems($data['items']);
            array_map([$order, 'addLineItem'], $lineItems);
        }

        return $order;
    }

    /**
     * @param array $data
     * @return LineItem[]
     */
    private function createLineItems($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $data = array_filter($data, function ($row) {
            return
                is_array($row) &&
                array_key_exists('productId', $row) && is_numeric($row['productId'])
                ;
        });

        $lineItems = $orderedProductIds = [];
        foreach ($data as $line) {

            $productId = (int) $line['productId'];
            // order only unique products
            if (in_array($productId, $orderedProductIds, true)) {
                continue;
            }
            $orderedProductIds[] = $productId;
            
            $product = $this->productRepo->find($line['productId']);
            if (!$product instanceof Product) {
                continue;
            }

            $lineItem = new LineItem();
            $lineItem->setProduct($product);

            $quantity = 1;
            if (array_key_exists('quantity', $line)) {
                $quantity = $line['quantity'];
            }
            $lineItem->setQuantity($quantity);

            if (array_key_exists('date', $line)) {
                $lineItem->setDate(new \DateTime($line['date']));
            }

            if (array_key_exists('size', $line) && in_array($line['size'], ['small', 'medium', 'big'], true)) {
                $lineItem->setSize($line['size']);
            } else {
                $lineItem->setSize('medium');
            }

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }
}