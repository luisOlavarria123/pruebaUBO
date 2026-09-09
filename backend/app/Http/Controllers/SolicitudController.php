<?php

namespace App\Http\Controllers;

use App\Enums\SolicitudEstado;
use App\Http\Requests\Solicitud\IndexSolicitudRequest;
use App\Http\Requests\Solicitud\StoreSolicitudRequest;
use App\Http\Requests\Solicitud\UpdateEstadoSolicitudRequest;
use App\Http\Requests\Solicitud\UpdateSolicitudRequest;
use App\Http\Resources\SolicitudResource;
use App\Models\Solicitud;
use App\Services\SolicitudService;
use Illuminate\Http\JsonResponse;

class SolicitudController extends Controller
{
    public function __construct(private readonly SolicitudService $solicitudes) {}

    public function index(IndexSolicitudRequest $request): JsonResponse
    {
        $page = $this->solicitudes->list($request->user(), $request->validated());

        return response()->json([
            'data' => SolicitudResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StoreSolicitudRequest $request): JsonResponse
    {
        $solicitud = $this->solicitudes->create($request->user(), $request->validated());
        $solicitud->load(['solicitante', 'categoria']);

        return response()->json(['data' => new SolicitudResource($solicitud)], 201);
    }

    public function show(Solicitud $solicitud): JsonResponse
    {
        $this->authorize('view', $solicitud);

        $solicitud->load(['solicitante', 'categoria']);

        return response()->json(['data' => new SolicitudResource($solicitud)]);
    }

    public function update(UpdateSolicitudRequest $request, Solicitud $solicitud): JsonResponse
    {
        $solicitud = $this->solicitudes->update($solicitud, $request->validated());

        return response()->json(['data' => new SolicitudResource($solicitud)]);
    }

    public function updateEstado(UpdateEstadoSolicitudRequest $request, Solicitud $solicitud): JsonResponse
    {
        $solicitud = $this->solicitudes->transitionEstado(
            $solicitud,
            SolicitudEstado::from($request->validated('estado')),
        );

        return response()->json(['data' => new SolicitudResource($solicitud)]);
    }

    public function destroy(Solicitud $solicitud): JsonResponse
    {
        $this->authorize('delete', $solicitud);

        $solicitud->delete();

        return response()->json(null, 204);
    }
}
