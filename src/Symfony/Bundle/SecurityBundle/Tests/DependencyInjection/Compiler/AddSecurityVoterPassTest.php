<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Compiler\AddSecurityVotersPass;
use Symfony\Component\DependencyInjection\Reference;

class AddSecurityVoterPassTest extends \PHPUnit_Framework_TestCase
{
    public function testThatSecurityVotersAreProcessedInPriorityOrder()
    {
        $services = array(
            'no_prio_service' => array(),
            'lowest_prio_service' => array(0 => array('priority' => 100)),
            'highest_prio_service' => array(0 => array('priority' => 200)),
            'zero_prio_service' => array(0 => array('priority' => 0)),
        );

        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')->getMock();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->setMethods(
            array('findTaggedServiceIds', 'getDefinition', 'hasDefinition')
        )->getMock();

        $container->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));
        $container->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->with('security.access.decision_manager')
            ->will($this->returnValue($definition));
        $container->expects($this->atLeastOnce())
            ->method('hasDefinition')
            ->with('security.access.decision_manager')
            ->will($this->returnValue(true));

        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('setVoters', array(
                array(
                    new Reference('highest_prio_service'),
                    new Reference('lowest_prio_service'),
                    new Reference('zero_prio_service'),
                    new Reference('no_prio_service')
                )
            ));

        $compilerPass = new AddSecurityVotersPass();
        $compilerPass->process($container);
    }
}
