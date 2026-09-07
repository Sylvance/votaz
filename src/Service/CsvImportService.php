<?php

namespace App\Service;

use App\Entity\District;
use App\Entity\Enum\VoterStatus;
use App\Entity\Voter;
use Doctrine\ORM\EntityManagerInterface;

final class CsvImportService
{
    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     *
     * @throws \InvalidArgumentException on malformed rows
     */
    public function importVoters(string $csvContent, ?District $defaultDistrict): array
    {
        $errors = [];
        $imported = 0;
        $skipped = 0;

        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent)) ?: [];
        $rows = array_map(static fn (string $line): array => str_getcsv($line, ',', '"', ''), $lines);

        // Drop a header row if the first line looks like a header.
        if ([] !== $rows && isset($rows[0][0]) && !is_numeric($rows[0][0])) {
            array_shift($rows);
        }

        foreach ($rows as $i => $row) {
            $lineNo = $i + 2;
            if (\count($row) < 5) {
                $errors[] = sprintf('Row %d: expected at least 5 columns (national ID, first name, last name, gender, date of birth).', $lineNo);
                ++$skipped;

                continue;
            }

            [$nationalId, $firstName, $lastName, $gender, $dob] = array_map('trim', array_slice($row, 0, 5));
            $email = isset($row[5]) ? trim($row[5]) : null;

            if ('' === $nationalId || '' === $firstName || '' === $lastName) {
                $errors[] = sprintf('Row %d: national ID, first name and last name are required.', $lineNo);
                ++$skipped;

                continue;
            }

            if (null !== $this->em->getRepository(Voter::class)->findOneByNationalId($nationalId)) {
                $errors[] = sprintf('Row %d: voter with national ID %s already exists.', $lineNo, $nationalId);
                ++$skipped;

                continue;
            }

            try {
                $dateOfBirth = new \DateTimeImmutable($dob);
            } catch (\Exception) {
                $errors[] = sprintf('Row %d: invalid date of birth "%s".', $lineNo, $dob);
                ++$skipped;

                continue;
            }

            if (!\in_array(mb_strtoupper($gender), ['M', 'F'], true)) {
                $errors[] = sprintf('Row %d: gender must be M or F.', $lineNo);
                ++$skipped;

                continue;
            }

            if (null !== $email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = sprintf('Row %d: invalid email "%s".', $lineNo, $email);
                ++$skipped;

                continue;
            }

            $voter = new Voter();
            $voter->setNationalId(preg_replace('/\s+/', '', $nationalId));
            $voter->setFirstName($firstName);
            $voter->setLastName($lastName);
            $voter->setGender(mb_strtoupper($gender));
            $voter->setDateOfBirth($dateOfBirth);
            $voter->setEmail($email);
            $voter->setDistrict($defaultDistrict);
            $voter->setStatus(VoterStatus::PENDING);

            $this->registrationService->createVoter($voter);

            ++$imported;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    public function __construct(
        private EntityManagerInterface $em,
        private VoterRegistrationService $registrationService,
    ) {
    }
}
