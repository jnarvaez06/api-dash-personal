<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Accounts', 'CRUD de cuentas financieras del usuario autenticado.')]
class AccountController extends Controller
{
    #[Endpoint('List accounts', 'Cuentas del usuario autenticado, paginadas de a 15, más recientes primero.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Accounts retrieved successfully.',
        'data' => [
            'data' => [[
                'id' => 1,
                'name' => 'Cuenta de ahorros',
                'type' => 'bank_account',
                'initial_balance' => '1500000.00',
                'is_active' => true,
                'created_at' => '2026-01-10T10:00:00.000000Z',
                'updated_at' => '2026-01-10T10:00:00.000000Z',
            ]],
            'links' => [
                'first' => 'http://dashpersonal.test/api/accounts?page=1',
                'last' => 'http://dashpersonal.test/api/accounts?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Previous', 'page' => null, 'active' => false],
                    ['url' => 'http://dashpersonal.test/api/accounts?page=1', 'label' => '1', 'page' => 1, 'active' => true],
                    ['url' => null, 'label' => 'Next &raquo;', 'page' => null, 'active' => false],
                ],
                'path' => 'http://dashpersonal.test/api/accounts',
                'per_page' => 15,
                'to' => 1,
                'total' => 1,
            ],
        ],
    ])]
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Accounts retrieved successfully.',
            'data' => AccountResource::collection($accounts)->response()->getData(true),
        ]);
    }

    #[Endpoint('Create account', 'Crea una nueva cuenta financiera para el usuario autenticado.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Account created successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Cuenta de ahorros',
            'type' => 'bank_account',
            'initial_balance' => '1500000.00',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-10T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['type' => ['The selected type is invalid.']],
    ], description: 'Error de validación.')]
    public function store(StoreAccountRequest $request)
    {
        $account = $request->user()
            ->accounts()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => new AccountResource($account->fresh()),
        ]);
    }

    #[Endpoint('Get account', 'Devuelve una cuenta del usuario autenticado por id.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Account retrieved successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Cuenta de ahorros',
            'type' => 'bank_account',
            'initial_balance' => '1500000.00',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-10T10:00:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La cuenta no existe o no pertenece al usuario autenticado.')]
    public function show(Request $request, int $account)
    {
        $account = $request->user()
            ->accounts()
            ->findOrFail($account);

        return response()->json([
            'success' => true,
            'message' => 'Account retrieved successfully.',
            'data' => new AccountResource($account),
        ]);
    }

    #[Endpoint('Update account', 'Actualiza una cuenta del usuario autenticado. Todos los campos son opcionales.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Account updated successfully.',
        'data' => [
            'id' => 1,
            'name' => 'Cuenta de ahorros actualizada',
            'type' => 'bank_account',
            'initial_balance' => '1500000.00',
            'is_active' => true,
            'created_at' => '2026-01-10T10:00:00.000000Z',
            'updated_at' => '2026-01-11T09:30:00.000000Z',
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La cuenta no existe o no pertenece al usuario autenticado.')]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['type' => ['The selected type is invalid.']],
    ], description: 'Error de validación.')]
    public function update(UpdateAccountRequest $request, int $account)
    {
        $account = $request->user()
            ->accounts()
            ->findOrFail($account);

        $account->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully.',
            'data' => new AccountResource($account->fresh()),
        ]);
    }

    #[Endpoint('Delete account', 'Desactiva la cuenta (soft-disable: pone `is_active` en `false`), no la elimina de la base de datos.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Account deleted successfully.',
        'data' => null,
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La cuenta no existe o no pertenece al usuario autenticado.')]
    public function destroy(Request $request, int $account)
    {
        $account = $request->user()
            ->accounts()
            ->findOrFail($account);

        $account->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
            'data' => null,
        ]);
    }
}