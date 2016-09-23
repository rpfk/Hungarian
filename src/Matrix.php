<?php
namespace RPFK\Matrix;


class Matrix
{
    protected $array;

    protected $N;

    protected $M;

    public function getArray(): array
    {
        return $this->array;
    }

    public function setArray(array $array): Matrix
    {
        $this->array = $array;

        return $this;
    }

    public function getRow(integer $i): Line
    {
        if ($i >= $this->N) {
            throw new \Exception("Row $i does not exist");
        }

        return Line::createFromMatrix($this,'row',$i);
    }

    public function getRowArray(integer $i): array
    {
        if ($i >= $this->N) {
            throw new \Exception("Row $i does not exist");
        }

        return $this->array[$i];
    }

    public function getColumn(integer $j): Line
    {
        if ($j >= $this->M) {
            throw new \Exception("Column $j does not exist");
        }

        return Line::createFromMatrix($this,'column',$j);
    }

    public function getColumnArray(integer $j): array
    {
        if ($j >= $this->M) {
            throw new \Exception("Column $j does not exist");
        }

        return array_map(function ($row) use ($j) {
            return $row[$j];
        }, $this->array);
    }

    public function getCell(integer $row, integer $column): Cell
    {
        return Cell::createFromMatrix($this, $row, $column);
    }

    public function updateCell(Cell $cell): Matrix
    {
        $row = $cell->getRow();
        $column = $cell->getColumn();

        if ($row >= $this->N) {
            throw new \Exception("Row $row does not exist");
        }
        if ($column >= $this->M) {
            throw new \Exception("Column $column does not exist");
        }

        $this->array[$row][$column] = $cell->getValue();

        return $this;
    }

    public function updateLine(Line $line): Matrix
    {
        foreach($line->getCells() as $cell) {
            $this->updateCell($cell);
        }

        return $this;
    }

}