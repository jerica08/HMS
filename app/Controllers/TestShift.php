<?php

namespace App\Controllers;

class TestShift extends BaseController
{
    public function index()
    {
        log_message('debug', 'TestShift::index called successfully');
        return "Shift Management Test - Working!";
    }
}
