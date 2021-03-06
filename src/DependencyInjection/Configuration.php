<?php

namespace Pushword\Conversation\DependencyInjection;

use Pushword\Conversation\Form\MessageForm;
use Pushword\Conversation\Form\MultiStepMessageForm;
use Pushword\Conversation\Form\NewsletterForm;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const DEFAULT_APP_FALLBACK = [
        'conversation_notification_email_to',
        'conversation_notification_email_from',
        'conversation_notification_interval',
        //'form',
        'conversation_form_message',
        'conversation_form_multistep_message',
        'conversation_form_ms_message',
        'conversation_form_newsletter',
        'possible_origins',
    ];

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('conversation');
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->variableNode('app_fallback_properties')->defaultValue(self::DEFAULT_APP_FALLBACK)->cannotBeEmpty()->end()
                    ->scalarNode('entity_message')->defaultValue('Pushword\Conversation\Entity\Message')->cannotBeEmpty()->end()
                    ->scalarNode('conversation_notification_email_to')->defaultNull()->end()
                    ->scalarNode('conversation_notification_email_from')->defaultNull()->end()
                    ->scalarNode('conversation_notification_interval')
                        ->defaultValue('P12H')
                        ->info("DateInterval's format")
                    ->end()
                    //->arrayNode('form')->end()
                    ->scalarNode('conversation_form_message')
                        ->defaultValue(MessageForm::class)
                    ->end()
                    ->scalarNode('conversation_form_multistep_message')
                        ->defaultValue(MultiStepMessageForm::class)
                    ->end()
                    ->scalarNode('conversation_form_ms_message')
                        ->defaultValue(MultiStepMessageForm::class)
                    ->end()
                    ->scalarNode('conversation_form_newsletter')
                        ->defaultValue(NewsletterForm::class)
                    ->end()
                    ->scalarNode('possible_origins')->defaultNull()->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
