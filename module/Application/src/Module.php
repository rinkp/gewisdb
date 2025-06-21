<?php

declare(strict_types=1);

namespace Application;

use Application\Command\Factory\LoadFixturesCommandFactory;
use Application\Command\LoadFixturesCommand;
use Application\Extensions\Doctrine\Middleware\SetRoleMiddleware;
use Application\Mapper\ConfigItem as ConfigItemMapper;
use Application\Mapper\Factory\ConfigItemFactory as ConfigItemMapperFactory;
use Application\Service\Config as ConfigService;
use Application\Service\Email as EmailService;
use Application\Service\Factory\ConfigFactory as ConfigServiceFactory;
use Application\Service\Factory\EmailFactory as EmailServiceFactory;
use Application\Service\Factory\FileStorageFactory as FileStorageServiceFactory;
use Application\Service\FileStorage as FileStorageService;
use Application\View\Helper\FileUrl;
use Application\View\Helper\IsModuleActive;
use Application\View\Helper\NotificationCount;
use Database\Service\FrontPage as FrontPageService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Laminas\EventManager\EventInterface;
use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container as SessionContainer;
use Laminas\Validator\AbstractValidator;
use Locale;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Report\Listener\DatabaseDeletionListener;
use Report\Listener\DatabaseUpdateListener;

class Module
{
    public function init(ModuleManager $moduleManager): void
    {
        // Register event listener for when all modules are loaded to register our database listeners. This is necessary
        // because `onBootstrap` is never called when using `laminas/laminas-cli`.
        $events = $moduleManager->getEventManager();
        $events->attach('loadModules.post', [$this, 'modulesLoaded']);
    }

    public function onBootstrap(MvcEvent $e): void
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $locale = $this->determineLocale($e);

        $mvcTranslator = $e->getApplication()->getServiceManager()->get(MvcTranslator::class);
        $translator = $mvcTranslator->getTranslator();
        if ($translator instanceof I18nTranslator) {
            $translator->setlocale($locale);
        }

        Locale::setDefault($locale);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'logError']);
        $eventManager->attach(MvCEvent::EVENT_RENDER_ERROR, [$this, 'logError']);

        // Enable Laminas\Validator default translator
        AbstractValidator::setDefaultTranslator($mvcTranslator);
    }

    public function modulesLoaded(EventInterface $event): void
    {
        /** @var ServiceManager $container */
        $container = $event->getParam('ServiceManager');
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $eventManager = $entityManager->getEventManager();
        $eventManager->addEventListener([Events::postPersist], $container->get(DatabaseUpdateListener::class));
        $eventManager->addEventListener([Events::postUpdate], $container->get(DatabaseUpdateListener::class));
        $eventManager->addEventListener([Events::preRemove], $container->get(DatabaseDeletionListener::class));
    }

    public function logError(MvcEvent $e): void
    {
        $container = $e->getApplication()->getServiceManager();
        /** @var Logger $logger */
        $logger = $container->get('logger');

        if ('error-router-no-match' === $e->getError()) {
            // not an interesting error
            return;
        }

        if ('error-exception' === $e->getError()) {
            $logger->error($e->getParam('exception'));

            return;
        }

        $logger->error($e->getError());
    }

    protected function determineLocale(MvcEvent $e): string
    {
        $session = new SessionContainer('lang');
        if (!isset($session->lang)) {
            $session->lang = 'en';
        }

        return $session->lang;
    }

    /**
     * Get the configuration for this module.
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Get service configuration.
     */
    public function getServiceConfig(): array
    {
        return [
            'invokables' => [
                SetRoleMiddleware::class => SetRoleMiddleware::class,
            ],
            'factories' => [
                ConfigItemMapper::class => ConfigItemMapperFactory::class,
                ConfigService::class => ConfigServiceFactory::class,
                EmailService::class => EmailServiceFactory::class,
                FileStorageService::class => FileStorageServiceFactory::class,
                'application_mail_transport' => static function (ContainerInterface $container) {
                    $config = $container->get('config')['email'];

                    $class = '\Laminas\Mail\Transport\\' . $config['transport'];
                    $optionsClass = '\Laminas\Mail\Transport\\' . $config['transport'] . 'Options';
                    $transport = new $class();
                    $transport->setOptions(new $optionsClass($config['options']));

                    return $transport;
                },
                'logger' => static function (ContainerInterface $container) {
                    $logger = new Logger('gewisdb');
                    $config = $container->get('config')['logging'];

                    $handler = new RotatingFileHandler(
                        $config['logfile_path'],
                        $config['max_rotate_file_count'],
                        $config['minimal_log_level'],
                    );
                    $logger->pushHandler($handler);

                    return $logger;
                },
                LoadFixturesCommand::class => LoadFixturesCommandFactory::class,
            ],
        ];
    }

    /**
     * Get view helper configuration.
     */
    public function getViewHelperConfig(): array
    {
        return [
            'factories' => [
                'fileUrl' => static function (ContainerInterface $container) {
                    return new FileUrl($container->get('config'));
                },
                'isModuleActive' => static function (ContainerInterface $container) {
                    return new IsModuleActive($container);
                },
                'getNotificationCount' => static function (ContainerInterface $container) {
                    return new NotificationCount($container->get(FrontPageService::class));
                },
            ],
        ];
    }
}
