<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjectQuery\Tests;

use ObjectQuery\Exception\AliasAlreadyTakenInQueryContextException;
use ObjectQuery\Exception\IncompatibleCollectionException;
use ObjectQuery\Exception\InvalidAliasException;
use ObjectQuery\ObjectQuery;
use ObjectQuery\ObjectQueryContextEnvironment;
use ObjectQuery\ObjectQueryOrderEnum;

class QueryTest extends AbstractQueryTest
{
    public function testSimpleAlias(): void
    {
        $query = new ObjectQuery();
        $query->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->where(fn($person, ObjectQueryContextEnvironment $context) => $context->get('city')->name === 'Lyon');

        $this->assertCount(2, $query->select());
    }

    /**
     * TODO
     */
    public function testWrongAlias(): void
    {
        $query = new ObjectQuery();
        $query->from($this->cities, 'element')
            ->selectMany('persons', 'person')
            ->where(fn($city, ObjectQueryContextEnvironment $context) => $context->get('city')->name === 'Lyon');

        $this->expectException(InvalidAliasException::class);
        $this->expectExceptionMessage('Alias "city" is not defined in the context. Available alias are: person, element.');
        $query->select();
    }

    public function testAliasAlreadyInUse(): void
    {
        $this->expectException(AliasAlreadyTakenInQueryContextException::class);
        $this->expectExceptionMessage('Alias "__" is already taken in the query. You should choose another name for your alias.');

        $query = new ObjectQuery();
        $query
            ->from($this->cities, '__')
            ->selectMany('persons', '__');
    }

    public function testFromScalarCollection(): void
    {
        $query = new ObjectQuery();

        $this->expectException(IncompatibleCollectionException::class);
        $this->expectExceptionMessage('The given collection is incompatible with "from" because of the following reason: Mixed and scalar collections are not supported. Collection must only contain objects to be used by ObjectQuery.');
        $query
            ->from(self::NUMBERS);
    }

    public function testFromMixedCollection(): void
    {
        $query = new ObjectQuery();

        $this->expectException(IncompatibleCollectionException::class);
        $this->expectExceptionMessage('The given collection is incompatible with "from" because of the following reason: Mixed and scalar collections are not supported. Collection must only contain objects to be used by ObjectQuery.');
        $query
            ->from($this->cities + self::NUMBERS);
    }

    public function testSelectOnInitialQueryWithSubQueries(): void
    {
        $query = new ObjectQuery();
        $query
            ->from($this->cities)
            ->orderBy(ObjectQueryOrderEnum::Ascending, 'name')
            ->limit(1)
        ;

        $this->assertSame('Lyon', $query->selectOne('name'));

        $query
            ->selectMany('persons', '__')
        ;

        $query
            ->selectMany('children', '___')
            ->where(fn($child) => $child->age >= 30)
        ;

        $this->assertSame('Hubert, Bob', $query->concat(', ', 'name'));
    }

    public function testSelectOnInitialQueryWithSubQueriesAndIntermediateWhere(): void
    {
        $query = new ObjectQuery();
        $query
            ->from($this->cities)
            ->orderBy(ObjectQueryOrderEnum::Ascending, 'name')
            ->limit(1)
        ;

        $this->assertSame('Lyon', $query->selectOne('name'));

        $query
            ->selectMany('persons', '__')
            ->where(fn($person) => $person->height > 180)
        ;

        $query
            ->selectMany('children', '___')
            ->where(fn($child) => $child->age >= 30)
        ;

        $this->assertSame('Hubert', $query->selectOne('name'));
    }
}
