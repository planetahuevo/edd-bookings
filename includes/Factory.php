<?php

namespace Aventura\Edd\Bookings;

use \Aventura\Edd\Bookings\Controller\AjaxController;
use \Aventura\Edd\Bookings\Controller\AssetsController;
use \Aventura\Edd\Bookings\Controller\BookingController;
use \Aventura\Edd\Bookings\Controller\ServiceController;
use \Aventura\Edd\Bookings\Factory\BookingFactory;
use \Aventura\Edd\Bookings\Factory\FactoryAbstract;
use \Aventura\Edd\Bookings\Factory\ServiceFactory;
use \Aventura\Edd\Bookings\Settings\EddExtensionSettings;
use \Aventura\Edd\Bookings\Settings\Settings;

/**
 * Description of Factory
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class Factory extends FactoryAbstract
{

    const DEFAULT_CLASSNAME = 'Aventura\\Edd\\Bookings\\Plugin';

    /**
     * The parent plugin instance.
     * 
     * @var Plugin
     */
    protected $_plugin;

    /**
     * Constructs a new instance.
     * 
     * Overrides the constructor in FactoryAbstract, due to the Plugin parameter requirement.
     */
    public function __construct()
    {
        $this->setClassName(static::DEFAULT_CLASSNAME);
    }

    /**
     * Creates the plugin instance.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return Plugin The created plugin instance.
     */
    public function create(array $data = array())
    {
        // Create instance
        $className = $this->getClassName();
        $plugin = new $className($data);
        // Set the plugin's factory to this
        $plugin->setFactory($this);
        return $plugin;
    }

    /**
     * Creates a settings controller.
     *
     * @param array $data Optional array of data. Default: array()
     * @return Settings The created instance.
     */
    public function createSettings(array $data = array())
    {
        return new EddExtensionSettings($this->getPlugin(), __('Bookings', 'eddbk'));
    }

    /**
     * Creates a bookings controller.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return BookingController The created instance.
     */
    public function createBookingController(array $data = array())
    {
        $factory = new BookingFactory($this->getPlugin());
        return new BookingController($this->getPlugin(), $factory);
    }

    /**
     * Creates a service controller.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return ServiceController The created instance.
     */
    public function createServiceController(array $data = array())
    {
        $factory = new ServiceFactory($this->getPlugin());
        return new ServiceController($this->getPlugin(), $factory);
    }

    /**
     * Creates the ajax controller instance.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return AjaxController The created instance.
     */
    public function createAjaxController(array $data = array())
    {
        return new Controller\AjaxController($this->getPlugin());
    }

    /**
     * Creates the assets controller class.
     * 
     * @param array $data Option array of data. Default: array()
     * @return Assets The created instance.
     */
    public function createAssetsController(array $data = array())
    {
        return new AssetsController($this->getPlugin());
    }

    /**
     * Creates the internationalization class.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return I18n The created instance.
     */
    public function createI18n(array $data = array())
    {
        $domain = isset($data['domain'])
            ? $data['domain']
            : EDD_BK_TEXT_DOMAIN;
        $langDir = isset($data['langDir'])
            ? $data['langDir']
            : EDD_BK_LANG_DIR;
        return new I18n($domain, $langDir);
    }

    /**
     * Creates the patcher class instance.
     * 
     * @param array $data Optional array of data. Default: array()
     * @return Patcher The created instance.
     */
    public function createPatcher(array $data = array())
    {
        return new Patcher($this->getPlugin());
    }

    /**
     * Creates the hook manager instance.
     * 
     * @param array $data Option array of data. Default: array()
     * @return HookManager The created instance.
     */
    public function createHookManager(array $data = array())
    {
        $hookManager = new HookManager();
        if (isset($data['actions'])) {
            foreach ($data['actions'] as $action) {
                $callback = array($hookManager, 'addAction');
                call_user_func_array($callback, $action);
            }
        }
        if (isset($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                $callback = array($hookManager, 'addFilter');
                call_user_func_array($callback, $filter);
            }
        }
        return $hookManager;
    }

    /**
     * Creates a cart controller instance.
     *
     * @param array $data Option array of data. Default: array()
     * @return \Aventura\Edd\Bookings\Controller\CartController The created instance.
     */
    public function createCartController(array $data = array())
    {
        $cartController = new Controller\CartController($this->getPlugin());
        return $cartController;
    }

}
