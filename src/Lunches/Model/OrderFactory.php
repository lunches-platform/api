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
     * @throws \Lunches\Exception\ValidationException
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
     * @throws ValidationException
     */
    private function createLineItems($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $lineItems = $orderedProductIds = [];
        foreach ($data as $line) {

            try {
                $lineItems[] = $lineItem = $this->createLineItem($line);

                // order only unique products
                if (in_array($productId = $lineItem->getProduct()->getId(), $orderedProductIds, true)) {
                    continue;
                }
            } catch (ValidationException $e) {
                continue;
            }
            $orderedProductIds[] = $productId;
        }
        $lineItems = array_filter($lineItems);

        if (count($lineItems) === 0) {
            throw ValidationException::invalidOrder('There are no valid LineItems provided');
        }

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
        $lineItem = new LineItem();

        $required = ['date', 'productId', 'quantity', 'size'];

        $emptyRequired = array_diff($required, array_keys($line));
        if (count($emptyRequired) !== 0)  {
            throw ValidationException::requiredEmpty('Invalid LineItem', $required);
        }

        $lineItem->setQuantity($line['quantity']);
        $lineItem->setSize($line['size']);
        $lineItem->setDate($this->createDate($line['date']));

        $menus = $this->menuRepo->findBy([
            'date' => $lineItem->getDate(),
        ]);
        /** @var Menu $menu */
        $menu = array_shift($menus);

        if (!$menu) {
            throw ValidationException::invalidLineItem('There is no menu for specified date');
        }
        $product = $menu->getProductById((int)$line['productId']);
        if (!$product instanceof Product) {
            throw ValidationException::invalidLineItem('Menu for specified date doest have such product');
        }

        $lineItem->setProduct($product);

        return $lineItem;
    }

    private function createDate($dateStr)
    {
        try {
            $date = new \DateTime($dateStr);
        } catch (\Exception $e) {
            throw ValidationException::invalidDate();
        }

        $currentDate = new \DateTime((new \DateTime())->format('Y-m-d')); // remove time part
        if ($date < $currentDate) {
            throw ValidationException::invalidDate('Date in the past is not allowed');
        }

        return $date;
    }
}