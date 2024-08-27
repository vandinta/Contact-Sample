<?php

namespace App\Http\Controllers;

use App\Services\GooglePeopleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class GooglePeopleController extends Controller
{
    protected $googleService;

    public function __construct(GooglePeopleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function redirectToGoogle()
    {
        $client = $this->googleService->getClient();
        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->googleService->getClient();

        if ($request->has('code')) {
            $client->fetchAccessTokenWithAuthCode($request->input('code'));
            $accessToken = $client->getAccessToken();

            Log::info("INFO", ["message" => $accessToken]);

            return response()->json($accessToken, 200);
        }

        return redirect()->route('home')->with('error', 'Failed to authenticate with Google.');
    }

    public function index()
    {
        $client = $this->googleService->getClient();

        $accessToken = Session::get('google_access_token');
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            var_dump('error');
            die;
            return redirect()->route('google.auth');
        }

        $peopleService = $this->googleService->getPeopleService();
        $connections = $peopleService->people_connections->listPeopleConnections('people/me', [
            'pageSize' => 10,
            'personFields' => 'names,emailAddresses',
        ]);

        return response()->json($connections->getConnections());
    }

    public function store(Request $request)
    {
        $client = $this->googleService->getClient();

        $client->addScope(\Google_Service_PeopleService::CONTACTS);

        $accessToken = "ya29.a0AcM612xbzLBs9GReOVit5Ut7ehAMESmo9kx6IiCklpg4XeyvJ22Js3A_2JSfjjy8i_sKfUTl87sleOMKZbN6KzUPPA-GsVPX9UuoI99EjtDqjfBmmwtctBazLy9ltb7AI5mqw7vXIKB9LeqiC_LqoTrmRuBd6YK3RusaCgYKAYYSARMSFQHGX2Mievs2T44dbvUnICjZVTrsbg0170";
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            return response()->json(['error' => 'Token Expired'], 403);
        }

        $peopleService = $this->googleService->getPeopleService();

        $contact = new \Google_Service_PeopleService_Person();
        $contact->setNames([
            new \Google_Service_PeopleService_Name([
                'givenName' => $request->input('givenName', 'DefaultGivenName'),
                'familyName' => $request->input('familyName', 'DefaultFamilyName'),
            ])
        ]);

        $contact->setEmailAddresses([
            new \Google_Service_PeopleService_EmailAddress([
                'value' => $request->input('email', 'default@example.com'),
            ])
        ]);

        try {
            $createdContact = $peopleService->people->createContact($contact);
            return response()->json($createdContact, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create contact: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $resourceName)
    {
        $client = $this->googleService->getClient();

        if (!Session::has('google_access_token')) {
            return redirect()->route('google.auth');
        }

        $accessToken = Session::get('google_access_token');
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            return redirect()->route('google.auth');
        }

        $peopleService = $this->googleService->getPeopleService();

        $contact = $peopleService->people->get($resourceName);

        $contact->setNames([new \Google_Service_PeopleService_Name([
            'givenName' => $request->input('givenName'),
            'familyName' => $request->input('familyName')
        ])]);
        $contact->setEmailAddresses([new \Google_Service_PeopleService_EmailAddress([
            'value' => $request->input('email')
        ])]);

        $updatedContact = $peopleService->people->updateContact($resourceName, $contact, ['updatePersonFields' => 'names,emailAddresses']);

        return response()->json($updatedContact);
    }

    public function destroy($resourceName)
    {
        $client = $this->googleService->getClient();

        if (!Session::has('google_access_token')) {
            return redirect()->route('google.auth');
        }

        $accessToken = Session::get('google_access_token');
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            return redirect()->route('google.auth');
        }

        $peopleService = $this->googleService->getPeopleService();

        $peopleService->people->deleteContact($resourceName);

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}
