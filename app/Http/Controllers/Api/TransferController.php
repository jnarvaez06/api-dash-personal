<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\StoreTransferRequest;
use App\Http\Resources\MovementResource;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Transfers', 'Transferencias entre cuentas propias del usuario autenticado.')]
class TransferController extends Controller
{
    #[Endpoint('Create transfer', 'Crea una transferencia entre dos cuentas propias del usuario autenticado: genera dos movimientos enlazados (salida en la cuenta de origen, entrada en la de destino) dentro de una única transacción.')]
    #[Response(status: 201, content: [
        'success' => true,
        'message' => 'Transfer created successfully.',
        'data' => [
            'outgoing' => [
                'id' => 10,
                'account_id' => 1,
                'category_id' => null,
                'related_movement_id' => 11,
                'description' => 'Pago tarjeta de crédito',
                'amount' => '200000.00',
                'date' => '2026-09-30',
                'type' => 'expense',
                'is_transfer' => true,
                'created_at' => '2026-09-30T10:00:00.000000Z',
                'updated_at' => '2026-09-30T10:00:00.000000Z',
            ],
            'incoming' => [
                'id' => 11,
                'account_id' => 2,
                'category_id' => null,
                'related_movement_id' => 10,
                'description' => 'Pago tarjeta de crédito',
                'amount' => '200000.00',
                'date' => '2026-09-30',
                'type' => 'income',
                'is_transfer' => true,
                'created_at' => '2026-09-30T10:00:00.000000Z',
                'updated_at' => '2026-09-30T10:00:00.000000Z',
            ],
        ],
    ])]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['to_account_id' => ['The selected to account id is invalid.']],
    ], description: 'Error de validación (cuenta inexistente/inactiva/no propia del usuario, cuenta de destino igual a la de origen, monto no positivo, etc.).')]
    public function store(StoreTransferRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $fromAccount = $user->accounts()->findOrFail($data['from_account_id']);
        $toAccount = $user->accounts()->findOrFail($data['to_account_id']);

        $description = $data['description']
            ?? "Transferencia: {$fromAccount->name} → {$toAccount->name}";

        [$outgoing, $incoming] = DB::transaction(function () use ($user, $data, $description) {
            $outgoing = $user->movements()->create([
                'account_id' => $data['from_account_id'],
                'category_id' => null,
                'description' => $description,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => MovementType::EXPENSE,
                'is_transfer' => true,
            ]);

            $incoming = $user->movements()->create([
                'account_id' => $data['to_account_id'],
                'category_id' => null,
                'description' => $description,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => MovementType::INCOME,
                'is_transfer' => true,
                'related_movement_id' => $outgoing->id,
            ]);

            $outgoing->update(['related_movement_id' => $incoming->id]);

            return [$outgoing->fresh(), $incoming->fresh()];
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer created successfully.',
            'data' => [
                'outgoing' => new MovementResource($outgoing),
                'incoming' => new MovementResource($incoming),
            ],
        ], 201);
    }
}
