<?php

namespace ContraInteractive\WpLaravelLogin\Controllers;
use ContraInteractive\WpLaravelLogin\Services\WpSyncPasswordService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WpSyncPasswordApiController extends Controller
{
    protected WpSyncPasswordService $syncService;

    public function __construct(WpSyncPasswordService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function __invoke(Request $request)
    {
        $this->validateRequest($request);

        $payload = $request->only(['email', 'wp_hash', 'nonce', 'timestamp']);
        $signature = $request->header('X-Signature');

        if(!$signature){
            return response()->json(['error' => 'X-Signature is missing'], 403);
        }

        try {
            $this->syncService->sync($payload, $signature);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    protected function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], 422)
            );
        }
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'wp_hash' => 'required',
            'nonce' => 'required',
            'timestamp' => 'required',
        ];
    }
}