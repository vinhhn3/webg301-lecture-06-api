<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/products", name="api_products_")
 */
class ProductsController extends AbstractController
{
    private $productRepository;
    private $serializer;
    private $validator;

    public function __construct(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/view", name="products_view")
     */
    public function view(): Response
    {
        return $this->render('products/index.html.twig');
    }

    /**
     * @Route("/", name="list", methods={"GET"})
     */
    public function list(): Response
    {
        $products = $this->productRepository->findAll();
        $data = $this->serializer->serialize($products, 'json');

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show($id): Response
    {
        $product = $this->productRepository->find($id);

        $data = $this->serializer->serialize($product, 'json');

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $data = $request->getContent();
        $product = $this->serializer->deserialize($data, Product::class, 'json');

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return new Response($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return new Response(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(Request $request, $id): Response
    {
        $data = $request->getContent();
        $updatedProduct = $this->serializer->deserialize($data, Product::class, 'json');

        $errors = $this->validator->validate($updatedProduct);
        if (count($errors) > 0) {
            return new Response($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }
        $product = $this->productRepository->find($id);

        $product->setName($updatedProduct->getName());
        $product->setPrice($updatedProduct->getPrice());

        $this->getDoctrine()->getManager()->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $product = $this->productRepository->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
