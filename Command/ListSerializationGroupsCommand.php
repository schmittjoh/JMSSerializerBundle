<?php
namespace JMS\SerializerBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MergeableClassMetadata;
use Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ListSerializationGroupsCommand extends ContainerAwareCommand
{
    private $usedGroups = [];

    /**
     * @var int
     */
    private $maxClassNameLength = 0;

    /**
     * @var int
     */
    private $maxGroupNameLength = 0;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var Output
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('jms:serializer:list-groups')
            ->setDescription('list all groups used on the entites')
            ->addOption('short', null, InputOption::VALUE_OPTIONAL, 'print the occurences of each group', false);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $knownEntities = $em->getMetadataFactory()->getAllMetadata();
        $this->getJmsGroups($knownEntities);
        $this->detectMaxOutputLength();
        $this->printGroups($input->getOption('short'));
    }

    /**
     * @param string $group
     * @param string $propertyClass
     */
    protected function addToUsedGroups($group, $propertyClass)
    {
        if (!isset($this->usedGroups[$group])) {
            $this->usedGroups[$group] = [];
        }
        if (!isset($this->usedGroups[$group][$propertyClass])) {
            $this->usedGroups[$group][$propertyClass] = 0;
        }
        $this->usedGroups[$group][$propertyClass] += 1 ;
    }

    /**
     * @param $groupOccurence
     * @return array
     */
    protected function printGroupOccurences($groupOccurence)
    {
        foreach ($groupOccurence as $className => $count) {
            $maxWidth = $this->maxClassNameLength + 2;
            $diffToMaxLength = $this->maxClassNameLength - strlen($className);
            $offset = $maxWidth - $diffToMaxLength;
            $this->output->writeln(sprintf('%'.$offset.'s %'.$diffToMaxLength.'d', $className, $count));
        }
        $this->output->writeln('');
    }

    /**
     * @param string $groupName
     * @param int $count
     */
    protected function printGroup($groupName, $count)
    {
        $this->output->writeln(sprintf('<fg=yellow>%s</fg=yellow> (%d occurences)', $groupName, $count));
    }

    protected function detectMaxOutputLength()
    {
        foreach ($this->usedGroups as $group => $occurences) {
            $this->maxClassNameLength = max(
                max(array_map('strlen', array_keys($occurences))),
                $this->maxClassNameLength
            );
            $this->maxGroupNameLength = max(strlen($group), $this->maxGroupNameLength);
        }
    }

    /**
     * @param MergeableClassMetadata $jmsMetadata
     */
    protected function fetchJmsGroupsFromMetadata($jmsMetadata)
    {
        /** @var PropertyMetadata $property */
        foreach ($jmsMetadata->propertyMetadata as $property) {
            $groups = $property->groups;
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    if ($this->output->isDebug()) {
                        $this->output->writeln('found group in class ' . $property->class . ': ' . $group);
                    }
                    $this->addToUsedGroups($group, $property->class);
                }
            }
        }
    }

    /**
     * @param ClassMetadata[] $knownEntities
     */
    protected function getJmsGroups($knownEntities)
    {
        $this->output->writeln('<info>checking for serializer metadata...</info>');
        $serializer = $this->getContainer()->get('jms_serializer');
        foreach ($knownEntities as $entity) {
            $jmsMetadata = $serializer->getMetadataFactory()->getMetadataForClass(
                $entity->getReflectionClass()->getName()
            );
            $this->fetchJmsGroupsFromMetadata($jmsMetadata);
        }
        $this->output->writeln('<info>done.</info>' . PHP_EOL);
    }

    /**
     * @param bool $short
     */
    protected function printGroups($short = false)
    {
        $this->output->writeln('Found groups:');
        foreach ($this->usedGroups as $groupName => $groupOccurence) {
            $count = array_sum(array_values($groupOccurence));
            $this->printGroup($groupName, $count);

            if (!$short) {
                $this->printGroupOccurences($groupOccurence);
            }
        }
    }
}