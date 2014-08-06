<?php

namespace classFacet
{
    use Pinq\ITraversable,
        Pinq\Traversable;

    class Facet
    {

        public $data; // Original data
        public $key; // the field to be grouped on
        public $type; // F: full string; S: start of a string; R: range;
        public $range; // Only valid if $type is not F

        public function __construct($d, $k, $t, $r = '')
        {
            $this->data = $d;
            $this->key = $k;
            $this->type = $t;
            $this->range = $r;
        }

        public function getFacet()
        {
            $filter = '';

            if ($this->type == 'F') // Full string 
            {
                $filter = $this->data
                        ->groupBy(function($row)
                        {
                            return $row[$this->key];
                        }
                        )
                        ->select(function(ITraversable $data)
                {
                    return ['key' => $data->first()[$this->key], 'count' => $data->count()];
                }
                        )
                ;
            }
            elseif ($this->type == "S") //Start of string
            {
                $filter = $this->data
                        ->groupBy(function($row)
                        {
                            return substr($row[$this->key], 0, $this->range);
                        })
                        ->select(function (ITraversable $data)
                {
                    return ['key' => substr($data->first()[$this->key], 0, $this->range) . '...', 'count' => $data->count()];
                });
            }
            elseif ($this->type == "R") // A value range
            {
                $filter = $this->data
                        ->groupBy(function($row)
                        {
                            return floor($row[$this->key] / $this->range) * $this->range;
                        })
                        ->select(function (ITraversable $data)
                {
                    return ['key' => $data->last()[$this->key], 'count' => $data->count()];
                });
            }

            return $filter;
        }

    }

}