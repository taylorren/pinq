<?php

use Pinq\ITraversable,
    Pinq\Traversable;

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'pinqDemo.php';

$app = new Silex\Application();
$app['debug'] = true;

//Register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

// Register Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => 'rsywx_test',
        'user' => 'root',
        'password' => 'tr0210',
        'charset' => 'utf8',
        'host' => 'localhost',
    ),
));

$app->get('/', function() use ($app)
{
    return $app['twig']->render('index.html.twig');
});

$app->get('/demo1', function () use ($app)
{
    global $demo;
    $books = $demo->test1($app);
    $data = Traversable::from($books);

    //Apply first filter
    $filter1 = $data
            ->where(function($row)
            {
                return $row['price'] > 90 && $row['price'] < 99;
            })
            ->orderByDescending(function($row)
    {
        return $row['id'];
    });

    $filter2 = $filter1
            ->groupBy(function($row)
            {
                return $row['author'];
            })
            ->select(
            function(ITraversable $filter1)
    {
        return ['author' => $filter1->first()['author'], 'count' => $filter1->count()];
    }
    );

    //var_dump($filter2);
    //return $app['twig']->render('demo1.html.twig', array('books'=>$books));
    return $app['twig']->render('demo1.html.twig', array('orig' => $data, 'filter1' => $filter1, 'filter2'=>$filter2));
}
);

$demo = new pinqDemo\Demo($app);

$app->run();
