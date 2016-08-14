<?php

namespace Lunches\Command;

use Doctrine\ORM\EntityManager;
use Knp\Command\Command;
use Lunches\Exception\OrderException;
use Lunches\Model\Order;
use Lunches\Model\OrderRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StartOrderProgressCommand.
 */
class StartOrderProgressCommand extends Command
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('order:start-progress')
            ->setDescription('Starts progress on each "created" order');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getOrderRepository()->findCreatedOrders() as $row) {

            /** @var Order $order */
            $order = $row[0];
            try {
                $order->startProgress();
            } catch (OrderException $e) {
                continue;
            }
        }
        $this->getEm()->flush();
        return 0;
    }

    /**
     * @return OrderRepository
     */
    private function getOrderRepository()
    {
        return $this->getEm()->getRepository('\Lunches\Model\Order');
    }

    /**
     * @return EntityManager
     */
    private function getEm()
    {
        return $this->getSilexApplication()['doctrine.em'];
    }
}
