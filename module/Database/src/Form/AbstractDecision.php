<?php

namespace Database\Form;

use Database\Form\Fieldset\Meeting as MeetingFieldset;
use Database\Model\Meeting as MeetingModel;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;

abstract class AbstractDecision extends Form
{
    public function __construct(MeetingFieldset $meeting)
    {
        parent::__construct();

        $meeting->setName('meeting');
        $this->add($meeting);

        $this->add([
            'name' => 'point',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'decision',
            'type' => Hidden::class,
        ]);

        // TODO: filters
    }

    /**
     * Set data for the decision.
     */
    public function setDecisionData(
        MeetingModel $meeting,
        int $point,
        int $decision,
    ): void {
        $this->get('meeting')->setMeetingData($meeting);
        $this->get('point')->setValue($point);
        $this->get('decision')->setValue($decision);
    }
}
