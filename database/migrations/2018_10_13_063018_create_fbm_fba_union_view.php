<?php

use Illuminate\Database\Migrations\Migration;

class CreateFbmFbaUnionView extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('DROP VIEW IF EXISTS kms_stock');
        $this->statement('
CREATE VIEW kms_stock AS
SELECT
    seller_id,
    seller_name,
    seller_sku,
    asin,
	item_code,
	item_name,
	fbm_stock,
	fba_stock,
	fba_transfer
FROM
	fbm_stock
LEFT JOIN fba_stock  USING(item_code) WHERE item_code IS NOT NULL
UNION
SELECT
    seller_id,
	seller_name,
    seller_sku,
    asin,
	item_code,
	item_name,
	fbm_stock,
	fba_stock,
	fba_transfer
FROM
	fbm_stock
RIGHT JOIN fba_stock  USING(item_code) WHERE item_code IS NOT NULL
');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->statement('DROP VIEW IF EXISTS kms_stock');
    }
}
