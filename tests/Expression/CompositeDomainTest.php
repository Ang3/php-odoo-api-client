<?php

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\Expression\Comparison;
use Ang3\Component\Odoo\Expression\CompositeDomain;
use Ang3\Component\Odoo\Expression\DomainInterface;
use ReflectionException;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Expression\CompositeDomain
 */
class CompositeDomainTest extends AbstractDomainTest
{
    /**
     * @covers ::setOperation
     * @covers ::getOperator
     * @covers ::add
     * @covers ::remove
     * @covers ::has
     *
     * @throws ReflectionException
     */
    public function testAccessorsAndMutators(): void
    {
        $comparison = new CompositeDomain(CompositeDomain::AND, []);
        $fakeDomain = $this->createMock(DomainInterface::class);

        $this
            ->createObjectTester($comparison)
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
        $comparisonA = $this->createFakeComparison('A');
        $comparisonB = $this->createFakeComparison('B');
        $comparisonC = $this->createFakeComparison('C');

        $data = [
            // [ <operator>, <domains>, <expected_result> ],
            [ // 0
                CompositeDomain::AND, [], [],
            ],
            [ // 1
                CompositeDomain::AND, [$comparisonA],
                $comparisonA->toArray(),
            ],
            [ // 2
                CompositeDomain::AND, [$comparisonA, $comparisonB],
                ['&', $comparisonA->toArray(), $comparisonB->toArray()],
            ],
            [ // 3
                CompositeDomain::AND, [$comparisonA, $comparisonB, $comparisonC],
                ['&', $comparisonA->toArray(), '&', $comparisonB->toArray(), $comparisonC->toArray()],
            ],
            [ // 4
                CompositeDomain::OR, [], [],
            ],
            [ // 5
                CompositeDomain::OR, [$comparisonA],
                $comparisonA->toArray(),
            ],
            [ // 6
                CompositeDomain::OR, [$comparisonA, $comparisonB],
                ['|', $comparisonA->toArray(), $comparisonB->toArray()],
            ],
            [ // 7
                CompositeDomain::OR, [$comparisonA, $comparisonB, $comparisonC],
                ['|', $comparisonA->toArray(), '|', $comparisonB->toArray(), $comparisonC->toArray()],
            ],
            [ // 8
                CompositeDomain::NOT, [], [],
            ],
            [ // 9
                CompositeDomain::NOT, [$comparisonA],
                ['!', $comparisonA->toArray()],
            ],
            [ // 10
                CompositeDomain::NOT, [$comparisonA, $comparisonB],
                ['!', '&', $comparisonA->toArray(), $comparisonB->toArray()],
            ],
            [ // 11
                CompositeDomain::NOT, [$comparisonA, $comparisonB, $comparisonC],
                ['!', '&', $comparisonA->toArray(), '&', $comparisonB->toArray(), $comparisonC->toArray()],
            ],
        ];

        /**
         * @see https://www.odoo.com/fr_FR/forum/aide-1/question/domain-notation-using-multiple-and-nested-and-2170
         */
        $orXA = $this->createFakeCompositeDomain(['|', ['A'], ['B']]);
        $orXB = $this->createFakeCompositeDomain(['|', ['C'], '|', ['D'], ['E']]);
        $expectedResult = ['&', '|', ['A'], ['B'], '|', ['C'], '|', ['D'], ['E']];
        $data[] = [CompositeDomain::AND, [$orXA, $orXB], $expectedResult];

        // Final test
        $orXA = $this->createFakeCompositeDomain(['|', ['A'], ['B']]);
        $orXB = $this->createFakeCompositeDomain(['|', ['C'], '|', ['D'], ['E']]);
        $orXC = $this->createFakeCompositeDomain(['|', ['F'], '|', ['G'], ['H']]);
        $expectedResult = ['&', '|', ['A'], ['B'], '&', '|', ['C'], '|', ['D'], ['E'], '|', ['F'], '|', ['G'], ['H']];
        $data[] = [CompositeDomain::AND, [$orXA, $orXB, $orXC], $expectedResult];

        return $data;
    }

    /**
     * @covers ::toArray
     *
     * @dataProvider provideToArrayDataSet
     *
     * @param mixed $expectedResult
     */
    public function testToArray(string $operator, array $domains = [], $expectedResult = null, string $message = ''): void
    {
        $domain = new CompositeDomain($operator, $domains);

        $this->assertEquals($expectedResult, $domain->toArray(), $message);
    }

    /**
     * @param mixed $expression
     */
    protected function createFakeCompositeDomain($expression, ?DomainInterface $normalization = null): CompositeDomain
    {
        $compositeDomain = $this->createMock(CompositeDomain::class);
        $compositeDomain
            ->method('toArray')
            ->willReturn(is_array($expression) ? $expression : (array) $expression);

        $normalization = $normalization ?: $compositeDomain;
        $compositeDomain
            ->method('normalize')
            ->willReturn($normalization);

        return $compositeDomain;
    }

    /**
     * @param mixed $expression
     */
    protected function createFakeComparison($expression): Comparison
    {
        $comparison = $this->createMock(Comparison::class);
        $comparison
            ->method('toArray')
            ->willReturn(is_array($expression) ? $expression : (array) $expression);

        return $comparison;
    }
}
