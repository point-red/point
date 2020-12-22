<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Inventory\InventoryAuditJoin;
use App\Traits\Model\Inventory\InventoryAuditRelation;

class InventoryAudit extends TransactionModel
{
    use InventoryAuditJoin, InventoryAuditRelation;

    public static $morphName = 'InventoryAudit';

    protected $connection = 'tenant';

    public static $alias = 'inventory_audit';

    public $timestamps = false;

    public $defaultNumberPrefix = 'IA';

    public static function create($data)
    {
        $inventoryAudit = new self;
        $inventoryAudit->warehouse_id = $data['warehouse_id'];
        $inventoryAudit->save();

        $form = new Form;
        $form->saveData($data, $inventoryAudit);

        $items = $data['items'];
        if ($items) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['item_id'] && $items[$i]['quantity']) {
                    if (get_if_set($items[$i]['dna']) && $items[$i]['dna']) {
                        foreach ($items[$i]['dna'] as $dna) {
                            if ($dna['quantity'] > 0) {

                                $detail = new InventoryAuditItem;
                                $detail->inventory_audit_id = $inventoryAudit->id;
                                $detail->unit = $items[$i]['unit'];
                                $detail->converter = $items[$i]['converter'];
                                $detail->quantity = $dna['quantity'];
                                $detail->production_number = $dna['production_number'];
                                $detail->expiry_date = $dna['expiry_date'];
                                $detail->item_id = $items[$i]['item_id'];
                                $detail->save();

                                $options = [];
                                if ($detail->item->require_expiry_date) {
                                    $options['expiry_date'] = $detail->expiry_date;
                                }
                                if ($detail->item->require_production_number) {
                                    $options['production_number'] = $detail->production_number;
                                }
                
                                $options['quantity_reference'] = $detail->quantity;
                                $options['unit_reference'] = $detail->unit;
                                $options['converter_reference'] = $detail->converter;
                            }
                            
                        }
                    } else {
                        if ($items[$i]['quantity']) {
                            $detail = new InventoryAuditItem;
                            $detail->inventory_audit_id = $inventoryAudit->id;
                            $detail->unit = $items[$i]['unit'];
                            $detail->converter = $items[$i]['converter'];
                            $detail->quantity = $items[$i]['quantity'];
                            $detail->item_id = $items[$i]['item_id'];
                            $detail->save();
                        
                            $options = [];
                            if ($detail->item->require_expiry_date) {
                                $options['expiry_date'] = $detail->expiry_date;
                            }
                            if ($detail->item->require_production_number) {
                                $options['production_number'] = $detail->production_number;
                            }
            
                            $options['quantity_reference'] = $detail->quantity;
                            $options['unit_reference'] = $detail->unit;
                            $options['converter_reference'] = $detail->converter;
                        }
                    }
                }
            }
        }

        return $inventoryAudit;
    }

    private static function updateStock($inventoryAudit)
    {
        foreach ($inventoryAudit->items as $inventoryAuditItem) {
            InventoryHelper::audit(
                $inventoryAudit->form,
                $inventoryAudit->warehouse,
                $inventoryAuditItem->item,
                $inventoryAuditItem->quantity,
                $inventoryAuditItem->unit,
                $inventoryAuditItem->converter,
                [
                    'expiry_date' => $inventoryAuditItem->expiry_date,
                    'production_number' => $inventoryAuditItem->production_number,
                ]
            );
        }
    }
}
