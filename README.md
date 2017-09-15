# TwigProvider

ServiceProvider which provides Twig (with some extension) for KodiApp.

## Installation

```bash
$ composer require kodi-app/kodi-twig-provider
```

## About Twig

Check the [official documentation](https://github.com/nagyatka/pandabase)

Supported Twig version: v2.4.*

We use the original Twig_Environment to rendering thus you can use every functionality which it provides but we extended it with new `render()` function and ContentProviders for the easier usability.

## Initialization of Twig (via TwigServiceProvider)

Initialization of one connection:
```php
$application->run([
    // ...
    KodiConf::SERVICES => [
        // List of Services
        [
            "class_name" => TwigServiceProvider::class,
            "parameters" => [
                // [Mandatory] Absolute path to directory which contains the *.twig files
                Twig::TWIG_PATH             => PATH_BASE."/src/KodiTest/View",
                
                // [Optional] Relative path to page template
                Twig::PAGE_TEMPLATE_PATH    => "/frame/frame.twig",
                
                // [Optional] List of ContentProviders
                Twig::CONTENT_PROVIDERS     => [
                    [
                        "class_name" => PageTitleProvider::class,
                        "parameters" => [
                            "name"  => "page_title",
                            "title" => "Hello world!"
                        ]
                    ],
                    // ...
                ]
            ]
        ]
        // ...
    ],
    // ...
]);
```

## Usage of Twig

**Get Twig instance:**
```php
/** @var Twig $twig You can get Twig via Application singleton instance */
$twig = Application::get("twig")->getTwigEnvironment;

/** @var Twig_Environment $twig If you want to use the original Twig_Environment */
$twig = $twig->getTwigEnvironment();
```

### About our Twig extension

In our extension we provide another concept of html content rendering (based in original twig) to reduce communication overhead between the server and the browser.
We defined a so called page_template which contains all the "static" parts of your page. For example a page_template can contain page header, sidebar, footer, menu, etc.

**TODO: Finish the explanation of page_template.**


#### Render function
```php
/**
 * Renders the html content to string based on parameter. If the HTTP request is an AJAX request it will render only the template
 * in other cases it renders also the page_template and puts the template content to the appropriate position of the page_template.
 *
 * If you want to prevent the usage of page_template you have to set the $forceRawTemplate parameter to true.
 *
 *
 * @param string $templateName Relative path to *.twig template file
 * @param array $parameters Parameters for twig template file
 * @param bool $forceRawTemplate Prevents the rendering of page template of it is true.
 * @param null $pageTemplate Relative path to a page_template file if you dont want to use the default one.
 * @return string
 * @throws HttpInternalServerErrorException When the pageTemplate does not exist.
 */
public function render($templateName, array $parameters = [], bool $forceRawTemplate = false,$pageTemplate = null) {
    // ...
}
```

In the page_template twig file you have to put following line. The twig will render your templates to this position.
```twig
    {% include app.content_template_name %}
```

#### ContentProviders

You are able to attach so called ContentProviders to `app` variable. 
You can define the list of used providers via the `Twig::CONTENT_PROVIDERS` configuration setting.

**Example of usage:**
Configuration:
```php
Twig::CONTENT_PROVIDERS => [
    [
        "class_name" => PageTitleProvider::class,
        "parameters" => [
            "name"  => "page_title",
            "title" => "Hello world!"
        ]
    ],
    // ...
]
```
In twig:
```twig
<title>{{app.page_title}}</title>
```
Notice that you have to use the `name` parameter (in configuration) to refer a ContentProvider.

 
**IMPORTANT:** In the extension, the `app` variable name is reserved for passing the ContentProviders in twig files!

If you want to create your own ContentProvider you have to implement the abstract ContentProvider class.






