<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categoria\DestroyCategoriaRequest;
use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => CategoriaResource::collection(Categoria::orderBy('nombre')->get()),
        ]);
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create($request->validated());

        return response()->json(['data' => new CategoriaResource($categoria)], 201);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        $categoria->update($request->validated());

        return response()->json(['data' => new CategoriaResource($categoria)]);
    }

    public function destroy(DestroyCategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        $categoria->delete();

        return response()->json(null, 204);
    }
}
