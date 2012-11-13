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
        $store->setDefaultCurrency('EUR');
        $store->setTaxationEnabled(true);

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
        try {
            $products = $productManager->findBy(array());
            if ($products) {
                foreach ($products as $product) {
                    $assets = $product->getImages();
                    foreach ($assets as $asset) {
                        try {
                            $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->remove($asset);
                        } catch (\Exception $e) {
                            //
                        }
                    }
                    $productManager->deleteProduct($product);
                }

            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->flush();

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
            'And then some',
            'Being sick aint nice',
            'What else to do',
        );

        $images = array(
            'demo1.jpg',
            'demo2.jpg',
            'demo3.jpg',
            'demo4.jpg',
            'demo5.jpg',
            'demo6.jpg',
            'demo7.jpg',
            'demo8.jpg',
            'demo9.jpg',
            'demo10.jpg',
            'demo11.jpg',
            'demo12.jpg',
        );

        $descriptions = array(
            'Proin at eros non eros adipiscing mollis. Donec semper turpis sed diam. Sed consequat ligula nec tortor. Integer eget sem. Ut vitae enim eu est vehicula gravida. Morbi ipsum ipsum, porta nec, tempor id, auctor vitae, purus. Pellentesque neque. Nulla luctus erat vitae libero. Integer nec enim. Phasellus aliquam enim et tortor. Quisque aliquet, quam elementum condimentum feugiat, tellus odio consectetuer wisi, vel nonummy sem neque in elit. Curabitur eleifend wisi iaculis ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In non velit non ligula laoreet ultrices. Praesent ultricies facilisis nisl. Vivamus luctus elit sit amet mi. Phasellus pellentesque, erat eget elementum volutpat, dolor nisl porta neque, vitae sodales ipsum nibh in ligula. Maecenas mattis pulvinar diam. Curabitur sed leo.',
            'Nunc mauris ligula, dapibus non cursus posuere, congue a eros. Ut auctor, nibh in porta iaculis, risus enim mollis dolor, sed dignissim lorem mauris vitae eros. Ut volutpat aliquam ullamcorper. Ut a lectus a enim congue placerat. Aenean vehicula auctor urna, at semper sapien aliquet condimentum. Maecenas luctus adipiscing malesuada. Donec non turpis a diam laoreet vestibulum. Ut tristique facilisis imperdiet. Nulla elit orci, luctus vitae venenatis eu, convallis at nibh.',
            'Aenean id rhoncus sapien. Integer eget nunc enim. Vivamus non risus non dolor cursus aliquam vitae et urna. Ut eget bibendum diam. Curabitur vehicula, risus nec semper auctor, leo tortor pretium est, nec pellentesque leo velit at lacus. Vivamus vel erat et risus sodales vestibulum. Aenean lacinia enim nec metus adipiscing eget ullamcorper ipsum interdum. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque eget accumsan risus. Vivamus justo elit, laoreet vel tristique sit amet, malesuada vel quam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Curabitur lacinia pulvinar orci, ut iaculis velit egestas id. Nulla facilisi. Suspendisse blandit aliquet laoreet. Nulla commodo, lorem vitae rutrum lacinia, orci nisi semper justo, a sagittis enim eros eget risus. Nulla molestie consectetur est quis rutrum.',
            'In eget urna massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In porttitor sodales porta. Nulla et elit in arcu pretium pharetra. Nulla diam felis, lobortis at tempor a, fermentum vitae velit. Quisque ac velit non diam elementum blandit vitae et ipsum. Nulla interdum semper metus vitae tempus. Pellentesque ultricies leo bibendum dui accumsan eget vulputate eros consectetur. Morbi egestas tincidunt tellus, vitae venenatis erat mollis id.',
            'Curabitur pretium tristique diam. Nulla facilisi. Integer purus nisl, varius ac malesuada in, tincidunt et lectus. Nullam pulvinar odio id lorem mollis sed lacinia nulla blandit. Suspendisse potenti. Quisque gravida eros quis velit interdum condimentum. Aliquam lacinia, metus vitae porttitor accumsan, erat sem dictum sapien, scelerisque dapibus lectus nunc in lacus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque pharetra nulla in augue porta malesuada. Sed porttitor consequat feugiat. Vestibulum pulvinar dictum hendrerit. Donec vulputate metus id est tristique in sollicitudin purus aliquam. Curabitur non odio sed risus sollicitudin facilisis. Vivamus ultricies pulvinar sapien vel tempus. Proin placerat fermentum sem, id semper nulla auctor et. Phasellus lobortis, nulla ut euismod laoreet, ligula dolor viverra turpis, vel ullamcorper nisi diam et enim.',
            'Cras mollis ultricies lectus, eget gravida odio rutrum a. Curabitur neque arcu, scelerisque ac ultrices ut, volutpat eu leo. Pellentesque nec orci felis. Nam in sem ut orci tristique varius. Nulla ultricies sodales blandit. Sed at justo ut lorem viverra cursus. Phasellus sed enim odio, vel feugiat odio. Vestibulum ornare suscipit ipsum et sagittis. Sed vitae magna mauris, ut imperdiet nisi. Suspendisse luctus mauris ac tellus imperdiet quis molestie lectus volutpat. Sed fermentum, erat sit amet tempor commodo, lorem augue ullamcorper erat, non iaculis dolor nulla quis felis. Mauris ultricies nunc non ipsum eleifend nec ultrices nisi fermentum.',
            'Maecenas viverra mauris sed massa iaculis a semper erat ultricies. Cras id viverra lacus. Quisque et dolor et augue sagittis placerat sit amet et neque. Pellentesque feugiat scelerisque neque ut porttitor. Fusce sit amet mi nec leo gravida convallis. Quisque sollicitudin, metus quis gravida interdum, dolor dolor sollicitudin velit, sit amet elementum massa justo at est. Fusce non tellus lacus. Aliquam aliquam gravida consectetur. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Curabitur dignissim faucibus odio id auctor. Aliquam erat volutpat.',
        );

        for ($i = 0; $i < 12; $i++) {
            /** @var $product \Application\DevBundle\Document\Product */
            $product = $productManager->createProduct();
            $pricing = $this->generateRandomPricing();
            $product->setName($names[$i]);
            $product->setSlug(preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($names[$i])));
            $product->setDescription($descriptions[array_rand($descriptions)]);
            $product->setAttributes($features);
            $product->setPricing($pricing);
            $product->setType('default');


            // Pick a random image from above
            $imageFile = array_shift($images);
            if (null !== $imageFile) {

                $image = $this->createImage($imageFile);

                $productImages = array($image);

                if (isset($images[0])) {
                    $productImages[] = $this->createImage($images[0]);
                }

                $product->setImages($productImages);
            }

            $productManager->updateProduct($product);
        }

        $output->writeln('<comment>Created 9 products with 2 features and pricing</comment>');
    }

    private function generateRandomPricing()
    {
        $pricing = array();
        $pricing['netUnitPrice']    = rand(2,40);
        $pricing['unitPriceMSRP']   = rand(2,40);
        $pricing['unitPriceTax']    = rand(2,40);
        $pricing['unitPriceTotal']  = rand(2,40);
        $pricing['unitMSRPTotal']   = rand(2,40);

        return $pricing;
    }

    /**
     * @param $fileName
     * @return \Application\DevBundle\Document\Asset
     */
    private function createImage($fileName)
    {
        $image = new \Application\DevBundle\Document\Asset();

        // Create a tmpfile
        $tmpFile = tempnam(sys_get_temp_dir(), 'prd');
        // Copy file to tmpfile
        copy(__DIR__ . '/../Resources/images/' . $fileName, $tmpFile);
        // Create a new File
        $file = new \Symfony\Component\HttpFoundation\File\File($tmpFile);
        // Set the file
        $image->setFile($file);
        $image->setMime('image/jpeg');

        return $image;
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
