<?php

namespace Bolt\Extension\TwoKings\HierarchicalRoutes\Listener;

use Bolt\Events\StorageEvent;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event class to handle storage related events.
 *
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class StorageEventListener implements EventSubscriberInterface
{
    /** @var Application Bolt's Application object */
    private $app;

    /**
     * Initiate the listener with Bolt Application instance and extension config.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handles POST_SAVE storage event
     *
     * @param StorageEvent $event
     */
    public function onPostSave(StorageEvent $event)
    {
        $config  = $this->app['hierarchicalroutes.config'];
        $service = $this->app['hierarchicalroutes.service'];

        if ($config->get('cache/enabled', true) && $config->get('settings/rebuild-on-save', true)) {
            $service->build(false);
        }
    }

    /**
     * Handles POST_DELETE storage event
     *
     * @param StorageEvent $event
     */
    public function onPostDelete(StorageEvent $event)
    {
        $config  = $this->app['hierarchicalroutes.config'];
        $service = $this->app['hierarchicalroutes.service'];

        if ($config->get('cache/enabled', true) && $config->get('settings/rebuild-on-save', true)) {
            $service->build(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }
}
