<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 09. 03.
 * Time: 20:38
 */

namespace KodiTwigProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TwigServiceProvider implements ServiceProviderInterface
{

    private $configuration;

    /**
     * SessionProvider constructor.
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }


    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $configuration = $this->configuration;

        $pimple['twig'] = function ($c) use($configuration) {
            return new Twig($configuration);
        };
    }
}