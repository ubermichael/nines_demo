<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Artefact;
use PHPUnit\Framework\TestCase;

class ArtefactTest extends TestCase {
    private ?Artefact $artefact = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Artefact::class, $this->artefact);
    }

    public function testTitle() : void {
        $this->assertSame($this->artefact, $this->artefact->setTitle('New Title'));
        $this->assertSame('New Title', $this->artefact->getTitle());
    }

    public function testDescription() : void {
        $this->assertSame($this->artefact, $this->artefact->setDescription('<p>New Description</p>'));
        $this->assertSame('<p>New Description</p>', $this->artefact->getDescription());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->artefact = new Artefact();
    }
}
