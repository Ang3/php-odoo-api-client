<?php

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\Expression\Operation;
use ReflectionException;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Expression\Operation
 */
class OperationTest extends AbstractDomainTest
{
    /**
     * @covers ::setType
     * @covers ::getType
     * @covers ::setId
     * @covers ::getId
     * @covers ::setData
     * @covers ::getData
     *
     * @throws ReflectionException
     */
    public function testAccessorsAndMutators(): void
    {
        $operation = new Operation(Operation::CREATE, null, ['foo' => 'bar']);

        $this
            ->createObjectTester($operation)
            ->assertPropertyAccessorsAndMutators('type', Operation::UPDATE)
            ->assertPropertyAccessorsAndMutators('id', 5)
            ->assertPropertyAccessorsAndMutators('data', ['bar'])
        ;
    }

    /**
     * Data provider for the test for methods ::toArray() / ::getCommand().
     */
    public function provideToArrayDataSet(): array
    {
        return [
            // #0
            [Operation::CREATE, null, ['foo' => 'bar'], [0, 0, ['foo' => 'bar']]],
            // #1
            [Operation::UPDATE, 1337, ['foo' => 'bar'], [1, 1337, ['foo' => 'bar']]],
            // #2
            [Operation::DELETE, 0, [1337], [2, 0, [1337]]],
            // #3
            [Operation::REMOVE, 0, [1337], [3, 0, [1337]]],
            // #4
            [Operation::ADD, 0, [1337], [4, 0, [1337]]],
            // #5
            [Operation::CLEAR, 0, null, [5, 0, 0]],
            // #5
            [Operation::REPLACE, 0, [1337], [6, 0, [1337]]],
        ];
    }

    /**
     * @covers ::getCommand
     * @covers ::toArray
     *
     * @dataProvider provideToArrayDataSet
     *
     * @throws ReflectionException
     */
    public function testGetCommand(int $type, int $id = null, array $data = null, array $result = []): void
    {
        $operation = new Operation($type, $id, $data);

        $this->assertEquals($result, $operation->toArray());
        $this->assertEquals($result, $operation->getCommand());
    }
}
