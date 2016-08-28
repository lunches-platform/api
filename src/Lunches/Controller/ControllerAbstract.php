<?php

namespace Lunches\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ControllerAbstract
{
    /**
     * @param string $message
     * @param int $code
     *
     * @param array $data
     * @return JsonResponse
     */
    protected function failResponse($message, $code = 400, array $data = [])
    {
        $responseData = [
            'message' => $message,
        ];
        if (0 !== count($data)) {
            $responseData['errors'] = $data;
        }
        return new JsonResponse($responseData, $code);
    }

    /**
     * @param array|null $data
     * @param $code
     * @param array $headers
     * @return JsonResponse
     */
    protected function successResponse(array $data = null, $code = 200, array $headers = [])
    {
        return new JsonResponse($data, $code, $headers);
    }
    protected function isAccessTokenValid(Request $request)
    {
        $accessToken = $request->get('accessToken');
        $validToken = 'f14d16e1e90dd412d8b29ddb64168f112f753';

        return $accessToken === $validToken;
    }

    protected function authResponse()
    {
        return $this->failResponse('Access token is not valid', 401);
    }

}