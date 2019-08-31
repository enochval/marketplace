<?php

namespace App\Http\Controllers;

use App\Utils\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use Response;

    /**
     * @OA\OpenApi(
     *     @OA\Info(
     *         version="1.0.0",
     *         title="Timbala",
     *         description="This is the service definitions for Timbala.  You can find out more about Timbala. at [https://timbala.now.sh](https://timbala.now.sh).  For this documentaion, you can use the api key `special-key` to test the authorization filters.",
     *         termsOfService="https://timbala.now.sh/",
     *         @OA\Contact(
     *             email="osarenrenenoch@gmail.com"
     *         ),
     *         @OA\License(
     *             name="Apache 2.0",
     *             url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *         ),
     *         termsOfService="http://swagger.io/terms/"
     *     ),
     *     @OA\Server(
     *         description="local",
     *         url="http://0.0.0.0:8000/api/v1"
     *     ),
     *     @OA\Server(
     *         description="staging",
     *         url="https://"
     *     ),
     *     @OA\ExternalDocumentation(
     *         description="Find out more about SLS MFB",
     *         url="https://timbala.now.sh"
     *     )
     * )
     */
}
