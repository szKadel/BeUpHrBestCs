<?php

namespace Functional\Api\Vacation;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Vacation\VacationFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class VacationFileObjectTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testCreateAMediaObject(): void
    {
        $file = new UploadedFile('files/image.jpg', 'image.jpg');
        $client = self::createClient();

        $client->request('POST', '/api/vacationFile', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                // If you have additional fields in your MediaObject entity, use the parameters.
                'parameters' => [
                    'title' => 'My file uploaded',
                ],
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(VacationFile::class);
        $this->assertJsonContains([
            'title' => 'My file uploaded',
        ]);
    }
}