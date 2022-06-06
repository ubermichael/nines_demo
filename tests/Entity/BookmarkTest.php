<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Bookmark;
use PHPUnit\Framework\TestCase;

class BookmarkTest extends TestCase {
    private ?Bookmark $bookmark = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Bookmark::class, $this->bookmark);
    }

    public function testTitle() : void {
        $this->assertSame($this->bookmark, $this->bookmark->setTitle('New Title'));
        $this->assertSame('New Title', $this->bookmark->getTitle());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->bookmark = new Bookmark();
    }
}
