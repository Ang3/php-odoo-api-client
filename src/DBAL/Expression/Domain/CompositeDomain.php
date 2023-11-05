<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Expression\Domain;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class CompositeDomain implements DomainInterface
{
    /**
     * Logical operators.
     */
    public const AND = '&';
    public const OR = '|';
    public const NOT = '!';

    /**
     * @var string[]
     */
    private static array $operators = [
        self::AND,
        self::OR,
        self::NOT,
    ];

    /**
     * @param DomainInterface[] $domains
     */
    public function __construct(private string $operator, private array $domains = [])
    {
    }

    public static function criteria(array $criteria = []): self
    {
        $domains = [];

        foreach ($criteria as $fieldName => $value) {
            $domains[] = new Comparison($fieldName, Comparison::EQUAL_TO, $value);
        }

        return new self(self::AND, $domains);
    }

    public function __clone()
    {
        foreach ($this->domains as $key => $domain) {
            $this->domains[$key] = clone $domain;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return DomainInterface[]|\Generator
     */
    public function getIterator(): \Generator
    {
        foreach ($this->getDomains() as $key => $domain) {
            yield $key => $domain;
        }
    }

    /**
     * @static
     *
     * @return string[]
     */
    public static function getOperators(): array
    {
        return self::$operators;
    }

    public function toArray(): array
    {
        $domain = $this->prepare();

        if (!($domain instanceof self)) {
            return $domain ? [$domain->toArray()] : [];
        }

        $result = [$domain->getOperator()];

        foreach ($domain->getDomains() as $domain) {
            $domainArray = $domain->toArray();

            if ($domain instanceof self) {
                foreach ($domainArray as $value) {
                    $result[] = $value;
                }

                continue;
            }

            $result[] = $domainArray;
        }

        return $result;
    }

    /**
     * @internal
     *
     * Create a copy according to arity policy of Odoo polish notation
     */
    private function prepare(): ?DomainInterface
    {
        $domains = $this->domains;
        $nbDomains = \count($domains);

        if (0 === $nbDomains) {
            return null;
        }

        if (1 === $nbDomains) {
            return self::NOT === $this->operator ? $this : array_shift($domains);
        }

        if (self::NOT === $this->operator) {
            $andX = new self(self::AND, $domains);

            return new self($this->operator, array_filter([$andX->prepare()]));
        }

        if (2 === $nbDomains) {
            return $this;
        }

        foreach ($domains as $key => $subDomain) {
            if ($subDomain instanceof self) {
                $domains[$key] = $subDomain->prepare();

                if (!$domains[$key]) {
                    unset($domains[$key]);
                }
            }
        }

        $firstDomain = array_shift($domains);
        $subDomain = new self($this->operator, array_filter($domains));

        return new self($this->operator, array_filter([
            $firstDomain, $subDomain->prepare(),
        ]));
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
            if (!$domain) {
                continue;
            }

            $this->add($domain);
        }

        return $this;
    }

    public function add(DomainInterface $domain): self
    {
        if (!$this->has($domain)) {
            $this->domains[] = $domain;
        }

        return $this;
    }

    public function remove(DomainInterface $domain): self
    {
        foreach ($this->domains as $key => $value) {
            if ($value === $domain) {
                unset($this->domains[$key]);
            }
        }

        return $this;
    }

    public function has(DomainInterface $domain): bool
    {
        return \in_array($domain, $this->domains, true);
    }

    public function count(): int
    {
        return \count($this->domains);
    }

    public function isEmpty(): bool
    {
        return 0 === \count($this->domains);
    }
}
