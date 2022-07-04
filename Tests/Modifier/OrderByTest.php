<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjectQuery\Tests\Modifier;

use ObjectQuery\Exception\InvalidModifierConfigurationException;
use ObjectQuery\ObjectQuery;
use ObjectQuery\ObjectQueryOrderEnum;
use ObjectQuery\Tests\AbstractQueryTest;

class OrderByTest extends AbstractQueryTest
{
    public function testObjectsAscendingOrderBy(): void
    {
        $query = new ObjectQuery();
        $query->from($this->cities)
            ->orderBy(ObjectQueryOrderEnum::Ascending, 'minimalAge');

        $result = $query->select();
        $this->assertSame('Paris', $result[0]->name);
        $this->assertSame('Lyon', $result[1]->name);
    }

    public function testObjectsDescendingOrderBy(): void
    {
        $query = new ObjectQuery();
        $query->from($this->cities)
            ->orderBy(ObjectQueryOrderEnum::Descending, 'minimalAge');

        $result = $query->select();
        $this->assertSame('Lyon', $result[0]->name);
        $this->assertSame('Paris', $result[1]->name);
    }

    public function testObjectsShuffleWithOrderFieldFailure(): void
    {
        $query = new ObjectQuery();
        $query->from($this->cities)
            ->orderBy(ObjectQueryOrderEnum::Shuffle, 'minimalAge');

        $this->expectException(InvalidModifierConfigurationException::class);
        $this->expectExceptionMessage('The modifier "orderBy" is wrongly configured: An order field must not be provided when shuffling a collection.');
        $query->select();
    }

    public function testObjectsShuffle(): void
    {
        $query = (new ObjectQuery())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child')
            ->orderBy(ObjectQueryOrderEnum::Shuffle);

        $firstShuffle = $query->concat(', ', 'name');
        $secondShuffle = $query->concat(', ', 'name');

        $this->assertNotSame($firstShuffle, $secondShuffle);
    }
}
