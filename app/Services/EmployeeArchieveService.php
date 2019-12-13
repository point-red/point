<?php
namespace App\Services;

use App\Model\HumanResource\Employee\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeArchieveService {

    public function create($employeeIds) {
        DB::connection('tenant')->beginTransaction();
        $archivedAt = date('Y-m-d H:i:s');
        $ids=array();
        foreach($employeeIds as $id) {
            $employee = Employee::findOrFail($id);
            if(!$employee) continue;
            $employee->archived_at=$archivedAt;
            $employee->save();   
            $ids[] = $id;
        }
        DB::connection('tenant')->commit();
        return $ids;
    }

    /**
     * Get all archive name
     * 
     * @return Array
     */
    public function findArchiveName() {
        DB::connection('tenant')->beginTransaction();
        
        $archiveDates = Employee::select(DB::raw('DATE(archived_at) as archive'))
                                ->whereNotNull('archived_at')
                                ->groupBy('archive')
                                ->get();
        if(empty($archiveDates)) {
            return array();
        }
        return array_values($archiveDates->pluck('archive')->toArray());
    }
}


