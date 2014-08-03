<?php

namespace pinqDemo
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
            elseif ($this->type = "R") // A value range
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

    class Demo
    {

        private $books = '';

        public function __construct($app)
        {
            $sql = 'select * from book_book order by id';
            $this->books = $app['db']->fetchAll($sql);
        }

        public function test1($app)
        {
            return $this->books;
        }

        public function test2($app, $data)
        {
            $facet = $this->getFacet($data);
            return $app['twig']->render('demo2.html.twig', array('facet' => $facet, 'data' => $data));
        }

        public function test3($app, $originalData, $key, $value)
        {
            $data = \Pinq\Traversable::from($originalData);
            $facet = $this->getFacet($data);

            $filter = null;

            if ($key == 'author')
            {
                $filter = $data
                        ->where(function($row) use ($value)
                        {
                            return $row['author'] == $value;
                        })
                        ->orderByAscending(function($row) use ($key)
                {
                    return $row['price'];
                })
                ;
            }
            elseif ($key == 'price')
            {
                $filter = $data
                        ->where(function($row) use ($value)
                        {
                            $lo = floor($value / 10) * 10;
                            $hi = $lo + 10;

                            return $row['price'] < $hi && $row['price'] >= $lo;
                        })
                        ->orderByAscending(function($row) use ($key)
                {
                    return $row['author'];
                })
                ;
            }
            else //$key==title
            {
                $filter = $data
                        ->where(function($row) use ($value)
                        {
                            return $value == substr($row['title'], 0, 6) . '...';
                        })
                        ->orderByAscending(function($row) use ($key)
                {
                    return $row['author'];
                })
                ;
            }

            return $app['twig']->render('demo2.html.twig', array('facet' => $facet, 'data' => $filter));
        }

        private function getFacet($originalData)
        {
            $facet = array();

            $data = \Pinq\Traversable::from($originalData);

            // 3 samples on constructing different Facet objects and return the facet
            $filter1 = new \pinqDemo\Facet($data, 'author', 'F');
            $filter2 = new \pinqDemo\Facet($data, 'title', 'S', 6);
            $filter3 = new \pinqDemo\Facet($data, 'price', 'R', 10);

            $facet[$filter1->key] = $filter1->getFacet();
            $facet[$filter2->key] = $filter2->getFacet();
            $facet[$filter3->key] = $filter3->getFacet();
            return $facet;
        }

    }

}
