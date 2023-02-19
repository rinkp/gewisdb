<?php

namespace Database\Form\Board;

use Database\Form\AbstractDecision;
use Laminas\Mvc\I18n\Translator;
use Database\Form\Fieldset\{
    Meeting as MeetingFieldset,
    SubDecision as SubDecisionFieldset,
};
use Laminas\Form\Element\{
    Date,
    Submit,
};
use Laminas\InputFilter\InputFilterProviderInterface;

class Release extends AbstractDecision implements InputFilterProviderInterface
{
    public function __construct(
        private readonly Translator $translator,
        MeetingFieldset $meeting,
        SubDecisionFieldset $installation,
    ) {
        parent::__construct($meeting);

        $this->add(clone $installation);

        $this->add([
            'name' => 'date',
            'type' => Date::class,
            'options' => [
                'label' => $this->translator->translate('Effective From'),
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Submit::class,
            'attributes' => [
                'value' => $this->translator->translate('Relieve Board Member'),
            ],
        ]);
    }

    /**
     * Specification of input filter.
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'date' => [
                'required' => true,
            ],
        ];
    }
}
