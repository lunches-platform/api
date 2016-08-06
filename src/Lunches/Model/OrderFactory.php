<?php

namespace Lunches\Model;

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
     * @return Order
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
     * @return User
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

    private function getMenu(\DateTime $shipmentDate)
    {
        $menu = $this->menuRepo->findByDate($shipmentDate);
        if (!$menu) {
            throw RuntimeException::notFound('Menu', 'There is no menu for specified date' );
        }
        return $menu;
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
     *
     * @throws ValidationException
     */
    private function createLineItems($data, $shipmentDate)
    {
        if (!array_key_exists('items', $data) || !is_array($data['items'])) {
            return [];
        }
        $menu = $this->getMenu($shipmentDate);

        $lineItems = $orderedProductIds = [];
        foreach ($data['items'] as $line) {

            try {
                $lineItems[] = $lineItem = $this->createLineItem($line, $menu);

                // order only unique products
                if (in_array($productId = $lineItem->getProduct()->getId(), $orderedProductIds, true)) {
                    continue;
                }
            } catch (ValidationException $e) {
                continue;
            } catch (RuntimeException $e) {
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
     * @param Menu  $menu
     * @return LineItem|bool
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    private function createLineItem(array $line, Menu $menu)
    {
        $lineItem = new LineItem();

        $required = ['productId', 'size'];

        $emptyRequired = array_diff($required, array_keys($line));
        if (count($emptyRequired) !== 0)  {
            throw ValidationException::requiredEmpty('Invalid LineItem', $required);
        }

        $lineItem->setSize($line['size']);

        $product = $menu->getProductById((int)$line['productId']);
        if (!$product instanceof Product) {
            throw ValidationException::invalidLineItem('Menu for specified date doest have such product');
        }

        $lineItem->setProduct($product);

        return $lineItem;
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