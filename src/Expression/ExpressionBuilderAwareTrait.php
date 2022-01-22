<?php

namespace Ang3\Component\Odoo\Expression;

trait ExpressionBuilderAwareTrait
{
    /**
     * @var ExpressionBuilder|null
     */
    protected $expressionBuilder;

    public function expr(): ExpressionBuilder
    {
        return $this->getExpressionBuilder();
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        if (!$this->expressionBuilder) {
            $this->expressionBuilder = new ExpressionBuilder();
        }

        return $this->expressionBuilder;
    }
}
