<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Entity;

use App\Entity\Poem;
use PHPUnit\Framework\TestCase;

class PoemTest extends TestCase {
    private ?Poem $poem = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Poem::class, $this->poem);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->poem = new Poem();
    }
}
