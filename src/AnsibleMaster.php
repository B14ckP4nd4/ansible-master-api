<?php

namespace BlackPanda\AnsibleMasterApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use function PHPUnit\Framework\isJson;

class AnsibleMaster
{
    protected $url;
    protected $api_username;
    protected $api_password;
    protected $bearerToken;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->api_username = config('ansiblemaster.api_username');
        $this->api_password = config('ansiblemaster.api_password');
        $this->url = config('ansiblemaster.api_url');

        if(empty($this->url) || empty($this->api_username) || empty($this->api_username) )
            throw new \Exception("Ansible Configuration doesn't set properly");

        $this->setBearerToken();
    }

    /**
     * @throws \Exception
     */
    private function setBearerToken(): void
    {
        $bearerToken = Redis::get('ansible_master_bearerToken');

        if(!$bearerToken)
            $this->login();

        if($bearerToken){
            $bearerToken = json_decode($bearerToken,true);

            $this->bearerToken = $bearerToken['token'];

            if( !$this->isBearerTokenValid() )
                $this->login();

        }
    }

    private function login(): void
    {

        $loginAuth = [
            'email' => $this->api_username,
            'password' => $this->api_password,
        ];

        $response = $this->HandleRequest('login',$loginAuth);

        if(!isset($response['access_token']))
            throw new \Exception("Login Failed !");

        Redis::set('ansible_master_bearerToken',json_encode([
            'token' => $response['access_token'],
            'created_at' => Carbon::now()->timestamp,
        ]));

        $this->bearerToken = $response['access_token'];
    }

    private function isBearerTokenValid(): bool
    {
        $status = $this->HandleRequest('status');

        if(isset($status['message']) && $status['message'] == 'Unauthenticated.')
            return false;

        if(isset($status['status']) && $status['status'] == 'ok')
            return true;

        return false;
    }

    private function HandleRequest($route = null,$post=[],$headers = []){

        $url = $this->url;

        if($route){
            $url .= $route;
        }

        $headers[] = 'Accept: application/json';

        if($this->bearerToken)
            $headers[] = 'Authorization: Bearer '.$this->bearerToken;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);

        if(!empty($post))
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $resp = curl_exec($curl);

        curl_close($curl);

        return $this->decodeResponse($resp);
    }

    protected function decodeResponse($response){
        if(isJson($response))
            return json_decode($response,true);

        return $response;
    }

}
