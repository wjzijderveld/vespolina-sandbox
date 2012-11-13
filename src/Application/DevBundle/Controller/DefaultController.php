<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Vespolina\StoreBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homeAction()
    {
        $context = array('taxonomyTerm' => '_all');
        /** @var $manager \Vespolina\ProductBundle\Document\ProductManager */
        $manager = $this->container->get('vespolina.product.product_manager');

        $products = $manager->findBy(array(), array('_id' => 'ASC'));

        $storeHandler = $this->getStoreHandler();
        $storeZone = $storeHandler->resolveStoreZone($context);

        //Get products in this store zone as a doctrine query
//        $productsQuery = $storeHandler->getZoneProducts($storeZone, true, $context);
//        $products = $productsQuery->execute();

        return $this->render('ApplicationDevBundle:Default:home.html.twig', array('products' => $products));
    }

    public function listAction()
    {

    }

    /**
     * @Route("/show/{productId}")
     * @param $productId
     */
    public function showAction($productId)
    {
        /** @var $manager \Vespolina\ProductBundle\Document\ProductManager */
        $manager = $this->container->get('vespolina.product.product_manager');

        $product = $manager->findProductById($productId);

        if (!$product) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->container->get('router')->generate('application_dev_default_home'));
        }

        return $this->render('ApplicationDevBundle:Product:show.html.twig', array('product' => $product));
    }

}
