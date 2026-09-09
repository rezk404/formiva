<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Insight;
use App\Models\Inquiry;
use App\Models\Project;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'projects' => Project::query()->count(),
                'publishedProjects' => Project::query()->published()->count(),
                'inquiries' => Inquiry::query()->count(),
                'newInquiries' => Inquiry::query()->where('status', InquiryStatus::New)->count(),
                'clients' => Client::query()->count(),
                'insights' => Insight::query()->count(),
            ],
        ]);
    }
}
