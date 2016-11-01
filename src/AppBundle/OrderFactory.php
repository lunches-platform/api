<?php

namespace AppBundle;

use AppBundle\Entity\Dish;
use AppBundle\Entity\DishRepository;
use AppBundle\Entity\LineItem;
use AppBundle\Entity\MenuRepository;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderRepository;
use AppBundle\Entity\PriceRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Exception\LineItemException;
use AppBundle\Exception\RuntimeException;
use AppBundle\Exception\ValidationException;
use Doctrine\Bundle\DoctrineBundle\Registry;

class OrderFactory
{
    /** @var DishRepository  */
    protected $dishRepository;

    /** @var OrderRepository  */
    protected $orderRepo;

    /** @var MenuRepository  */
    protected $menuRepo;

    /** @var PriceRepository  */
    protected $priceRepo;

    /** @var UserRepository  */
    protected $userRepo;

    /**
     * OrderFactory constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->orderRepo = $doctrine->getRepository('AppBundle:Order');
        $this->dishRepository = $doctrine->getRepository('AppBundle:Dish');
        $this->menuRepo = $doctrine->getRepository('AppBundle:Menu');
        $this->priceRepo = $doctrine->getRepository('AppBundle:Price');
        $this->userRepo = $doctrine->getRepository('AppBundle:User');
    }

    /**
     * @param array $data
     *
     * @return Order
     * @throws \AppBundle\Exception\LineItemException
     * @throws \AppBundle\Exception\RuntimeException
     * @throws \AppBundle\Exception\ValidationException
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
     * @return User
     * @throws RuntimeException
     * @throws ValidationException
     */
    private function getUser(array $data)
    {
        // TODO move that check to all required Order fields like LineItem has, so we can remove this method completely
        if (!array_key_exists('userId', $data)) {
            throw ValidationException::invalidOrder('Each order must have userId');
        }
        // TODO refactor to get($userId) and incapsulate exception
        $user = $this->userRepo->find($data['userId']);

        if (!$user instanceof User) {
            throw RuntimeException::notFound('User');
        }

        return $user;
    }

    private function getMenus(\DateTime $shipmentDate)
    {
        // TODO refactor to getByDate() and incapsulate exception
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
     * @throws \AppBundle\Exception\LineItemException
     * @throws ValidationException
     */
    public function createLineItems($data, $shipmentDate)
    {
        if (!array_key_exists('items', $data) || !is_array($data['items'])) {
            throw ValidationException::invalidOrder('There are no valid LineItems provided');
        }
        /** @var array $items */
        $items = $data['items'];

        $lineItems = $orderedDishIds = [];
        foreach ($items as $line) {
            $lineItem = $this->createLineItem($line, $shipmentDate);

            // order only unique dishes
            if (in_array($dishId = $lineItem->getDish()->getId(), $orderedDishIds, true)) {
                continue;
            }
            $orderedDishIds[] = $dishId;
            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }

    /**
     * @param array $line
     * @param \DateTime $shipmentDate
     *
     * @return bool|LineItem
     * @throws \AppBundle\Exception\LineItemException
     * @throws \AppBundle\Exception\ValidationException
     */
    private function createLineItem(array $line, \DateTime $shipmentDate)
    {
        $this->assertRequiredExists($line);

        $lineItem = new LineItem();
        $lineItem->setSize($line['size']);
        $lineItem->setDish($this->getDish($line['dishId'], $shipmentDate));

        return $lineItem;
    }

    /**
     * @param int $dishId
     * @param \DateTime $shipmentDate
     * @return Dish|null
     * @throws LineItemException
     */
    private function getDish($dishId, \DateTime $shipmentDate)
    {
        $dish = $this->dishRepository->get($dishId);

        // TODO runs for each LineItem, move outside
        $menus = $this->getMenus($shipmentDate);
        foreach ($menus as $menu) {
            if ($menu->hasDish($dish)) {
                return $dish;
            }
        }
        throw LineItemException::notCookingToday($dish, $shipmentDate);
    }

    private function assertRequiredExists($line)
    {
        $required = ['dishId', 'size'];

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
            throw ValidationException::invalidDate('Can not order dish for today or in the past');
        }

        return $date;
    }
}