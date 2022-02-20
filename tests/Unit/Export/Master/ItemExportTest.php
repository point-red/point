<?php

namespace Tests\Unit\Export\Master;

use App\Exports\Master\ItemExport;
use Tests\TestCase;

class ItemExportTest extends TestCase
{
    /** @test */
    public function heading_test()
    {
        $itemExport  = new ItemExport();

        $actual = $itemExport->heading();

        $this->assertContains( 'Item Code', $actual);

    }

    /** @test */
    public function get_data_test()
    {
        $itemExport  = new ItemExport();

        $actual = $itemExport->getData();

        $this->assertSame([], $actual->toArray());
    }
}
