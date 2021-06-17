<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Legacy\Core;

use Windwalker\Legacy\Core\Application\ServiceAwareTrait;
use Windwalker\Legacy\Core\Application\WebApplication;
use Windwalker\Legacy\Core\Application\WindwalkerApplicationInterface;
use Windwalker\Legacy\Core\Config\Config;
use Windwalker\Legacy\Core\Console\CoreConsole;
use Windwalker\Legacy\Core\Object\NullObject;
use Windwalker\Legacy\Core\Package\AbstractPackage;
use Windwalker\Legacy\Core\Package\PackageResolver;
use Windwalker\Legacy\Core\Provider\BootableDeferredProviderInterface;
use Windwalker\Legacy\Core\Provider\BootableProviderInterface;
use Windwalker\Legacy\Core\Provider\SystemProvider;
use Windwalker\Legacy\DI\ClassMeta;
use Windwalker\Legacy\DI\Container;
use Windwalker\Legacy\DI\ServiceProviderInterface;
use Windwalker\Legacy\Event\ListenerPriority;
use Windwalker\Legacy\Structure\Structure;

/**
 * The main Windwalker instantiate class.
 *
 * This class will load in both Web and Console. Write some configuration if you want to use in all environment.
 *
 * @since  2.0
 */
trait WindwalkerTrait
{
    use ServiceAwareTrait;

    /**
     * Property booted.
     *
     * @var  boolean
     */
    protected $booted = false;

    /**
     * getName
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * bootWindwalkerTrait
     *
     * @param WindwalkerApplicationInterface $app
     *
     * @return  void
     */
    protected function bootWindwalkerTrait(WindwalkerApplicationInterface $app)
    {
        //
    }

    /**
     * getConfig
     *
     * @return  Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * boot
     *
     * @return  void
     * @throws \ReflectionException
     * @throws \Windwalker\Legacy\DI\Exception\DependencyResolutionException
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Version check
        if (PHP_VERSION_ID < 50600) {
            exit('Please use PHP 5.6 or later.');
        }

        $this->bootTraits($this);

        $this->mode = $this->loadMode();

        $this->loadConfiguration($this->config);

        $this->registerProviders();

        // Set some default objects
        if ($this->container->exists('dispatcher')) {
            $this->dispatcher = $this->container->get('dispatcher');
        } else {
            $this->dispatcher = new NullObject();
        }

        $this->logger = $this->container->get('logger');

        $this->registerListeners();

        $this->registerPackages();

        $this->triggerEvent('onAfterInitialise', ['app' => $this]);

        $this->booted = true;
    }

    /**
     * loadConfiguration
     *
     * @param Structure $config
     * @param string    $name
     *
     * @return  void
     */
    protected function loadConfiguration(Structure $config, $name = null)
    {
        $name = $name ?: $this->getName();

        // Load library config
        $configName = $this->isWeb() ? 'web' : 'console';

        $config->loadFile(__DIR__ . '/../../config/' . $configName . '.php', 'php', ['load_raw' => true]);

        // Load application config
        $file = $this->rootPath . '/etc/app/' . $name . '.php';

        if (is_file($file)) {
            $config->loadFile($file, 'php', ['load_raw' => true]);
        }

        $configs = (array) $config->get('configs', []);

        ksort($configs);

        foreach ($configs as $file) {
            if ($file === false || !is_file($file)) {
                continue;
            }

            $config->loadFile($file, pathinfo($file, PATHINFO_EXTENSION), ['load_raw' => true]);
        }

        $this->container->setParameters($config);
    }

