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
      $salesVisitations = \App\Model\Plugin\PinPoint\SalesVisitation::all();
      foreach ($salesVisitations as $salesVisitation) {
        $form = $salesVisitation->form()->first();
        $form->usesUserLogs(false); // set false, in order to not log updated_by
        $form->update([
           'date' => date( 'Y-m-d H:i:s', strtotime($salesVisitation->created_at.' + 7 hours') )
        ]);
      }
    }
}
