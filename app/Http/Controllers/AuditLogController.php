<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;

class AuditLogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $u = Audit::orderBy('id', 'desc')->paginate(15);
        $data['audits'] = $u;

        return view('audit-log.index', $data);
    }
}
