<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
	/**
	 * Display the specified resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function show()
    {
        return view('dashboards.show', [
        	'workspaces' => Auth::user()->workspacesOwned,
        ]);
    }
}
