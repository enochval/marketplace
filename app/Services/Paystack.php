<?php


namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Paystack
{
    private $base_uri = 'https://api.paystack.co';
    private $client;

    public function __construct()
    {
        $this->setClient();
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     */
    public function setClient(): void
    {
        $this->client = new Client([
            'base_uri' => $this->base_uri,
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @param $bvn
     * @return mixed
     * @throws Exception
     */
    public function bvnVerification($bvn)
    {
        $url_segment = "/bank/resolve_bvn/${bvn}";

        try {

            $response = $this->getClient()->get($url_segment);

            return $this->prettyResponse($response);

        } catch (ClientException $e) {

            throw new Exception($this->errorMessage($e));
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function initialize(array $params)
    {
        $url_segment = '/transaction/initialize';

        try {
            $response = $this->getClient()->post($url_segment, $params);

            return $this->prettyResponse($response);
        } catch (ClientException $e) {

            throw new Exception($this->errorMessage($e));
        }
    }

    /**
     * @param $transaction_reference
     * @return mixed
     * @throws Exception
     */
    public function verifyTransaction(string $transaction_reference)
    {
        $url_segment = '/transaction/verify/'.$transaction_reference;

        try {

            $response = $this->getClient()->get($url_segment);

            return $this->prettyResponse($response);

        } catch (ClientException $e) {

            throw new Exception($this->errorMessage($e));
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function recurring(array $params)
    {
        $url_segment = '/transaction/charge_authorization';

        try {
            $response = $this->getClient()->post($url_segment, $params);

            return $this->prettyResponse($response);
        } catch (ClientException $e) {

            throw new Exception($this->errorMessage($e));
        }
    }

    private function prettyResponse($response, bool $to_array = true)
    {
        return json_decode($response->getBody()->getContents(), $to_array);
    }

    private function errorMessage($err_response): string
    {
        ['message' => $message] = json_decode($err_response->getResponse()->getBody()->getContents(), true);
        return $message;
    }
}
