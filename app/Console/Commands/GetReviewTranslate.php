<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;
use Google\Cloud\Translate\V2\TranslateClient;

class GetReviewTranslate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:reviewTranslate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $translate = new TranslateClient([
            'key' => env('GOOGLE_TRANSLATION_KEY')
        ]);

        $date_from = '2020-06-01';
        $reviewIds = DB::table('review')->where('is_content_translated', '=', 0)->where('date', '>=', $date_from)->orderBy('date', 'desc')->limit(10)->pluck('id');
        foreach ($reviewIds as $v) {
            $review = DB::table('review')->where('id', '=', $v)->first();
            $review = json_decode(json_encode($review), true);
            $review_content = $review['review_content'];
            $result = $translate->translate($review_content,
                [
                    'target' => 'zh-CN'
                ]);
            $updateData = array();
            $updateData['is_content_translated'] = 1;
            $updateData['review_content_cn'] = $result['text'];

            DB::table('review')->where('id', '=', $v)->update($updateData);
        }

    }
}
