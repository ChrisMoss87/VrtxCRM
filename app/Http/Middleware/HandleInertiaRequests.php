<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $quoteParts = str(Inspiring::quotes()->random())->explode('-');
        $message = mb_trim($quoteParts[0] ?? '');
        $author = mb_trim($quoteParts[1] ?? 'Unknown');

        // Get modules for navigation (only if user is authenticated and tenant is initialized)
        $modules = [];
        if ($request->user() && tenancy()->initialized) {
            // Only select needed columns to reduce query overhead
            $modules = ModuleModel::select('id', 'name', 'api_name', 'icon')
                ->orderBy('name')
                ->get()
                ->map(fn ($module) => [
                    'id' => $module->id,
                    'name' => $module->name,
                    'api_name' => $module->api_name,
                    'icon' => $module->icon,
                ])
                ->toArray();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => $message, 'author' => $author],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'modules' => $modules,
        ];
    }
}
