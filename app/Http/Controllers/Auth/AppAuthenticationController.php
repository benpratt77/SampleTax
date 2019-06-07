<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Guzzle\Http\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Predis\Client as RedisClient;

class AppAuthenticationController extends Controller
{
    /** @var RedisClient */
    private $redis;

    /** @var AuthService */
    private $authService;

    /**
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->redis = new RedisClient();
        $this->authService = $authService;
    }

    /**
     * This callback action is called when initially setting the app up.
     * This uses a one time key that allows us to retrieve the access_token via a post request.
     *
     * @param Request $request
     * @return string
     */
    public function callbackAction(Request $request): string
    {
        $payload = $this->authService->getAuthPayload(
            $request->get('code'),
            $request->get('scope'),
            $request->get('context')
        );
        $client = new Client($this->authService->getBcAuthService());

        // TODO Get this access token properly.
        // At present this will not work as the token sent by the app is one time use.
        // I cannot get this tobe sent locally need to upload this to https
        $data = ['access_token' => 'g761rd0vu161frqn1x2h47gv18bdf94', 'store_hash' => 'u8stgwcn9s'];
        $this->redis->set("stores/{$data['store_hash']}/auth", json_encode($data));

        return 'This works for now. need to update to get he access token properly';
        /**
         *
         * $req = $client->post('/oauth2/token', [], $payload, [
         * 'exceptions' => false,
         * ]);
         *
         * $resp = $req->send();
         * if ($resp->getStatusCode() == 200) {
         * $data = $resp->json();
         * list($context, $storeHash) = explode('/', $data['context'], 2);
         * $key = $this->getUserKey($storeHash, $data['user']['email']);
         * // Store the user data and auth data in our key-value store so we can fetch it later and make requests.
         * $this->redis->set($key, json_encode($data['user'], true));
         * $this->redis->set("stores/{$storeHash}/auth", json_encode($data));
         * return 'Hello ' . json_encode($data);
         * } else {
         * return 'Something went wrong... [' . $resp->getStatusCode() . '] ' . $resp->getBody();
         * }
         **/
    }

    /**
     * This is the default loaded action when our app is opened from the app screen.
     *
     * @param Request $request
     * @return string
     */
    public function loadAction(Request $request): string
    {
        //Check that the request is legitimate and get data from the signedRequest object.
        $data = $this->authService->verifySignedRequest($request->get('signed_payload'));
        if (empty($data)) {
            return 'Invalid Payload';
        }
        // Get the key based on the username and storehash.
        $key = $this->authService->getUserKey($data['store_hash'], $data['user']['email']);
        $user = json_decode($this->redis->get($key));
        if (empty($user)) {
            $user = $data['user'];
            $this->redis->set($key, json_encode($user));
        }

        //Set up the client
        $bcClient = $this->authService->getConfiguredBcClient($data['store_hash']);

        //Check that we a username. If so provide then the discconect button
        $userDetails = $bcClient->get("https://api.bigcommerce.com/stores/{$data['store_hash']}/v3/tax/connect/SampleTaxProvider");
        if (isset($userDetails->data->username) && $userDetails->data->username !== '') {
            return view('connected');
        }

        // If not then provide the facility to provide these
        return view('login');
    }

    /**
     * This action is fires upon uninstall from the App page in the Control Panel of BigCommerce.
     * In our case this clears out the connection details so that SampleTax cannot be used anymore.
     *
     * @param Request $request
     * @return string
     */
    public function removeUserAction(Request $request): string
    {
        //Check that the request is legitimate and get data from the signedRequest object.
        $data = $this->authService->verifySignedRequest($request->get('signed_payload'));
        if (empty($data)) {
            return 'Invalid signed_payload.';
        }
        //Get the Key from Redis and then delet it.
        $key = $this->authService->getUserKey($data['store_hash'], $data['user']['email']);
        $this->redis->del([$key]);
        //Configure the BcClient and then delete th connection details from the BC.
        $bcClient = $this->authService->getConfiguredBcClient($data['store_hash']);
        $bcClient->delete("https://api.bigcommerce.com/stores/{$data['store_hash']}/v3/tax/connect/SampleTaxProvider");

        //Return this to BC to let them remove the user.
        return '[Remove User] ' . $data['user']['email'];
    }

    /**
     * Action for controlling Disconnect flow from within the app.
     * This simply makes a call to BC to convert the username and password to an empty string.
     *
     * @param Request $request
     * @return string
     */
    public function disconnectAction(Request $request): string
    {
        $storeHash = $request->get('store_hash');
        $bcClient = $this->authService->getConfiguredBcClient($storeHash);
        $bcClient->delete("https://api.bigcommerce.com/stores/{$storeHash}/v3/tax/connect/SampleTaxProvider");

        //Redirect back to the index page.
        return redirect()->back();
    }

    /**
     * Action for providing username and password to enable SampleTax.
     * This simply makes a call to BC to add in the username and password for sampleTax.
     *
     * @param Request $request
     * @return Response
     */
    public function updateCredentialsAction(Request $request)
    {
        $postData = [
            'username' => $request->get('username'),
            'password' => $request->get('password')
        ];

        if($postData['username'] === "" || $postData['password'] === ""){
            return view('error', ['username'=> $request->get('username')]);
        }

        $storeHash = $request->get('store_hash');
        $bcClient = $this->authService->getConfiguredBcClient($storeHash);
        $bcClient->put("https://api.bigcommerce.com/stores/{$storeHash}/v3/tax/connect/SampleTaxProvider", $postData);

        $results = $bcClient->get("https://api.bigcommerce.com/stores/{$storeHash}/v3/tax/connect/SampleTaxProvider");
        if (isset($results->data->username) && $results->data->username !== '') {
            return redirect()->back();
        }

        return view('error', ['username'=> $request->get('username')]);
    }
}
