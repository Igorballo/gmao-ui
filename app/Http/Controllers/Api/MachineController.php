<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MachineResource;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('machines.consulter') || $request->user()->can('pannes.creer'), 403);

        $machines = Machine::query()
            ->with(['pannes.intervention'])
            ->orderBy('nom')
            ->get();

        return MachineResource::collection($machines);
    }
}
