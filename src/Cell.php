<?php
namespace RPFK\Matrix;

class Cell
{
    protected $column;

    protected $row;

    protected $value;

    public function add(number $k): Cell
    {
        $this->value += $k;

        return $this;
    }

    public function divide(number $k): Cell
    {
        $this->value = $this->value / $k;

        return $this;
    }

    public function multiply(number $k): Cell
    {
        $this->value = $this->value * $k;

        return $this;
    }

    public function subtract(number $k): Cell
    {
        $this->value -= $k;

        return $this;
    }

    public function getColumn(): integer
    {
        return $this->column;
    }

    public function setColumn(integer $column): Cell
    {
        $this->column = $column;

        return $this;
    }

    public function getRow(): integer
    {
        return $this->row;
    }

    public function setRow(integer $row): Cell
    {
        $this->row = $row;

        return $this;
    }

    public function getValue(): number
    {
        return $this->value;
    }

    public function setValue($value): Cell
    {
        $this->value = $value;

        return $this;
    }

    public function __construct(number $value, integer $column = null, integer $row = null)
    {
        $this->column = $column;
        $this->row = $row;
        $this->value = $value;
    }

    public static function create(number $value): Cell
    {
        return new Cell($value);
    }

    public static function createFromLine(Line $line, integer $i): Cell
    {
        $value = $line->getCellValue($i);

        switch ($line->getType()) {
            case 'row':
                $row = $line->getNumber();
                $column = $i;
                break;
            case 'column':
                $column = $line->getNumber();
                $row = $i;
                break;
            default:
                throw new \Exception("Line does not have a correct type");
        }

        return new Cell($value, $column, $row);
    }

    public static function createFromMatrix(Matrix $matrix, integer $row, integer $column): Cell
    {
        $value = $matrix->getCellValue($row,$column);

        return new Cell($value, $column, $row);
    }
}