<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\ConsentReadModel;
use App\Models\LicenseReadModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $consents = ConsentReadModel::paginate(10);
        $licenses = LicenseReadModel::paginate(10);

        return Inertia::render('compliance/Dashboard', [
            'consents' => $consents,
            'licenses' => $licenses,
        ]);
    }
}

