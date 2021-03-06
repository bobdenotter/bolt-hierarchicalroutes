<?php

namespace Bolt\Extension\TwoKings\HierarchicalRoutes;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\SimpleExtension;
use Bolt\Extension\TwoKings\HierarchicalRoutes\Config\Config;
use Bolt\Extension\TwoKings\HierarchicalRoutes\Controller\HierarchicalRoutesController;
use Bolt\Extension\TwoKings\HierarchicalRoutes\Listener\StorageEventListener;
use Bolt\Menu\MenuEntry;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HierarchicalRoutesExtension class
 *
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class HierarchicalRoutesExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        // https://docs.bolt.cm/extensions/essentials#adding-storage-events
        $storageEventListener = new StorageEventListener($this->getContainer());
        $dispatcher->addListener(StorageEvents::POST_SAVE, [$storageEventListener, 'onPostSave']);
        $dispatcher->addListener(StorageEvents::POST_DELETE, [$storageEventListener, 'onPostDelete']);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        return [
            '/' => new HierarchicalRoutesController(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendRoutes(ControllerCollection $collection)
    {
        $collection->match('/extend/hierarchical-routes', [$this, 'tree']);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerMenuEntries()
    {
        $menuEntry = (new MenuEntry('my-custom-backend-page', 'hierarchical-routes'))
            ->setLabel('Hierarchial Routes')
            ->setIcon('fa:sitemap')
            ->setPermission('settings')
        ;

        return [$menuEntry];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates' => [
                'position' => 'prepend',
                'namespace' => 'bolt'
            ]
        ];
    }

    /**
     *
     */
    public function tree()
    {
        $app = $this->getContainer();
        /*
        $assets = [
            (new Stylesheet('extension.css')),
            (new JavaScript('CollapsibleLists.js')),
            (new JavaScript('extension.js')),
        ];

        foreach ($assets as $asset) {
            $asset->setZone(Zone::BACKEND);
            $file = $this->getWebDirectory()->getFile($asset->getPath());
            $asset->setPackageName('extensions')->setPath($file->getPath());
            $app['asset.queue.file']->add($asset);
        }
        //*/

        $app = $this->getContainer();

        $data = [
            'title' => "Hierarchical Routes Tree",
            'tree'  => $app['hierarchicalroutes.service']->getTree(),
        ];

        $html = $app['twig']->render("@bolt/tree.twig", $data);

        return new Response($html);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['hierarchicalroutes.config'] = $app->share(function () { return new Config($this->getConfig()); });
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new Provider\HierarchicalRoutesProvider(),
        ];
    }
}
