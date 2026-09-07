<?php

namespace App\Tests\Functional;

class AdminVoterTest extends BaseWebTestCase
{
    public function testAdminCanAssignDistrictToVoter(): void
    {
        $admin = $this->seedAdmin();
        $district = $this->seedDistrict('Import District');
        $voter = new \App\Entity\Voter();
        $voter->setNationalId('NID-VOTER-1');
        $voter->setVoterNumber('VN-0001-000001');
        $voter->setUsername('voter-one');
        $voter->setPassword('x');
        $voter->setFirstName('Sample');
        $voter->setLastName('Voter');
        $voter->setGender('F');
        $voter->setDateOfBirth(new \DateTimeImmutable('-30 years'));
        $voter->setEmail('sample.voter@example.org');
        $voter->setStatus(\App\Entity\Enum\VoterStatus::PENDING);
        $this->em->persist($voter);
        $this->em->flush();

        $client = $this->client;
        $client->loginUser($admin, 'main');
        $client->request('POST', '/admin/voters/'.$voter->getId().'/set-district', ['district_id' => (string) $district->getId()]);
        self::assertResponseRedirects();

        $this->em->refresh($voter);
        self::assertSame($district->getId(), $voter->getDistrict()?->getId());
    }

    public function testAdminCanImportVotersFromCsv(): void
    {
        $admin = $this->seedAdmin();
        $this->seedDistrict('Import District');
        $client = $this->client;
        $client->loginUser($admin, 'main');

        $csv = "national_id,first_name,last_name,gender,date_of_birth,email\n".
            "NID-IMPORT-1,Imported,Voter,M,1985-05-05,imported.voter@example.org\n";
        $this->writeTempCsv($csv);

        $client->request('POST', '/admin/voters/import', ['district_id' => '1'], [
            'csv_file' => new \Symfony\Component\HttpFoundation\File\UploadedFile(
                $this->tempCsv,
                'voters.csv',
                'text/csv',
                null,
                true,
            ),
        ]);
        self::assertResponseRedirects('/admin/voters');

        $voter = $this->em->getRepository(\App\Entity\Voter::class)->findOneBy(['nationalId' => 'NID-IMPORT-1']);
        self::assertNotNull($voter);
        self::assertSame('Imported', $voter->getFirstName());
        self::assertSame('pending', $voter->getStatus()->value);
    }

    private ?string $tempCsv = null;

    private function writeTempCsv(string $content): void
    {
        $path = sys_get_temp_dir().'/votaz_test_import_'.uniqid().'.csv';
        file_put_contents($path, $content);
        $this->tempCsv = $path;
    }

    private function seedDistrict(string $name): \App\Entity\District
    {
        $district = new \App\Entity\District();
        $district->setName($name);
        $district->setCode('DST-'.strtoupper(substr(md5($name), 0, 6)));
        $this->em->persist($district);
        $this->em->flush();

        return $district;
    }
}