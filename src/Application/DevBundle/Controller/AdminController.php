<?php
/**
 * This file and its content is copyright of Beeldspraak Website Creators BV - (c) Beeldspraak 2012. All rights reserved.
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 *
 * @author      Beeldspraak <info@beeldspraak.com>
 * @copyright   Copyright 2012, Beeldspraak Website Creators BV
 * @link        http://beeldspraak.com
 *
 */
namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Application\DevBundle\Form\ProductType;
use Application\Vespolina\ProductBundle\Document\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Route(pattern="/admin/product/list")
     */
    public function listProductAction(Request $request)
    {
        /** @var $manager \Vespolina\ProductBundle\Document\ProductManager */
        $manager = $this->get('vespolina.product_manager');
        $products = $manager->findBy(array());

        return $this->render('ApplicationDevBundle:Admin:list.html.twig', array('products' => $products));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Route(pattern="/admin/product/create")
     */
    public function createAction(Request $request)
    {
        /** @var $manager \Vespolina\ProductBundle\Document\ProductManager */
        $manager = $this->get('vespolina.product_manager');
        $product = $manager->createProduct();

        $form = $this->createForm(new ProductType(), $product);

        return $this->render('ApplicationDevBundle:Admin:create.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Route(pattern="/admin/product/edit/{productId}")
     */
    public function editAction($productId)
    {
        $productManager = $this->get('vespolina.product_manager');
        $product = $productManager->findProductById($productId);

        if (!$product) {
            $this->get('session')->setFlash('notice', sprintf('Object %s does not exists', $productId));
            return new RedirectResponse($this->get('router')->generate('application_dev_admin_listproduct'));
        }

        $form = $this->createForm(new ProductType(), $product);

        if ($this->getRequest()->isMethod('POST')) {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $productManager->updateProduct($product);
            }
        }


        return $this->render('ApplicationDevBundle:Admin:edit.html.twig', array('form' => $form->createView(), 'product' => $product));
    }
}
