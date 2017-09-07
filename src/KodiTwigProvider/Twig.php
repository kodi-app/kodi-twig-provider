<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 09. 03.
 * Time: 19:31
 */

namespace KodiTwigProvider;
use KodiCore\Application;
use KodiCore\Core\KodiConf;
use KodiCore\Exception\Http\HttpInternalServerErrorException;
use Twig_Extension_Debug;

/**
 * Class Twig
 *
 * TODO: Add content providers
 *
 * @package KodiTwigProvider
 */
class Twig
{
    const TWIG_PATH = "path";
    const PAGE_TEMPLATE_PATH = "page_frame_template_path";

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $useAjax;

    /**
     * Twig constructor.
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        //Konfiguráció betöltése
        $this->configuration = $configuration;
        $this->contentProviders = [];
        if(isset($this->configuration["content_providers"])) {
            $this->addContentProvider($this->configuration["content_providers"]);
            unset($this->configuration["content_providers"]);
        }

        // Twig inicializálása
        $loader = new \Twig_Loader_Filesystem($configuration[self::TWIG_PATH]);
        $this->twig = new \Twig_Environment($loader,[
            "debug" => Application::getEnvMode() == KodiConf::ENV_DEVELOPMENT
        ]);

        // Ajax csekkolása
        $this->useAjax =(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ?  true : false;

        // Escape
        try {
            $escaper = new \Twig_Extension_Escaper('html');
            $this->twig->addExtension($escaper);
        }
        catch (\LogicException $exception) {}

        if(Application::getEnvMode() == KodiConf::ENV_DEVELOPMENT)
            $this->twig->addExtension(new Twig_Extension_Debug());

        // Saját függvények definiálása
        $this->initializeBaseTwigFunction();
    }

    /**
     * ContentProvider hozzáadása.
     *
     * @param ContentProvider | ContentProvider[] $contents
     */
    public function addContentProvider($contents) {
        if($contents == null) {
            throw new \InvalidArgumentException("You must provide at least one content provider in contents");
        }
        if(is_array($contents)) {
            $this->contentProviders = array_merge($this->contentProviders , $contents);
        } else {
            $this->contentProviders[] = $contents;
        }
    }

    /**
     * A twig segítségével lerendereli a html tartalmat. AJAX kérések esetén csak a megadott template-t rendereli ki,
     * viszont ha nem AJAX kérést kapott a szerver, akkor a Twig inicializálásánál megadott oldalkeretbe tölti bele a
     * template-t.
     *
     * Ha mindenképpen azt szeretnénk, hogy csak a template fájl renderelődjön ki, akkor a $forceRawTemplate paramétert
     * true-ra kell állítani!
     *
     * A renderelésnél elérhető az összes olyan paraméter, amit az alkalmazás addPageFrameContent függvényén keresztül
     * lett beállítva. A Twig fájlokban ezek az 'app.*' változó néven keresztül érhetőek el.
     * Példa:
     *  $twig->addContentProvider(new PageTitleContentProvider("Oldal címe"));
     *
     *  <title>{{ app.page_title }}</title> ==> <title>Oldal címe</title>
     *
     * @param $templateName
     * @param array $parameters
     * @param bool $forceRawTemplate
     * @param null $desiredFrameTemplate
     * @return string
     * @throws HttpInternalServerErrorException
     */
    public function render($templateName, array $parameters = [], bool $forceRawTemplate = false,$desiredFrameTemplate = null) {
        // Különböző contentek betöltése
        foreach ($this->contentProviders as $contentProvider) {
            $parameters["app"][$contentProvider->getKey()] = $contentProvider->getValue();
        }
        if($this->useAjax || $forceRawTemplate) {
            return $this->twig->render($templateName,$parameters);
        } else {
            if(is_array($this->configuration[self::PAGE_TEMPLATE_PATH])) {
                $templates = $this->configuration[self::PAGE_TEMPLATE_PATH];
                $actualRoute = Application::getInstance()->getCore()->getRouter()->getActualRoute();
                if($desiredFrameTemplate != null) {
                    $desiredFrame = $desiredFrameTemplate;
                }
                elseif(isset($actualRoute["page_frame"])) {
                    $desiredFrame = Application::getInstance()->getCore()->getRouter()->getActualRoute()["page_frame"];
                }
                else {
                    $desiredFrame = "default";
                }

                if(array_key_exists($desiredFrame,$templates)) {
                    $pageFrameName = $templates[$desiredFrame];
                }
                else {
                    throw new HttpInternalServerErrorException("Undefined page_frame template path in twig. Check configuration");
                }
            } else {
                $pageFrameName = $this->configuration[self::PAGE_TEMPLATE_PATH];
            }
            $parameters["app"]["content_template_name"] = $templateName;
            return $this->twig->render($pageFrameName,$parameters);
        }
    }

    /**
     *  Betölti a twigbe az általunk definiált függvényeket.
     */
    private function initializeBaseTwigFunction() {
        // Development-e a környezet
        $is_dev = new \Twig_SimpleFunction('is_dev', function(){
            return Application::getEnvMode() == KodiConf::ENV_DEVELOPMENT;
        });
        $this->twig->addFunction($is_dev);

        /*
         * Put other twig function here for better visibility.
         */
    }

    /**
     * Visszaadja a belső twig környezetet.
     * @return \Twig_Environment
     */
    public function getTwigEnvironment() {
        return $this->twig;
    }



}