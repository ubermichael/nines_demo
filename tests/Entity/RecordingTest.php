<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Recording;
use PHPUnit\Framework\TestCase;

class RecordingTest extends TestCase {
    private ?Recording $recording = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Recording::class, $this->recording);
    }

    public function testTitle() : void {
        $this->assertSame($this->recording, $this->recording->setTitle('New Title'));
        $this->assertSame('New Title', $this->recording->getTitle());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->recording = new Recording();
    }
}
