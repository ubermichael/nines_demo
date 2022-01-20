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
use Exception;
use Nines\MediaBundle\Entity\Link;

class BookmarkFixtures extends Fixture implements FixtureGroupInterface {
    public static function getGroups() : array {
        return ['test'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager) : void {
        for ($i = 1; $i <= 5; $i++) {
            $bookmark = new Bookmark();
            $bookmark->setTitle('Title ' . $i);
            $manager->persist($bookmark);
            $manager->flush();

            $link = new Link();
            $link->setUrl('https://example.com/link/' . $i);
            $link->setText('Text ' . $i);
            $link->setEntity($bookmark);
            $manager->persist($link);
            $manager->flush();

            $this->setReference('bookmark.' . $i, $bookmark);
        }
    }
}
