<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\Repository\RecordingRepository;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\MediaBundle\Service\AudioManager;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class RecordingTest extends ControllerTestCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    private const TYPEAHEAD_QUERY = 'title';

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/recording/');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/recording/1');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/1');
        $this->assertResponseIsSuccessful();
        // One for the recording, one for the audio file.
        $this->assertSame(2, $crawler->selectLink('Edit')->count());
    }

    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/recording/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(5, $json);
    }

    public function testUserTypeahead() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/recording/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(5, $json);
    }

    public function testAdminTypeahead() : void {
        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/recording/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(5, $json);
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/recording/search');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'recording',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'recording',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'recording',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/recording/1/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/recording/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'recording[title]' => 'Updated Title',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/recording/new');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testAnonNewPopup() : void {
        $crawler = $this->client->request('GET', '/recording/new_popup');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testUserNewPopup() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/recording/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'recording[title]' => 'Updated Title',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/6', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminNewPopup() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/recording/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'recording[title]' => 'Updated Title',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/7', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        /** @var RecordingRepository $repo */
        $repo = self::$container->get(RecordingRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/5');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/recording/5"] button')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/recording/', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAnonNewAudio() : void {
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/1/new_audio');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'audio[public]' => 1,
            'audio[description]' => 'Description',
            'audio[license]' => 'License',
        ]);
        $form['audio[file]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditAudio() : void {
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/1/edit_audio/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'audio[public]' => 0,
            'audio[description]' => 'Updated Description',
            'audio[license]' => 'Updated License',
        ]);
        $form['audio[newFile]']->upload(dirname(__FILE__, 3) . '/lib/Nines/MediaBundle/Tests/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeleteAudio() : void {
        $crawler = $this->client->request('DELETE', '/recording/1/delete_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeleteAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/recording/1/delete_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeleteAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/recording/4/delete_audio/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/recording/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/recording/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/recording/4/delete_audio/4"]')->form();
        $form->getNode()->setAttribute('action', '/recording/3/delete_audio/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }
}
