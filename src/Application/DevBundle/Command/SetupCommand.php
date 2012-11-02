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
namespace Application\DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class SetupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:rebuild')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Rebuild all')
            ->addOption('taxonomy', null, InputOption::VALUE_NONE, 'Rebuild Taxonomy')
            ->addOption('products', null, InputOption::VALUE_NONE, 'Rebuild Products')
            ->addOption('store', null, InputOption::VALUE_NONE, 'Rebuild Store')
            ->setDescription('Reinstalls the complete shop')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Set GeneratorBundle dialog Helper
        /** @var $dialog DialogHelper */
        $this->getHelperSet()->set($dialog = new DialogHelper());

        // Drop old data
        $this->dropDatabase($input, $output);

        if ($input->getOption('store') || $input->getOption('all')) {
            $this->buildStore($input, $output);
        }

        if ($input->getOption('taxonomy') || $input->getOption('all')) {
            $this->buildTaxonomy($input, $output);
        }

        if ($input->getOption('products') || $input->getOption('all')) {
            $this->buildProducts($input, $output);
        }
    }

    protected function buildStore(InputInterface $input, OutputInterface $output)
    {
        /** @var $storeManager \Vespolina\StoreBundle\Document\StoreManager */
        $storeManager = $this->getContainer()->get('vespolina.store_manager');

        /** @var $store \Vespolina\StoreBundle\Document\Store */
        $store = $storeManager->createStore('default_store', 'Willem-Jan.net');

        $storeManager->updateStore($store);

        $output->writeln(sprintf('<info>Created "default_store"</info>'));
    }

    protected function buildTaxonomy(InputInterface $input, OutputInterface $output)
    {
        // Generate new data
        $output->writeln($this->getHelper('formatter')->formatSection('Taxonomy', 'Creating '));

        /** @var $taxManager \Vespolina\TaxonomyBundle\Document\TaxonomyManager */
        $taxManager = $this->getContainer()->get('vespolina.taxonomy_manager');

        $taxonomy = $taxManager->createTaxonomy('products', 'tags');
        $taxonomy->addTerm($taxManager->createTerm('Nature'));
        $taxonomy->addTerm($taxManager->createTerm('Animals'));
        $taxonomy->addTerm($taxManager->createTerm('People'));
        $taxManager->updateTaxonomy($taxonomy);

        $output->writeln(sprintf('<info>Created Taxonomy %s with 3 terms</info>', 'products'));
    }

    protected function buildProducts(InputInterface $input, OutputInterface $output)
    {
        /** @var $taxManager \Vespolina\TaxonomyBundle\Document\TaxonomyManager */
        $taxManager = $this->getContainer()->get('vespolina.taxonomy_manager');
        /** @var $productManager \Vespolina\ProductBundle\Document\ProductManager */
        $productManager = $this->getContainer()->get('vespolina.product_manager');
        $product = $productManager->findProductBySlug('rabbit-from-blijdorp-zoo');
        if ($product) {
            $productManager->deleteProduct($product);
        }

        $features = array();
        $feature = new \Vespolina\ProductBundle\Document\Attribute();
        $feature->setName('pixel_width');
        $feature->setType('integer');
        $features[] = $feature;

        $feature = new \Vespolina\ProductBundle\Document\Attribute();
        $feature->setName('pixel_height');
        $feature->setType('integer');
        $features[] = $feature;

        $names = array(
            'Day at Blijdorp',
            'Happy walking',
            'Shooting polarbears',
            'Skating on water',
            'Walking in the woods',
            'Feeding cows',
            'Crafting wooden shoes',
            'Models are hard',
            'Coding like crazy',
        );

        for ($i = 0; $i < 9; $i++) {
            /** @var $product \Vespolina\Entity\ProductInterface */
            $product = $productManager->createProduct();
            $product->setName($names[$i]);
            $product->setSlug(preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($names[$i])));
            $product->setAttributes($features);

            $productManager->updateProduct($product);
        }

        $output->writeln('<comment>Created single product with 2 features</comment>');
    }

    private function dropDatabase(InputInterface $input, OutputInterface $output)
    {
        /** @var $conn \Doctrine\MongoDB\Connection */
        $conn = $this->getContainer()->get('doctrine_mongodb.odm.default_connection');
        /** @var $conf \Doctrine\ODM\MongoDB\Configuration */
        $conf = $conn->getConfiguration();
        $defaultDB = $conf->getDefaultDB();

        if ('y' !== $this->getDialog()->ask($output, $this->getDialog()->getQuestion(sprintf('Are you sure you want to drop database "%s"? (y/n) ', $defaultDB), 'n'), 'n')) {
            $output->writeln(sprintf('<comment>Kept database "%s"</comment>', $defaultDB));
            return;
        }

        if ($conn->dropDatabase($defaultDB)) {
            $output->writeln(sprintf('<comment>Dropped database "%s"</comment>', $defaultDB));
        } else {
            $output->writeln(sprintf('<error>Failed to drop database "%s"</error>', $defaultDB));
        }
    }

    /**
     * @return DialogHelper
     */
    protected function getDialog()
    {
        return $this->getHelper('dialog');
    }
}
