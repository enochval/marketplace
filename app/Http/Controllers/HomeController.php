<?php


namespace App\Http\Controllers;

use App\Repositories\Contracts\IAdminRepository;
use Exception;

class HomeController extends Controller
{
    /**
     * @var IAdminRepository
     */
    private $adminRepository;

    public function __construct(IAdminRepository $adminRepository)
    {
        $this->middleware('auth:api');


        $this->adminRepository = $adminRepository;
    }

    /**
     * @OA\Get(
     *     path="/utils/cities",
     *     operationId="cities",
     *     tags={"Utils"},
     *     security={{"authorization_token": {}}},
     *     summary="Get available cities",
     *     description="",
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function getCities()
    {
        try {
            $response = $this->adminRepository->getCities();
            return $this->withData($response);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/utils/categories",
     *     operationId="categories",
     *     tags={"Utils"},
     *     security={{"authorization_token": {}}},
     *     summary="Get available categories",
     *     description="",
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function getCategories()
    {
        try {
            $response = $this->adminRepository->getCategories();
            return $this->withData($response);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
