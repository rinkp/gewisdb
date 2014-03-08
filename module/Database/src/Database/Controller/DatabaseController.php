<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DatabaseController extends AbstractActionController
{

    /**
     * Index action.
     */
    public function indexAction()
    {
        return new ViewModel(array());
    }

    /**
     * Create a new meeting.
     */
    public function createAction()
    {
        $service = $this->getMeetingService();
        $request = $this->getRequest();

        if ($request->isPost() && $service->createMeeting($request->getPost())) {
            return new ViewModel(array(
                'succes' => true
            ));
        }

        return new ViewModel(array(
            'form' => $service->getCreateMeetingForm()
        ));
    }

    /**
     * Get the meeting service.
     *
     * @return \Database\Service\Meeting
     */
    public function getMeetingService()
    {
        return $this->getServiceLocator()->get('database_service_meeting');
    }
}
