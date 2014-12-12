<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class QueryController extends AbstractActionController
{

    /**
     * Index action.
     */
    public function indexAction()
    {
        $service = $this->getQueryService();

        if ($this->getRequest()->isPost()) {
            $result = $service->execute($this->getRequest()->getPost());

            if (null !== $result) {
                return new ViewModel(array(
                    'form' => $service->getQueryForm(),
                    'exportform' => $service->getQueryExportForm(),
                    'result' => $result
                ));
            }
        }

        return new ViewModel(array(
            'form' => $service->getQueryForm()
        ));
    }

    /**
     * Export action.
     */
    public function exportAction()
    {
        $service = $this->getQueryService();

        if ($this->getRequest()->isPost()) {
            $result = $service->export($this->getRequest()->getPost());

            if (null !== $result) {
                // TODO: show result
            }
        }
        return $this->redirect()->toRoute('query');
    }

    /**
     * Get the query service.
     *
     * @return \Database\Service\Query
     */
    public function getQueryService()
    {
        return $this->getServiceLocator()->get('database_service_query');
    }
}