<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler;

use ArturDoruch\ClassValidator\ClassValidator;
use ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter\ValueFormatterInterface;
use ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter\ValueFormatterManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogContextValueFormattersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $formatterManagerDefinition = new Definition(ValueFormatterManager::class, [$container->getDefinition('twig')]);
        $container->setDefinition('arturdoruch_eventlogger.templating.log_context.value_formatter_manager', $formatterManagerDefinition);

        $formattersConfig = $container->getParameter('arturdoruch_eventlogger.templating.log_context.value_formatters');

        foreach ($formattersConfig as $config) {
            ClassValidator::validateImplementsInterface($config['class'], ValueFormatterInterface::class, 'log context value formatter');
            $definition = new Definition($config['class']);

            if (!empty($config['template'])) {
                $definition->addMethodCall('setTemplate', [$config['template']]);
            }

            $definition->addMethodCall('setOptions', [$config['options']]);

            $formatterManagerDefinition->addMethodCall('add', [$definition]);
        }
    }
}
 