<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Swagger\Annotations as SWG;

/**
 * Class AppBundle.
 */
class AppBundle extends Bundle
{
    /**
     * @SWG\Info(
     *   title="Lunches API",
     *   description="REST API of Lunches platform e-commerce solution",
     *   version="1.0.0",
     *   @SWG\Contact(
     *     name="Lunches API Team",
     *     url="https://lunches.com.ua",
     *     email="support@lunches.com.ua",
     *   )
     * )
     * @SWG\Swagger(
     *     basePath="/",
     *     schemes={"http","https"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     swagger="2.0",
     * )
     * @SWG\Tag(
     *     name="Menus",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Orders",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Users",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Transactions",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Prices",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Dishes",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Dish Images",
     *     description="",
     * ),
     * @SWG\Tag(
     *     name="Images",
     *     description="",
     * ),
     * @SWG\Definition(
     *     definition="Error",
     *     required={"code", "message"},
     *     @SWG\Property(
     *         property="code",
     *         type="integer",
     *     ),
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="errors",
     *         type="array",
     *     )
     * )
     * @SWG\Parameter(
     *     name="accessToken",
     *     in="query",
     *     description="Authentication token to access restricted resources",
     *     required=true,
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="dishId",
     *     in="path",
     *     description="ID of dish",
     *     required=true,
     *     type="integer",
     * ),
     * @SWG\Parameter(
     *     name="imageId",
     *     in="path",
     *     description="ID of image",
     *     required=true,
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="startDate",
     *     in="query",
     *     description="Filter data which _date_ value greater than **startDate**",
     *     type="string",
     *     format="date",
     * ),
     * @SWG\Parameter(
     *     name="endDate",
     *     in="query",
     *     description="Filter data which _date_ value less than **endDate**",
     *     type="string",
     *     format="date",
     * ),
     * @SWG\Parameter(
     *     name="orderId",
     *     in="path",
     *     description="ID of Order",
     *     type="integer",
     *     required=true,
     * ),
     * @SWG\Parameter(
     *     name="username",
     *     in="path",
     *     type="string",
     *     description="User name",
     *     required=true,
     * ),
     * @SWG\Parameter(
     *     name="transactionId",
     *     in="path",
     *     description="Transaction ID",
     *     type="string",
     *     required=true,
     * ),
     * @SWG\Parameter(
     *     description="Filter items by LIKE pattern",
     *     type="string",
     *     in="query",
     *     name="like",
     * ),
     * @SWG\Parameter(
     *     name="shipmentDate",
     *     description="Filter orders which will be shipped on specified date",
     *     type="string",
     *     format="date",
     *     in="query",
     * ),
     */
}
