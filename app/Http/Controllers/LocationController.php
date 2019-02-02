<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class LocationController extends Controller
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
        $this->appId = env("HERE_API_APP_ID");
        $this->appCode = env("HERE_API_APP_CODE");
        $this->apiUrl = env("HERE_API_URL");
    }

    /**
     * Index route.
     *
     * @param Request  $request
     * @param string  $searchText
     * @return Response
     */
    public function index(Request $request, $searchText) {
        return $this->searchAddress($searchText);
    }
    
    /**
     * Search Address
     *
     * @param string  $searchText
     * @return Response
     */
    public function searchAddress($searchText) {
        $res = $this->client->request("GET", $this->apiUrl . "geocode.json", [
            "query" => [
                "app_id" => $this->appId,
                "app_code" => $this->appCode,
                "searchtext" => urldecode($searchText),
                "prox" => "51.5083,-0.1256,67058", // London
            ]
        ]);

        if ($res->getStatusCode() !== 200) {
            return response()->json()->setStatusCode(500);
        }

        $responseBody = (string) $res->getBody();
        $searchResponse = json_decode($responseBody);

        if (!count($searchResponse->Response->View)) {
            return response()->json([
                "results" => [],
            ]);
        }

        $results = array_map(function($result) {
            return [
                "label" => $result->Location->Address->Label,
                "lat" => $result->Location->DisplayPosition->Latitude,
                "lon" => $result->Location->DisplayPosition->Longitude,
            ];
        }, $searchResponse->Response->View[0]->Result);

        return response()->json([
            "results" => $results,
        ]);
    }
}
