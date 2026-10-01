<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\StoreTransferRequest;
use App\Http\Requests\Transfers\UpdateTransferRequest;
use App\Http\Resources\MovementResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Transfers', 'Transferencias entre cuentas propias del usuario autenticado.')]
class TransferController extends Controller
{
    #[Endpoint('Create transfer', 'Crea una transferencia entre dos cuentas propias del usuario autenticado: genera dos movimientos enlazados por transfer_id (salida en la cuenta de origen, entrada en la de destino) dentro de una única transacción.')]
    #[Response(status: 201, content: [
        'success' => true,
        'message' => 'Transfer created successfully.',
        'data' => [
            'outgoing' => [
                'id' => 10,
                'account_id' => 1,
                'category_id' => null,
                'transfer_id' => '9f1c9b1a-7e3e-4b7a-8f0a-1b1f2a3c4d5e',
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
                'transfer_id' => '9f1c9b1a-7e3e-4b7a-8f0a-1b1f2a3c4d5e',
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

        $transferId = (string) Str::uuid();

        [$outgoing, $incoming] = DB::transaction(function () use ($user, $data, $description, $transferId) {
            $outgoing = $user->movements()->create([
                'account_id' => $data['from_account_id'],
                'category_id' => null,
                'transfer_id' => $transferId,
                'description' => $description,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => MovementType::EXPENSE,
                'is_transfer' => true,
            ]);

            $incoming = $user->movements()->create([
                'account_id' => $data['to_account_id'],
                'category_id' => null,
                'transfer_id' => $transferId,
                'description' => $description,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'type' => MovementType::INCOME,
                'is_transfer' => true,
            ]);

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

    #[Endpoint('Update transfer', 'Actualiza los dos movimientos de una transferencia a la vez (origen, destino, monto, fecha, descripción). Todos los campos son opcionales. El tipo (expense/income) de cada pierna no cambia.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Transfer updated successfully.',
        'data' => [
            'outgoing' => [
                'id' => 10,
                'account_id' => 1,
                'category_id' => null,
                'transfer_id' => '9f1c9b1a-7e3e-4b7a-8f0a-1b1f2a3c4d5e',
                'description' => 'Pago tarjeta de crédito (actualizada)',
                'amount' => '250000.00',
                'date' => '2026-09-30',
                'type' => 'expense',
                'is_transfer' => true,
                'created_at' => '2026-09-30T10:00:00.000000Z',
                'updated_at' => '2026-09-30T11:00:00.000000Z',
            ],
            'incoming' => [
                'id' => 11,
                'account_id' => 2,
                'category_id' => null,
                'transfer_id' => '9f1c9b1a-7e3e-4b7a-8f0a-1b1f2a3c4d5e',
                'description' => 'Pago tarjeta de crédito (actualizada)',
                'amount' => '250000.00',
                'date' => '2026-09-30',
                'type' => 'income',
                'is_transfer' => true,
                'created_at' => '2026-09-30T10:00:00.000000Z',
                'updated_at' => '2026-09-30T11:00:00.000000Z',
            ],
        ],
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La transferencia no existe o no pertenece al usuario autenticado.')]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['to_account_id' => ['The to account id field and from account id must be different.']],
    ], description: 'Error de validación (cuenta inexistente/inactiva/no propia del usuario, cuenta de destino igual a la de origen tras aplicar los cambios, monto no positivo, etc.).')]
    public function update(string $transfer, UpdateTransferRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        [$outgoing, $incoming] = $this->findTransferMovements($user, $transfer);

        $fromAccountId = $data['from_account_id'] ?? $outgoing->account_id;
        $toAccountId = $data['to_account_id'] ?? $incoming->account_id;

        if ($fromAccountId === $toAccountId) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'data' => null,
                'errors' => ['to_account_id' => ['The to account id field and from account id must be different.']],
            ], 422);
        }

        $amount = $data['amount'] ?? $outgoing->amount;
        $date = $data['date'] ?? $outgoing->date;
        $description = array_key_exists('description', $data) ? $data['description'] : $outgoing->description;

        DB::transaction(function () use ($outgoing, $incoming, $fromAccountId, $toAccountId, $amount, $date, $description) {
            $outgoing->update([
                'account_id' => $fromAccountId,
                'amount' => $amount,
                'date' => $date,
                'description' => $description,
            ]);

            $incoming->update([
                'account_id' => $toAccountId,
                'amount' => $amount,
                'date' => $date,
                'description' => $description,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer updated successfully.',
            'data' => [
                'outgoing' => new MovementResource($outgoing->fresh()),
                'incoming' => new MovementResource($incoming->fresh()),
            ],
        ]);
    }

    #[Endpoint('Delete transfer', 'Elimina definitivamente los dos movimientos de la transferencia.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Transfer deleted successfully.',
        'data' => null,
    ])]
    #[Response(status: 404, content: [
        'success' => false,
        'message' => 'Resource not found.',
        'data' => null,
    ], description: 'La transferencia no existe o no pertenece al usuario autenticado.')]
    public function destroy(string $transfer, Request $request)
    {
        $user = $request->user();

        $movements = $this->findTransferMovements($user, $transfer);

        DB::transaction(function () use ($movements) {
            foreach ($movements as $movement) {
                $movement->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer deleted successfully.',
            'data' => null,
        ]);
    }

    /**
     * Find the outgoing/incoming movement pair for a transfer id, scoped to the user.
     *
     * @return array{0: \App\Models\Movements, 1: \App\Models\Movements}
     */
    private function findTransferMovements($user, string $transferId): array
    {
        $movements = $user->movements()
            ->where('transfer_id', $transferId)
            ->where('is_transfer', true)
            ->get();

        $outgoing = $movements->firstWhere('type', MovementType::EXPENSE);
        $incoming = $movements->firstWhere('type', MovementType::INCOME);

        if ($movements->count() !== 2 || ! $outgoing || ! $incoming) {
            abort(response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ], 404));
        }

        return [$outgoing, $incoming];
    }
}
