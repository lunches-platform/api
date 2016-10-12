<?php

namespace AppBundle\Command;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderRepository;
use AppBundle\Exception\OrderException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChangeOrderStatusCommand.
 */
class ChangeOrderStatusCommand extends ContainerAwareCommand
{
    /** @var  OutputInterface */
    protected $output;
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('order:change-status')
            ->setDescription('Changes orders statuses from one state to another')
            ->addArgument(
                'status',
                InputArgument::REQUIRED,
                'Status to change'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        switch ($input->getArgument('status')) {
            case Order::STATUS_IN_PROGRESS:
                $this->startProgress();
                break;
            case Order::STATUS_DELIVERED:
                $this->deliver();
                break;
            case Order::STATUS_CLOSED:
                $this->close();
                break;
        }
        $this->getEm()->flush();
        return 0;
    }

    private function startProgress()
    {
        foreach ($this->getOrderRepository()->findBy(['status' => Order::STATUS_CREATED]) as $order) {

            /** @var Order $order */
            try {
                $order->startProgress();
            } catch (OrderException $e) {
                $this->writeError($order, $e);
                continue;
            }
        }
    }

    private function deliver()
    {
        $carrier = 'Slavik';
        foreach ($this->getOrderRepository()->findBy(['status' => Order::STATUS_IN_PROGRESS]) as $order) {

            /** @var Order $order */
            try {
                $order->deliver($carrier);
            } catch (OrderException $e) {
                $this->writeError($order, $e);
                continue;
            }
        }
    }
    
    private function close()
    {
        foreach ($this->getOrderRepository()->findPaidAndDelivered() as $order) {

            try {
                $order->close();
            } catch (OrderException $e) {
                $this->writeError($order, $e);
                continue;
            }
        }
    }

    private function writeError(Order $order, OrderException $e)
    {
        $this->output->writeln('Can not change Order #'.$order->getId().'status: '. $e->getMessage());
    }

    /**
     * @return OrderRepository
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    private function getOrderRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('AppBundle:Order');
    }

    /**
     * @return EntityManager
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     */
    private function getEm()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
