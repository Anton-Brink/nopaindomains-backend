<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ExternalApiController extends Controller
{
    public function fetchData(Request $request)
    {
        try {
            Log::info('External API call initiated');
            $client = new Client();
            $externalQueryType = $request->query('type');
            switch ($externalQueryType) {
                case 'domain_lookup':
                    $apiKey = config('services.domain.api_key') ?? env('DA_API_KEY');
                    $domain = $request->query('domain');
                    $response = $client->get("https://domain-availability.whoisxmlapi.com/api/v1", [
                        'query' => [
                            'apiKey' => $apiKey,
                            'domainName' => $domain,
                            'credits' => 'DA'
                        ]
                    ]);

                    Log::info('External API call successful', [
                        'user_id' => $request->user()->id,
                        'status' => $response->getStatusCode()
                    ]);

                    return response()->json(
                        json_decode($response->getBody()->getContents())
                    );
                    break;
                
                default:
                    throw "Invalid Query Type";
                    break;
            }
        } catch (\Exception $e) {
            Log::error('External API call failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'error' => 'External API call failed'
            ], 500);
        }
    }
}