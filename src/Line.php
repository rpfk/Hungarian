<?php
namespace RPFK\Matrix;

class Line
{
    protected $array;

    protected $N;

    protected $type;

    protected $number;

    public function add(number $k): Line
    {
        array_walk($this->array, function (&$cell) use ($k) {
            $cell += $k;
        });

        return $this;
    }

    public function addMax(): Line
    {
        return $this->add($this->maxValue());
    }

    public function addMin(): Line
    {
        return $this->add($this->minValue());
    }

    public function count(number $value): integer
    {
        return count($this->search($value));
    }

    public function countMax(): integer
    {
        return count($this->max());
    }

    public function countMin(): integer
    {
        return count($this->min());
    }

    public function divide(number $k): Line
    {
        array_walk($this->array, function (&$cell) use ($k) {
            $cell = $cell / $k;
        });

        return $this;
    }

    public function max(): array
    {
        $max = $this->maxValue();

        return $this->search($max);
    }

    public function maxValue(): number
    {
        return max($this->array);
    }

    public function min(): array
    {
        $min = $this->minValue();

        return $this->search($min);
    }

    public function minValue(): number
    {
        return min($this->array);
    }

    public function multiply(number $k): Line
    {
        array_walk($this->array, function (&$cell) use ($k) {
            $cell = $cell * $k;
        });

        return $this;
    }

    public function search(number $value): array
    {
        return array_map(function ($key) {
            return Cell::createFromLine($this, $key);
        }, array_keys($this->array, $value, true));
    }

    public function subtract(number $k): Line
    {
        array_walk($this->array, function (&$cell) use ($k) {
            $cell -= $k;
        });

        return $this;
    }

    public function subtractMax(): Line
    {
        return $this->subtract($this->maxValue());
    }

    public function subtractMin(): Line
    {
        return $this->subtract($this->minValue());
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function setArray(array $array): Line
    {
        $this->array = $array;

        return $this;
    }

    public function getN(): integer
    {
        return $this->N;
    }

    public function setN(integer $N): Line
    {
        $this->N = $N;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Line
    {
        $this->type = $type;

        return $this;
    }

    public function getNumber(): integer
    {
        return $this->number;
    }

    public function setNumber(integer $number): Line
    {
        $this->number = $number;

        return $this;
    }

    public function getCell(integer $i): Cell
    {
        if ($i >= $this->N) {
            throw new \Exception("Cell $i does not exist");
        }

        return Cell::createFromLine($this, $i);
    }

    public function getCells(): array
    {
        return array_map(function ($key) {
            return Cell::createFromLine($this, $key);
        }, array_keys($this->array));
    }

    public function getCellValue(integer $i): number
    {
        if ($i >= $this->N) {
            throw new \Exception("Cell $i does not exist");
        }

        return $this->array[$i];
    }

    public function updateCell(Cell $cell): Line
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        switch ($this->getType()) {
            case 'row':
                if ($row != $this->getNumber()) {
                    throw new \Exception("Row $row does not exist");
                }
                if ($column >= $this->N) {
                    throw new \Exception("Column $column does not exist");
                }

                $i = $column;

                break;

            case 'column':
                if ($column != $this->getNumber()) {
                    throw new \Exception("Column $column does not exist");
                }
                if ($row >= $this->N) {
                    throw new \Exception("Row $row does not exist");
                }

                $i = $row;

                break;

            default:
                throw new \Exception("Line does not have a correct type");
        }

        $this->array[$i] = $cell->getValue();

        return $this;
    }

    public function __construct(array $array, string $type = null, integer $number = null)
    {
        $this->array = $array;
        $this->N = count($array);
        $this->number = $number;
        $this->type = $type;
    }

    public static function create(array $array): Line
    {
        return new Line($array);
    }

    public static function createFromMatrix(Matrix $matrix, string $type, integer $number): Line
    {
        switch ($type) {
            case 'row':
                $value = $matrix->getRowArray($number);
                break;

            case 'column':
                $value = $matrix->getColumnArray($number);
                break;

            default:
                throw new \Exception("Line does not have a correct type");
        }

        return new Line($value, $type, $number);
    }
}