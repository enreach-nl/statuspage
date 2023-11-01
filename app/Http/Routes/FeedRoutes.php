<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

/**
 * This is the status page routes class.
 *
 * @author James Brooks <james@alt-three.com>
 */
class FeedRoutes
{
    /**
     * Defines if these routes are for the browser.
     *
     * @var bool
     */
    public static $browser = true;

    /**
     * Define the status page routes.
     *
     * @param \Illuminate\Contracts\Routing\Registrar $router
     *
     * @return void
     */
    public function map(Registrar $router)
    {
        $router->group([
            'middleware' => ['ready', 'localize'],
        ], function (Registrar $router) {

            // feed_rss_status

            $router->get('feed/rss/status', [
                'as'   => 'get:feed_rss_status',
                'uses' => 'FeedController@feedRssStatus',
            ]);
            $router->get('rss', [ // LEGACY
                'as'   => 'get:feed_rss_status_legacy',
                'uses' => 'FeedController@feedRssStatus',
            ]);
            $router->get('rss/index.php', [ // LEGACY
                'as'   => 'get:feed_rss_status_legacy2',
                'uses' => 'FeedController@feedRssStatus',
            ]);

            // feed_ical_maintenance

            $router->get('feed/ical/maintenance', [
                'as'   => 'get:feed_ical_maintenance',
                'uses' => 'FeedController@feedIcalMaintenance',
            ]);
            $router->get('rss/ical.php', [ // LEGACY
                'as'   => 'get:feed_ical_maintenance_legacy',
                'uses' => 'FeedController@feedIcalMaintenance',
            ]);

            // feed_json_status

            $router->get('feed/json/status', [
                'as'   => 'get:feed_json_status',
                'uses' => 'FeedController@feedJsonStatus',
            ]);
            $router->get('rss/json.php', [ // LEGACY
                'as'   => 'get:feed_json_status_legacy',
                'uses' => 'FeedController@feedJsonStatus',
            ]);

            // feed_rss_incidents

            $router->get('feed/rss/incidents', [
                'as'   => 'get:feed_rss_incidents',
                'uses' => 'FeedController@feedRssIncidents',
            ]);

        });
    }
}
