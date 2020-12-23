<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FbaManageInventory extends Model
{
    protected $connection = 'amazon';
    protected $table = 'fba_manage_inventory';
    const LISTINGEXISTS = [
        '0'=>'No',
        '1'=>'Yes',
    ];
}
