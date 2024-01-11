<?php

declare(strict_types=1);

namespace Database\Form\Fieldset;

use Application\Model\Enums\MeetingTypes;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\InArray;

/**
 * @phpstan-type DecisionFieldsetType = array{
 *  meeting_type: value-of<MeetingTypes>,
 *  meeting_number: numeric-string,
 *  point: numeric-string,
 *  number: numeric-string,
 * }
 */
class Decision extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('fdecision');

        $this->add([
            'name' => 'meeting_type',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'meeting_number',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'point',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'number',
            'type' => Hidden::class,
        ]);
    }

    /**
     * Specification for input filters.
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'meeting_type' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => MeetingTypes::values(),
                        ],
                    ],
                ],
            ],
            'meeting_number' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => Digits::class,
                    ],
                ],
            ],
            'point' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => Digits::class,
                    ],
                ],
            ],
            'number' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => Digits::class,
                    ],
                ],
            ],
        ];
    }
}
