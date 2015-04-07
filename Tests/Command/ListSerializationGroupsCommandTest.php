<?php
namespace JMS\SerializerBundle\Tests\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Tests\Command\Fixture\ExampleEntity;
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
        $metadataFactory = $this->getMockBuilder('\Metadata\MetadataFactory')->disableOriginalConstructor()->getMock();
        $annotationDriver = new AnnotationDriver(new AnnotationReader());
        $metadata = $annotationDriver->loadMetadataForClass(new \ReflectionClass(new ExampleEntity()));
        $metadataFactory->expects($this->once())->method('getMetadataForClass')->willReturn($metadata);
        $metadataFactory->expects($this->once())->method('getAllClassNames')->willReturn(['ExampleEntity']);
        $serializerMock->expects($this->any())->method('getMetadataFactory')->willReturn($metadataFactory);
        $containerMock->expects($this->exactly(1))->method('get')->with('jms_serializer')->willReturn($serializerMock);
    }

    public function testExecute()
    {
        $application = new Application($this->kernelMock);
        $listSerilizationGroupsCommand = new ListSerializationGroupsCommand();
        $listSerilizationGroupsCommand->setContainer($this->kernelMock->getContainer());
        $application->add($listSerilizationGroupsCommand);

        $command = $application->find('jms:serializer:list-groups');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $output = $commandTester->getDisplay();
        $this->assertRegExp('/TestGroup1 \(1 occurences\)/', $output);
        $this->assertRegExp('/TestGroup2 \(2 occurences\)/', $output);
        $this->assertRegExp('/TestGroup3/', $output);
    }
}