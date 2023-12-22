<?php

namespace LimLabs\KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('kafka');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('clients')
            ->arrayPrototype()
            ->children()
            ->scalarNode('brokers')->end()
            ->scalarNode('log_level')->end()
            ->scalarNode('debug')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}