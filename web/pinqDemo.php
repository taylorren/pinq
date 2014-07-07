<?php

namespace pinqDemo
{

    use Pinq\ITraversable,
        Pinq\Traversable;

    function facettitle($data)
    {
        $filter = $data
                ->groupBy(function($row)
                {
                    return substr($row['title'], 0, 6);
                })
                ->select(function(ITraversable $data)
        {
            return ['key' => substr($data->first()['title'], 0, 6) . '...', 'count' => $data->count()];
        });

        return $filter;
    }

    function facetauthor($data)
    {
        $filter = $data
                ->groupBy(function($row)
                {
                    return $row['author'];
                })
                ->select(
                        function(ITraversable $data)
                {
                    return ['key' => $data->first()['author'], 'count' => $data->count()];
                })
                ->orderByAscending(function($row)
        {
            return $row['key'];
        })
        ;

        return $filter;
    }

    function facetprice($data)
    {
        $filter = $data
                ->groupBy(function($row)
                {
                    return floor($row['price'] / 10) * 10;
                })
                ->select(
                        function(ITraversable $data)
                {
                    return ['key' => $data->last()['price'], 'count' => $data->count()];
                }
                )
                ->orderByAscending(function($row)
        {
            return $row['key'];
        })
        ;

        return $filter;
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
                            $lo=floor($value/10)*10;
                            $hi=$lo+10;
                            
                            return $row['price'] < $hi && $row['price']>=$lo;
                        })
                        ->orderByAscending(function($row) use ($key)
                {
                    return $row['title'];
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

            $keys = ['author', 'price', 'title']; // Can be passed to this class from outside instead of fixed in a more flexible solution

            foreach ($keys as $key)
            {
                $funcName = '\pinqDemo\facet' . $key;

                if (function_exists($funcName))
                {
                    $filter = call_user_func($funcName, $data);
                    $facet[$key] = $filter;
                }
            }

            return $facet;
        }

    }

}
