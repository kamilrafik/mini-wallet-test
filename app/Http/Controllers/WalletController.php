<?php

namespace App\Http\Controllers;

use App\Features\Wallet\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    )
    {
    }

    public function indexTransactions()
    {

    }

    public function createTransaction(Request $request)
    {
        $result = $this->walletService->createTransaction($request);

        return response()->json([
            'success' => $result->isSuccessful(),
            'result' => $result->toArray(),
        ]);
    }
}
