<?php

namespace App\Model\Manufacture\ManufactureInput;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\FormApproval;
use App\Model\Manufacture\ManufactureMachine\ManufactureMachine;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Manufacture\ManufactureOutput\ManufactureOutput;
use App\Model\TransactionModel;
use Carbon\Carbon;

class ManufactureInput extends TransactionModel
{
    public static $morphName = 'ManufactureInput';

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $fillable = [
    	'manufacture_machine_id',
        'manufacture_process_id',
        'manufacture_formula_id',
        'manufacture_machine_name',
        'manufacture_process_name',
        'manufacture_formula_name',
        'notes',
    ];

    public $defaultNumberPrefix = 'MI';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function rawMaterials()
    {
        return $this->hasMany(ManufactureInputRawMaterial::class);
    }

    public function finishGoods()
    {
        return $this->hasMany(ManufactureInputFinishGood::class);
    }

    public function manufactureMachine()
    {
        return $this->belongsTo(ManufactureMachine::class);
    }

    public function manufactureProcess()
    {
        return $this->belongsTo(ManufactureProcess::class);
    }

    public function manufactureFormula()
    {
        return $this->belongsTo(ManufactureFormula::class);
    }

    public function approvers()
    {
        return $this->hasManyThrough(FormApproval::class, Form::class, 'formable_id', 'form_id')->where('formable_type', self::$morphName);
    }

    public function outputProducts()
    {
        return $this->hasMany(ManufactureOutput::class)->active();
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public static function create($data)
    {
        $input = new self;
        $input->fill($data);

        $rawMaterials = self::mapRawMaterials($data['raw_materials'] ?? []);
        $finishGoods = self::mapFinishGoods($data['finish_goods'] ?? []);

        $input->save();

        $input->rawMaterials()->saveMany($rawMaterials);
        $input->finishGoods()->saveMany($finishGoods);

        $form = new Form;
        $form->approved = true;
        $form->saveData($data, $input);

        foreach ($data['raw_materials'] as $rawMaterial) {
            $item = $rawMaterial['item'];
            if ($item['require_production_number'] || $item['require_expiry_date']) {
                foreach ($rawMaterial['inventories'] as $inventory) {
                    if ($inventory['quantity'] !== null) {
                        $options = [];
                        if (array_key_exists('production_number', $inventory)) {
                            $options['production_number'] = $inventory['production_number'];
                        }
                        if (array_key_exists('expiry_date', $inventory)) {
                            $options['expiry_date'] = $inventory['expiry_date'];
                        }
                        InventoryHelper::decrease($form->id, $rawMaterial['warehouse_id'], $rawMaterial['item_id'], $inventory['quantity'], $options);
                    }
                }
            } else {
                InventoryHelper::decrease($form->id, $rawMaterial['warehouse_id'], $rawMaterial['item_id'], $rawMaterial['quantity']);
            }
        }

        return $input;
    }

    private static function mapRawMaterials($rawMaterials)
    {
        return array_map(function ($rawMaterial) {
            $inputRawMaterial = new ManufactureInputRawMaterial;
            $inputRawMaterial->fill($rawMaterial);

            return $inputRawMaterial;
        }, $rawMaterials);
    }

    private static function mapFinishGoods($finishGoods)
    {
        return array_map(function ($finishGood) {
            $inputFinishGood = new ManufactureInputFinishGood;
            $inputFinishGood->fill($finishGood);

            return $inputFinishGood;
        }, $finishGoods);
    }

    private function isNotReferenced()
    {
        if ($this->outputProducts->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by output product', $this->outputProducts);
        }
    }
}
