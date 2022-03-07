<?php
declare(strict_types=1);

namespace App\Model\WeatherConditions;

use ArrayAccess;

/**
 * @todo as option we can extend from illuminate/collections here
 */
class ItemList implements ArrayAccess
{
    public function __construct(protected array $items = [])
    {
    }

    public function add(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return array|Item[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
