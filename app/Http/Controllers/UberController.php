<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class UberController extends Controller
{
    private $appId;
    private $appCode;
    private $apiUrl;

    /**
     * Initialise controller.
     *
     * @param Request  $request
     * @param string  $searchText
     * @return Response
     */
    public function __construct() {
        $this->client = new \GuzzleHttp\Client();
        $this->serverToken = env("UBER_API_SERVER_TOKEN");
        $this->apiUrl = env("UBER_API_URL");
    }

    /**
     * Index route.
     *
     * @param Request  $request
     * @param string  $searchText
     * @return Response
     */
    public function index(Request $request) {
        $params = $request->all();
        $startLat = $params["startLat"];
        $startLon = $params["startLon"];
        $endLat = $params["endLat"];
        $endLon = $params["endLon"];
        return $this->getPriceEstimate($startLat, $startLon, $endLat, $endLon);
    }
    
    /**
     * Get price estimate
     *
     * @param string  $startLat 
     * @param string  $startLon
     * @param string  $endLat
     * @param string  $endLon
     * @return Response
     */
    public function getPriceEstimate($startLat, $startLon, $endLat, $endLon) {
        $res = $this->client->request("GET", $this->apiUrl, [
            "query" => [
                "start_latitude" => $startLat,
                "start_longitude" => $startLon,
                "end_latitude" => $endLat,
                "end_longitude" => $endLon,
            ],
            "headers" => [
                'Authorization' => "Token " . $this->serverToken,
                "Accept-Language" => "en_GB",
                "Content-Type" => "Content-Type: application/json",
            ]
        ]);

        if ($res->getStatusCode() !== 200) {
            return response()->json()->setStatusCode(500);
        }

        $responseBody = (string) $res->getBody();
        $estimateResponse = json_decode($responseBody);

        if (!count($estimateResponse->prices)) {
            // Empty results mean estimate is > 100 miles, not supported by Uber API.
            return response()->json()->setStatusCode(400);
        }

        $results = array_map(function($result) {
            return [
                "displayName" => $result->display_name,
                "estimate" => $result->estimate,
                "distance" => $result->distance,
            ];
        }, $estimateResponse->prices);

        return response()->json([
            "results" => $results,
        ]);
    }
}
