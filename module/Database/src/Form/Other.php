<?php

namespace Database\Form;

use Database\Form\Fieldset\Meeting as MeetingFieldset;
use Laminas\Form\Element\{
    Submit,
    Text,
};
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;

class Other extends AbstractDecision implements InputFilterProviderInterface
{
    public function __construct(MeetingFieldset $meeting)
    {
        parent::__construct($meeting);

        $this->add([
            'name' => 'content',
            'type' => Text::class,
            'options' => [
                'label' => 'Besluit',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Verzend',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'content' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 3,
                        ],
                    ],
                ],
            ],
        ];
    }
}
