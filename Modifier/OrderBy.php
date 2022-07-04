<?php

/*
 * (c) Alexandre Daubois <alex.daubois@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ObjectQuery\Modifier;

use ObjectQuery\Exception\InvalidModifierConfigurationException;
use ObjectQuery\ObjectQuery;
use ObjectQuery\ObjectQueryContext;
use ObjectQuery\ObjectQueryOrderEnum;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class OrderBy extends AbstractModifier
{
    private readonly ObjectQueryOrderEnum $orderBy;
    private readonly ?string $orderField;

    protected PropertyAccessor $propertyAccessor;

    public function __construct(ObjectQuery $parentQuery, ObjectQueryOrderEnum $orderBy = ObjectQueryOrderEnum::None, ?string $orderField = null)
    {
        parent::__construct($parentQuery);

        $this->orderBy = $orderBy;
        $this->orderField = $orderField;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function apply(array $source, ObjectQueryContext $context): array
    {
        if (null !== $this->orderField && ObjectQueryOrderEnum::Shuffle === $this->orderBy) {
            throw new InvalidModifierConfigurationException('orderBy', 'An order field must not be provided when shuffling a collection');
        }

        if (ObjectQueryOrderEnum::Shuffle === $this->orderBy) {
            \shuffle($source);

            return $source;
        }

        if (ObjectQueryOrderEnum::None !== $this->orderBy) {
            if (null === $this->orderField) {
                throw new InvalidModifierConfigurationException('orderBy', 'An order field must be provided');
            }

            \usort($source, function ($elementA, $elementB): bool {
                return ObjectQueryOrderEnum::Descending === $this->orderBy
                    ? $this->propertyAccessor->getValue($elementA, $this->orderField) <=> $this->propertyAccessor->getValue($elementB, $this->orderField)
                    : $this->propertyAccessor->getValue($elementB, $this->orderField) <=> $this->propertyAccessor->getValue($elementA, $this->orderField)
                ;
            });

            return $source;
        }

        return $source;
    }
}
