<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PpcProfile extends Model
{
    protected $guarded = [];

    const STATUS = [
        'enabled'=>'enabled',
        'paused'=>'paused',
        'archived'=>'archived',
    ];

    const AD_TYPE = [
        'SProducts'=>'Sponsored Products',
        'SDisplay'=>'Sponsored Display',
        'SBrands'=>'Sponsored Brands',
    ];


    const BIDDING = [
        'legacyForSales'=>'Dynamic bids - down only',
        'autoForSales'=>'Dynamic bids - up and down',
        'manual'=>'Fixed bid',
    ];

    const BIDOPTIMIZATION = [
        'clicks'=>'Optimize for page visits',
        'conversions'=>'Optimize for conversion',
        'reach'=>'Optimize for viewable impressions',
    ];

    const PREMIUMBIDADJUSTMENT = [
        '0'=>'No',
        '1'=>'Yes',
    ];


    const TACTIC = [
        'T00020'=>'Product',
        'T00030'=>'Audiences',
    ];

    const MATCHTYPE = [
        'exact'=>'Exact',
        'phrase'=>'Phrase',
        'broad'=>'Broad',
    ];

    const EXPRESSION = [
        'queryBroadMatches'=>'queryBroadMatches',
        'queryPhraseMatches'=>'queryPhraseMatches',
        'queryExactMatches'=>'queryExactMatches',
        'asinCategorySameAs'=>'asinCategorySameAs',
        'asinBrandSameAs'=>'asinBrandSameAs',
        'asinPriceLessThan'=>'asinPriceLessThan',
        'asinPriceBetween'=>'asinPriceBetween',
        'asinPriceGreaterThan'=>'asinPriceGreaterThan',
        'asinReviewRatingLessThan'=>'asinReviewRatingLessThan',
        'asinReviewRatingBetween'=>'asinReviewRatingBetween',
        'asinReviewRatingGreaterThan'=>'asinReviewRatingGreaterThan',
        'asinSameAs'=>'asinSameAs',
        'queryBroadRelMatches'=>'queryBroadRelMatches',
        'queryHighRelMatches'=>'queryHighRelMatches',
        'asinSubstituteRelated'=>'asinSubstituteRelated',
        'asinAccessoryRelated'=>'asinAccessoryRelated',
        'asinAgeRangeSameAs'=>'asinAgeRangeSameAs',
        'asinGenreSameAs'=>'asinGenreSameAs',
        'asinIsPrimeShippingEligible'=>'asinIsPrimeShippingEligible',
    ];


    
    const SDISPLAYEXPRESSION = [
        'asinCategorySameAs'=>'asinCategorySameAs',
        'asinBrandSameAs'=>'asinBrandSameAs',
        'asinPriceLessThan'=>'asinPriceLessThan',
        'asinPriceBetween'=>'asinPriceBetween',
        'asinPriceGreaterThan'=>'asinPriceGreaterThan',
        'asinReviewRatingLessThan'=>'asinReviewRatingLessThan',
        'asinReviewRatingBetween'=>'asinReviewRatingBetween',
        'asinReviewRatingGreaterThan'=>'asinReviewRatingGreaterThan',
        'asinSameAs'=>'asinSameAs',
        'asinAgeRangeSameAs'=>'asinAgeRangeSameAs',
        'asinGenreSameAs'=>'asinGenreSameAs',
        'asinIsPrimeShippingEligible'=>'asinIsPrimeShippingEligible',
    ];
    
}
