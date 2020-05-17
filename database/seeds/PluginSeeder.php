<?php

use App\Model\Plugin;
use Illuminate\Database\Seeder;

class PluginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plugins = [];

        $plugin = [
            'name' => 'KPI',
            'description' => 'A Key Performance Indicator (KPI) is a measurable value that demonstrates how effectively a company is achieving key business objectives. Organizations use KPIs to evaluate their success at reaching targets.',
            'price' => 0,
            'is_monthly_price' => false,
            'price_per_user' => 15000,
            'is_monthly_price_per_user' => true,
            'is_active' => true,
        ];
        array_push($plugins, $plugin);

        $plugin = [
            'name' => 'PIN POINT',
            'description' => 'Manage your sales activity.',
            'price' => 0,
            'is_monthly_price' => false,
            'price_per_user' => 15000,
            'is_monthly_price_per_user' => true,
            'is_active' => false,
        ];
        array_push($plugins, $plugin);

        $plugin = [
            'name' => 'SCALE WEIGHT',
            'description' => 'Connect your scale weight system',
            'price' => 1000000,
            'is_monthly_price' => false,
            'price_per_user' => 0,
            'is_monthly_price_per_user' => false,
            'is_active' => false,
        ];
        array_push($plugins, $plugin);

        $plugin = [
            'name' => 'STOCK BOOKKEEPING',
            'description' => 'Manage your stock without headache',
            'price' => 0,
            'is_monthly_price' => false,
            'price_per_user' => 0,
            'is_monthly_price_per_user' => false,
            'is_active' => true,
        ];
        array_push($plugins, $plugin);

        $this->create($plugins);
    }

    private function create($plugins)
    {
        foreach ($plugins as $array) {
            if (!$this->isExists($array['name'])) {
                continue;
            }

            $plugin = new Plugin;
            $plugin->name = $array['name'];
            $plugin->description = $array['description'];
            $plugin->price = $array['price'];
            $plugin->is_monthly_price = $array['is_monthly_price'];
            $plugin->price_per_user = $array['price_per_user'];
            $plugin->is_monthly_price_per_user = $array['is_monthly_price_per_user'];
            $plugin->is_active = $array['is_active'];
            $plugin->save();
        }
    }

    private function isExists($name)
    {
        $plugin = Plugin::where('name', $name)->first();
        if ($plugin) {
            return false;
        }

        return true;
    }
}
