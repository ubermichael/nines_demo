<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends ControllerTestCase {
    /**
     * @dataProvider urlData
     *
     * @param mixed $code
     */
    public function testUrl(string $url, $code = Response::HTTP_OK) : void {
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame($code);
    }

    /**
     * @return string[][]
     */
    public function urlData() : array {
        return [
            ['/'],
            ['/privacy'],
        ];
    }
}
