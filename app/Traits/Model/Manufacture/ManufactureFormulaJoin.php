<?php

namespace App\Traits\Model\Manufacture;

use App\Model\Form;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormulaFinishedGood;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormulaRawMaterial;
use App\Model\Master\Item;

trait ManufactureFormulaJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', ManufactureFormula::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', ManufactureFormula::$morphName);
            });
        }

        if (in_array('raw_materials', $joins)) {
            $query = $query->leftjoin(ManufactureFormulaRawMaterial::getTableName().' as '.ManufactureFormulaRawMaterial::$alias,
                ManufactureFormulaRawMaterial::$alias.'.manufacture_formula_id', '=', ManufactureFormula::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as raw_material_'.Item::$alias,
                    'raw_material_'.Item::$alias.'.id', '=', ManufactureFormulaRawMaterial::$alias.'.item_id');
            }
        }

        if (in_array('finished_goods', $joins)) {
            $query = $query->leftjoin(ManufactureFormulaFinishedGood::getTableName().' as '.ManufactureFormulaFinishedGood::$alias,
                ManufactureFormulaFinishedGood::$alias.'.manufacture_formula_id', '=', ManufactureFormula::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as finished_goods_'.Item::$alias,
                    'finished_goods_'.Item::$alias.'.id', '=', ManufactureFormulaFinishedGood::$alias.'.item_id');
            }
        }

        return $query;
    }
}
