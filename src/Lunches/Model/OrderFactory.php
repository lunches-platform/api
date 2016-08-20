<?php

namespace Lunches\Model;

use Lunches\Exception\LineItemException;
use Lunches\Exception\RuntimeException;
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

    /** @var PriceRepository  */
    protected $priceRepo;

    /** @var UserRepository  */
    protected $userRepo;

    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->orderRepo = $entityManager->getRepository('Lunches\Model\Order');
        $this->productRepo = $entityManager->getRepository('Lunches\Model\Product');
        $this->menuRepo = $entityManager->getRepository('Lunches\Model\Menu');
        $this->priceRepo = $entityManager->getRepository('Lunches\Model\Price');
        $this->userRepo = $entityManager->getRepository('Lunches\Model\User');
    }

    /**
     * @param array $data
     *
     * @return Order
     * @throws \Lunches\Exception\LineItemException
     *
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    public function createNewFromArray(array $data)
    {
        $order = new Order();

        if (count($data) === 0) {
            return $order;
        }

        $order->setOrderNumber($this->orderRepo->generateOrderNumber());
        $order->setUser($this->getUser($data));
        $order->setAddress($this->getAddress($data, $order->getUser()));
        $order->setShipmentDate($this->createDate($data));

        array_map([$order, 'addLineItem'],
            $this->createLineItems($data, $order->getShipmentDate())
        );
        $order->setPrice($this->calculatePrice($order));

        return $order;
    }

    private function getAddress(array $data, User $user)
    {
        return array_key_exists('address', $data) ? $data['address'] : $user->getAddress();
    }

    /**
     * @param array $data
     *
     * @return User
     *
     * @throws RuntimeException
     * @throws ValidationException
     */
    private function getUser(array $data)
    {
        if (!array_key_exists('userId', $data)) {
            throw ValidationException::invalidOrder('Each order must have userId');
        }
        $user = $this->userRepo->find($data['userId']);

        if (!$user instanceof User) {
            throw RuntimeException::notFound('User');
        }

        return $user;
    }

    private function getMenus(\DateTime $shipmentDate)
    {
        $menus = $this->menuRepo->findByDate($shipmentDate);
        if (!$menus) {
            throw RuntimeException::notFound('Menu', 'There is no menu for specified date');
        }

        return $menus;
    }

    private function calculatePrice(Order $order)
    {
        $prices = $this->priceRepo->findByDate($order->getShipmentDate());

        if ($prices->count() === 0) {
            throw RuntimeException::priceNotFound($order->getShipmentDate());
        }

        return $prices->getOrderPrice($order);
    }

    /**
     * @param array $data
     * @param \DateTime $shipmentDate
     *
     * @return LineItem[]
     * @throws \Lunches\Exception\LineItemException
     * @throws ValidationException
     */
    private function createLineItems($data, $shipmentDate)
    {
        if (!array_key_exists('items', $data) || !is_array($data['items'])) {
            throw ValidationException::invalidOrder('There are no valid LineItems provided');
        }

        $lineItems = $orderedProductIds = [];
        foreach ($data['items'] as $line) {
            $lineItems[] = $lineItem = $this->createLineItem($line, $shipmentDate);

            // order only unique products
            if (in_array($productId = $lineItem->getProduct()->getId(), $orderedProductIds, true)) {
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
     * @param \DateTime $shipmentDate
     *
     * @return bool|LineItem
     * @throws \Lunches\Exception\ValidationException
     *
     * @throws LineItemException
     */
    private function createLineItem(array $line, \DateTime $shipmentDate)
    {
        $this->assertRequiredExists($line);

        $lineItem = new LineItem();
        $lineItem->setSize($line['size']);
        $lineItem->setProduct($this->getProduct($line['productId'], $shipmentDate));

        return $lineItem;
    }

    /**
     * @param int       $productId
     * @param \DateTime $shipmentDate
     *
     * @return Product|null
     *
     * @throws LineItemException
     */
    private function getProduct($productId, \DateTime $shipmentDate)
    {
        $product = $this->productRepo->get($productId);

        $menus = $this->getMenus($shipmentDate);
        foreach ($menus as $menu) {
            if ($menu->hasProduct($product)) {
                return $product;
            }
        }
        throw LineItemException::notCookingToday($product, $shipmentDate);
    }

    private function assertRequiredExists($line)
    {
        $required = ['productId', 'size'];

        $emptyRequired = array_diff($required, array_keys($line));
        if (count($emptyRequired) !== 0) {
            throw ValidationException::requiredEmpty('Invalid LineItem', $required);
        }
    }

    private function createDate($data)
    {
        if (!array_key_exists('shipmentDate', $data)) {
            throw ValidationException::invalidOrder('Date field is not provided');
        }
        $dateStr = $data['shipmentDate'];
        if (!$dateStr) {
            throw ValidationException::invalidDate('Date must be specified');
        }
        try {
            $date = new \DateTime($dateStr);
        } catch (\Exception $e) {
            throw ValidationException::invalidDate();
        }

        $currentDate = new \DateTime((new \DateTime())->format('Y-m-d')); // remove time part
        if ($date <= $currentDate) {
            throw ValidationException::invalidDate('Can not order product for today or in the past');
        }

        return $date;
    }
}