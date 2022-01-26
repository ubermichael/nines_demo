<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Poem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Nines\DublinCoreBundle\DataFixtures\ElementFixtures;
use Nines\DublinCoreBundle\Entity\Value;

class PoemFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
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
            $fixture = new Poem();
            $manager->persist($fixture);
            $this->setReference('poem.' . $i, $fixture);
            $manager->flush();

            for ($j = 1; $j <= 5; $j++) {
                $value = new Value();
                $value->setData("Value {$i}.{$j}");
                $value->setElement($this->getReference('element.' . $j));
                $fixture->addValue($value);
                $manager->persist($value);
            }
            $manager->flush();
        }
    }

    /**
     * @return array<string>
     */
    public function getDependencies() : array {
        return [
            ElementFixtures::class,
        ];
    }
}
