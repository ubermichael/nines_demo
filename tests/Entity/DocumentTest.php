<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    private ?Document $document = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Document::class, $this->document);
    }

    public function testTitle() : void {
        $this->assertSame($this->document, $this->document->setTitle('New Title'));
        $this->assertSame('New Title', $this->document->getTitle());
    }

    public function testDescription() : void {
        $this->assertSame($this->document, $this->document->setDescription('<p>New Description</p>'));
        $this->assertSame('<p>New Description</p>', $this->document->getDescription());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->document = new Document();
    }
}
