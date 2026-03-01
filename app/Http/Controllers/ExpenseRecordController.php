<?php

namespace App\Http\Controllers;

class ExpenseRecordController extends Controller
{
    public function choose()
    {
        return view('expenses.choose');
    }
}
