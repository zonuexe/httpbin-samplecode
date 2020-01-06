<?php

namespace zonuexe\Httpbin;

/**
 * A book.
 *
 * @property-read string $name 書名
 * @property-read array<string> 著者名
 * @property-read int $amount 数量
 */
final class Book
{
    /** @var string 書名 */
    private $name;

    /** @var array<string> 著者名 */
    private $authors;

    /** @var int */
    private $amount;

    private function __construct(string $name, array $authors, int $amount)
    {
        assert($amount >= 0);

        $this->name = $name;
        $this->authors = $authors;
        $this->amount = $amount;
    }

    /**
     * @param array{name:string,authors:array<string>,int:amount} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['authors'], $data['amount']);
    }
}
