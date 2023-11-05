<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\DBAL\Expression;

use Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Expression\Domain\DomainInterface;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain
 *
 * @internal
 */
final class CompositeDomainTest extends AbstractDomainTest
{
    /**
     * @covers ::add
     * @covers ::getOperator
     * @covers ::has
     * @covers ::remove
     * @covers ::setOperation
     *
     * @throws \ReflectionException
     */
    public function testAccessorsAndMutators(): void
    {
        $domain = new CompositeDomain(CompositeDomain::AND, []);
        $fakeDomain = $this->createMock(DomainInterface::class);

        $this
            ->createObjectTester($domain)
            ->assertPropertyAccessorsAndMutators('operator', CompositeDomain::OR)
            ->assertPropertyAccessorsAndMutators('domains', $fakeDomain, [
                'is_collection' => true,
                'adder' => ['name' => 'add'],
                'remover' => ['name' => 'remove'],
                'hasser' => ['name' => 'has'],
            ])
        ;
    }

    /**
     * Data provider for the test for method ::toArray().
     */
    public function provideToArrayDataSet(): array
    {
        $domainA = $this->createFakeDomain('A');
        $domainB = $this->createFakeDomain('B');
        $domainC = $this->createFakeDomain('C');
        $domainD = $this->createFakeDomain('D');
        $domainE = $this->createFakeDomain('E');
        $domainF = $this->createFakeDomain('F');
        $domainG = $this->createFakeDomain('G');
        $domainH = $this->createFakeDomain('H');

        $data = [
            // [ <operator>, <domains>, <expected_result> ],
            [ // 0
                CompositeDomain::AND, [], [],
            ],
            [ // 1
                CompositeDomain::AND, [$domainA],
                [$domainA->toArray()],
            ],
            [ // 2
                CompositeDomain::AND, [$domainA, $domainB],
                ['&', $domainA->toArray(), $domainB->toArray()],
            ],
            [ // 3
                CompositeDomain::AND, [$domainA, $domainB, $domainC],
                ['&', $domainA->toArray(), '&', $domainB->toArray(), $domainC->toArray()],
            ],
            [ // 4
                CompositeDomain::OR, [], [],
            ],
            [ // 5
                CompositeDomain::OR, [$domainA],
                [ $domainA->toArray() ],
            ],
            [ // 6
                CompositeDomain::OR, [$domainA, $domainB],
                ['|', $domainA->toArray(), $domainB->toArray()],
            ],
            [ // 7
                CompositeDomain::OR, [$domainA, $domainB, $domainC],
                ['|', $domainA->toArray(), '|', $domainB->toArray(), $domainC->toArray()],
            ],
            [ // 8
                CompositeDomain::NOT, [], [],
            ],
            [ // 9
                CompositeDomain::NOT, [$domainA],
                ['!', $domainA->toArray()],
            ],
            [ // 10
                CompositeDomain::NOT, [$domainA, $domainB],
                ['!', '&', $domainA->toArray(), $domainB->toArray()],
            ],
            [ // 11
                CompositeDomain::NOT, [$domainA, $domainB, $domainC],
                ['!', '&', $domainA->toArray(), '&', $domainB->toArray(), $domainC->toArray()],
            ],
        ];

        /**
         * @see https://www.odoo.com/fr_FR/forum/aide-1/question/domain-notation-using-multiple-and-nested-and-2170
         */
        $orXA = new CompositeDomain(CompositeDomain::OR, [$domainA, $domainB]);
        $orXB = new CompositeDomain(CompositeDomain::OR, [$domainC, $domainD, $domainE]);
        $expectedResult = ['&', '|', ['A'], ['B'], '|', ['C'], '|', ['D'], ['E']];
        $data[] = [CompositeDomain::AND, [$orXA, $orXB], $expectedResult];

        // #13 Final test
        $orXA = new CompositeDomain(CompositeDomain::OR, [$domainA, $domainB]);
        $orXB = new CompositeDomain(CompositeDomain::OR, [$domainC, $domainD, $domainE]);
        $orXC = new CompositeDomain(CompositeDomain::OR, [$domainF, $domainG, $domainH]);
        $expectedResult = ['&', '|', ['A'], ['B'], '&', '|', ['C'], '|', ['D'], ['E'], '|', ['F'], '|', ['G'], ['H']];
        $data[] = [CompositeDomain::AND, [$orXA, $orXB, $orXC], $expectedResult];

        return $data;
    }

    /**
     * @covers ::toArray
     *
     * @dataProvider provideToArrayDataSet
     */
    public function testToArray(string $operator, array $domains = [], mixed $expectedResult = null, string $message = ''): void
    {
        $domain = new CompositeDomain($operator, $domains);
        static::assertSame($expectedResult, $domain->toArray(), $message);
    }

    protected function createFakeDomain(mixed $expression): DomainInterface
    {
        $fakeDomain = $this->createMock(DomainInterface::class);
        $fakeDomain
            ->method('toArray')
            ->willReturn(\is_array($expression) ? $expression : (array) $expression)
        ;

        return $fakeDomain;
    }
}
