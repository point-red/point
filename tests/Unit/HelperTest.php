<?php

namespace Tests\Unit;

use Tests\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function get_if_set_test()
    {
        $existsVariable = 'test';

        $this->assertIsString(get_if_set($existsVariable));

        $this->assertNull(get_if_set($notExistsVariable));
    }

    /** @test */
    public function capitalize_test()
    {
        $var = 'test';

        $capitalize = capitalize($var);

        $this->assertStringContainsString('Test', $capitalize);
    }

    /** @test */
    public function get_invitation_code_test()
    {
        $invitationCode = get_invitation_code();

        $this->assertIsString($invitationCode);
    }

    /** @test */
    public function date_tz_test()
    {
        /**
         * UTC = 0
         * Asia/Jakarta = +7.
         */
        $date = date_tz('01/10/2019 00:00:00', 'UTC', 'Asia/Jakarta');

        $this->assertStringContainsString('2019-01-10 07:00:00', $date);

        $date = date_tz('01/10/2019 00:00:00');

        $this->assertStringContainsString('2019-01-10 00:00:00', $date);
    }

    /** @test */
    public function str_clean_test()
    {
        $str = "  with  two  spaces  ";
        $expected = "with two spaces";

        $this->assertEquals($expected, str_clean($str));
    }
}
