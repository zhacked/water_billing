<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPageController extends Controller
{
    public function index()
    {
        $id = Auth::user()->id;

        $clients = User::with([
            'bills' => function ($q) {
                $q->where('is_paid', false);
            },
            'meter'
        ])->where('id', $id)->first();

        $bills = Bills::with(['meterReading'])->where('user_id', $id)->latest()->paginate(10);

        $totalPenalty = $bills->getCollection()->sum('penalty');
        $totalUnpaid = Bills::where('user_id', $id)
            ->where('is_paid', false)
            ->sum('amount_due');

        return view("pages.client_record.index", compact("clients", "bills", 'totalUnpaid', 'totalPenalty'));
    }
}
