<?php

namespace Database\Controller\Factory;

use Checker\Service\Checker as CheckerService;
use Database\Controller\MemberController;
use Database\Service\Member as MemberService;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MemberControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return MemberController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): MemberController {
        /** @var MvcTranslator $translator */
        $translator = $container->get(MvcTranslator::class);
        /** @var CheckerService $checkerService */
        $checkerService = $container->get(CheckerService::class);
        /** @var MemberService $memberService */
        $memberService = $container->get(MemberService::class);

        return new MemberController(
            $translator,
            $checkerService,
            $memberService,
        );
    }
}
