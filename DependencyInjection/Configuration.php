<?php

namespace EE\ExtraMailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ee_extra_mailer');

        $rootNode
            ->children()
                ->arrayNode('from_email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('address')->defaultValue('webmaster@example.com')->cannotBeEmpty()->end()
                        ->scalarNode('sender_name')->defaultValue('webmaster')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode('theme')
                    ->defaultValue('FloralWhite')
                    ->info('one of color themes')
                    ->example('FloralWhite')
                ->end()
             ->end()
             ;
        
        return $treeBuilder;
    }
}
