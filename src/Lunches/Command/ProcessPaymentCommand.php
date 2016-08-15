<?php

namespace Lunches\Command;

use Doctrine\ORM\EntityManager;
use Knp\Command\Command;
use Lunches\Model\OrderRepository;
use Lunches\Model\Transaction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPaymentCommand extends Command
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('order:pay')
            ->setDescription('Pay for each order');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getOrderRepository()->findNonPaidOrders() as $order) {
            $transaction = $order->pay();
            $msg = 'Order #'.$order->getId().' is ';
            if ($transaction instanceof Transaction) {
                $this->getEm()->persist($transaction);
                $msg .= 'paid';
            } else {
                $msg .= 'non paid';
            }
            $output->writeln($msg);
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
