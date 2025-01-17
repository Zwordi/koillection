<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Attribute\Upload;
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Enum\DateFormatEnum;
use App\Enum\LocaleEnum;
use App\Enum\RoleEnum;
use App\Enum\VisibilityEnum;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "koi_user")]
#[ORM\Index(name: "idx_user_visibility", columns: ["visibility"])]
#[UniqueEntity(fields: ["email"], message: "error.email.not_unique")]
#[UniqueEntity(fields: ["username"], message: "error.username.not_unique")]
#[ApiResource(
    normalizationContext: ["groups" => ["user:read"]],
    denormalizationContext: ["groups" => ["user:write"]],
    collectionOperations: ["get"],
    itemOperations: ["get", "put", "patch"]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, BreadcrumbableInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36, unique: true, options: ["fixed" => true])]
    #[Groups(["user:read"])]
    private string $id;

    #[ORM\Column(type: "string", length: 32, unique: true)]
    #[Assert\Regex(pattern: "/^[a-z\d_]{2,32}$/i", message: "error.username.incorrect")]
    #[Groups(["user:read", "user:write"])]
    private ?string $username = null;

    #[ORM\Column(type: "string", unique: true)]
    #[Assert\Email]
    #[Groups(["user:read", "user:write"])]
    private ?string $email = null;

    #[ORM\Column(type: "string")]
    private ?string $password;

    #[Assert\Regex(pattern: "/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Za-z]).*$/", message: "error.password.incorrect")]
    #[Groups(["user:read", "user:write"])]
    private ?string $plainPassword = null;

    #[Upload(path: "avatar", maxWidth: 200, maxHeight: 200)]
    #[Assert\Image(mimeTypes: ["image/png", "image/jpeg", "image/webp"])]
    #[Groups(["user:write"])]
    private ?File $file = null;

    #[ORM\Column(type: "string", nullable: true, unique: true)]
    #[Groups(["user:read"])]
    private ?string $avatar = null;

    #[ORM\Column(type: "boolean")]
    private bool $enabled;

    #[ORM\Column(type: "array")]
    private array $roles;

    #[ORM\Column(type: "string", length: 3)]
    #[Assert\Currency]
    #[Groups(["user:read", "user:write"])]
    private string $currency;

    #[ORM\Column(type: "string", length: 5)]
    #[Groups(["user:read", "user:write"])]
    #[Assert\Choice(choices: LocaleEnum::LOCALES)]
    private string $locale;

    #[ORM\Column(type: "string", length: 50)]
    #[Groups(["user:read", "user:write"])]
    #[Assert\Timezone]
    private ?string $timezone = null;

    #[ORM\Column(type: "string", length: 10)]
    #[Groups(["user:read", "user:write"])]
    #[Assert\Choice(choices: DateFormatEnum::FORMATS)]
    private string $dateFormat;

    #[ORM\Column(type: "bigint", options: ["default" => 268435456])]
    #[Groups(["user:read"])]
    private int $diskSpaceAllowed;

    #[ORM\Column(type: "string", length: 10)]
    #[Groups(["user:read", "user:write"])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility;

    #[ORM\OneToMany(targetEntity: "Collection", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $collections;

    #[ORM\OneToMany(targetEntity: "Tag", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $tags;

    #[ORM\OneToMany(targetEntity: "TagCategory", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $tagCategories;

    #[ORM\OneToMany(targetEntity: "Wishlist", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $wishlists;

    #[ORM\OneToMany(targetEntity: "Template", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $templates;

    #[ORM\OneToMany(targetEntity: "Log", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $logs;

    #[ORM\OneToMany(targetEntity: "Album", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $albums;

    #[ORM\OneToMany(targetEntity: "Inventory", mappedBy: "owner", cascade: ["remove"])]
    private DoctrineCollection $inventories;

    #[ORM\Column(type: "date", nullable: true)]
    #[Groups(["user:read"])]
    private ?DateTimeInterface $lastDateOfActivity = null;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    #[Groups(["user:read", "user:write"])]
    private bool $darkModeEnabled;

    #[ORM\Column(type: "time", nullable: true)]
    #[Groups(["user:read", "user:write"])]
    private ?\DateTime $automaticDarkModeStartAt;

    #[ORM\Column(type: "time", nullable: true)]
    #[Groups(["user:read", "user:write"])]
    private ?\DateTime $automaticDarkModeEndAt;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $wishlistsFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $tagsFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $signsFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $albumsFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $loansFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $templatesFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $historyFeatureEnabled;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    #[Groups(["user:read", "user:write"])]
    private bool $statisticsFeatureEnabled;

    #[ORM\Column(type: "datetime")]
    #[Groups(["user:read"])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Groups(["user:read"])]
    private ?DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->roles = ['ROLE_USER'];
        $this->diskSpaceAllowed = 536870912;
        $this->enabled = true;
        $this->currency = 'EUR';
        $this->locale = LocaleEnum::LOCALE_EN_GB;
        $this->visibility = VisibilityEnum::VISIBILITY_PRIVATE;
        $this->dateFormat = DateFormatEnum::FORMAT_HYPHEN_YMD;
        $this->darkModeEnabled = false;
        $this->wishlistsFeatureEnabled = true;
        $this->tagsFeatureEnabled = true;
        $this->signsFeatureEnabled = true;
        $this->albumsFeatureEnabled = true;
        $this->loansFeatureEnabled = true;
        $this->templatesFeatureEnabled = true;
        $this->historyFeatureEnabled = true;
        $this->statisticsFeatureEnabled = true;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getUsername();
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    public function isAdmin(): bool
    {
        return \in_array(RoleEnum::ROLE_ADMIN, $this->roles, true);
    }

    public function isInDarkMode() : bool
    {
        if ($this->isDarkModeEnabled()) {
            return true;
        }

        if ($this->getAutomaticDarkModeStartAt() && $this->getAutomaticDarkModeEndAt()) {
            // Apply timezone to get current time for the user
            $timezone = new \DateTimeZone('Europe/Paris');
            $currentTime = strtotime((new \DateTime())->setTimezone($timezone)->format('H:i'));
            $startTime = strtotime($this->getAutomaticDarkModeStartAt()->format('H:i'));
            $endTime = strtotime($this->getAutomaticDarkModeEndAt()->format('H:i'));

            if (
                (
                    $startTime < $endTime &&
                    $currentTime >= $startTime &&
                    $currentTime <= $endTime
                ) ||
                (
                    $startTime > $endTime && (
                        $currentTime >= $startTime ||
                        $currentTime <= $endTime
                    )
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function getDateFormatForJs() : string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_JS];
    }

    public function getDateFormatForForm() : string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_FORM];
    }

    public function getOwner(): ?self
    {
        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }

    public function getSalt() : ?string
    {
        return null;
    }

    public function setSalt(?string $salt) : self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword(string $password) : self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword() : ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword) : self
    {
        $this->plainPassword = $plainPassword;
        $this->password = $plainPassword;

        return $this;
    }

    public function getRoles() : array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles) : self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role) : self
    {
        $role = strtoupper($role);
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role) : self
    {
        if (false !== $key = \array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = \array_values($this->roles);
        }

        return $this;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getDiskSpaceAllowed(): ?int
    {
        return $this->diskSpaceAllowed;
    }

    public function setDiskSpaceAllowed(int $diskSpaceAllowed): self
    {
        $this->diskSpaceAllowed = $diskSpaceAllowed;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getLastDateOfActivity(): ?DateTimeInterface
    {
        return $this->lastDateOfActivity;
    }

    public function setLastDateOfActivity(?DateTimeInterface $lastDateOfActivity): self
    {
        $this->lastDateOfActivity = $lastDateOfActivity;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;
        //Force Doctrine to trigger an update
        if ($file instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function isWishlistsFeatureEnabled(): bool
    {
        return $this->wishlistsFeatureEnabled;
    }

    public function setWishlistsFeatureEnabled(bool $wishlistsFeatureEnabled): User
    {
        $this->wishlistsFeatureEnabled = $wishlistsFeatureEnabled;

        return $this;
    }

    public function isTagsFeatureEnabled(): bool
    {
        return $this->tagsFeatureEnabled;
    }

    public function setTagsFeatureEnabled(bool $tagsFeatureEnabled): User
    {
        $this->tagsFeatureEnabled = $tagsFeatureEnabled;

        return $this;
    }

    public function isSignsFeatureEnabled(): bool
    {
        return $this->signsFeatureEnabled;
    }

    public function setSignsFeatureEnabled(bool $signsFeatureEnabled): User
    {
        $this->signsFeatureEnabled = $signsFeatureEnabled;

        return $this;
    }

    public function isAlbumsFeatureEnabled(): bool
    {
        return $this->albumsFeatureEnabled;
    }

    public function setAlbumsFeatureEnabled(bool $albumsFeatureEnabled): User
    {
        $this->albumsFeatureEnabled = $albumsFeatureEnabled;

        return $this;
    }

    public function isLoansFeatureEnabled(): bool
    {
        return $this->loansFeatureEnabled;
    }

    public function setLoansFeatureEnabled(bool $loansFeatureEnabled): User
    {
        $this->loansFeatureEnabled = $loansFeatureEnabled;

        return $this;
    }

    public function isTemplatesFeatureEnabled(): bool
    {
        return $this->templatesFeatureEnabled;
    }

    public function setTemplatesFeatureEnabled(bool $templatesFeatureEnabled): User
    {
        $this->templatesFeatureEnabled = $templatesFeatureEnabled;

        return $this;
    }

    public function isHistoryFeatureEnabled(): bool
    {
        return $this->historyFeatureEnabled;
    }

    public function setHistoryFeatureEnabled(bool $historyFeatureEnabled): User
    {
        $this->historyFeatureEnabled = $historyFeatureEnabled;

        return $this;
    }

    public function isStatisticsFeatureEnabled(): bool
    {
        return $this->statisticsFeatureEnabled;
    }

    public function setStatisticsFeatureEnabled(bool $statisticsFeatureEnabled): User
    {
        $this->statisticsFeatureEnabled = $statisticsFeatureEnabled;

        return $this;
    }

    public function isDarkModeEnabled(): bool
    {
        return $this->darkModeEnabled;
    }

    public function setDarkModeEnabled(bool $darkModeEnabled): User
    {
        $this->darkModeEnabled = $darkModeEnabled;

        return $this;
    }

    public function getAutomaticDarkModeStartAt(): ?\DateTime
    {
        return $this->automaticDarkModeStartAt;
    }

    public function setAutomaticDarkModeStartAt(?\DateTime $automaticDarkModeStartAt): User
    {
        $this->automaticDarkModeStartAt = $automaticDarkModeStartAt;

        return $this;
    }

    public function getAutomaticDarkModeEndAt(): ?\DateTime
    {
        return $this->automaticDarkModeEndAt;
    }

    public function setAutomaticDarkModeEndAt(?\DateTime $automaticDarkModeEndAt): User
    {
        $this->automaticDarkModeEndAt = $automaticDarkModeEndAt;

        return $this;
    }
}
