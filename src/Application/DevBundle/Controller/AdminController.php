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

    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Route(pattern="/admin/product/edit")
     */
    public function editAction(Request $request)
    {

    }
}
