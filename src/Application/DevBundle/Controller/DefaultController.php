<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/create/categories")
     */
    public function categoryAction()
    {


        return new Response("Recreated category taxonomy");
    }

    /**
     * @Route("/create/products")
     */
    public function productAction()
    {
        /** @var $taxManager \Vespolina\TaxonomyBundle\Document\TaxonomyManager */
        $taxManager = $this->get('vespolina.taxonomy_manager');
        /** @var $productManager \Vespolina\ProductBundle\Document\ProductManager */
        $productManager = $this->get('vespolina.product_manager');
        $product = $productManager->findProductBySlug('rabbit-from-blijdorp-zoo');
        if ($product) {
            $productManager->deleteProduct($product);
        }

        $taxonomy = $taxManager->findBy(array('name' => 'products'));

        $features = array();
        $feature = new \Vespolina\ProductBundle\Document\Feature();
        $feature->setName('pixel_width');
        $feature->setType('integer');
        $features[] = $feature;

        $feature = new \Vespolina\ProductBundle\Document\Feature();
        $feature->setName('pixel_height');
        $feature->setType('integer');
        $features[] = $feature;

        /** @var $product \Vespolina\Entity\ProductInterface */
        $product = $productManager->createProduct();
        $product->setName('Rabbit from Blijdorp Zoo');
        $product->setSlug('rabbit-from-blijdorp-zoo');
        $product->setFeatures($features);

        $productManager->updateProduct($product);

        return new Response("Recreated products");
    }

    /**
     * @Route("/create/product-prices")
     */
    public function productPricesAction()
    {
        /** @var $productManager \Vespolina\ProductBundle\Document\ProductManager */
        $productManager = $this->get('vespolina.product_manager');
        $product = $productManager->findProductBySlug('rabbit-from-blijdorp-zoo');
    }
}
