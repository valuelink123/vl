<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use App\Product;
use App\Classes\amzScrape;
class ProductReview extends Model {

    use  ExtendedMysqlQueries;
    protected $guarded = [];
    public $timestamps = false;


    public static function fillReviewerIDFromAmazonSite($review_id,$reviewer_id_encrypt,$productId)
    {

        $urlconfig = getSiteUrl();
        $product =  Product::whereId($productId)->first();
        $siteurl = $urlconfig[$product->marketplaceid];
        unset($scrape);
        unset($page);
        $scrape = new amzScrape('47.88.102.116:8123', 'wangjianeng:38829362', false);
        $page = $scrape->get_reviews_customer_id($siteurl, $reviewer_id_encrypt);
        return ['id'=>$review_id,'error'=>$page['error'],'data'=>$page['reviewer_id']];
    }
}
