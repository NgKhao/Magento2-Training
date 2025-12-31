<?php

namespace Packt\HelloWorld\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

// Import Factory của Model và Collection
use Packt\HelloWorld\Model\SubscriptionFactory;
use Packt\HelloWorld\Model\ResourceModel\Subscription\CollectionFactory;

class SubscriptionCommand extends Command
{
    const INPUT_KEY_ACTION = 'action';
    const INPUT_KEY_EMAIL = 'email';

    protected $subscriptionFactory;
    protected $collectionFactory;

    public function __construct(SubscriptionFactory $subscriptionFactory, CollectionFactory $collectionFactory)
    {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_ACTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'action: create, update, list',
                'list'
            ),
            new InputOption(
                self::INPUT_KEY_EMAIL,
                null,
                InputOption::VALUE_REQUIRED,
                'Email address for subscription'
            )
        ];

        $this->setName('subscription:manage')
            ->setDescription('Manage subscription')
            ->setDefinition($options); // Đăng ký các options đã tạo cho command.
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getOption(self::INPUT_KEY_ACTION);
        $email = $input->getOption(self::INPUT_KEY_EMAIL);

        $output->writeln("<info>Action: $action</info>");

        try {
            switch ($action){
                case 'create':
                    if(empty($email)){
                        $output->writeln('<error>Email is required for create action</error>');
                        return 1;
                    }

                    $this->createSubscription($email, $output);
                    break;
                case 'update':
                    if(empty($email)){
                        $output->writeln('<error>Email is required for update action</error>');
                        return 1;

                    }

                    $this->updateSubcription($email, $output);
                    break;
                case 'list':
                    $this->listSubscriptions($output);
                    break;
                default:
                    $output->writeln('<error>Invalid action. Use create, update, or list.</error>');
                    break;
            }

        } catch (\Exception $exception){
            $output->writeln('<error>Error: ' . $exception->getMessage() . '</error>' );
            return 1;
        }
        $output->writeln('<info>fishished.</info>');
        return 0;
    }

    protected function createSubscription($email, OutputInterface $output)
    {
        $subscription = $this->subscriptionFactory->create();

        $subscription->setEmail($email);
        $subscription->setFirstname('Test');
        $subscription->setLastname('User');
        $subscription->setStatus('pendding');

        $subscription->save();

        $output->writeln("Created subscription for email: " . $email);
    }

    protected function updateSubcription($email, OutputInterface $output)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('email', $email);
        $subscription = $collection->getFirstItem(); // Lấy phần tử đầu tiên trong collection
        if($subscription && $subscription->getId()) {
            $subscription->setStatus('approved');
            $subscription->save();
            $output->writeln('Update subscription status to approved for email: ' . $email);
        } else {
            $output->writeln('No subscription found for email: ' . $email);
        }
    }

    protected function listSubscriptions(OutputInterface $output)
    {
        $collection = $this->collectionFactory->create();

        if($collection->getSize() == 0) {
            $output->writeln('No subscriptions found');
            return;
        }

        $output->writeln("ID \t| Email");
        $output->writeln('-----------------------');
            foreach ($collection as $item) {
                $output->writeln($item->getId() . " \t| " . $item->getEmail());
            }
        }

}
