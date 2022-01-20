<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Bookmark;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class BookmarkFixtures extends Fixture implements FixtureGroupInterface {
    public static function getGroups() : array {
        return ['test'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) : void {
        for ($i = 1; $i <= 5; $i++) {
            $fixture = new Bookmark();
            $fixture->setTitle('Title ' . $i);

            $manager->persist($fixture);
            $this->setReference('bookmark.' . $i, $fixture);
        }
        $manager->flush();
    }
}
