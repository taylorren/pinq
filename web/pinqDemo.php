<?php

use Pinq\ITraversable,
    Pinq\Traversable;

namespace pinqDemo
{

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
                    return ['author' => $data->first()['author'], 'count' => $data->count()];
                }
                );
                
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
            return $app['twig']->render('demo2.html.twig', array('facet' => $facet));
        }

        private function getFacet($originalData)
        {
            $facet=array();
            
            $data=  \Pinq\Traversable::from($originalData);
                    
            $keys = ['author', 'price', 'page']; // Can be passed to this class from outside instead of fixed in a more flexible solution

            foreach ($keys as $key)
            {
                $funcName = '\pinqDemo\facet' . $key;

                if (function_exists($funcName))
                {
                    $filter=call_user_func($funcName, $data);
                    $facet[]=$filter;
                }
            }
            
            return $facet;
        }

    }

}