<?php namespace GeneaLabs\LaravelCasts\Providers;

use GeneaLabs\LaravelCasts\Facades\FormFacade;
use GeneaLabs\LaravelCasts\Facades\HtmlFacade;
use GeneaLabs\LaravelCasts\FormBuilder;
use GeneaLabs\LaravelCasts\HtmlBuilder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Blade;
use Exception;

class LaravelCastsService extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        if (app()->environment('testing', 'local', 'development')) {
            $routesPath = __DIR__ . '/../../routes/web.php';

            if (starts_with(app()->version(), '5.1.')) {
                $routesPath = __DIR__ . '/../../routes/no-middleware-group.php';
            }

            require($routesPath);
        }

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'genealabs-laravel-casts');
        $this->publishes([
            __DIR__ . '/../../config/genealabs-laravel-casts.php' => config_path('genealabs-laravel-casts.php'),
        ], 'config');

        $this->registerBladeDirective('open', 'form');
        $this->registerBladeDirective('model');
        $this->registerBladeDirective('hidden');
        $this->registerBladeDirective('label');
        $this->registerBladeDirective('text');
        $this->registerBladeDirective('email');
        $this->registerBladeDirective('url');
        $this->registerBladeDirective('date');
        $this->registerBladeDirective('datetime');
        $this->registerBladeDirective('password');
        $this->registerBladeDirective('file');
        $this->registerBladeDirective('textarea');
        $this->registerBladeDirective('checkbox');
        $this->registerBladeDirective('radio');
        $this->registerBladeDirective('submit');
        $this->registerBladeDirective('cancel');
        $this->registerBladeDirective('select');
        $this->registerBladeDirective('selectRange');
        $this->registerBladeDirective('selectRangeWithInterval');
        $this->registerBladeDirective('combobox');
        $this->registerBladeDirective('close', 'endform');

        if (starts_with(app()->version(), '5.3.')) {
            $this->registerComponents();
        }
    }

    public function register()
    {
        $this->registerHtmlBuilder();
        $this->registerFormBuilder();
        AliasLoader::getInstance()->alias('Form', FormFacade::class);
        AliasLoader::getInstance()->alias('HTML', HtmlFacade::class);
    }

    public function provides() : array
    {
        return ['genealabs-laravel-casts'];
    }

    private function registerHtmlBuilder()
    {
        $this->app->singleton('html', function ($app) {
            return new HtmlBuilder($app['url'], $app['view']);
        });
    }

    private function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->getToken());

            return $form->setSessionStore($app['session.store']);
        });
    }

    private function registerBladeDirective($formMethod, $alias = null)
    {
        $alias = $alias ?: $formMethod;

        if (array_key_exists($alias, Blade::getCustomDirectives())) {
            throw new Exception("Blade directive '{$alias}' is already registered.");
        }

        app('blade.compiler')->directive($alias, function ($parameters) use ($formMethod) {
            $parameters = trim($parameters, "()");

            return "<?php echo app('form')->{$formMethod}({$parameters}); ?>";
        });
    }

    private function registerComponents()
    {
        $options = [
            'type',
            'controlHtml',
            'name',
            'value' => null,
            'options' => [],
            'fieldWidth' => 9,
            'labelWidth' => 3,
            'isHorizontal' => false,
            'isInline' => false,
            'errors' => [],
        ];

        app('form')->component(
            "bootstrap3Control",
            "genealabs-laravel-casts::components.bootstrap3.control",
            $options
        );

        app('form')->component(
            "bootstrap4Control",
            "genealabs-laravel-casts::components.bootstrap4.control",
            $options
        );

        app('form')->component(
            "vanillaControl",
            "genealabs-laravel-casts::components.vanilla.control",
            $options
        );
    }
}
