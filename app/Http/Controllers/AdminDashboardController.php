<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /** @var array<string, array{avatar: string, icon: string}> */
    private const MODULE_CARD_STYLE = [
        'movies' => ['avatar' => 'bg-primary-transparent', 'icon' => 'bx-movie'],
        'series' => ['avatar' => 'bg-danger-transparent', 'icon' => 'bx-list-ul'],
        'web_tv' => ['avatar' => 'bg-success-transparent', 'icon' => 'bx-tv'],
        'wallpapers' => ['avatar' => 'bg-info-transparent', 'icon' => 'bx-image'],
        'games' => ['avatar' => 'bg-warning-transparent', 'icon' => 'bx-joystick'],
        'software' => ['avatar' => 'bg-secondary-transparent', 'icon' => 'bx-code-alt'],
        'applications' => ['avatar' => 'bg-purple-transparent', 'icon' => 'bx-grid-alt'],
    ];

    /** @var array<string, array{border: string, fill: string}> */
    private const CHART_COLORS = [
        'movies' => ['border' => 'rgb(59, 130, 246)', 'fill' => 'rgba(59, 130, 246, 0.35)'],
        'series' => ['border' => 'rgb(239, 68, 68)', 'fill' => 'rgba(239, 68, 68, 0.35)'],
        'web_tv' => ['border' => 'rgb(34, 197, 94)', 'fill' => 'rgba(34, 197, 94, 0.35)'],
        'wallpapers' => ['border' => 'rgb(6, 182, 212)', 'fill' => 'rgba(6, 182, 212, 0.35)'],
        'games' => ['border' => 'rgb(249, 115, 22)', 'fill' => 'rgba(249, 115, 22, 0.35)'],
        'software' => ['border' => 'rgb(148, 163, 184)', 'fill' => 'rgba(148, 163, 184, 0.35)'],
        'applications' => ['border' => 'rgb(168, 85, 247)', 'fill' => 'rgba(168, 85, 247, 0.35)'],
    ];

    public function index(Request $request): View
    {
        $admin = Admin::findOrFail((int) $request->session()->get('admin_id'));
        $adminId = (int) $admin->getKey();
        $allowedKeys = array_values(array_intersect(
            array_keys(config('modules')),
            $admin->module_permissions ?? []
        ));
        $allowedModules = collect(config('modules'))->only($allowedKeys)->all();
        $contentModels = config('module_admin.content_models', []);

        $moduleCards = $this->buildModuleCards($adminId, $allowedKeys, $allowedModules, $contentModels);
        $totalItems = (int) collect($moduleCards)->sum('count');
        [$chartLabels, $chartRangeLabel, $chartDatasets] = $this->buildMonthlyChart(
            $adminId,
            $allowedKeys,
            $allowedModules,
            $contentModels
        );

        return view('admin_dashboard.index', [
            'panelAdmin' => $admin,
            'allowedModules' => $allowedModules,
            'moduleCards' => $moduleCards,
            'stats' => [
                'modules' => count($allowedModules),
                'total_items' => $totalItems,
            ],
            'chartLabels' => $chartLabels,
            'chartRangeLabel' => $chartRangeLabel,
            'chartDatasets' => $chartDatasets,
        ]);
    }

    public function module(Request $request, string $module): View|RedirectResponse
    {
        $admin = Admin::findOrFail((int) $request->session()->get('admin_id'));
        $modules = config('modules');
        abort_unless(array_key_exists($module, $modules), 404);
        abort_unless(in_array($module, $admin->module_permissions ?? [], true), 403);

        $crudRoutes = config('module_admin.routes', []);
        if (isset($crudRoutes[$module])) {
            return redirect()->route($crudRoutes[$module].'.index');
        }

        return view('admin_dashboard.module', [
            'panelAdmin' => $admin,
            'moduleKey' => $module,
            'moduleName' => $modules[$module],
        ]);
    }

    /**
     * @param  list<string>  $allowedKeys
     * @param  array<string, string>  $allowedModules
     * @param  array<string, class-string<Model>>  $contentModels
     * @return list<array{key: string, name: string, count: int, href: string, avatar_class: string, icon: string}>
     */
    private function buildModuleCards(int $adminId, array $allowedKeys, array $allowedModules, array $contentModels): array
    {
        $routes = config('module_admin.routes', []);
        $cards = [];

        foreach ($allowedKeys as $key) {
            if (! isset($allowedModules[$key])) {
                continue;
            }
            $count = 0;
            if (isset($contentModels[$key])) {
                /** @var class-string<Model> $class */
                $class = $contentModels[$key];
                $count = (int) $class::query()->where('created_by', $adminId)->count();
            }
            $crudBase = $routes[$key] ?? null;
            $href = $crudBase ? route($crudBase.'.index') : route('admin.modules.show', $key);
            $style = self::MODULE_CARD_STYLE[$key] ?? ['avatar' => 'bg-primary-transparent', 'icon' => 'bx-grid-alt'];
            $cards[] = [
                'key' => $key,
                'name' => $allowedModules[$key],
                'count' => $count,
                'href' => $href,
                'avatar_class' => $style['avatar'],
                'icon' => $style['icon'],
            ];
        }

        return $cards;
    }

    /**
     * @param  list<string>  $allowedKeys
     * @param  array<string, string>  $allowedModules
     * @param  array<string, class-string<Model>>  $contentModels
     * @return array{0: list<string>, 1: string, 2: list<array<string, mixed>>}
     */
    private function buildMonthlyChart(int $adminId, array $allowedKeys, array $allowedModules, array $contentModels): array
    {
        $labels = [];
        $monthBounds = [];

        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $labels[] = $start->format('M Y');
            $monthBounds[] = [$start, $end];
        }

        $rangeStart = Carbon::now()->subMonths(11)->startOfMonth();
        $rangeEnd = Carbon::now()->endOfMonth();
        $rangeLabel = $rangeStart->format('j M, Y').' – '.$rangeEnd->format('j M, Y');

        $datasets = [];
        foreach ($allowedKeys as $key) {
            if (! isset($allowedModules[$key], $contentModels[$key])) {
                continue;
            }
            /** @var class-string<Model> $class */
            $class = $contentModels[$key];
            $data = [];
            foreach ($monthBounds as [$start, $end]) {
                $data[] = (int) $class::query()
                    ->where('created_by', $adminId)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
            }
            $colors = self::CHART_COLORS[$key] ?? ['border' => 'rgb(99, 102, 241)', 'fill' => 'rgba(99, 102, 241, 0.35)'];
            $datasets[] = [
                'label' => $allowedModules[$key],
                'data' => $data,
                'borderColor' => $colors['border'],
                'backgroundColor' => $colors['fill'],
                'fill' => true,
                'tension' => 0.35,
                'borderWidth' => 2,
            ];
        }

        return [$labels, $rangeLabel, $datasets];
    }
}
