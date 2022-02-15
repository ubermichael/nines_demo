<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Title;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TitleFixtures extends Fixture implements FixtureGroupInterface {
    public static function getGroups() : array {
        return ['test', 'dev'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) : void {
        for ($i = 1; $i <= 5; $i++) {
            $fixture = new Title();
            $fixture->setMain('Main ' . $i);
            $fixture->setSub('Sub ' . $i);
            $fixture->setPrice((float) $i);
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $manager->persist($fixture);
            $this->setReference('title.' . $i, $fixture);
        }
        $manager->flush();
    }
}
