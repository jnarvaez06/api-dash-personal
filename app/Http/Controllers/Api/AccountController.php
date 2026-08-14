<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Accounts retrieved successfully.',
            'data' => AccountResource::collection($accounts),
        ]);
    }

    public function store(StoreAccountRequest $request)
    {
        $account = $request->user()
            ->accounts()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => new AccountResource($account),
        ]);
    }

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