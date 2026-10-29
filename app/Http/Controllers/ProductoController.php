<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de productos.
 *
 * GET    /api/productos          -> index   (200)
 * POST   /api/productos          -> store   (201 / 422)
 * GET    /api/productos/{id}     -> show    (200 / 404)
 * PUT    /api/productos/{id}     -> update  (200 / 404 / 422)
 * PATCH  /api/productos/{id}     -> update  (200 / 404 / 422)
 * DELETE /api/productos/{id}     -> destroy (200 / 404)
 */
class ProductoController extends Controller
{
    /** Lista paginada con filtros: ?buscar=, ?categoria=, ?por_pagina= */
    public function index(Request $request)
    {
        $productos = Producto::query()
            ->buscar($request->query('buscar'))
            ->when($request->query('categoria'), fn ($q, $c) => $q->where('categoria', $c))
            ->orderByDesc('id')
            ->paginate(max(1, min((int) $request->query('por_pagina', 10), 100)));

        return ProductoResource::collection($productos);
    }

    public function store(StoreProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated());

        return (new ProductoResource($producto))
            ->additional(['mensaje' => 'Producto creado correctamente'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Producto $producto): ProductoResource
    {
        return new ProductoResource($producto);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): ProductoResource
    {
        $producto->update($request->validated());

        return (new ProductoResource($producto))
            ->additional(['mensaje' => 'Producto actualizado correctamente']);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json(['mensaje' => 'Producto eliminado correctamente']);
    }
}
