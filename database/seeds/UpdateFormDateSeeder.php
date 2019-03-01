<?php

use App\Model\Master\Address;
use App\Model\Master\Phone;
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
//        $salesVisitations = \App\Model\Plugin\PinPoint\SalesVisitation::with('form')->get();
//        foreach ($salesVisitations as $salesVisitation) {
//            if (date('H:i:s', strtotime($salesVisitation->form->date)) == '00:00:00') {
//                $salesVisitation->form->update([
//                    'date' => date('Y-m-d', strtotime($salesVisitation->form->date)) . ' ' . date( 'H:i:s', strtotime($salesVisitation->created_at))
//                ]);
//            }
//        }

        foreach (\App\Model\Master\Customer::all() as $customer) {
            $sv = \App\Model\Plugin\PinPoint\SalesVisitation::where('customer_id', $customer->id)->first();

            if ($sv) {
                $address = new Address;
                $address->address = $sv->address;
                $address->addressable_type = \App\Model\Master\Customer::class;
                $address->addressable_id = $customer->id;
                $address->save();

                $phone = new Phone();
                $phone->number = $sv->phone;
                $phone->phoneable_type = \App\Model\Master\Customer::class;
                $phone->phoneable_id = $customer->id;
                $phone->save();
            }
        }
    }
}
