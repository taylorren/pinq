<?php

namespace pinqDemo
{
    include "classFacet.php";
    

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
            $filter1 = new \classFacet\Facet($data, 'author', 'F');
            $filter2 = new \classFacet\Facet($data, 'title', 'S', 6);
            $filter3 = new \classFacet\Facet($data, 'price', 'R', 10);

            $facet[$filter1->key] = $filter1->getFacet();
            $facet[$filter2->key] = $filter2->getFacet();
            $facet[$filter3->key] = $filter3->getFacet();
            return $facet;
        }

    }

}
