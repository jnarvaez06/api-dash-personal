<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\MovementResource;
use App\Http\Requests\Movements\StoreMovementRequest;
use App\Http\Requests\Movements\UpdateMovementRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Movements', 'CRUD de movimientos (ingresos, gastos e inversiones) del usuario autenticado.')]
class MovementController extends Controller
{
    #[Endpoint('List movements', 'Movimientos del usuario autenticado, paginados de a 15, más recientes primero por fecha.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Movements retrieved successfully.',
        'data' => [
            'data' => [[
                'id' => 1,
                'account_id' => 1,
                'category_id' => 1,
                'related_movement_id' => null,
                'description' => 'Compra en el supermercado',
                'amount' => '150000.00',
                'date' => '2026-08-20',
                'type' => 'expense',
                'is_transfer' => false,
                'created_at' => '2026-08-20T10:00:00.000000Z',
                'updated_at' => '2026-08-20T10:00:00.000000Z',
            ]],
            'links' => [
                'first' => 'http://dashpersonal.test/api/movements?page=1',
                'last' => 'http://dashpersonal.test/api/movements?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Previous', 'page' => null, 'active' => false],
                    ['url' => 'http://dashpersonal.test/api/movements?page=1', 'label' => '1', 'page' => 1, 'active' => true],
                    ['url' => null, 'label' => 'Next &raquo;', 'page' => null, 'active' => false],
                ],
                'path' => 'http://dashpersonal.test/api/movements',
                'per_page' => 15,
                'to' => 1,
                'total' => 1,
            ],
        ],
    ])]
    public function index(Request $request)
    {
        $movements = $request->user()
            ->movements()
            ->latest('date')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Movements retrieved successfully.',
            'data' => MovementResource::collection($movements)->response()->getData(true),
        ]);
    }

    #[Endpoint('Create movement', 'Crea un nuevo movimiento para el usuario autenticado, asociado a una categoría propia.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Movement created successfully.',
        'data' => [
            'id' => 1,
            'account_id' => 1,
            'category_id' => 1,
            'related_movement_id' => null,
            'description' => 'Compra en el supermercado',
            'amount' => '150000.00',
            'date' => '2026-08-20',
            'type' => 'expense',
            'is_transfer' => false,
            'created_at' => '2026-08-20T10:00:00.000000Z',
            'updated_at' => '2026-08-20T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['category_id' => ['The selected category id is invalid.']],
    ], description: 'Error de validación (incluye usar una cuenta o categoría que no pertenece al usuario).')]
    public function store(StoreMovementRequest $request)
    {
        $movement = $request->user()->movements()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Movement created successfully.',
            'data' => new MovementResource($movement->fresh()),
        ]);
    }

    #[Endpoint('Get movement', 'Devuelve un movimiento del usuario autenticado por id.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Movement retrieved successfully.',
        'data' => [
            'id' => 1,
            'account_id' => 1,
            'category_id' => 1,
            'related_movement_id' => null,
            'description' => 'Compra en el supermercado',
            'amount' => '150000.00',
            'date' => '2026-08-20',
            'type' => 'expense',
            'is_transfer' => false,
            'created_at' => '2026-08-20T10:00:00.000000Z',
            'updated_at' => '2026-08-20T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'El movimiento no existe o no pertenece al usuario autenticado.')]
    public function show(Request $request, int $movement)
    {
        $movement = $request->user()->movements()->findOrFail($movement);

        return response()->json([
            'success' => true,
            'message' => 'Movement retrieved successfully.',
            'data' => new MovementResource($movement),
        ]);
    }

    #[Endpoint('Update movement', 'Actualiza un movimiento del usuario autenticado. Todos los campos son opcionales.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Movement updated successfully.',
        'data' => [
            'id' => 1,
            'account_id' => 1,
            'category_id' => 1,
            'related_movement_id' => null,
            'description' => 'Compra en el supermercado (actualizada)',
            'amount' => '160000.00',
            'date' => '2026-08-20',
            'type' => 'expense',
            'is_transfer' => false,
            'created_at' => '2026-08-20T10:00:00.000000Z',
            'updated_at' => '2026-08-21T09:30:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'El movimiento no existe o no pertenece al usuario autenticado.')]
    #[Response(status: 409, content: [
        'success' => false,
        'message' => 'Transfer movements cannot be modified directly.',
        'data' => null,
    ], description: 'El movimiento fue creado por una transferencia (is_transfer=true) y no puede editarse por este endpoint.')]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['amount' => ['The amount field must be a number.']],
    ], description: 'Error de validación.')]
    public function update(UpdateMovementRequest $request, int $movement)
    {
        $movement = $request->user()->movements()->findOrFail($movement);

        if ($movement->is_transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer movements cannot be modified directly.',
                'data' => null,
            ], 409);
        }

        $movement->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Movement updated successfully.',
            'data' => new MovementResource($movement->fresh()),
        ]);
    }

    #[Endpoint('Delete movement', 'Elimina definitivamente el movimiento. A diferencia de Accounts/Categories, no hay soft-disable: los movimientos no tienen columna `is_active`.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Movement deleted successfully.',
        'data' => null,
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'El movimiento no existe o no pertenece al usuario autenticado.')]
    #[Response(status: 409, content: [
        'success' => false,
        'message' => 'Transfer movements cannot be deleted directly.',
        'data' => null,
    ], description: 'El movimiento fue creado por una transferencia (is_transfer=true) y no puede borrarse por este endpoint.')]
    public function destroy(Request $request, int $movement)
    {
        $movement = $request->user()->movements()->findOrFail($movement);

        if ($movement->is_transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer movements cannot be deleted directly.',
                'data' => null,
            ], 409);
        }

        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Movement deleted successfully.',
            'data' => null,
        ]);
    }
}
