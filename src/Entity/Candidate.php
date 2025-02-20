<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $depertement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cni = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $examinationCenter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificateYearBAC = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificateYearGCE = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $registrationDate = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $transactionNumber;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToOne(inversedBy: 'candidate', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $cniIssueDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secondaryEducationStartYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secondaryEducationEndYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $educationSubsystem = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secondCycleStudyType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $baccalaureateCountry = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $baccalaureateSeries = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $baccalaureateMention = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceALevelCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceALevelPapersCount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceALevelGradeACount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceOLevelYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceOLevelPapersCount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentOperator = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $probatoireYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $candidateID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $medicalCertificate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $criminalRecordExtract = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(length: 255)]
    private ?string $birthCertificate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $educationalSystemCheck = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bacSubject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $probatoireSubject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceAlevelSubject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceOlevelSubject = null;

    #[ORM\Column(nullable: true)]
    private ?string $bacSubjectMark = null;

    #[ORM\Column(nullable: true)]
    private ?string $probatoireSubjectMark = null;

    #[ORM\Column(nullable: true)]
    private ?string $gceAlevelSubjectGrade = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gceOlevelSubjectGrade = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $admissionType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getDepertement(): ?string
    {
        return $this->depertement;
    }

    public function setDepertement(?string $depertement): static
    {
        $this->depertement = $depertement;

        return $this;
    }

    public function getCni(): ?string
    {
        return $this->cni;
    }

    public function setCni(?string $cni): static
    {
        $this->cni = $cni;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getExaminationCenter(): ?string
    {
        return $this->examinationCenter;
    }

    public function setExaminationCenter(?string $examinationCenter): static
    {
        $this->examinationCenter = $examinationCenter;

        return $this;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(?string $certificate): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(?string $placeOfBirth): static
    {
        $this->placeOfBirth = $placeOfBirth;

        return $this;
    }

    public function getCertificateYearBAC(): ?string
    {
        return $this->certificateYearBAC;
    }

    public function setCertificateYearBAC(?string $certificateYearBAC): static
    {
        $this->certificateYearBAC = $certificateYearBAC;

        return $this;
    }

    public function getCertificateYearGCE(): ?string
    {
        return $this->certificateYearGCE;
    }

    public function setCertificateYearGCE(?string $certificateYearGCE): static
    {
        $this->certificateYearGCE = $certificateYearGCE;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getTransactionNumber(): ?string
    {
        return $this->transactionNumber;
    }

    public function setTransactionNumber(?string $transactionNumber): static
    {
        $this->transactionNumber = $transactionNumber;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCniIssueDate(): ?\DateTimeInterface
    {
        return $this->cniIssueDate;
    }

    public function setCniIssueDate(?\DateTimeInterface $cniIssueDate): static
    {
        $this->cniIssueDate = $cniIssueDate;

        return $this;
    }

    public function getSecondaryEducationStartYear(): ?string
    {
        return $this->secondaryEducationStartYear;
    }

    public function setSecondaryEducationStartYear(?string $secondaryEducationStartYear): static
    {
        $this->secondaryEducationStartYear = $secondaryEducationStartYear;

        return $this;
    }

    public function getSecondaryEducationEndYear(): ?string
    {
        return $this->secondaryEducationEndYear;
    }

    public function setSecondaryEducationEndYear(?string $secondaryEducationEndYear): static
    {
        $this->secondaryEducationEndYear = $secondaryEducationEndYear;

        return $this;
    }

    public function getEducationSubsystem(): ?string
    {
        return $this->educationSubsystem;
    }

    public function setEducationSubsystem(?string $educationSubsystem): static
    {
        $this->educationSubsystem = $educationSubsystem;

        return $this;
    }

    public function getSecondCycleStudyType(): ?string
    {
        return $this->secondCycleStudyType;
    }

    public function setSecondCycleStudyType(?string $secondCycleStudyType): static
    {
        $this->secondCycleStudyType = $secondCycleStudyType;

        return $this;
    }

    public function getBaccalaureateCountry(): ?string
    {
        return $this->baccalaureateCountry;
    }

    public function setBaccalaureateCountry(?string $baccalaureateCountry): static
    {
        $this->baccalaureateCountry = $baccalaureateCountry;

        return $this;
    }

    public function getBaccalaureateSeries(): ?string
    {
        return $this->baccalaureateSeries;
    }

    public function setBaccalaureateSeries(?string $baccalaureateSeries): static
    {
        $this->baccalaureateSeries = $baccalaureateSeries;

        return $this;
    }

    public function getBaccalaureateMention(): ?string
    {
        return $this->baccalaureateMention;
    }

    public function setBaccalaureateMention(?string $baccalaureateMention): static
    {
        $this->baccalaureateMention = $baccalaureateMention;

        return $this;
    }

    public function getGceALevelCountry(): ?string
    {
        return $this->gceALevelCountry;
    }

    public function setGceALevelCountry(?string $gceALevelCountry): static
    {
        $this->gceALevelCountry = $gceALevelCountry;

        return $this;
    }

    public function getGceALevelPapersCount(): ?string
    {
        return $this->gceALevelPapersCount;
    }

    public function setGceALevelPapersCount(?string $gceALevelPapersCount): static
    {
        $this->gceALevelPapersCount = $gceALevelPapersCount;

        return $this;
    }

    public function getGceALevelGradeACount(): ?string
    {
        return $this->gceALevelGradeACount;
    }

    public function setGceALevelGradeACount(?string $gceALevelGradeACount): static
    {
        $this->gceALevelGradeACount = $gceALevelGradeACount;

        return $this;
    }

    public function getGceOLevelYear(): ?string
    {
        return $this->gceOLevelYear;
    }

    public function setGceOLevelYear(?string $gceOLevelYear): static
    {
        $this->gceOLevelYear = $gceOLevelYear;

        return $this;
    }

    public function getGceOLevelPapersCount(): ?string
    {
        return $this->gceOLevelPapersCount;
    }

    public function setGceOLevelPapersCount(?string $gceOLevelPapersCount): static
    {
        $this->gceOLevelPapersCount = $gceOLevelPapersCount;

        return $this;
    }

    public function getPaymentOperator(): ?string
    {
        return $this->paymentOperator;
    }

    public function setPaymentOperator(?string $paymentOperator): static
    {
        $this->paymentOperator = $paymentOperator;

        return $this;
    }

    public function getProbatoireYear(): ?string
    {
        return $this->probatoireYear;
    }

    public function setProbatoireYear(?string $probatoireYear): static
    {
        $this->probatoireYear = $probatoireYear;

        return $this;
    }

    public function generateCandidateID(EntityManagerInterface $entityManager): void
    {
        do {
            // Generate a random 7-digit number
            $randomNumber = str_pad((string)random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
            $candidateID = '#CUE-' . $randomNumber;

            // Check if this ID already exists in the database
            $existingCandidate = $entityManager->getRepository(Candidate::class)->findOneBy(['candidateID' => $candidateID]);
        } while ($existingCandidate !== null);

        $this->candidateID = $candidateID;
    }
    
    public function getCandidateID(): ?string
    {
        return $this->candidateID;
    }

    public function setCandidateID(?string $candidateID): static
    {
        $this->candidateID = $candidateID;

        return $this;
    }

    public function getBirthCertificate(): ?string
    {
        return $this->birthCertificate;
    }

    public function setBirthCertificate(string $birthCertificate): static
    {
        $this->birthCertificate = $birthCertificate;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    #[ORM\PostPersist]
    public function setRegistrationDateOnCreate(): void
    {
        if ($this->registrationDate === null) {
            $this->registrationDate = new \DateTime();
        }
    }

    public function isEditWindowOpen(): bool
    {
        if ($this->registrationDate === null) {
            return true; // If no registration date, assume it's a new registration
        }

        $now = new \DateTime();
        $diff = $now->diff($this->registrationDate);
        return $diff->days < 7;
    }

    public function getMedicalCertificate(): ?string
    {
        return $this->medicalCertificate;
    }

    public function setMedicalCertificate(?string $medicalCertificate): self
    {
        $this->medicalCertificate = $medicalCertificate;
        return $this;
    }

    public function getCriminalRecordExtract(): ?string
    {
        return $this->criminalRecordExtract;
    }

    public function setCriminalRecordExtract(?string $criminalRecordExtract): self
    {
        $this->criminalRecordExtract = $criminalRecordExtract;
        return $this;
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(?string $transcript): self
    {
        $this->transcript = $transcript;
        return $this;
    }

    public function getEducationalSystemCheck(): ?string
    {
        return $this->educationalSystemCheck;
    }

    public function setEducationalSystemCheck(?string $educationalSystemCheck): static
    {
        $this->educationalSystemCheck = $educationalSystemCheck;

        return $this;
    }

    public function getBacSubject(): ?string
    {
        return $this->bacSubject;
    }

    public function setBacSubject(?string $bacSubject): static
    {
        $this->bacSubject = $bacSubject;

        return $this;
    }

    public function getProbatoireSubject(): ?string
    {
        return $this->probatoireSubject;
    }

    public function setProbatoireSubject(?string $probatoireSubject): static
    {
        $this->probatoireSubject = $probatoireSubject;

        return $this;
    }

    public function getGceAlevelSubject(): ?string
    {
        return $this->gceAlevelSubject;
    }

    public function setGceAlevelSubject(?string $gceAlevelSubject): static
    {
        $this->gceAlevelSubject = $gceAlevelSubject;

        return $this;
    }

    public function getGceOlevelSubject(): ?string
    {
        return $this->gceOlevelSubject;
    }

    public function setGceOlevelSubject(?string $gceOlevelSubject): static
    {
        $this->gceOlevelSubject = $gceOlevelSubject;

        return $this;
    }

    public function getBacSubjectMark(): ?string
    {
        return $this->bacSubjectMark;
    }

    public function setBacSubjectMark(?string $bacSubjectMark): static
    {
        $this->bacSubjectMark = $bacSubjectMark;

        return $this;
    }

    public function getProbatoireSubjectMark(): ?string
    {
        return $this->probatoireSubjectMark;
    }

    public function setProbatoireSubjectMark(?string $probatoireSubjectMark): static
    {
        $this->probatoireSubjectMark = $probatoireSubjectMark;

        return $this;
    }

    public function getGceAlevelSubjectGrade(): ?string
    {
        return $this->gceAlevelSubjectGrade;
    }

    public function setGceAlevelSubjectGrade(?string $gceAlevelSubjectGrade): static
    {
        $this->gceAlevelSubjectGrade = $gceAlevelSubjectGrade;

        return $this;
    }

    public function getGceOlevelSubjectGrade(): ?string
    {
        return $this->gceOlevelSubjectGrade;
    }

    public function setGceOlevelSubjectGrade(?string $gceOlevelSubjectGrade): static
    {
        $this->gceOlevelSubjectGrade = $gceOlevelSubjectGrade;

        return $this;
    }

    public function getAdmissionType(): ?string
    {
        return $this->admissionType;
    }

    public function setAdmissionType(?string $admissionType): static
    {
        $this->admissionType = $admissionType;

        return $this;
    }

}