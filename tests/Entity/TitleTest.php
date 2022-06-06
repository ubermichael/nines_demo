<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Title;
use PHPUnit\Framework\TestCase;

class TitleTest extends TestCase {
    private ?Title $title = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Title::class, $this->title);
    }

    public function testMain() : void {
        $this->assertSame($this->title, $this->title->setMain('New Main'));
        $this->assertSame('New Main', $this->title->getMain());
    }

    public function testSub() : void {
        $this->assertSame($this->title, $this->title->setSub('New Sub'));
        $this->assertSame('New Sub', $this->title->getSub());
    }

    public function testPrice() : void {
        $this->assertSame($this->title, $this->title->setPrice(1256.0));
        $this->assertSame(1256.0, $this->title->getPrice());
    }

    public function testDescription() : void {
        $this->assertSame($this->title, $this->title->setDescription('<p>New Description</p>'));
        $this->assertSame('<p>New Description</p>', $this->title->getDescription());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->title = new Title();
    }
}
