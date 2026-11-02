<?php

use yii\db\Migration;

/**
 * Loads the trade-in price list (TRADE_IN_PRICES.xlsx, Grade A prices, JOD).
 *
 * - Rows that appeared more than once in the sheet for the same model + storage
 *   are stored as a price range (price = lowest, price_max = highest).
 * - Phones that were already in the table are hidden (is_active = 0), not deleted,
 *   so nothing is lost. Re-enable them from the admin page if needed.
 */
class m261102_120100_seed_trade_in_prices extends Migration
{
    /** [brand, model name, storage, min price, max price] */
    private function rows()
    {
        return [
        ['Apple', 'iPhone 11', '128GB', 100, 100],
        ['Apple', 'iPhone 12', '128GB', 120, 120],
        ['Apple', 'iPhone 12 Pro', '128GB', 140, 140],
        ['Apple', 'iPhone 12 Pro', '256GB', 160, 160],
        ['Apple', 'iPhone 12 Pro Max', '128GB', 160, 160],
        ['Apple', 'iPhone 12 Pro Max', '256GB', 180, 180],
        ['Apple', 'iPhone 13', '128GB', 200, 200],
        ['Apple', 'iPhone 13', '256GB', 220, 220],
        ['Apple', 'iPhone 13 Pro', '128GB', 260, 260],
        ['Apple', 'iPhone 13 Pro', '256GB', 280, 280],
        ['Apple', 'iPhone 13 Pro Max', '128GB', 300, 300],
        ['Apple', 'iPhone 13 Pro Max', '256GB', 330, 330],
        ['Apple', 'iPhone 14', '128GB', 235, 235],
        ['Apple', 'iPhone 14', '256GB', 250, 250],
        ['Apple', 'iPhone 14 Plus', '128GB', 260, 260],
        ['Apple', 'iPhone 14 Plus', '256GB', 290, 290],
        ['Apple', 'iPhone 14 Pro', '128GB', 310, 310],
        ['Apple', 'iPhone 14 Pro', '256GB', 320, 320],
        ['Apple', 'iPhone 14 Pro Max', '128GB', 340, 340],
        ['Apple', 'iPhone 14 Pro Max', '256GB', 370, 370],
        ['Apple', 'iPhone 14 Pro Max', '512GB', 390, 390],
        ['Apple', 'iPhone 15', '128GB', 310, 310],
        ['Apple', 'iPhone 15', '256GB', 330, 330],
        ['Apple', 'iPhone 15 Plus', '128GB', 340, 340],
        ['Apple', 'iPhone 15 Plus', '256GB', 360, 360],
        ['Apple', 'iPhone 15 Pro', '128GB', 380, 380],
        ['Apple', 'iPhone 15 Pro', '256GB', 400, 400],
        ['Apple', 'iPhone 15 Pro', '512GB', 420, 420],
        ['Apple', 'iPhone 15 Pro Max', '256GB', 450, 450],
        ['Apple', 'iPhone 15 Pro Max', '512GB', 470, 470],
        ['Apple', 'iPhone 15 Pro Max', '1TB', 500, 500],
        ['Apple', 'iPhone 16', '128GB', 400, 400],
        ['Apple', 'iPhone 16', '256GB', 425, 425],
        ['Apple', 'iPhone 16 Plus', '128GB', 430, 430],
        ['Apple', 'iPhone 16 Plus', '256GB', 450, 450],
        ['Apple', 'iPhone 16 Pro', '128GB', 460, 460],
        ['Apple', 'iPhone 16 Pro', '256GB', 480, 480],
        ['Apple', 'iPhone 16 Pro', '512GB', 500, 500],
        ['Apple', 'iPhone 16 Pro Max', '256GB', 570, 570],
        ['Apple', 'iPhone 16 Pro Max', '512GB', 590, 590],
        ['Apple', 'iPhone 16 Pro Max', '1TB', 610, 610],
        ['Apple', 'iPhone 17 Pro Max', '256GB', 720, 760],
        ['Apple', 'iPhone 17 Pro Max', '512GB', 850, 880],
        ['Apple', 'iPhone 17 Pro Max', '1TB', 880, 930],
        ['Apple', 'iPhone 17 Pro', '256GB', 670, 720],
        ['Apple', 'iPhone 17 Pro', '512GB', 710, 740],
        ['Apple', 'iPhone 17 Pro', '1TB', 750, 780],
        ['Apple', 'iPhone 17', '256GB', 520, 570],
        ['Apple', 'iPhone 17 Air', '256GB', 450, 450],
        ['Apple', 'iPhone 18 Pro Max', '256GB', 900, 950],
        ['Apple', 'iPhone 18 Pro Max', '512GB', 1000, 1050],
        ['Apple', 'iPhone 18 Pro Max', '1TB', 1050, 1100],
        ['Apple', 'iPhone 18 Pro Max', '2TB', 1150, 1200],
        ['Apple', 'iPhone 18 Pro', '256GB', 830, 860],
        ['Apple', 'iPhone 18 Pro', '512GB', 860, 880],
        ['Apple', 'iPhone 18 Pro', '1TB', 900, 920],
        ['Apple', 'iPhone 18 Pro', '2TB', 950, 1000],
        ['Apple', 'iPhone Duo', '256GB', 1300, 1300],
        ['Apple', 'iPhone Duo', '512GB', 1400, 1400],
        ['Apple', 'iPhone Duo', '1TB', 1400, 1400],
        ['Apple', 'iPhone Duo', '2TB', 1500, 1500],
        ['Samsung', 'Galaxy S21+', null, 80, 80],
        ['Samsung', 'Galaxy S21 Ultra', '256GB', 120, 120],
        ['Samsung', 'Galaxy S22', null, 100, 100],
        ['Samsung', 'Galaxy S22+', null, 120, 120],
        ['Samsung', 'Galaxy S22 Ultra', '256GB', 200, 200],
        ['Samsung', 'Galaxy S22 Ultra', '512GB', 220, 220],
        ['Samsung', 'Galaxy S23', null, 130, 130],
        ['Samsung', 'Galaxy S23 FE', null, 120, 120],
        ['Samsung', 'Galaxy S23+', null, 150, 150],
        ['Samsung', 'Galaxy S23 Ultra', '512GB', 300, 300],
        ['Samsung', 'Galaxy S24', null, 170, 170],
        ['Samsung', 'Galaxy S24+', '256GB', 190, 190],
        ['Samsung', 'Galaxy S24+', '512GB', 200, 200],
        ['Samsung', 'Galaxy S24 Ultra', '256GB', 330, 330],
        ['Samsung', 'Galaxy S24 Ultra', '1TB', 350, 350],
        ['Samsung', 'Galaxy S24 FE', null, 170, 170],
        ['Samsung', 'Galaxy S25', null, 350, 350],
        ['Samsung', 'Galaxy S25 FE', null, 280, 280],
        ['Samsung', 'Galaxy S25 Ultra', null, 450, 460],
        ['Samsung', 'Galaxy S26', null, 430, 440],
        ['Samsung', 'Galaxy S26 FE', null, 380, 380],
        ['Samsung', 'Galaxy S26 Ultra', null, 800, 850],
        ['Samsung', 'Galaxy Z Flip4', null, 100, 100],
        ['Samsung', 'Galaxy Z Flip5', null, 170, 170],
        ['Samsung', 'Galaxy Z Flip6', null, 300, 300],
        ['Samsung', 'Galaxy Z Fold4', null, 220, 220],
        ['Samsung', 'Galaxy Z Fold5', null, 300, 300],
        ['Samsung', 'Galaxy Z Fold6', null, 400, 400],
        ];
    }

    private static function label($name, $storage)
    {
        return $storage ? $name . ' ' . $storage : $name;
    }

    public function safeUp()
    {
        $table = '{{%iphone_models}}';
        $this->update($table, ['is_active' => 0]);

        $data = [];
        foreach ($this->rows() as $i => [$brand, $name, $storage, $min, $max]) {
            $data[] = [
                self::label($name, $storage),
                $brand,
                $name,
                $storage,
                $min,
                $max > $min ? $max : null,
                1,
                ($i + 1) * 10,
            ];
        }
        $this->batchInsert(
            $table,
            ['model', 'brand', 'name', 'storage', 'price', 'price_max', 'is_active', 'sort_order'],
            $data
        );
    }

    public function safeDown()
    {
        $table = '{{%iphone_models}}';
        foreach ($this->rows() as [$brand, $name, $storage]) {
            $this->delete($table, [
                'brand' => $brand,
                'name' => $name,
                'storage' => $storage,
                'model' => self::label($name, $storage),
            ]);
        }
        $this->update($table, ['is_active' => 1]);
    }
}
