<?php
declare(strict_types=1);

namespace bslagter\klas\app;

final class Zipcode
{
    /** @var string */
    private $zipcode;
    /** @var Student[] */
    private $students = [];

    public function __construct(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function getId(): string
    {
        return sha1($this->zipcode);
    }

    public function addStudent(Student $student): self
    {
        $this->students[] = $student;
        return $this;
    }

    /**
     * @return Student[]
     */
    public function getStudents(): array
    {
        return $this->students;
    }

    public function getNumberOfStudents(): int
    {
        return count($this->students);
    }
}
