<?php
/**
 * Plugin Name: LaRuta Redis Object Cache
 * Plugin URI: https://github.com/blueoceanideas/lrt-redis-object-cache
 * Description: A modified version of pressjitsu/pj-object-cache-red
 *  and pantheon-systems/wp-redis and
 * Version: 1.0
 * Author: Blue Ocean Ideas, Pressjitsu, Inc. Pantheon, Josh Koenig, Matthew Boynes, Daniel Bachhuber, Alley Interactive
 */

if (defined('WP_CLI') && WP_CLI && !class_exists('WP_Redis_CLI_Command')) {
    require_once dirname(__FILE__) . '/cli.php';
}

/**
 * Get helpful details on the Redis connection. Used by the WP-CLI command.
 * @return array
 */
function wp_redis_get_info()
{
    global $wp_object_cache, $redis_server;

    if (!defined('WP_REDIS_OBJECT_CACHE') || !WP_REDIS_OBJECT_CACHE) {
        return new WP_Error('lrt-redis-object-cache', 'LaRuta Redis object-cache.php file is missing from the wp-content/ directory.');
    }

    if (!$wp_object_cache->is_redis_connected) {
        return new WP_Error('lrt-redis-object-cache', $wp_object_cache->missing_redis_message);
    }

    $info = $wp_object_cache->redis->info();
    $uptime_in_days = $info['uptime_in_days'];
    if (1 === $info['uptime_in_days']) {
        $uptime_in_days .= ' day';
    } else {
        $uptime_in_days .= ' days';
    }
    $database = !empty($redis_server['database']) ? $redis_server['database'] : 0;
    $key_count = 0;
    if (isset($info['db' . $database]) && preg_match('#keys=([\d]+)#', $info['db' . $database], $matches)) {
        $key_count = $matches[1];
    }
    return [
        'status' => 'connected',
        'used_memory' => $info['used_memory_human'],
        'uptime' => $uptime_in_days,
        'key_count' => $key_count,
        'instantaneous_ops' => $info['instantaneous_ops_per_sec'] . '/sec',
        'lifetime_hitrate' => round(($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100), 2) . '%',
        'redis_host' => $redis_server['host'],
        'redis_port' => !empty($redis_server['port']) ? $redis_server['port'] : 6379,
        'redis_auth' => !empty($redis_server['auth']) ? $redis_server['auth'] : '',
        'redis_database' => $database,
    ];
}