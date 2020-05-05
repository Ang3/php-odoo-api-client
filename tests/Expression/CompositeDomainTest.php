<?php

namespace Ang3\Component\Odoo\Tests\Expression;

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
                $domainA->toArray(),
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
                $domainA->toArray(),
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
    protected function createFakeDomain($expression): DomainInterface
    {
        $fakeDomain = $this->createMock(DomainInterface::class);
        $fakeDomain
            ->method('toArray')
            ->willReturn(is_array($expression) ? $expression : (array) $expression);

        return $fakeDomain;
    }
}
