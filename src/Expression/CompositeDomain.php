<?php

namespace Ang3\Component\Odoo\Expression;

use Generator;

class CompositeDomain implements DomainInterface, \IteratorAggregate
{
    /**
     * Logical operators.
     */
    public const AND = '&';
    public const OR = '|';
    public const NOT = '!';

    /**
     * @var string
     */
    private $operator;

    /**
     * @var DomainInterface[]
     */
    private $domains = [];

    public function __construct(string $operator, array $domains = [])
    {
        $this->operator = $operator;
        $this->setDomains($domains);
    }

    public function __clone()
    {
        foreach ($this->domains as $key => $domain) {
            $this->domains[$key] = clone $domain;
        }
    }

    /**
     * @return DomainInterface[]|Generator
     */
    public function getIterator(): Generator
    {
        foreach ($this->getDomains() as $key => $domain) {
            yield $key => $domain;
        }
    }

    public function toArray(): array
    {
        $result = [$this->operator];
        $normalizedDomain = $this->normalize();

        if (!$normalizedDomain) {
            return [];
        }

        if (!($normalizedDomain instanceof self)) {
            return $normalizedDomain->toArray();
        }

        foreach ($normalizedDomain as $domain) {
            if ($domain instanceof self) {
                $expr = $domain->toArray();

                foreach ($expr as $value) {
                    $result[] = $value;
                }

                continue;
            }

            $result[] = $domain->toArray();
        }

        return $result;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return DomainInterface[]
     */
    public function getDomains(): array
    {
        return array_values($this->domains);
    }

    public function setDomains(array $domains = []): self
    {
        $this->domains = [];

        foreach ($domains as $domain) {
            if ($domain) {
                $this->add($domain);
            }
        }

        return $this;
    }

    public function add(DomainInterface ...$domains): self
    {
        foreach ($domains as $domain) {
            if (!$this->has($domain)) {
                $this->domains[] = $domain;
            }
        }

        return $this;
    }

    public function remove(DomainInterface ...$domains): self
    {
        foreach ($domains as $value) {
            foreach ($this->domains as $key => $domain) {
                if ($domain === $value) {
                    unset($this->domains[$key]);
                }
            }
        }

        return $this;
    }

    public function has(DomainInterface $domain): bool
    {
        return in_array($domain, $this->domains, true);
    }

    public function count(): int
    {
        return count($this->domains);
    }

    public function normalize(): ?DomainInterface
    {
        if ((self::NOT === $this->operator)) {
            if ($this->count() < 1) {
                return null;
            }

            if ($this->count() > 1) {
                $andX = new self(self::AND);

                foreach ($this->domains as $operand) {
                    $andX->add($operand);
                }

                $andX = $andX->normalize();

                if (!$andX) {
                    return null;
                }

                $this->setDomains([$andX]);
            }

            return $this;
        }

        $operands = $this->domains;

        foreach ($operands as $key => $operand) {
            if ($operand instanceof self) {
                $operand = $operand->normalize();
            }

            if (!$operand) {
                unset($operands[$key]);
            }

            $operands[$key] = $operand;
        }

        if (!$operands) {
            return null;
        }

        $nbOperands = count($operands);

        if ($nbOperands < 2) {
            return array_pop($operands);
        }

        if (2 === $nbOperands) {
            return new self($this->operator, $operands);
        }

        $normalizedDomain = new self($this->operator);
        $currentDomain = $normalizedDomain;
        $lastOperandKey = $nbOperands - 1;

        foreach ($operands as $key => $operand) {
            if ($key < $lastOperandKey && 1 === $currentDomain->count()) {
                $subDomain = new self($this->operator, [$operand]);
                $currentDomain->add($subDomain);
                $currentDomain = $subDomain;

                continue;
            }

            $currentDomain->add($operand);
        }

        return $normalizedDomain;
    }
}
