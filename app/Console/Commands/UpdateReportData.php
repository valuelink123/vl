<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class UpdateReportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:reportData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected static $mailDriverChanged = false;
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
        DB::beginTransaction();
        try{
            $maxId = DB::table('sendbox_report')->max('id');
            $maxId = intval($maxId)?intval($maxId):0;
            $new = DB::statement("insert into sendbox_report select * from sendbox where sendbox.id>$maxId");
            $old =DB::statement("update sendbox_report,sendbox set 
            sendbox_report.from_address=sendbox.from_address,
            sendbox_report.to_address=sendbox.to_address,
            sendbox_report.date=sendbox.date,
            sendbox_report.user_id=sendbox.user_id,
            sendbox_report.status=sendbox.status
            where sendbox_report.id=sendbox.id and sendbox.id <=$maxId");

            $maxId = DB::table('inbox_report')->max('id');
            $maxId = intval($maxId)?intval($maxId):0;
            $new = DB::statement("insert into inbox_report select * from inbox where inbox.id>$maxId");
            $old =DB::statement("update inbox_report,inbox set 
            inbox_report.user_id=inbox.user_id,
            inbox_report.amazon_order_id=inbox.amazon_order_id,
            inbox_report.sku=inbox.sku,
            inbox_report.asin=inbox.asin,
            inbox_report.item_no=inbox.item_no
            where inbox_report.id=inbox.id and inbox.id <=$maxId");
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
        
    }
}