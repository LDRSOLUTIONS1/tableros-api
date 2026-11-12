<?php

namespace App\Http\Controllers;

use App\Models\PowerBiDashboardPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\ExcelSource;
use Illuminate\Validation\ValidationException;

class ExcelSourcePageController extends Controller
{
    public function index()
    {
        $paginas = PowerBiDashboardPage::with([
            'dashboard:id,nombre,descripcion',
            'excelSources:id,codigo,nombre',
        ])->select(
            'id',
            'power_bi_dashboard_id',
            'nombre',
            'descripcion',
            'orden',
            'estado',
            'created_at',
        )
            ->withCount('excelSources as total_excels')
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($paginas, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePages($request);

        $pagina = PowerBiDashboardPage::create($validated);

        return response()->json([
            'message' => 'Página creada correctamente',
            'data'    => $pagina
        ], 201);
    }


    public function show($id)
    {
        $pagina = PowerBiDashboardPage::with([
            'dashboard:id,nombre,descripcion',
        ])->select(
            'id',
            'power_bi_dashboard_id',
            'nombre',
            'descripcion',
            'orden',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($pagina, 200);
    }

    public function update(Request $request, $id)
    {
        $pagina = PowerBiDashboardPage::activos()
            ->findOrFail($id);

        $validated = $this->validatePages($request, $id);

        $pagina->update($validated);

        return response()->json([
            'message' => 'Página actualizada correctamente',
            'data'    => $pagina
        ], 200);
    }

    public function validatePages(Request $request, $id = null)
    {
        return $request->validate(
            [
                'power_bi_dashboard_id' => 'required|exists:power_bi_dashboards,id',
                'nombre'                => 'required|string|max:255|unique:power_bi_dashboard_pages,nombre,' . $id,
                'descripcion'           => 'nullable|string|max:255',
                'orden'                 => 'nullable|integer|min:0',
                'estado'                => 'nullable|in:0,1,2',
            ],
            [
                'power_bi_dashboard_id.required' => 'El campo tablero es obligatorio.',
                'power_bi_dashboard_id.exists'   => 'El tablero seleccionado no existe.',
                'nombre.required'                => 'El campo nombre es obligatorio.',
                'nombre.unique'                  => 'El nombre ya está en uso.',
                'nombre.max'                     => 'El campo nombre no puede tener más de 255 caracteres.',
                'descripcion.max'                => 'El campo descripción no puede tener más de 255 caracteres.',
                'orden.integer'                  => 'El campo orden debe ser un número entero.',
                'orden.min'                      => 'El campo orden debe ser mayor o igual a 0.',
                'estado.in'                      => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }

    public function assignExcels(Request $request, $id)
    {
        abort_unless(
            in_array((int) $request->user()->role_id, [1, 2], true),
            403,
            'No tienes permisos para configurar archivos de Excel.'
        );

        $validated = $request->validate([
            'excel_source_ids'   => 'present|array',
            'excel_source_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:excel_sources,id',
            ],
        ], [
            'present'  => 'El campo :attribute debe enviarse.',
            'required' => 'El campo :attribute es requerido.',
            'array'    => 'El campo :attribute debe ser una lista.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'distinct' => 'No debes repetir una fuente Excel.',
            'exists'   => 'La fuente Excel seleccionada no existe.',
        ], [
            'excel_source_ids'   => 'archivos de Excel',
            'excel_source_ids.*' => 'fuente Excel',
        ]);

        $pagina = DB::transaction(function () use ($id, $validated) {
            $pagina = PowerBiDashboardPage::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $seleccionados = array_map(
                'intval',
                $validated['excel_source_ids']
            );

            $asociados = $pagina->excelSources()
                ->pluck('excel_sources.id')
                ->map(fn($sourceId) => (int) $sourceId)
                ->all();

            $nuevos = array_values(
                array_diff($seleccionados, $asociados)
            );

            if (
                count($nuevos) > 0 &&
                ExcelSource::whereIn('id', $nuevos)
                ->where('estado', 2)
                ->count() !== count($nuevos)
            ) {
                throw ValidationException::withMessages([
                    'excel_source_ids' => [
                        'Solo puedes agregar fuentes Excel activas.',
                    ],
                ]);
            }

            $orden = (int) $pagina->excelSources()
                ->max('excel_source_dashboard_page.orden');

            $asignaciones = [];

            foreach ($seleccionados as $sourceId) {
                if (in_array($sourceId, $asociados, true)) {
                    $asignaciones[$sourceId] = [];
                } else {
                    $asignaciones[$sourceId] = [
                        'es_requerido'  => false,
                        'instrucciones' => '',
                        'orden'         => ++$orden,
                    ];
                }
            }

            $pagina->excelSources()->sync($asignaciones);

            return $pagina
                ->load([
                    'dashboard:id,nombre,descripcion',
                    'excelSources:id,codigo,nombre',
                ])
                ->loadCount('excelSources as total_excels');
        });

        return response()->json([
            'message' => 'Asociaciones de Excel actualizadas correctamente.',
            'data'    => $pagina,
        ], 200);
    }
}
