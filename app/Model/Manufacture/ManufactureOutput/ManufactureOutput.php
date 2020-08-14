<?php

namespace App\Model\Manufacture\ManufactureOutput;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\Manufacture\ManufactureMachine\ManufactureMachine;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\TransactionModel;

class ManufactureOutput extends TransactionModel
{
    public static $morphName = 'ManufactureOutput';

    public $timestamps = false;

    protected $connection = 'tenant';

    public static $alias = 'manufacture_output';

    protected $fillable = [
        'manufacture_process_id',
        'manufacture_input_id',
        'manufacture_process_name',
        'notes',
    ];

    public $defaultNumberPrefix = 'MO';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function finishedGoods()
    {
        return $this->hasMany(ManufactureOutputFinishedGood::class);
    }

    public function manufactureMachine()
    {
        return $this->belongsTo(ManufactureMachine::class);
    }

    public function manufactureProcess()
    {
        return $this->belongsTo(ManufactureProcess::class);
    }

    public function manufactureInput()
    {
        return $this->belongsTo(ManufactureInput::class);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
    }

    public static function create($data)
    {
        $input = ManufactureInput::findOrFail($data['manufacture_input_id']);
        $output = new self;
        $output->fill($data);
        $output->manufacture_machine_id = $input->manufacture_machine_id;
        $output->manufacture_machine_name = $input->manufacture_machine_name;

        $finishedGoods = self::mapFinishedGoods($data['finished_goods'] ?? []);

        $output->save();

        $output->finishedGoods()->saveMany($finishedGoods);

        $form = new Form;
        $form->approval_status = 1;
        $form->saveData($data, $output);

        foreach ($finishedGoods as $finishedGood) {
            $options = [];
            if ($finishedGood->expiry_date) {
                $options['expiry_date'] = $finishedGood->expiry_date;
            }
            if ($finishedGood->production_number) {
                $options['production_number'] = $finishedGood->production_number;
            }
            InventoryHelper::increase($form, $finishedGood->warehouse, $finishedGood->item, $finishedGood->quantity, $finishedGood->unit, $finishedGood->converter, $options);
        }

        return $output;
    }

    private static function mapFinishedGoods($finishedGoods)
    {
        return array_map(function ($finishedGood) {
            $outputFinishedGood = new ManufactureOutputFinishedGood;
            $outputFinishedGood->fill($finishedGood);

            return $outputFinishedGood;
        }, $finishedGoods);
    }
}
