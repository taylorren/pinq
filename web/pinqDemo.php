<?php

namespace pinqDemo
{

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

    }

}