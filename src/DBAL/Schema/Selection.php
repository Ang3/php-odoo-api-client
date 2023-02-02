<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

class Selection
{
    /**
     * @var Choice[]
     */
    private array $choices;

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

    /**
     * @return Choice[]
     */
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
