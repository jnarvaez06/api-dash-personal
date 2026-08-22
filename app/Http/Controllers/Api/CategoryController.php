<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Categories', 'CRUD de categorías del usuario autenticado.')]
class CategoryController extends Controller
{
    #[Endpoint('List categories', 'Categorías del usuario autenticado, paginadas de a 15, más recientes primero.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Categories retrieved successfully.',
        'data' => [
            'data' => [[
                'id' => 1,
                'name' => 'Alimentación',
                'is_active' => true,
                'created_at' => '2026-01-10T10:00:00.000000Z',
                'updated_at' => '2026-01-10T10:00:00.000000Z',
            ]],
            'links' => [
                'first' => 'http://dashpersonal.test/api/categories?page=1',
                'last' => 'http://dashpersonal.test/api/categories?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Previous', 'page' => null, 'active' => false],
                    ['url' => 'http://dashpersonal.test/api/categories?page=1', 'label' => '1', 'page' => 1, 'active' => true],
                    ['url' => null, 'label' => 'Next &raquo;', 'page' => null, 'active' => false],
                ],
                'path' => 'http://dashpersonal.test/api/categories',
                'per_page' => 15,
                'to' => 1,
                'total' => 1,
            ],
        ],
    ])]
    public function index(Request $request)
    {
        $categories = $request->user()
            ->categories()
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => CategoryResource::collection($categories)->response()->getData(true),
        ]);
    }

    #[Endpoint('Create category', 'Crea una nueva categoría para el usuario autenticado.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Category created successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Alimentación',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-10T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['name' => ['The name field is required.']],
    ], description: 'Error de validación.')]
    public function store(StoreCategoryRequest $request)
    {
        $category = $request->user()
            ->categories()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category->fresh()),
        ]);
    }

    #[Endpoint('Get category', 'Devuelve una categoría del usuario autenticado por id.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Category retrieved successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Alimentación',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-10T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La categoría no existe o no pertenece al usuario autenticado.')]
    public function show(Request $request, int $category)
    {
        $category = $request->user()
            ->categories()
            ->findOrFail($category);

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully.',
            'data' => new CategoryResource($category),
        ]);
    }

    #[Endpoint('Update category', 'Actualiza una categoría del usuario autenticado. Todos los campos son opcionales.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Category updated successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Alimentación actualizada',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-11T09:30:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La categoría no existe o no pertenece al usuario autenticado.')]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['name' => ['The name field must be a string.']],
    ], description: 'Error de validación.')]
    public function update(UpdateCategoryRequest $request, int $category)
    {
        $category = $request->user()
            ->categories()
            ->findOrFail($category);

        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category->fresh()),
        ]);
    }

    #[Endpoint('Delete category', 'Desactiva la categoría (soft-disable: pone `is_active` en `false`), no la elimina de la base de datos.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Category deleted successfully.',
        'data' => null,
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La categoría no existe o no pertenece al usuario autenticado.')]
    public function destroy(Request $request, int $category)
    {
        $category = $request->user()
            ->categories()
            ->findOrFail($category);

        $category->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
            'data' => null,
        ]);
    }
}
