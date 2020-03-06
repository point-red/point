<?php

namespace App\Model\Manufacture\ManufactureInput;

use App\Exceptions\IsReferencedException;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\Manufacture\ManufactureMachine\ManufactureMachine;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Manufacture\ManufactureOutput\ManufactureOutput;
use App\Model\TransactionModel;

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

    public function finishedGoods()
    {
        return $this->hasMany(ManufactureInputFinishedGood::class);
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
        $finishedGoods = self::mapFinishedGoods($data['finished_goods'] ?? []);

        $input->save();

        $input->rawMaterials()->saveMany($rawMaterials);
        $input->finishedGoods()->saveMany($finishedGoods);

        $form = new Form;
        $form->saveData($data, $input);

        foreach ($input->rawMaterials as $rawMaterial) {
            $options = [];
            if ($rawMaterial->expiry_date) {
                $options['expiry_date'] = $rawMaterial->expiry_date;
            }
            if ($rawMaterial->production_number) {
                $options['production_number'] = $rawMaterial->production_number;
            }
            $options['quantity_reference'] = $rawMaterial->quantity;
            $options['unit_reference'] = $rawMaterial->unit;
            $options['converter_reference'] = $rawMaterial->converter;
            InventoryHelper::decrease($input->form, $rawMaterial->warehouse, $rawMaterial->item, $rawMaterial->quantity, $rawMaterial->unit, $rawMaterial->converter, $options);
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

    private static function mapFinishedGoods($finishedGoods)
    {
        return array_map(function ($finishedGood) {
            $inputFinishedGood = new ManufactureInputFinishedGood;
            $inputFinishedGood->fill($finishedGood);

            return $inputFinishedGood;
        }, $finishedGoods);
    }

    private function isNotReferenced()
    {
        if ($this->outputProducts->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by output product', $this->outputProducts);
        }
    }
}
