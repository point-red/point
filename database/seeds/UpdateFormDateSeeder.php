<?php

use Illuminate\Database\Seeder;

class UpdateFormDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salesVisitations = \App\Model\Plugin\PinPoint\SalesVisitation::with('form')->get();
        foreach ($salesVisitations as $salesVisitation) {
            if (date('H:i:s', strtotime($salesVisitation->form->date)) == '00:00:00') {
                $salesVisitation->form->update([
                    'date' => date('Y-m-d', strtotime($salesVisitation->form->date)) . ' ' . date( 'H:i:s', strtotime($salesVisitation->created_at))
                ]);
            }
        }
    }
}
