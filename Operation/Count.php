<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjectQuery\Operation;

use ObjectQuery\ObjectQueryContext;

final class Count extends AbstractOperation
{
    public function apply(array $source, ObjectQueryContext $context): int
    {
        return \count($this->applySelect($source, $context));
    }
}
