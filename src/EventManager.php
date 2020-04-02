<?php

namespace Yiisoft\EventDispatcher;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Yiisoft\EventDispatcher\Provider\ConcreteProvider;
use Yiisoft\EventDispatcher\Provider\Provider;

class EventManager
{
    private ListenerProviderInterface $listenerProvider;

    private ContainerInterface $container;

    public function __construct(ListenerProviderInterface $listenerProvider, ContainerInterface $container)
    {
        if (!($listenerProvider instanceof Provider || $listenerProvider instanceof ConcreteProvider)) {
            throw new \InvalidArgumentException(
                'Listener provider must be instance of Yiisoft\EventDispatcher\Provider or
                Yiisoft\EventDispatcher\ConcreteProvider.'
            );
        }
        $this->listenerProvider = $listenerProvider;
        $this->container = $container;
    }

    public function registerListeners(array $listeners): void
    {
        foreach ($listeners as $event => $listener) {
            if (is_string($event)) {
                foreach ($listener as $callable) {
                    if (!is_callable($callable)) {
                        throw new \RuntimeException('Listener must be a callable.');
                    }
                    if (is_array($callable) && !is_object($callable[0])) {
                        $callable = [$this->container->get($callable[0]), $callable[1]];
                    }
                    $this->listenerProvider->attach($callable, $event);
                }
            } else {
                if (!is_callable($listener)) {
                    throw new \RuntimeException('Listener must be a callable.');
                }
                $this->listenerProvider->attach($listener);
            }
        }
    }
}