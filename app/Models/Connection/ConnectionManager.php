<?php

namespace Webmakerr\App\Models\Connection;

use Webmakerr\App\Models\Model;
use Webmakerr\Framework\Database\DBManager;
use Webmakerr\Database\Overrides\DbConnection;
use Webmakerr\Framework\Database\ConnectionResolver;

class ConnectionManager
{
    public static function connect(&$app)
    {

        $resolver = new ConnectionResolver([
            'mysql' => new DbConnection(
                $GLOBALS['wpdb'],
                $app->config->get('database')
            ),
            'sqlite' => new DbConnection(
                $GLOBALS['wpdb']
            ),
        ]);

        $resolver->setDefaultConnection('mysql');

        Model::setConnectionResolver($resolver);

        Model::setEventDispatcher($app['events']);

        $app->singleton('db', function ($app) use ($resolver) {
            return new DBManager($resolver);
        });
    }
}