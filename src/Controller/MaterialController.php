<?php

namespace App\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Material;
use App\Entity\Concept;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MaterialController
 * @package App\Controller
 * @OA\Tag(name="Material")
 */
class MaterialController extends AbstractFOSRestController
{

    const RDF_ABOUT_LIMIT = 10;

    /**
     * Search materials.
     * @OA\Response(
     *  response=200,
     *  description="Material list.",
     *  @OA\JsonContent(type="array",
     *      @OA\Items(allOf={@OA\Schema(ref=@Model(type=Material::class))},
     *      @OA\Property(property="concept", type="array", @OA\Items(ref=@Model(type=Concept::class)))))
     * )
     * @OA\Response(response="404", description="No material found.")
     * @OA\Response(response="400", description="Too much rdfAbout in parameter.")
     * 
     * @OA\RequestBody(description="Search parameters",
     *     @OA\Schema(type="object",
     *         @OA\Property(property="rdfAboutConceptFilter")))
     * 
     * @Rest\Post(path="/material", name="app_material_list")
     * @Rest\RequestParam(name="rdfAboutConceptFilter", default={}, description="rdfAbout list of concept used to filter material list.")
     * @Rest\View
     * 
     * @param string[] $rdfAboutConceptFilter
     * @return View
     */
    public function listAction(array $rdfAboutConceptFilter): View
    {
        if (count($rdfAboutConceptFilter) > self::RDF_ABOUT_LIMIT) {
            return View::create('Too much rdfAbout in parameter. Max ' . self::RDF_ABOUT_LIMIT, Response::HTTP_BAD_REQUEST);
        }

        $repo = $this->getDoctrine()->getRepository(Material::class);
        $materials = $repo->findAllMaterial($rdfAboutConceptFilter);

        if (count($materials) === 0) {
            return View::create('No material found.', Response::HTTP_NOT_FOUND);
        }

        return View::create($materials, Response::HTTP_OK);
    }
}
