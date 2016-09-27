<?php
namespace RPFK\Hungarian;

class Hungarian
{
    public $allocation = [];

    public $allocated_columns = [];

    public $allocated_rows = [];

    public $matrix;

    protected $columns;

    protected $rows;

    public $covered_columns = [];

    public $covered_rows = [];

    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
        $this->rows = count($matrix);
        $this->column = count(reset($matrix));
    }

    public function getMatrix()
    {
        return $this->matrix;
    }

    public function covered()
    {
        $columns = $this->covered_columns;
        $row = $this->covered_rows;
        return compact('columns', 'row');
    }

    public function transpose()
    {
        return array_map(null, ...$this->matrix);
    }

    public function uncoveredMatrix(): array
    {
        $covered_rows = $this->covered_rows;
        $covered_columns = $this->covered_columns;

        $filtered = array_filter($this->matrix, function ($row) use ($covered_rows) {
            return !in_array($row, $covered_rows, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($filtered as $row => $cells) {
            $filtered[$row] = array_filter($cells, function ($column) use ($covered_columns) {
                return !in_array($column, $covered_columns, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $filtered;
    }

    public function unallocatedMatrix(): array
    {
        $allocated_rows = $this->allocated_rows;
        $allocated_columns = $this->allocated_columns;

        $filtered = array_filter($this->matrix, function ($row) use ($allocated_rows) {
            return !in_array($row, $allocated_rows, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($filtered as $row => $cells) {
            $filtered[$row] = array_filter($cells, function ($column) use ($allocated_columns) {
                return !in_array($column, $allocated_columns, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $filtered;
    }

    public function reduceRow()
    {
        foreach ($this->matrix as $row => $cells) {
            $min = min($cells);

            foreach ($cells as $column => $cell) {
                $this->matrix[$row][$column] -= $min;
            }
        }
    }

    public function reduceColumn()
    {
        foreach ($this->transpose() as $column => $cells) {
            $min = min($cells);

            foreach ($cells as $row => $cell) {
                $this->matrix[$row][$column] -= $min;
            }
        }
    }

    public function solve()
    {
        $this->reduceRow();
        $this->reduceColumn();

        while (true) {
            if ($this->hasOptimum()) {
                break;
            }
            $this->modifyCovered();
        }

        return $this;
    }

    public function hasOptimum(): bool
    {
        $this->covered_columns = [];
        $this->covered_rows = [];

        while (true) {

            if ($this->rows == count($this->covered_columns) + count($this->covered_rows)) {
                return true;
            }

            $filtered = $this->uncoveredMatrix();

            if (empty($filtered)) {
                break;
            };

            $zeros = [];
            $zeros_row = [];
            $zeros_column = [];

            foreach ($filtered as $row => $cells) {
                foreach ($cells as $column => $cell) {
                    if ($this->matrix[$row][$column] == 0) {
                        $zeros[$row][$column] = 1;
                    } else {
                        $zeros[$row][$column] = 0;
                    }
                }
            }

            foreach ($zeros as $row => $cells) {
                $zeros_row[$row] = array_sum($cells);
            }

            foreach (reset($zeros) as $column => $cell) {
                $zeros_column[$column] = array_sum(array_combine(array_keys($zeros),array_column($zeros, $column)));
            }

            if (max($zeros_column) == 0) {
                break;
            } elseif (
                max($zeros_column) > max($zeros_row) || (
                    max($zeros_column) == max($zeros_row) &&
                    count($zeros_column) > count($zeros_row)
                )
            ) {
                $this->covered_columns[] = array_search(max($zeros_column), $zeros_column, true);
            } else {
                $this->covered_rows[] = array_search(max($zeros_row), $zeros_row, true);
            }
        }

        return false;
    }

    public function modifyCovered()
    {
        $filtered = $this->uncoveredMatrix();

        $min = INF;

        foreach ($filtered as $row => $cells) {
            foreach ($cells as $column => $cell) {
                $min = ($cell < $min) ? $cell : $min;
            }
        }

        foreach ($filtered as $row => $cells) {
            foreach ($cells as $column => $cell) {
                $this->matrix[$row][$column] -= $min;
            }
        }

        foreach ($this->covered_rows as $row) {
            foreach ($this->covered_columns as $column) {
                $this->matrix[$row][$column] += $min;
            }
        }

    }

    public function allocate(int $row, int $column)
    {
        $this->allocation[$row] = $column;
        $this->allocated_columns[] = $column;
        $this->allocated_rows[] = $row;
        return $this;
    }

    public function allocation()
    {
        while (true) {

            $filtered = $this->unallocatedMatrix();

            if (empty($filtered)) {
                break;
            };

            $zeros = [];
            $zeros_row = [];
            $zeros_column = [];

            foreach ($filtered as $row => $cells) {
                foreach ($cells as $column => $cell) {
                    if ($this->matrix[$row][$column] == 0) {
                        $zeros[$row][$column] = 1;
                    } else {
                        $zeros[$row][$column] = 0;
                    }
                }
            }

            foreach ($zeros as $row => $cells) {
                $zeros_row[$row] = array_sum($cells);
            }

            foreach (reset($zeros) as $column => $cell) {
                $zeros_column[$column] = array_sum(array_combine(array_keys($zeros),array_column($zeros, $column)));
            }

            if (count(array_keys($zeros_row, 1, true)) > 0) {
                foreach (array_keys($zeros_row, 1, true) as $row) {
                    $column = array_search(1, $zeros[$row], true);
                    $this->allocate($row, $column);
                }
            } elseif (count(array_keys($zeros_column, 1, true)) > 0) {
                foreach (array_keys($zeros_column, 1, true) as $column) {
                    $row = array_search(1, array_combine(array_keys($zeros),array_column($zeros, $column)), true);
                    $this->allocate($row, $column);
                }
            } elseif (min($zeros_column) < min($zeros_row)) {
                $column = array_search(min($zeros_column), $zeros_column, true);
                $row = array_search(1, array_combine(array_keys($zeros),array_column($zeros, $column)), true);
                $this->allocate($row, $column);
            } else {
                $row = array_search(min($zeros_row), $zeros_row, true);
                $column = array_search(1, $zeros[$row], true);
                $this->allocate($row, $column);
            }
        }

        return $this->allocation;
    }
}