<?php

namespace App\Model\Manufacture\ManufactureOutput;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\FormApproval;
use App\Model\Manufacture\ManufactureMachine\ManufactureMachine;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\TransactionModel;
use Carbon\Carbon;

class ManufactureOutput extends TransactionModel
{
    public static $morphName = 'ManufactureOutput';

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $fillable = [
    	'manufacture_machine_id',
        'manufacture_input_id',
        'manufacture_machine_name',
        'notes',
    ];

    public $defaultNumberPrefix = 'MO';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function finishGoods()
    {
        return $this->hasMany(ManufactureOutputFinishGood::class);
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

    public function approvers()
    {
        return $this->hasManyThrough(FormApproval::class, Form::class, 'formable_id', 'form_id')->where('formable_type', self::$morphName);
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
        $output = new self;
        $output->fill($data);

        $finishGoods = self::mapFinishGoods($data['finish_goods'] ?? []);

        $output->save();

        $output->finishGoods()->saveMany($finishGoods);

        $form = new Form;
        $form->approved = true;
        $form->saveData($data, $output);

        foreach ($data['finish_goods'] as $finishGood) {
            $item = $finishGood['item'];
            if ($item['require_production_number'] || $item['require_expiry_date']) {
                $options = [];
                if (array_key_exists('production_number', $finishGood)) {
                    $options['production_number'] = $finishGood['production_number'];
                }
                if (array_key_exists('expiry_date', $finishGood)) {
                    $options['expiry_date'] = $finishGood['expiry_date'];
                }
                InventoryHelper::increase($form->id, $finishGood['warehouse_id'], $finishGood['item_id'], $finishGood['produced_quantity'], 0, $options);
            } else {
                InventoryHelper::increase($form->id, $finishGood['warehouse_id'], $finishGood['item_id'], $finishGood['produced_quantity'], 0);
            }
        }

        return $output;
    }

    private static function mapFinishGoods($finishGoods)
    {
        return array_map(function ($finishGood) {
            $outputFinishGood = new ManufactureOutputFinishGood;
            $outputFinishGood->fill($finishGood);
            $outputFinishGood->quantity = $finishGood['produced_quantity'];

            return $outputFinishGood;
        }, $finishGoods);
    }
}
