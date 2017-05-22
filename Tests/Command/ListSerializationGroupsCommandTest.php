<?php
namespace JMS\SerializerBundle\Tests\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use Metadata\MetadataFactory;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use JMS\SerializerBundle\Command\ListSerializationGroupsCommand;

class ListSerializationGroupsCommandTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel | \PHPUnit_Framework_MockObject_MockObject
     */
    private $kernelMock;
    /**
     * @var \Doctrine\ORM\EntityManager | \PHPUnit_Framework_MockObject_MockObject
     *
     */
    private $emMock;
    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadataFactory |  \PHPUnit_Framework_MockObject_MockObject
     */
    private $doctrineMetadataFactoryMock;

    /**
     * @var \JMS\Serializer\Serializer | \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;
    /**
     * @var \Symfony\Component\DependencyInjection\Container | \PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    public function setUp()
    {
        $this->prepareContainerMocks();
        $this->prepareMetadata();
    }

    public function testExecute()
    {
        $application = new Application($this->kernelMock);
        $listSerializationGroupsCommand = new ListSerializationGroupsCommand();
        $listSerializationGroupsCommand->setContainer($this->kernelMock->getContainer());
        $application->add($listSerializationGroupsCommand);

        $command = $application->find('jms:serializer:list-groups');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $output = $commandTester->getDisplay();
        $this->assertRegExp('/TestGroup1 \(1 occurences\)/', $output);
        $this->assertRegExp('/TestGroup2 \(2 occurences\)/', $output);
        $this->assertRegExp('/TestGroup3/', $output);
    }

    /**
     * @return array
     */
    protected function prepareContainerMocks()
    {
        $this->containerMock = $this->shortGetMock('Symfony\Component\DependencyInjection\Container');
        $this->serializerMock = $this->shortGetMock('JMS\Serializer\Serialize', ['getMetadataFactory'], []);
        $this->emMock = $this->shortGetMock('Doctrine\ORM\EntityManager');
        $this->doctrineMetadataFactoryMock = $this->shortGetMock('\Doctrine\ORM\Mapping\ClassMetadataFactory');
        $this->kernelMock = $this->shortGetMock('Symfony\Component\HttpKernel\Kernel');

        $this->kernelMock->expects($this->any())
            ->method('getContainer')->willReturn($this->containerMock);
        $this->containerMock->expects($this->at(0))
            ->method('get')->with('doctrine.orm.entity_manager')->willReturn($this->emMock);
        $this->containerMock->expects($this->at(1))
            ->method('get')->with('jms_serializer')->willReturn($this->serializerMock);
    }

    /**
     * @param $className
     * @param array $methods
     * @param bool|array $constructorData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function shortGetMock($className, array $methods = [],  $constructorData = false) {
        $mb = $this->getMockBuilder($className);
        if (false === $constructorData) {
            $mb->disableOriginalConstructor();
        } else {
            $mb->setConstructorArgs($constructorData);
        }
        if (!empty($methods)) {
            $mb->setMethods($methods);
        }

        return $mb->getMock();
    }

    protected function prepareMetadata()
    {
        $classMetadata = new ClassMetadata('ExampleEntity');
        $classMetadata->reflClass = new ReflectionClass('JMS\SerializerBundle\Tests\Command\Fixture\ExampleEntity');
        $factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));

        $this->doctrineMetadataFactoryMock->expects($this->once())
            ->method('getAllMetadata')->willReturn([$classMetadata]);
        $this->emMock->expects($this->once())
            ->method('getMetadataFactory')->willReturn($this->doctrineMetadataFactoryMock);
        $this->serializerMock->expects($this->any())
            ->method('getMetadataFactory')->willReturn($factory);
    }
}