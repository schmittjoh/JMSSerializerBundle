<?php
namespace JMS\SerializerBundle\Tests\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Serializer;
use JMS\SerializerBundle\Tests\Command\Fixture\ExampleEntity;
use Metadata\MetadataFactory;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use JMS\SerializerBundle\Command\ListSerializationGroupsCommand;

class ListSerializationGroupsCommandTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $kernelMock;
    private $emMock;
    private $doctrineMetadataFactoryMock;

    public function setUp()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()->getMock();
        $this->kernelMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')
            ->disableOriginalConstructor()->getMock();
        $this->kernelMock->expects($this->any())
            ->method('getContainer')
            ->willReturn($containerMock);
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serialize')
            ->setMethods(['getMetadataFactory'])
            ->getMock();
        $this->emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor('Doctrine\ORM\EntityManager')
            ->getMock();
        $this->doctrineMetadataFactoryMock = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata = new ClassMetadata('ExampleEntity');
        $reflClass = new ReflectionClass('ExampleEntity');
        $classMetadata->reflClass = $reflClass;
        $this->doctrineMetadataFactoryMock->expects($this->once())
            ->method('getAllMetadata')
            ->willReturn([$classMetadata]);

        $this->emMock->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($this->doctrineMetadataFactoryMock);

        $annotationDriver = new AnnotationDriver(new AnnotationReader());
        $factory = new MetadataFactory($annotationDriver);
        $serializerMock->expects($this->any())->method('getMetadataFactory')->willReturn($factory);
        $containerMock->expects($this->at(0))->method('get')->with('doctrine.orm.entity_manager')->willReturn($this->emMock);
        $containerMock->expects($this->at(1))->method('get')->with('jms_serializer')->willReturn($serializerMock);
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
}