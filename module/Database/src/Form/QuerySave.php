<?php

declare(strict_types=1);

namespace Database\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Mvc\I18n\Translator;

class QuerySave extends Query implements InputFilterProviderInterface
{
    public function __construct(private readonly Translator $translator)
    {
        parent::__construct($this->translator);

        $this->add([
            'name' => 'category',
            'type' => Text::class,
            'options' => [
                'label' => $this->translator->translate('Category'),
            ],
        ]);

        $this->add([
            'name' => 'name',
            'type' => Text::class,
            'options' => [
                'label' => $this->translator->translate('Name'),
            ],
        ]);

        $this->add([
            'name' => 'submit_save',
            'type' => Submit::class,
            'attributes' => [
                'label' => $this->translator->translate('Save'),
                'value' => 'save',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        $filter = parent::getInputFilterSpecification();
        $filter += [
            'name' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ],
        ];

        return $filter;
    }
}
