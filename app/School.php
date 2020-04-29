<?php
declare(strict_types=1);

namespace bslagter\klas\app;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class School
{
    /** @var Address[] */
    private $addresses = [];
    /** @var Zipcode[] */
    private $zipcodes = [];
    /** @var Group[] */
    private $groups = [];

    public function getAddressByString(string $address): Address
    {
        // compare lower case
        $address = strtolower($address);

        // remove spaces around the address
        $address = trim($address);

        // remove multiple whitespace in the address
        $address = preg_replace('/\s+/', ' ', $address);

        if ($address === '') {
            $address = uniqid();
        }

        if (!array_key_exists($address, $this->addresses)) {
            $this->addresses[$address] = new Address($address);
        }

        return $this->addresses[$address];
    }

    public function getZipcodeByString(string $zipcode): Zipcode
    {
        // compare lower case
        $zipcode = strtolower($zipcode);

        // remove spaces around the address
        $zipcode = trim($zipcode);

        // remove multiple whitespace in the address
        $zipcode = preg_replace('/\s+/', ' ', $zipcode);

        if ($zipcode === '') {
            $zipcode = uniqid();
        }

        if (!array_key_exists($zipcode, $this->zipcodes)) {
            $this->zipcodes[$zipcode] = new Zipcode($zipcode);
        }

        return $this->zipcodes[$zipcode];
    }

    public function getGroupByName(string $name): Group
    {
        if (!array_key_exists($name, $this->groups)) {
            $this->groups[$name] = new Group($name);
        }

        return $this->groups[$name];
    }

    public function distribute(int $nrSegments): void
    {
        $this->assignSegmentUsingAddress($nrSegments);
        $this->assignSegmentToSingles($nrSegments);
    }

    private function assignSegmentUsingAddress(int $nrSegments): void
    {
        $i = 0;
        foreach ($this->addresses as $address) {
            if ($address->getNumberOfStudents() < 2) {
                continue;
            }

            $segment = $i % $nrSegments;
            $reason = Student::REASON_FROM_ADDRESS;
            foreach ($address->getStudents() as $student) {
                if ($student->getPreference() > 0) {
                    $segment = $student->getPreference() - 1;
                    $reason = Student::REASON_FROM_PREFERENCE;
                }

                $student->setSegment($segment, $reason);
            }
            $i++;
        }
    }

    private function assignSegmentToSingles(int $nrSegments): void
    {
        foreach ($this->groups as $group) {
            $group->assignSegmentToSingles($nrSegments);
        }
    }

    public function asArray(): array
    {
        $array = [];

        foreach ($this->groups as $group) {
            $array[$group->getName()] = [];
            foreach ($group->getStudents() as $student) {
                $array[$group->getName()][] = [
                    'name' => $student->getName(),
                    'address' => $student->getAddress()->getAddress(),
                    'segment' => $student->getSegment(),
                    'reason' => $student->getSegmentReason(),
                ];
            }
        }

        return $array;
    }

    /**
     * @param string $path
     * @return static
     * @throws Exception
     */
    public static function fromSpreadsheet(string $path): self
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        #todo check header / nr of columns

        $school = new self();

        foreach ($sheet->getRowIterator(2) as $row) {

            $groupName = (string) $sheet->getCell('A' . $row->getRowIndex())->getValue();
            $studentName = (string) $sheet->getCell('B' . $row->getRowIndex())->getValue();
            $addressString = (string) $sheet->getCell('C' . $row->getRowIndex())->getValue();
            $zipcodeString = (string) $sheet->getCell('D' . $row->getRowIndex())->getValue();
            $preference = (int) $sheet->getCell('E' . $row->getRowIndex())->getValue();

            if ($groupName === "" || $studentName === "") {
                continue;
            }

            $group = $school->getGroupByName($groupName);
            $address = $school->getAddressByString($addressString);
            $zipcode = $school->getZipcodeByString($zipcodeString);

            $student = new Student($group, $studentName, $address, $zipcode, $preference);

            $group->addStudent($student);
            $address->addStudent($student);
            $zipcode->addStudent($student);
        }

        return $school;
    }

    /**
     * @throws Exception
     */
    public function toSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getCell('A1')->setValue('Klas');
        $sheet->getCell('B1')->setValue('Leerling');
        $sheet->getCell('C1')->setValue('Adres');
        $sheet->getCell('D1')->setValue('Postcode');
        $sheet->getCell('E1')->setValue('Segment');
        $sheet->getCell('F1')->setValue('Voorkeur');
        $sheet->getCell('G1')->setValue('Ingedeeld op');

        $i = 2;
        foreach ($this->groups as $group) {
            foreach ($group->getStudents() as $student) {

                $sheet->getCell('A' . $i)->setValue($group->getName());
                $sheet->getCell('B' . $i)->setValue($student->getName());
                $sheet->getCell('C' . $i)->setValue($student->getAddress()->getAddress());
                $sheet->getCell('D' . $i)->setValue($student->getZipcode()->getZipcode());
                $sheet->getCell('E' . $i)->setValue($student->getSegmentPlusOne());
                $sheet->getCell('F' . $i)->setValue($student->getPreference());
                $sheet->getCell('G' . $i)->setValue($student->getSegmentReason());
                $i++;
            }
        }

        return $spreadsheet;
    }
}
