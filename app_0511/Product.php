<?php namespace App;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;
use App\Classes\amzScrape;
use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;

class Product extends Model {

    use  ExtendedMysqlQueries;
    protected $guarded = [];

    public static function fillDataFromAmazonSite($asin,$marketplaceid,$productId)
    {

        $urlconfig = getSiteUrl();
        $asins[]=$asin;
        $siteurl = $urlconfig[$marketplaceid];
        unset($scrape);
        unset($results);
        unset($data);
        $scrape = new amzScrape('47.88.102.116:8123', 'wangjianeng:38829362', false);
        $results = $scrape->scrape_products($asins, $siteurl);
        if(!sizeof($results)){
            return false;
        }
        if($results[0]['error']=="Product page does not exist"){
            return ['asin'=>$asin,'marketplaceid'=>$marketplaceid,'id'=>$productId,'not_found'=>true];
        }

        $data['title']= $results[0]['name'];
        $data['sellerCount']=intval($results[0]['seller_count']);
        $data['brand']=$results[0]['brand'];
        $data['star']=round($results[0]['product_rating'],1);
        $data['reviewCount']=intval(trim(str_replace(array('.',','),'',$results[0]['review_count'])));
        $data['fba']=$results[0]['fba'];
        $data['bulletPoints']=$results[0]['bullet_points'];
        $data['description']=$results[0]['description'];
        $data['price'] = round($results[0]['price_lowest']/100,2);
        $data['images'] = $results[0]['image_large'];
        $data['bsr'] = intval($results[0]['sales_rank']);
        $data['bsrGroup'] = $results[0]['product_group'];
        $data['category'] = $results[0]['product_group_hidden'];
        $data['bsrGroup'] = $results[0]['product_group'];


        return ['asin'=>$asin,'marketplaceid'=>$marketplaceid,'id'=>$productId,'not_found'=>false,'data'=>$data];
    }


    public static function fillReviewsFromAmazonSite($asin,$marketplaceid,$productId)
    {

        $urlconfig = getSiteUrl();
        $siteurl = $urlconfig[$marketplaceid];
        unset($scrape);
        unset($results);
        unset($data);
        $error = 0;
        $final = array();
        $scrape = new amzScrape('47.88.102.116:8123', 'wangjianeng:38829362', false);
        $page = $scrape->get_reviews($asin, $siteurl, 1);
        $one = $page['reviews'];
        if ($page['error'] != 0) $error = 1;
        sleep(rand(2, 6));
        $page = $scrape->get_reviews($asin, $siteurl, 2);
        $two = $page['reviews'];
        if ($page['error'] != 0) $error = 1;
        sleep(rand(2, 6));
        $page = $scrape->get_reviews($asin, $siteurl, 3);
        $three = $page['reviews'];
        if ($page['error'] != 0) $error = 1;


        $final = array_merge($one, $two, $three);

        return ['asin'=>$asin,'marketplaceid'=>$marketplaceid,'id'=>$productId,'error'=>$error,'data'=>$final];
    }

    public static function fillReviewerIDFromAmazonSite($asin,$marketplaceid,$productId)
    {

        $urlconfig = getSiteUrl();
        $siteurl = $urlconfig[$marketplaceid];
        unset($scrape);
        unset($results);
        unset($data);
        $scrape = new amzScrape('47.88.102.116:8123', 'wangjianeng:38829362', false);
        $page = $scrape->get_reviews($asin, $siteurl, 1);
        $one = $page['reviews'];

        sleep(rand(2, 6));
        $page = $scrape->get_reviews($asin, $siteurl, 2);
        $two = $page['reviews'];

        sleep(rand(2, 6));
        $page = $scrape->get_reviews($asin, $siteurl, 3);
        $three = $page['reviews'];

        $final = array_merge($one, $two, $three);
        if(sizeof($final)){
            $error = false;
        }else{
            $error = true;
        }
        return ['asin'=>$asin,'marketplaceid'=>$marketplaceid,'id'=>$productId,'error'=>$error,'data'=>$final];
    }
}
