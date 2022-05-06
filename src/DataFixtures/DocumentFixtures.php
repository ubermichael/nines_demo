<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Service\PdfManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentFixtures extends Fixture implements FixtureGroupInterface {
    public const FILES = [
        'holmes_1.pdf',
        'holmes_2.pdf',
        'holmes_3.pdf',
        'holmes_4.pdf',
        'holmes_5.pdf',
    ];

    private ?PdfManager $manager = null;

    public static function getGroups() : array {
        return ['test', 'dev'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $document = new Document();
            $document->setTitle('Title ' . $i);
            $document->setDescription("<p>This is paragraph {$i}</p>");
            $manager->persist($document);
            $manager->flush();

            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/lib/Nines/MediaBundle/Tests/data/pdf/' . $file, $file, 'application/pdf', null, true);
            $pdf = new Pdf();
            $pdf->setFile($upload);
            $pdf->setPublic(0 === ($i % 2));
            $pdf->setOriginalName($file);
            $pdf->setDescription("<p>This is paragraph {$i}</p>");
            $pdf->setLicense("<p>This is paragraph {$i}</p>");
            $pdf->setEntity($document);

            $manager->persist($pdf);
            $manager->flush();

            $this->setReference('document.' . $i, $document);
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(PdfManager $manager) : void {
        $this->manager = $manager;
    }
}
