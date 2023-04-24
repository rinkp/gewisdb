<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\Identity\Identity;
use Laminas\Session\Container as SessionContainer;

/**
 * @method Identity identity()
 */
class IndexController extends AbstractActionController
{
    public function langAction(): Response
    {
        $session = new SessionContainer('lang');
        $session->lang = $this->params()->fromRoute('lang');

        if (
            'en' !== $session->lang
            && 'nl' !== $session->lang
        ) {
            $session->lang = 'en';
        }

        if (null === $this->identity()) {
            // If not logged in, the language action was likely called from the enrolment form, so redirect back to it.
            return $this->redirect()->toRoute(
                'member/subscribe',
            );
        }

        return $this->redirect()->toRoute('home');
    }
}
