<?php

namespace Lunches\Model;

use Lunches\Exception\ValidationException;
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
     * @throws \Lunches\Exception\RuntimeException
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
     * @throws \Lunches\Exception\RuntimeException
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

            try {
                $lineItems[] = $this->createLineItem($line);
            } catch (ValidationException $e) {
                continue;
            }
        }
        $lineItems = array_filter($lineItems);

        return $lineItems;
    }

    /**
     * @param array $line
     * @return LineItem|bool
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    private function createLineItem(array $line)
    {
        $product = $this->productRepo->find($line['productId']);
        if (!$product instanceof Product) {
            return false;
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

        if (array_key_exists('size', $line)) {
            $lineItem->setSize($line['size']);
        } else {
            $lineItem->setSize('medium');
        }

        return $lineItem;
    }
}