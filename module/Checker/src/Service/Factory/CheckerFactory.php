<?php

namespace Checker\Service\Factory;

use Checker\Service\Checker as CheckerService;
use Checker\Service\Installation as InstallationService;
use Checker\Service\Meeting as MeetingService;
use Checker\Service\Member as MemberService;
use Checker\Service\Organ as OrganService;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CheckerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return CheckerService
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): CheckerService {
        /** @var InstallationService $installationService */
        $installationService = $container->get(InstallationService::class);
        /** @var MeetingService $meetingService */
        $meetingService = $container->get(MeetingService::class);
        /** @var MemberService $memberService */
        $memberService = $container->get(MemberService::class);
        /** @var OrganService $organService */
        $organService = $container->get(OrganService::class);
        /** @var TransportInterface $mailTransport */
        $mailTransport = $container->get('checker_mail_transport');
        /** @var array $config */
        $config = $container->get('config');

        return new CheckerService(
            $installationService,
            $meetingService,
            $memberService,
            $organService,
            $mailTransport,
            $config,
        );
    }
}
