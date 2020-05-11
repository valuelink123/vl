<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 08.07.2016
 * Time: 10:19
 */

namespace App\Services;
use Queue;
use Log;

class MultipleQueue
{
    const IMMEDIATELY = '';
    const DB_WRITE = "db-write";
    const ORDERS_GET = "orders-get";
    const SCHEDULE_GET = "shedule-get";

    public static function pushOn($queue, $job, $data = '')
    {
        tryagain:
        try {
            if (env('QUEUE_DRIVER') != 'sync') {
                $tubeName = env('BEANSTALKD_QUEUE') . "-" . $queue;
                Queue::pushOn($tubeName, $job, $data);
            } else
                Queue::push($job, $data);
        }
        catch (\Pheanstalk\Exception\ServerOutOfMemoryException $e)
        {
            Log::Info($e->getMessage());
            sleep(600);
            goto tryagain;
        }
    }


    public static function push($job,$data = "")
    {
        Queue::push( $job, $data);
    }


}