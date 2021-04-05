<?php

namespace Ang3\Component\Odoo\DBAL\Schema;

class Selection
{
    /**
     * @var Choice[]
     */
    private $choices;

    /**
     * @param Choice[] $choices
     */
    public function __construct(array $choices = [])
    {
        foreach ($choices as $choice) {
            $this->addChoice($choice);
        }
    }

    public function getIds(): array
    {
        $ids = [];

        foreach ($this->choices as $choice) {
            $ids[] = $choice->getId();
        }

        return $ids;
    }

    public function getNames(): array
    {
        $names = [];

        foreach ($this->choices as $choice) {
            $names[] = $choice->getName();
        }

        return $names;
    }

    public function getValues(): array
    {
        $values = [];

        foreach ($this->choices as $choice) {
            $values[] = $choice->getValue();
        }

        return $values;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @internal
     */
    private function addChoice(Choice $choice): void
    {
        $this->choices[] = $choice;
    }
}
