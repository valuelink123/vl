<?php namespace App\Models;

use App\Models\Traits\ExtendedMysqlQueries;
use Illuminate\Database\Eloquent\Model;

class GuCangSku extends Model {

    use  ExtendedMysqlQueries;
    public $timestamps = true;
    protected $guarded = [];

    public function setImageLinkAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['image_link'] = json_encode($value);
        }
	}
	
	public function getImageLinkAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setTaxInfoAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['tax_info'] = json_encode($value);
        }
	}
	
	public function getTaxInfoAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setExportCountryAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['export_country'] = json_encode($value);
        }
	}
	
	public function getExportCountryAttribute($value)
	{
		return json_decode($value, true);
	}

    public function setImportCountryAttribute($value)
	{
        if (is_array($value)) {
            $this->attributes['import_country'] = json_encode($value);
        }
	}
	
	public function getImportCountryAttribute($value)
	{
		return json_decode($value, true);
	}
}
