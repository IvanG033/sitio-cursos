<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $students = User::where('role', User::ROLE_STUDENT);

        return view('admin.dashboard', [
            'pendingCount' => (clone $students)->where('status', User::STATUS_PENDING)->count(),
            'approvedCount' => (clone $students)->where('status', User::STATUS_APPROVED)->count(),
            'blockedCount' => (clone $students)->where('status', User::STATUS_BLOCKED)->count(),
            'moduleCount' => Module::count(),
            'completions' => ModuleProgress::whereNotNull('completed_at')->count(),
            'pending' => (clone $students)->where('status', User::STATUS_PENDING)->latest()->take(8)->get(),
        ]);
    }
}