    /**
     * registerProviders
     *
     * @return  void
     * @throws \ReflectionException
     * @throws \Windwalker\Legacy\DI\Exception\DependencyResolutionException
     */
    protected function registerProviders()
    {
        /** @var Container $container */
        $container = $this->getContainer();

        // Register Aliases
        $aliases = (array) $this->get('di.aliases');

        foreach ($aliases as $alias => $target) {
            $container->alias($alias, $target);
        }

        $container->registerServiceProvider($systemProvider = new SystemProvider($this, $this->config));
        $systemProvider->boot($container);

        $providers = (array) $this->config->get('providers');

        foreach ($providers as $interface => &$provider) {
            if ($provider === false) {
                continue;
            }

            if (is_subclass_of($provider, ServiceProviderInterface::class)) {
                // Handle provider
                if ($provider instanceof ClassMeta || (is_string($provider) && class_exists($provider))) {
                    $provider = $container->newInstance($provider);
                }

                if ($provider === false) {
                    continue;
                }

                $container->registerServiceProvider($provider);

                if ($provider instanceof BootableProviderInterface
                    || method_exists($provider, 'boot')) {
                    $provider->boot($container);
                }
            } else {
                // Handle Service
                if (is_numeric($interface)) {
                    $container->prepareSharedObject($provider);
                } else {
                    $container->bindShared($interface, $provider);
                }
            }
        }

        foreach ($providers as $provider) {
            if ($provider === false) {
                continue;
            }

            if (is_subclass_of($provider, ServiceProviderInterface::class)
                && (
                    $provider instanceof BootableDeferredProviderInterface
                    || method_exists($provider, 'bootDeferred')
                )) {
                $provider->bootDeferred($container);
            }
        }
    }

    /**
     * registerPackages
     *
     * @return  static
     * @throws \ReflectionException
     * @throws \Windwalker\Legacy\DI\Exception\DependencyResolutionException
     */
    protected function registerPackages()
    {
        $packages = (array) $this->config->get('packages');

        /** @var PackageResolver $resolver */
        $resolver = $this->container->get('package.resolver');

        $resolver->registerPackages($packages);

        return $this;
    }

    /**
     * registerListeners
     *
     * @return  void
     */
    protected function registerListeners()
    {
        $listeners  = (array) $this->get('listeners');
        $dispatcher = $this->getDispatcher();

        $defaultOptions = [
            'class' => '',
            'priority' => ListenerPriority::NORMAL,
            'enabled' => true,
        ];

        foreach ($listeners as $name => $listener) {
            if ($listener instanceof ClassMeta || is_string($listener) || is_callable($listener)) {
                $listener = ['class' => $listener];
            }

            $listener = array_merge($defaultOptions, (array) $listener);

            if (!$listener['enabled']) {
                continue;
            }

            if (!is_numeric($name) && is_callable($listener['class'])) {
                $dispatcher->listen($name, $listener['class']);
            } else {
                $dispatcher->addListener($this->container->newInstance($listener['class']), $listener['priority']);
            }
        }
    }

    /**
     * getPackage
     *
     * @param string $name
     *
     * @return  AbstractPackage
     */
    public function getPackage($name = null)
    {
        /** @var PackageResolver $resolver */
        $resolver = $this->container->get('package.resolver');

        return $resolver->getPackage($name);
    }

    /**
     * addPackage
     *
     * @param string          $name
     * @param AbstractPackage $package
     *
     * @return  static
     * @throws \ReflectionException
     * @throws \Windwalker\Legacy\DI\Exception\DependencyResolutionException
     */
    public function addPackage($name, AbstractPackage $package)
    {
        /** @var PackageResolver $resolver */
        $resolver = $this->container->get('package.resolver');

        $resolver->addPackage($name, $package);

        return $this;
    }

    /**
     * isConsole
     *
     * @return  boolean
     */
    public function isConsole()
    {
        return $this instanceof CoreConsole;
    }

    /**
     * isWeb
     *
     * @return  boolean
     */
    public function isWeb()
    {
        return $this instanceof WebApplication;
    }

    /**
     * loadMode
     *
     * @return  string
     */
    protected function loadMode()
    {
        return env('APP_ENV') ?: env('WINDWALKER_MODE');
    }

    /**
     * isOffline
     *
     * @return  bool
     */
    public function isOffline()
    {
        $file = $this->get('path.temp', $this->rootPath . '/tmp') . '/offline';

        return is_file($file) ?: (bool) $this->get('system.offline');
    }
}
