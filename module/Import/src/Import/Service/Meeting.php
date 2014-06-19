<?php

namespace Import\Service;

use Application\Service\AbstractService;

class Meeting extends AbstractService
{

    /**
     * Get all meetings.
     */
    public function getMeetings()
    {
        return $this->getQuery()->fetchAllMeetings();
    }

    /**
     * Get all decisions from a meeting.
     *
     * @param array $meeting
     *
     * @return array decisions
     */
    public function getMeetingDecisions($meeting)
    {
        return $this->getQuery()->fetchDecisions(
            $meeting['vergadertypeid'], $meeting['vergadernr']);
    }

    /**
     * Get the query object.
     */
    public function getQuery()
    {
        return $this->getServiceManager()->get('import_database_query');
    }

    /**
     * Get the console object.
     */
    public function getConsole()
    {
        return $this->getServiceManager()->get('console');
    }
}