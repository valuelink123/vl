<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PpcProfile extends Model
{
    protected $connection = 'ppc';

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
}
