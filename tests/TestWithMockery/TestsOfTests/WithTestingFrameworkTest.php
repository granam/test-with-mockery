<?php declare(strict_types=1);

namespace Granam\Tests\TestWithMockery\TestsOfTests;

use Granam\TestWithMockery\Exceptions\MockingOfNonExistingMethod;
use Granam\TestWithMockery\TestWithMockery;
use Mockery\MockInterface;

class WithTestingFrameworkTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_mockery_expectations_now(): void
    {
        /** @var MockInterface|\DateTime $mock */
        $mock = $this->mockery(\DateTime::class);
        $mock->expects('getTimestamp')
            ->andReturn('bar');

        self::assertSame('bar', $mock->getTimestamp());
    }

    /**
     * @test
     */
    public function I_can_use_mockery_expectations_again(): void
    {
        /** @var MockInterface|\DateTime $mock */
        $mock = $this->mockery(\DateTime::class);
        $mock->expects('getTimestamp')
            ->andReturn('baz');

        self::assertSame('baz', $mock->getTimestamp());
    }

    /**
     * @test
     */
    public function I_can_mock_even_interface(): void
    {
        /** @var MockInterface|\IteratorAggregate $mock */
        $mock = $this->mockery(\IteratorAggregate::class);
        $mock->expects('getIterator')
            ->andReturn('bar');

        self::assertSame('bar', $mock->getIterator());
    }

    /**
     * @test
     */
    public function I_get_type_test(): void
    {
        $traversableTypeFromClassName = $this->type(\Traversable::class);
        $traversable = new \ArrayObject();
        self::assertInstanceOf(\Traversable::class, $traversable);
        self::assertTrue($traversableTypeFromClassName->match($traversable));

        $traversableTypeFromObject = $this->type($traversable);
        self::assertTrue($traversableTypeFromObject->match($traversable));

        $floatType = $this->type('float');
        $float = 1.2;
        self::assertTrue($floatType->match($float));
        $int = 1;
        self::assertFalse($floatType->match($int));

        $arrayType = $this->type([]);
        $array = [];
        self::assertTrue($arrayType->match($array));
    }

    /**
     * @test
     */
    public function I_can_get_sut_class_from_current_test(): void
    {
        self::assertSame(
            'Granam\TestWithMockery\TestsOfTests\WithTestingFramework',
            self::getSutClass()
        );
        self::assertSame(
            'Granam\TestWithMockery\TestsOfTests\WithTestingFramework',
            self::getSutClass(__CLASS__)
        );
        self::assertSame(
            'Foo',
            self::getSutClass('Foo')
        );
        self::assertSame(
            'Bar',
            self::getSutClass('Foo\Bar\Baz', '~^Foo\\\(.+)\\\Baz$~')
        );
    }

    /**
     * @test
     */
    public function I_can_get_sut_class_from_any_class(): void
    {
        self::assertSame(
            \DateTime::class,
            self::getSutClass(\DateTime::class)
        );
        self::assertSame(
            'DT',
            self::getSutClass(\DateTime::class, '~([A-Z])[a-z]+~')
        );
    }

    /**
     * @test
     */
    public function I_can_not_mock_non_existing_method_by_default(): void
    {
        $this->expectException(MockingOfNonExistingMethod::class);
        $this->expectExceptionMessageMatches('~resetGregorianCalendar~');
        $mock = $this->mockery(\DateTime::class);
        $mock->allows('resetGregorianCalendar');
    }

    /**
     * @test
     */
    public function I_can_mock_non_existing_method_if_desired(): void
    {
        $mock = $this->weakMockery(\DateTime::class);
        $mock->expects('resetGregorianCalendar')
            ->andReturn(0);
        /** @noinspection PhpUndefinedMethodInspection */
        self::assertSame(0, $mock->resetGregorianCalendar());
    }

    /**
     * @test
     * @depends I_can_mock_non_existing_method_if_desired
     */
    public function I_can_not_mock_non_existing_method_by_default_again(): void
    {
        $this->expectException(MockingOfNonExistingMethod::class);
        $this->expectExceptionMessageMatches('~resetGregorianCalendar~');
        $mock = $this->mockery(\DateTime::class);
        $mock->allows('resetGregorianCalendar');
    }

    /**
     * @test
     * @backupStaticAttributes
     */
    public function Custom_non_existing_method_check_is_still_needed()
    {
        \Mockery::setGenerator(\Mockery::getDefaultGenerator());
        self::assertIsNotCallable([\DateTime::class, 'resetGregorianCalendar'], 'Seems our "non-existing" method exists in fact');
        $mock = \Mockery::mock(\DateTime::class);
        $updatedMock = $mock->allows('resetGregorianCalendar');
        self::assertNotEmpty($updatedMock, 'Seems something changed - but without exception...?');
    }

}
